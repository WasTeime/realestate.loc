<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%promocode}}`.
 */
class m240528_134914_create_promocode_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%promocode}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'promo' => $this->string(12)->notNull(),
        ]);

        $this->createIndex(
            'idx-promo-user_id',
            'promocode',
            'user_id'
        );

        $this->addForeignKey(
            'fk-post-author_id',
            'promocode',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropIndex(
            'idx-promo-user_id',
            'promocode'
        );

        $this->dropForeignKey(
            'fk-post-author_id',
            'promocode'
        );

        $this->dropTable('{{%promocode}}');
    }
}
