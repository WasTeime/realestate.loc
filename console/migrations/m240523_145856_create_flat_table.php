<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%flat}}`.
 */
class m240523_145856_create_flat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%flat}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'subtitle' => $this->string(),
            'description' => $this->text(),
            'cost' => $this->float()->notNull(),
            'floor' => $this->integer()->notNull(),
            'flat_img' => $this->string(),
            'address' => $this->string(),
            'additional_name' => $this->string(),
            'additional_img' => $this->string(),
            'access_api' => $this->boolean()->notNull(),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата изменения'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%flat}}');
    }
}
