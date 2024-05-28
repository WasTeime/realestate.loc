<?php

namespace common\models;

use common\models\AppActiveRecord;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%room}}".
 *
 * @property int              $id
 * @property string           $name
 * @property float            $square
 * @property string|null      $uid
 * @property int              $flat_id
 * @property int              $created_at Дата создания
 * @property int              $updated_at Дата изменения
 *
 * @property-read Flat        $flat
 */
#[Schema(properties: [
    new Property(property: 'id', type: 'integer'),
    new Property(property: 'name', type: 'string'),
    new Property(property: 'square', type: 'float'),
    new Property(property: 'uid', type: 'string'),
    new Property(property: 'created_at', type: 'integer'),
    new Property(property: 'updated_at', type: 'integer'),
])]
class Room extends AppActiveRecord
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
        return '{{%room}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'square'], 'required'],
            [['square'], 'number'],
            [['flat_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'uid'], 'string', 'max' => 255],
            [['flat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flat::class, 'targetAttribute' => ['flat_id' => 'id']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'square' => Yii::t('app', 'Square'),
            'uid' => Yii::t('app', 'Uid'),
            'flat_id' => Yii::t('app', 'Flat ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'square',
            'uid',
            'created_at',
            'updated_at',
        ];
    }

    final public function getFlat(): ActiveQuery
    {
        return $this->hasOne(Flat::class, ['id' => 'flat_id']);
    }
}
