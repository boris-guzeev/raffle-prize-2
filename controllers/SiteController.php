<?php

namespace app\controllers;

use app\models\Converter;
use app\models\Limit;
use app\models\Prize;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Game;
use yii\db\Exception;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect('site/login');
        }

        $itemPrizes = Prize::findAll(['type' => 'item', 'user_id' => Yii::$app->user->identity->id]);

        // отобразить в лейауте общую сумму текущего пользователя
        $sum = Prize::find()
            ->select('value')
            ->where(['user_id' => Yii::$app->user->identity->id])
            ->sum('value');
        $this->view->params['sum'] = $sum ? $sum : 0;

        // возмем данные по лимитам денег и предметов
        $limitsResult = Limit::find()
            ->select('value')
            ->where(['for_type' => 'money'])
            ->orWhere(['for_type' => 'items'])
            ->column();
        $moneyLimit = $limitsResult[0];
        $itemsLimit = $limitsResult[1];
        // если максимальный выигыш меньше, чем оставшийся баланс, то максимальный выигрыш делаем оставшимся балансом
        $moneyLimit = Yii::$app->params['rules']['maxMoney'] < $moneyLimit
            ? Yii::$app->params['rules']['maxMoney']
            : $moneyLimit;

        return $this->render('index', [
            'itemPrizes' => $itemPrizes,
            'moneyLimit' => $moneyLimit,
            'itemsLimit' => $itemsLimit
        ]);
    }

    /**
     * Запуск игры
     *
     * @return array информация об итоге
     * @throws \Exception
     */
    public function actionPlay()
    {
        $result = (new Game())->play(); // запустим розыгрыш

        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        return [
            $result
        ];
    }

    /**
     * Конвертация денег в баллы лояльности
     *
     * @param $prizeId <p>Ид денежного приза текущего пользователя</p>
     * @return integer <p>Число очков, которое получилось после конвертации</p>
     * @throws \Throwable
     */
    public function actionMoneyPoint($prizeId)
    {
        $points = 0;
        // найдем нужный денеждый приз
        $money = Prize::find()
            ->select('value')
            ->where(['type' => 'money', 'id' => $prizeId, 'user_id' => Yii::$app->user->identity->id])
            ->scalar();
        if ($money) {
            $converter = new Converter();
            $ratio = \Yii::$app->params['rules']['ratio'];
            $points = $converter->toPoints($money, $ratio);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $prize = Prize::findOne($prizeId);
                $prize->delete();
                $user = User::findOne(['id' => Yii::$app->user->identity->id]);
                $user->points += $points;
                $user->save();
                $transaction->commit();

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return $points;
    }

    /**
     * Конвертация предметов в деньги
     *
     * @param $prizeId <p>Ид приза-предмета текущего пользователя</p>
     * @return string <p>Стоимость после конвертации</p>
     */
    public function actionItemMoney($prizeId)
    {
        $item = Prize::find()
            ->where(['type' => 'items', 'id' => $prizeId, 'user_id' => Yii::$app->user->identity->id])
            ->one();
        $converter = new Converter();
        $price = $converter->toMoney($item->value);
        $item->type = 'money';
        $item->value = $price;
        $item->save();

        return $price;
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
