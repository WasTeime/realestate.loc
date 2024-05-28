<?php

namespace common\models;

use common\models\AppActiveRecord;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%docs}}".
 *
 * @property int    $id
 * @property string $key
 * @property string $file
 * @property int    $created_at Дата создания
 * @property int    $updated_at Дата изменения
 */
#[Schema(properties: [
    new Property(property: 'id', type: 'integer'),
    new Property(property: 'key', type: 'string'),
    new Property(property: 'file', type: 'string'),
    new Property(property: 'created_at', type: 'integer'),
    new Property(property: 'updated_at', type: 'integer'),
])]
class Docs extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%docs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['key', 'file'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['key', 'file'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'key' => Yii::t('app', 'Key'),
            'file' => Yii::t('app', 'File'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'key',
            'file',
            'created_at',
            'updated_at',
        ];
    }
}
