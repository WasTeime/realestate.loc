<?php

namespace admin\controllers;

use admin\controllers\AdminController;
use common\components\helpers\UserUrl;
use common\models\Flat;
use common\models\FlatSearch;
use common\models\Room;
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
 * FlatController implements the CRUD actions for Flat model.
 *
 * @package admin\controllers
 */
final class FlatController extends AdminController
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
     * Lists all Flat models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $model = new Flat();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
        }

        $searchModel = new FlatSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'index',
            ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'model' => $model]
        );
    }

    /**
     * Displays a single Flat model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * @param Room[] $subModels
     */
    private function _setParentId(
        array $subModels,
        Flat $flat,
        Transaction $transaction,
        bool &$flag
    ): void {
        foreach ($subModels as $subModel) {
            $subModel->flat_id = $flat->id;
            if (!($flag = $subModel->save(false))) {
                $transaction->rollBack();
                break;
            }
        }
    }

    /**
     * Creates a new Flat model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param string|null $redirect если нужен иной редирект после успешного создания
     *
     * @throws InvalidConfigException
     */
    public function actionCreate(string $redirect = null): Response|string
    {
        $model = new Flat();
        $rooms = [new Room()];

        if ($model->load(Yii::$app->request->post())) {
            $rooms = Room::createMultiple();
            Room::loadMultiple($rooms, Yii::$app->request->post());

            $valid = $model->validate() && Room::validateMultiple($rooms);

            if ($valid && $transaction = Yii::$app->db->beginTransaction()) {
                try {
                    if ($flag = $model->save(false)) {
                        $this->_setParentId($rooms, $model, $transaction, $flag);
                    }
                    if ($flag) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
                        return match ($redirect) {
                            'create' => $this->redirect(['create']),
                            'index' => $this->redirect(UserUrl::setFilters(FlatSearch::class)),
                            default => $this->redirect(['view', 'id' => $model->id])
                        };
                    }
                } catch (Exception $exception) {
                    Yii::$app->session->addFlash('error', $exception->getMessage());
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('create', ['model' => $model, 'rooms' => $rooms]);
    }

    /**
     * Updates an existing Flat model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        $rooms = $model->rooms;


        if ($model->load(Yii::$app->request->post())) {
            $oldRoomsIDs = ArrayHelper::map($rooms, 'id', 'id');
            $rooms = Room::createMultiple($rooms);
            Room::loadMultiple($rooms, Yii::$app->request->post());
            $deletedAnswerIDs = array_diff($oldRoomsIDs, array_filter(ArrayHelper::map($rooms, 'id', 'id')));

            $valid = $model->validate() && Room::validateMultiple($rooms);
            if ($valid && $transaction = Yii::$app->db->beginTransaction()) {
                try {
                    if ($flag = $model->save(false)) {
                        if (!empty($deletedAnswerIDs)) {
                            Room::deleteAll(['id' => $deletedAnswerIDs]);
                        }
                        $this->_setParentId($rooms, $model, $transaction, $flag);
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

        return $this->render('update', ['model' => $model, 'rooms' => $rooms]);
    }

    /**
     * Deletes an existing Flat model.
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
        return $this->redirect(UserUrl::setFilters(FlatSearch::class));
    }

    /**
     * Finds the Flat model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): Flat
    {
        if (($model = Flat::findOne($id)) !== null) {
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
                'modelClass' => Flat::class
            ]
        ];
    }
}
