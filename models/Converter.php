<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.10.2020
 * Time: 20:34
 */

namespace app\models;


class Converter
{
    public function toPoints($money, $ratio)
    {
        if (is_numeric($money) && $money > 0) {
            return intval(round($money * $ratio));
        } else {
            throw new \Exception('$money - должно быть положительным числом больше 0');
        }
    }

    public function toMoney($itemName)
    {
        return \Yii::$app->params['rules']['items'][$itemName];
    }
}