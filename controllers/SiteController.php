<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\RegistrationForm;
use app\models\CurrencyHistory;
use app\models\My\MyHelper;

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
                'only' => ['login', 'logout', 'signup', 'currency', 'currency-history'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'signup'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout', 'currency', 'currency-history'],
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
        return $this->render('index');
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

    /**
     * Signs user up.
     *
     * @return string|Response
     * @throws Yii\base\Exception
     */
    public function actionSignup() {
        $model = new RegistrationForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Signs user up.
     *
     * @return string|Response
     * @throws Yii\base\Exception
     */
    public function actionCurrency() {
        $request = MyHelper::createRequest("https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5");

        $currency_array = [];
        foreach ($request as $item) {
            $currency_array[] = [
                'date' => date('d.m.Y', time()),
                'currency' => $item['ccy'],
                'sale' => $item['sale'],
                'buy' => $item['buy'],
            ];
        }
        return $this->render('currency', [
            'currency_array' => $currency_array,
        ]);
    }

    /**
     * Получаем историю по валютам
     *
     * @param $date_start
     * @param $date_end
     * @return string
     */
    public function actionCurrencyHistory($date_start, $date_end) {

        $models = CurrencyHistory::find()
            ->andWhere(['>=', 'date', strtotime($date_start)])
            ->andWhere(['<=', 'date', strtotime($date_end)])
            ->orderBy('id DESC')
            ->all();

        $currency_array = [];
        /** @var CurrencyHistory[] $models */
        foreach ($models as $model) {
            $currency_array[] = [
                'date' => date('d.m.Y', $model->date),
                'currency' => $model->currency->name,
                'sale' => $model->sale,
                'buy' => $model->buy,
            ];
        }

        return $this->render('currency_history', [
            'currency_array' => $currency_array,
        ]);
    }
}
