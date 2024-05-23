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
            'name' => $this->string()->notNull(),
            'square' => $this->float()->notNull(),
            'uid' => $this->string(),
            'flat_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата изменения'),
        ]);

        $this->addForeignKey(
            'fk-flat-room_id',
            'flat_id',
            'id',
            'flat',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropForeignKey(
            'fk-flat-room_id',
            'room'
        );

        $this->dropTable('{{%room}}');
    }
}
