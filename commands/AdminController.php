<?php

namespace app\commands;

use app\models\BankApi;
use app\models\Converter;
use app\models\Prize;
use yii\console\Controller;
use yii\console\ExitCode;
use \Yii;
use yii\db\Exception;
use yii\db\Query;

class AdminController extends Controller
{
    /**
     * Отправка денежных средств со счёта пользователя
     * @param integer $userId ИД пользователя, чьи платежи нужно отправить в банк
     * @param integer $quantity Количество платежей, которое нужно отправить
     * @return int Exit code
     * @throws Exception
     */
    public function actionSend($userId, $quantity)
    {
        // найдем последние неотправленные денежные призы в нужном количестве
        $unsendedMoney = Prize::find()
            ->select(['id', 'value'])
            ->where(['user_id' => $userId, 'sent_datetime' => null])
            ->limit($quantity)
            ->orderBy('id DESC')
            ->all();

        // посчитаем их сумму и отправим на счёт
        $sum = 0;
        foreach ($unsendedMoney as $item) {
            $sum += $item->value;
        }
        $api = new BankApi('myCompany', '1234');
        $userAccount = $api->getClientRequisites($userId);

        // при успешном ответе от банка, что средства поступили - отмечаем призы как отправленные
        $transaction = Yii::$app->db->beginTransaction();
        if ($api->send($userAccount, $sum)) {
            foreach ($unsendedMoney as $item) {
                $item->sent_datetime = date('Y-m-d H:i:s');
                $item->save();
            }
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
        return ExitCode::OK;
    }
}