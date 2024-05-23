<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%room}}`.
 */
class m240523_145918_create_room_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%room}}', [
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
        $this->dropTable('{{%room}}');
    }
}
