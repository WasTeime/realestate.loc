<?php

use common\enums\Boolean;
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
        $this->dropTable('{{%text}}');

        $this->createTable('{{%text}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'group' => $this->string(),
            'text' => $this->text()->notNull(),
            'comment' => $this->string(),
            'deletable' => $this->boolean()->defaultValue(Boolean::No->value),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата изменения'),
        ]);

        // Default Data
        $this->batchInsert('{{%text}}', ['key', 'group', 'text'], [
            ['main_address', 'contacts', 'Основной адрес'],
            ['main_phone', 'contacts', 'Основной телефон'],
            ['sales_office_address', 'contacts', 'Офис продаж. Адрес'],
            ['sales_office_phone', 'contacts', 'Офис продаж. Телефон'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%text}}');

        $this->createTable('{{%text}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'key' => $this->string()->notNull()->comment('Ключ текстового поля'),
            'value' => $this->text()->notNull()->comment('Значение текстового поля'),
        ]);
    }
}
