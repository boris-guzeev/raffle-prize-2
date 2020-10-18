<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.10.2020
 * Time: 17:20
 */

namespace app\models;


class Limit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'limits';
    }

}