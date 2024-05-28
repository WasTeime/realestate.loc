<?php

namespace common\models;

use common\models\AppActiveRecord;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%text}}".
 *
 * @property int         $id
 * @property string      $key
 * @property string|null $group
 * @property string      $text
 * @property string|null $comment
 * @property int|null    $deletable
 * @property int         $created_at Дата создания
 * @property int         $updated_at Дата изменения
 */
#[Schema(properties: [
    new Property(property: 'id', type: 'integer'),
    new Property(property: 'key', type: 'string'),
    new Property(property: 'group', type: 'string'),
    new Property(property: 'text', type: 'string'),
    new Property(property: 'comment', type: 'string'),
    new Property(property: 'deletable', type: 'integer'),
    new Property(property: 'created_at', type: 'integer'),
    new Property(property: 'updated_at', type: 'integer'),
])]
class Text extends AppActiveRecord
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
        return '{{%text}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['key', 'text'], 'required'],
            [['text'], 'string'],
            [['deletable', 'created_at', 'updated_at'], 'integer'],
            [['key', 'group', 'comment'], 'string', 'max' => 255]
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
            'group' => Yii::t('app', 'Group'),
            'text' => Yii::t('app', 'Text'),
            'comment' => Yii::t('app', 'Comment'),
            'deletable' => Yii::t('app', 'Deletable'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'key',
            'group',
            'text',
            'comment',
            'deletable',
            'created_at',
            'updated_at',
        ];
    }
}
