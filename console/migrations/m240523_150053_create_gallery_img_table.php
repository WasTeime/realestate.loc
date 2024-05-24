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
            'gallery_id' => $this->integer(),
            'img' => $this->string()->notNull(),
            'name' => $this->string(),
            'text' => $this->string(),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата изменения'),
        ]);

        $this->addForeignKey(
            'fk-gallery_img-gallery_id',
            'gallery_img',
            'gallery_id',
            'gallery',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropForeignKey('fk-gallery_img-gallery_id', 'gallery_img');

        $this->dropTable('{{%gallery_img}}');
    }
}
