<?php

namespace admin\controllers;

use admin\controllers\AdminController;
use common\components\helpers\UserUrl;
use common\models\Gallery;
use common\models\GalleryImg;
use common\models\GallerySearch;
use Exception;
use kartik\grid\EditableColumnAction;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\db\Transaction;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * GalleryController implements the CRUD actions for Gallery model.
 *
 * @package admin\controllers
 */
final class GalleryController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => ['delete' => ['POST']]
                ]
            ]
        );
    }

    /**
     * Lists all Gallery models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string|Response
    {
        $model = new Gallery();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
        }

        $searchModel = new GallerySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'index',
            ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'model' => $model]
        );
    }

    /**
     * Displays a single Gallery model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * @param GalleryImg[] $subModels
     */
    private function _setParentId(
        array $subModels,
        Gallery $gallery,
        Transaction $transaction,
        bool &$flag
    ): void {
        foreach ($subModels as $subModel) {
            $subModel->gallery_id = $gallery->id;
            if (!($flag = $subModel->save(false))) {
                $transaction->rollBack();
                break;
            }
        }
    }

    /**
     * Creates a new Gallery model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param string|null $redirect если нужен иной редирект после успешного создания
     *
     * @throws InvalidConfigException
     */
    public function actionCreate(string $redirect = null): Response|string
    {
        $model = new Gallery();
        $galleryImgs = [new GalleryImg()];

        if ($model->load(Yii::$app->request->post())) {
            $galleryImgs = GalleryImg::createMultiple();
            GalleryImg::loadMultiple($galleryImgs, Yii::$app->request->post());

            $valid = $model->validate() && GalleryImg::validateMultiple($galleryImgs);

            if ($valid && $transaction = Yii::$app->db->beginTransaction()) {
                try {
                    if ($flag = $model->save(false)) {
                        $this->_setParentId($galleryImgs, $model, $transaction, $flag);
                    }
                    if ($flag) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
                        return match ($redirect) {
                            'create' => $this->redirect(['create']),
                            'index' => $this->redirect(UserUrl::setFilters(GallerySearch::class)),
                            default => $this->redirect(['view', 'id' => $model->id])
                        };
                    }
                } catch (Exception $exception) {
                    Yii::$app->session->addFlash('error', $exception->getMessage());
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('create', ['model' => $model, 'galleryImgs' => $galleryImgs]);
    }

    /**
     * Updates an existing Gallery model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        $galleryImgs = $model->images;

        if ($model->load(Yii::$app->request->post())) {
            $oldGalleryImgsIDs = ArrayHelper::map($galleryImgs, 'id', 'id');
            $galleryImgs = GalleryImg::createMultiple($galleryImgs);
            GalleryImg::loadMultiple($galleryImgs, Yii::$app->request->post());
            $deletedAnswerIDs = array_diff($oldGalleryImgsIDs, array_filter(ArrayHelper::map($galleryImgs, 'id', 'id')));

            $valid = $model->validate() && GalleryImg::validateMultiple($galleryImgs);
            if ($valid && $transaction = Yii::$app->db->beginTransaction()) {
                try {
                    if ($flag = $model->save(false)) {
                        if (!empty($deletedAnswerIDs)) {
                            GalleryImg::deleteAll(['id' => $deletedAnswerIDs]);
                        }
                        $this->_setParentId($galleryImgs, $model, $transaction, $flag);
                    }
                    if ($flag) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', "Элемент №$model->id изменен успешно");
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (Exception $exception) {
                    Yii::$app->session->addFlash('error', $exception->getMessage());
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('update', ['model' => $model, 'galleryImgs' => $galleryImgs]);
    }

    /**
     * Deletes an existing Gallery model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', "Элемент №$id удален успешно");
        return $this->redirect(UserUrl::setFilters(GallerySearch::class));
    }

    /**
     * Finds the Gallery model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): Gallery
    {
        if (($model = Gallery::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'change' => [
                'class' => EditableColumnAction::class,
                'modelClass' => Gallery::class
            ]
        ];
    }
}
