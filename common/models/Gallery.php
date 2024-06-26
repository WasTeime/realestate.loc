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
use yii\helpers\Html;

/**
 * This is the model class for table "{{%gallery}}".
 *
 * @property int         $id
 * @property string|null $name
 * @property int         $created_at Дата создания
 * @property int         $updated_at Дата изменения
 *
 * @property-read GalleryImg[] $images
 * @property-read int $countImages
 * @property-read string $galleryImages
 */
#[Schema(properties: [
    new Property(property: 'id', type: 'integer'),
    new Property(property: 'name', type: 'string'),
    new Property(property: 'images', type: 'array', items: new Items(ref: '#/components/schemas/GalleryImg')),
])]
class Gallery extends AppActiveRecord
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
        return '{{%gallery}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255]
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
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),

            'countImages' => Yii::t('app', 'Count Images'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'created_at',
            'updated_at',
            'images',
        ];
    }

    final public function getImages(): ActiveQuery
    {
        return $this->hasMany(GalleryImg::class, ['gallery_id' => 'id']);
    }

    final public function getCountImages(): int
    {
        return count($this->images);
    }

    final public function getGalleryImages(): string
    {
        $galleryImages = '';
        foreach ($this->images as $key => $img) {
            $galleryImages .= Html::img($img->img, ['width' => 150,]);
        }

        return
        '<div class="grid">' .
        $galleryImages
        .'</div>';
    }
}
