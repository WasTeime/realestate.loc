<?php

namespace common\models;

use common\components\helpers\UserFileHelper;
use Yii;

/**
 * This is the model class for table "{{%export_films_list}}".
 *
 * @property int         $id
 * @property string      $filename
 * @property int         $date
 * @property int         $count
 * @property-read string $downloadLink
 * @property-read int    $filesize
 * @property-read string $downloadLabel
 */
class ExportList extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%export_list}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['filename', 'date'], 'required'],
            [['date', 'count'], 'integer'],
            ['filename', 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'filename' => Yii::t('app', 'Filename'),
            'date' => Yii::t('app', 'Date'),
            'count' => Yii::t('app', 'Count'),
            'downloadLink' => Yii::t('app', 'Download Link')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete(): void
    {
        $filename = Yii::getAlias('@root/admin/runtime/export/') . $this->filename;
        if (file_exists($filename)) {
            unlink($filename);
        }
        parent::afterDelete();
    }

    /**
     * {@inheritdoc}
     */
    public static function ruleBasedCustomAttributes(): array
    {
        return ['downloadLink'];
    }

    /**
     * Ссылка на скачивание
     */
    public function getDownloadLink(): string
    {
        return '/admin/export/download/' . $this->filename;
    }

    public function getFilesize(): int
    {
        $filename = Yii::getAlias('@root/admin/runtime/export/') . $this->filename;
        if (file_exists($filename)) {
            return filesize($filename);
        }
        return 0;
    }

    public function getDownloadLabel(): string
    {
        return $this->filename . ' ' . UserFileHelper::bytesToString($this->filesize);
    }
}
