<?php
namespace app\models;

use \Yii;

use yii\db\Query;

/**
 * Class Game
 * @package app\models
 */
class Game
{
    public function play()
    {
        // для розыгрыша выбираем те типы призов, чей лимит еще не исчерпан, либо которые не имеют лимита
        $types = Limit::find()
            ->select('for_type')
            ->where(['>', 'value', 0])
            ->column();
        $types[] = 'points'; // добавим баллы, т.к. баллы участвуют всегда

        // случайным образом выберем тип разыгрываемого приза
        $rand = rand(0, count($types) - 1);
        $prizeType = $types[$rand];

        $user = User::findOne(['id' => Yii::$app->user->identity->id]);
        if ($prizeType == 'points') {
            $result = rand(Yii::$app->params['rules']['minPoints'], Yii::$app->params['rules']['maxPoints']);
            $user->points += $result;
            if ($user->save()) {
                return $result;
            }
        } else {
            // при денежном призе разыгрываем случайную сумму в пределах заданных правил и вычитаем её из общего счёта
            if ($prizeType == 'money') {
                $maxMoney = Yii::$app->params['rules']['maxMoney'];
                $total = Limit::find()
                    ->select('value')
                    ->where(['for_type' => 'money'])
                    ->scalar();
                $maxMoney = $total < $maxMoney ? $total : $maxMoney;
                $value = rand(Yii::$app->params['rules']['minMoney'], $maxMoney);
                $subtract = $value;
            } elseif ($prizeType == 'items') {
                // если приз предмет, то разыгрываем случайный предмет из списка
                // и уменьшаем на 1 общее количество разыгрываемых предметов
                $items = array_keys(Yii::$app->params['rules']['items']);
                $value = $items[rand(0, count($items) - 1)];
                $subtract = 1;
            } else {
                // считаем исключительной ситуацию если появился новый тип, но не описана логика его обработки.
                throw new \Exception('Такого типа подарка не существует. Необходимо написать его логику обработки!');
            }
            // обернём в транзакцию, чтобы сохранить целостность запроса
            $transaction = Yii::$app->db->beginTransaction();
            try {
                Yii::$app->db->createCommand()->insert(
                    'prizes',
                    [
                        'type' => $prizeType,
                        'user_id' => Yii::$app->user->identity->id,
                        'value' => $value
                    ])
                    ->execute();

                $limit = (new Query())
                    ->select('value')
                    ->from('limits')
                    ->where(['for_type' => $prizeType])
                    ->scalar();

                Yii::$app->db->createCommand()
                    ->update('limits', ['value' => $limit - $subtract], ['for_type' => $prizeType])
                    ->execute();
                $transaction->commit();

                return $value;
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        exit;
    }
}
