<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%gallery_img}}`.
 */
class m240523_150053_create_gallery_img_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%gallery_img}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата изменения'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%gallery_img}}');
    }
}
