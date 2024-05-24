<?php

namespace common\models;

use common\models\AppActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%gallery_img}}".
 *
 * @property int              $id
 * @property int|null         $gallery_id
 * @property string           $img
 * @property string|null      $name
 * @property string|null      $text
 * @property int              $created_at Дата создания
 * @property int              $updated_at Дата изменения
 *
 * @property-read Gallery     $gallery
 */
class GalleryImg extends AppActiveRecord
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
        return '{{%gallery_img}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['gallery_id', 'created_at', 'updated_at'], 'integer'],
            [['img'], 'required'],
            [['img', 'name', 'text'], 'string', 'max' => 255],
            [['gallery_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gallery::class, 'targetAttribute' => ['gallery_id' => 'id']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'gallery_id' => Yii::t('app', 'Gallery ID'),
            'img' => Yii::t('app', 'Img'),
            'name' => Yii::t('app', 'Name'),
            'text' => Yii::t('app', 'Text'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    final public function getGallery(): ActiveQuery
    {
        return $this->hasOne(Gallery::class, ['id' => 'gallery_id']);
    }
}
