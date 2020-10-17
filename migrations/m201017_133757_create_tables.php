<?php

use yii\db\Migration;

/**
 * Class m201017_133757_create_tables
 */
class m201017_133757_create_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'login' => $this->string()->notNull(),
            'password' => $this->string(),
            'points' => $this->integer()->unsigned()->defaultValue(0),
            'authKey' => $this->string()
        ]);

        $this->createTable('prizes', [
            'id' => $this->primaryKey(),
            'type' => "ENUM('money', 'item')",
            'value' => $this->string(),
            'user_id' => $this->integer(),
            'sent_datetime' => $this->dateTime()
        ]);
        $this->addForeignKey(
            'fk1',
            'prizes',
            'user_id',
            'users',
            'id'
        );

        $this->createTable('limits', [
            'id' => $this->primaryKey(),
            'for_type' => $this->string(),
            'value' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201017_133757_create_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201017_133757_create_tables cannot be reverted.\n";

        return false;
    }
    */
}
