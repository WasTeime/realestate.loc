<?php

namespace common\modules\mail\controllers;

use admin\controllers\AdminController;
use common\components\helpers\UserUrl;
use common\modules\mail\enums\MailingType;
use common\modules\mail\models\{Mailing, MailingSearch, TestMailing};
use Exception;
use kartik\grid\EditableColumnAction;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\{NotFoundHttpException, Response};

/**
 * MailingController implements the CRUD actions for Mailing model.
 *
 * @package mail\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class MailingController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']]
            ]
        ]);
    }

    /**
     * Lists all Mailing models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $searchModel = new MailingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * Displays a single Mailing model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Creates a new Mailing model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @throws InvalidConfigException
     */
    public function actionCreate(): Response|string
    {
        $model = new Mailing();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Рассылка №$model->id создана успешно");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing Mailing model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Изменения в рассылке №$id успешно сохранены");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing Mailing model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws Throwable
     * @throws StaleObjectException
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', "Рассылка №$id удалена успешно");
        return $this->redirect(UserUrl::setFilters(MailingSearch::class));
    }

    /**
     * Раздел тестирования отправок
     *
     * @throws InvalidConfigException
     */
    public function actionTest(): string
    {
        $model = new TestMailing();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $model->send();
                Yii::$app->session->setFlash('success', 'Рассылка успешна');
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        return $this->render('_testing_form', ['model' => $model]);
    }

    /**
     * Finds the Mailing model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): Mailing
    {
        if (($model = Mailing::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'change' => [
                'class' => EditableColumnAction::class,
                'modelClass' => Mailing::class,
                'outputValue' => static function (Mailing $model, string $attribute) {
                    return match ($attribute) {
                        'mailing_type' => MailingType::from($model->$attribute)->coloredDescription(),
                        'mail_template_id' => $model->mailTemplate->name,
                        default => $model->$attribute
                    };
                }
            ]
        ];
    }
}
