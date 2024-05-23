<?php

namespace admin\controllers;

use common\enums\ParamType;
use common\models\Param;
use kartik\grid\EditableColumnAction;
use Throwable;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\{ArrayHelper, StringHelper};
use yii\web\{NotFoundHttpException, Response};

/**
 * ParamsController implements the CRUD actions for Params model.
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ParamController extends AdminController
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
     * Lists all Params models.
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider(['query' => Param::find()]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Deletes an existing Params model.
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
        if ($model->deletable) {
            $model->delete();
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Params model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): Param
    {
        if (($model = Param::findOne($id)) !== null) {
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
                'modelClass' => Param::class,
                'outputValue' => static function (Param $model, string $attribute) {
                    if ($attribute === 'value') {
                        if ($model->type === ParamType::Text->value) {
                            return StringHelper::truncate($model->columnValue, 62);
                        }
                        return $model->columnValue;
                    }
                    return $model->$attribute;
                }
            ]
        ];
    }
}
