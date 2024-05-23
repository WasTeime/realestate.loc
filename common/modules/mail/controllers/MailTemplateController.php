<?php

namespace common\modules\mail\controllers;

use admin\controllers\AdminController;
use common\components\{helpers\UserUrl};
use common\modules\mail\models\{MailTemplate, MailTemplateSearch, Template};
use Exception;
use Pug\Yii\ViewRenderer;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Application;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\{ArrayHelper, Json};
use yii\web\{NotFoundHttpException, Response};

/**
 * DefaultController implements the CRUD actions for MailTemplate model.
 *
 * @package mail\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class MailTemplateController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST'], 'render-pug' => ['POST']]
            ]
        ]);
    }

    /**
     * Lists all MailTemplate models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $searchModel = new MailTemplateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * Displays a single MailTemplate model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    /**
     * Finds the MailTemplate model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): MailTemplate
    {
        if (($model = MailTemplate::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Creates a new MailTemplate model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @throws InvalidConfigException
     */
    public function actionCreate(): Response|string
    {
        $model = new MailTemplate();
        $model->name = 'new-name';
        $i = 0;
        while (MailTemplate::find()->where(['name' => $model->name])->exists()) {
            $i++;
            $model->name = "new-name-$i";
        }
        $template = Template::findFiles($model->name);

        if ($model->load(Yii::$app->request->post()) && $template->load(Yii::$app->request->post()) && $model->save()) {
            $template->saveFiles($model->name);
            Yii::$app->session->setFlash('success', "Шаблон №$model->id создан успешно");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model, 'template' => $template]);
    }

    /**
     * Updates an existing MailTemplate model.
     *
     * If the update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        $oldName = $model->name;
        $template = Template::findFiles($model->name);
        if ($model->load(Yii::$app->request->post()) && $template->load(Yii::$app->request->post()) && $model->save()) {
            $template->renameFiles($oldName, $model->name);
            Yii::$app->session->setFlash('success', "Изменения в шаблоне №$model->id сохранены успешно");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model, 'template' => $template]);
    }

    /**
     * Deletes an existing MailTemplate model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws Throwable
     * @throws StaleObjectException
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        Template::deleteFiles($model->name);
        $model->delete();
        Yii::$app->session->setFlash('success', "Шаблон №$model->id удален успешно");
        return $this->redirect(UserUrl::setFilters(MailTemplateSearch::class));
    }

    /**
     * @throws Exception
     */
    public function actionRenderPug(): string
    {
        if (Yii::$app->request->contentType === 'application/json') {
            $request = Json::decode(Yii::$app->request->rawBody);
            $layout = $request['layout'] ?? '';
            $layoutStyle = $request['layoutStyle'] ?? '';
            $content = $request['content'] ?? '';
            $style = $request['style'] ?? '';
        } else {
            $layout = trim(Yii::$app->request->post('layout'), '"');
            $layoutStyle = trim(Yii::$app->request->post('layoutStyle'), '"');
            $content = trim(Yii::$app->request->post('content'), '"');
            $style = trim(Yii::$app->request->post('style'), '"');
        }
        $domain = Yii::$app->request->hostInfo;
        $renderer = new ViewRenderer();
        $commonConfig = ArrayHelper::merge(
            require Yii::getAlias('@common/config/main.php'),
            require Yii::getAlias('@common/config/main-local.php')
        );
        $consoleConfig = ArrayHelper::merge(
            require Yii::getAlias('@console/config/main.php'),
            require Yii::getAlias('@console/config/main-local.php')
        );
        $config = ArrayHelper::merge($commonConfig, $consoleConfig);
        $origApp = Yii::$app;
        $app = new Application($config);
        $app->view->registerCss('body { margin: 0 }');
        $app->view->registerCss($layoutStyle);
        $app->view->registerCss($style);
        $username = '';
        if ($user = MailTemplate::getDummyUser()) {
            $username = $user->userExt->first_name . ' ' . $user->userExt->last_name;
            if ($username === ' ') {
                $username = $user->username;
            }
        }
        $variables = [
            'app' => $app,
            'view' => $app->view,
            'domain' => $domain,
            'content' => $renderer->pug->renderString($content, ['domain' => $domain, 'username' => $username]),
        ];
        $result = $renderer->pug->renderString($layout, $variables);
        Yii::$app = $origApp;
        return $result;
    }
}
