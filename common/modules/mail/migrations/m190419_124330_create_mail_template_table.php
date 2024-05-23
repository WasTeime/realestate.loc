<?php

namespace common\modules\mail\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%mail_template}}`.
 */
class m190419_124330_create_mail_template_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%mail_template}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'name' => $this->string(255)->notNull()->comment('Название'),
        ]);

        // Шаблоны по умолчанию
        $this->insert('{{%mail_template}}', [
            'name' => 'email-confirm',
        ]);

        $this->insert('{{%mail_template}}', [
            'name' => 'passwordResetToken',
        ]);

        $this->insert('{{%mail_template}}', [
            'name' => 'passwordSend',
        ]);

        $this->insert('{{%mail_template}}', [
            'name' => 'cheater-notify',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        $this->dropTable('{{%mail_template}}');
    }
}
