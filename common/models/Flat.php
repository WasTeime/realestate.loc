<?php

namespace common\models;

use common\models\AppActiveRecord;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%flat}}".
 *
 * @property int              $id
 * @property string           $title
 * @property string|null      $subtitle
 * @property string|null      $description
 * @property float            $cost
 * @property float            $floor
 * @property string|null      $flat_img
 * @property string|null      $address
 * @property string|null      $additional_name
 * @property string|null      $additional_img
 * @property int              $access_api
 * @property int              $created_at      Дата создания
 * @property int              $updated_at      Дата изменения
 *
 * @property-read Room[]      $rooms
 * @property-read int $roomsCount
 */
#[Schema(properties: [
    new Property(property: 'id', type: 'integer'),
    new Property(property: 'title', type: 'string'),
    new Property(property: 'subtitle', type: 'string'),
    new Property(property: 'description', type: 'string'),
    new Property(property: 'cost', type: 'float'),
    new Property(property: 'floor', type: 'integer'),
    new Property(property: 'flat_img', type: 'string'),
    new Property(property: 'address', type: 'string'),
    new Property(property: 'additional_name', type: 'string'),
    new Property(property: 'additional_img', type: 'string'),
    new Property(property: 'access_api', type: 'integer'),
    new Property(property: 'created_at', type: 'integer'),
    new Property(property: 'updated_at', type: 'integer'),
    new Property(property: 'rooms', type: 'array', items: new Items(ref: '#/components/schemas/Room')),
])]
class Flat extends AppActiveRecord
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
        return '{{%flat}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title', 'cost', 'floor', 'access_api'], 'required'],
            [['description'], 'string'],
            [['cost', 'floor'], 'number'],
            [['access_api', 'created_at', 'updated_at'], 'integer'],
            [['title', 'subtitle', 'flat_img', 'address', 'additional_name', 'additional_img'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'subtitle' => Yii::t('app', 'Subtitle'),
            'description' => Yii::t('app', 'Description'),
            'cost' => Yii::t('app', 'Cost'),
            'floor' => Yii::t('app', 'Floor'),
            'flat_img' => Yii::t('app', 'Flat Img'),
            'address' => Yii::t('app', 'Address'),
            'additional_name' => Yii::t('app', 'Additional Name'),
            'additional_img' => Yii::t('app', 'Additional Img'),
            'access_api' => Yii::t('app', 'Access Api'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'roomsCount' => Yii::t('app', 'Rooms Count')
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'subtitle',
            'description',
            'cost',
            'floor',
            'flat_img',
            'address',
            'additional_name',
            'additional_img',
            'access_api',
            'created_at',
            'updated_at',
            'rooms',
        ];
    }

    final public function getRooms(): ActiveQuery
    {
        return $this->hasMany(Room::class, ['flat_id' => 'id']);
    }

    final public function getRoomsCount()
    {
        return count($this->rooms);
    }
}
