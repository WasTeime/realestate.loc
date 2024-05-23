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
