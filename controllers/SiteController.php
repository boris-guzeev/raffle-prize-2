<?php

namespace app\controllers;

use app\models\Limit;
use app\models\Prize;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Game;

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
     * @return array информация об итоге
     * @throws \yii\db\Exception
     */
    public function actionPlay()
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

        $result = (new Game())->play($prizeType); // запустим розыгрыш

        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        return [
            $prizeType,
            $result
        ];
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
