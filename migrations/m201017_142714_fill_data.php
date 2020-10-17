<?php

use yii\db\Migration;

/**
 * Class m201017_142714_fill_data
 */
class m201017_142714_fill_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('users',
            ['login', 'password'],
            [
                ['ivan', '1234'],
                ['alex', '1234'],
                ['petr', '1234'],
                ['serg', '1234'],
                ['artem', '1234']
            ]
        );

        $this->batchInsert('limits',
            [
                'for_type',
                'value'
            ],
            [
                [ 'money', Yii::$app->params['rules']['moneyLimit'] ],
                [ 'items', Yii::$app->params['rules']['itemsLimit'] ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201017_142714_fill_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201017_142714_fill_data cannot be reverted.\n";

        return false;
    }
    */
}
