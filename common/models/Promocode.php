<?php

namespace common\models;

use admin\components\parsers\ParserInterface;
use admin\components\uploadForm\models\UploadForm;
use admin\components\uploadForm\models\UploadInterface;
use common\models\AppActiveRecord;
use common\modules\user\models\User;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use OpenSpout\Common\Entity\Cell;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%promocode}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $promo
 *
 * @property-read User $user
 */
#[Schema(properties: [
    new Property(property: 'promo', type: 'string'),
])]
class Promocode extends AppActiveRecord implements UploadInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%promocode}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id'], 'integer'],
            [['promo'], 'required'],
            [['promo'], 'string', 'max' => 12],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'promo' => Yii::t('app', 'Promo'),
        ];
    }

    public function fields()
    {
        return [
          'promo'
        ];
    }

    final public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @throws Exception
     */
    public static function insertFromFile(UploadForm $model, ParserInterface $parser): void
    {
        $values = [];
        $parser->fileRowIterate(
            $model->file->tempName,
            /**
             * @param Cell[] $cells
             * @throws Exception
             */
            static function (array $cells, int $key) use (&$values) {
                if ($key === 1) {
                    return;
                }
                $values[] = [$cells[0]->getValue()];
                if (count($values) >= 100) {
                    Yii::$app->db->createCommand()
                        ->batchInsert(self::tableName(), ['promo'], $values)->execute();
                    $values = [];
                }
            }
        );
        if (!empty($values)) {
            Yii::$app->db->createCommand()
                ->batchInsert(self::tableName(), ['promo'], $values)->execute();
        }
    }
}
