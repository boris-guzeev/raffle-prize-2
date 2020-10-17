<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.10.2020
 * Time: 21:03
 */

namespace app\models;


class Prize extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'prizes';
    }

}