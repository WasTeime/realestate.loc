<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%text}}`.
 */
class m240523_145939_create_text_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%text}}', [
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
        $this->dropTable('{{%text}}');
    }
}
