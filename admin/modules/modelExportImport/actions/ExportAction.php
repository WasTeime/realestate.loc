<?php

namespace admin\modules\modelExportImport\actions;

use admin\modules\modelExportImport\behaviors\ExportImportBehavior;
use Exception;
use Yii;
use yii\base\{Action, InvalidConfigException};
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\{RangeNotSatisfiableHttpException, Response};

/**
 * Class ExportAction
 *
 * @package modelExportImport\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ExportAction extends Action
{
    /**
     * @var ActiveRecord|string
     */
    public ActiveRecord|string $modelClass;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!isset($this->modelClass)) {
            throw new InvalidConfigException('`className` must be defined');
        }
        parent::init();
    }

    /**
     * @throws RangeNotSatisfiableHttpException
     * @throws Exception
     */
    public function run(int|string $id): Response
    {
        $pkField = $this->modelClass::primaryKey()[0];
        /** @var ActiveRecord|ExportImportBehavior $model */
        $model = $this->modelClass::find()->where([$pkField => $id])->one();
        $data = Json::encode($model->export());
        return Yii::$app->response->sendContentAsFile(
            $data,
            basename(str_replace('\\', '/', $model::class)) . '-' . $model->$pkField . '-' . date('d-m-y') . '.json'
        );
    }
}
