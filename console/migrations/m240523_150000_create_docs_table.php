<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%docs}}`.
 */
class m240523_150000_create_docs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%docs}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'file' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата изменения'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%docs}}');
    }
}
