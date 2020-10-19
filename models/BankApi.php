<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.10.2020
 * Time: 23:29
 */

namespace app\models;


class BankApi
{
    private $authKey;
    /**
     * BankApi constructor.
     * @param $login
     * @param $pass
     */
    public function __construct($login, $pass)
    {
        //$url = 'https://myBank.com/api/auth/$login/$pass'; $curl = curl_init(); и тд..
        $this->authKey = 'sdfdsfsdfsdfsdf';
    }

    /**
     * Запрос данных о счете в банке пользователя
     *
     * @param $login
     * @return string
     */
    public function getClientRequisites($login)
    {
        return 'test';
    }

    /**
     * Отпавка средств в банк
     *
     * @param $requisites
     * @param $sum
     * @return bool
     */
    public function send($requisites, $sum)
    {
        //$url = 'https://myBank.com/api/'; $curl = curl_init(); и тд..
        // в случае успешного ответа возвращаем
        return true;
    }
}