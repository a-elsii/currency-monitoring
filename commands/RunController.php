<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Currency;
use app\models\CurrencyHistory;
use app\models\My\MyHelper;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RunController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }


    /**
     * Создание монет в системе
     * @value php yii run/create-currency
     *
     * @throws \yii\web\HttpException
     */
    public function actionCreateCurrency()
    {
        $currencies = [
            'RUB' => 'RUB',
            'USD' => 'USD',
            'EUR' => 'EUR',
        ];

        foreach ($currencies as $key => $val)
            Currency::create($key, $val);
    }


    /**
     * Записиваем историю монеты
     * @value  php yii run/save-history-currency
     * @throws \yii\web\HttpException
     */
    public function actionSaveHistoryCurrency()
    {
        for ($i = 6; $i <= 13; $i++)
        {
            $date = "{$i}.10.2020";
            $request = MyHelper::createRequest("https://api.privatbank.ua/p24api/exchange_rates?json&date={$date}");

            if(!isset($request['exchangeRate']))
            {
                print 'Ошибка request';
                return true;
            }

            $currencies_history = [];
            $get_all_currency = Currency::getAllCurrency();
            foreach ($request['exchangeRate'] as $item) {

                if(!isset($item['currency']))
                    continue;

                $currency = $item['currency'];
                if(!in_array($currency, array_keys($get_all_currency)))
                    continue;

                $currencies_history[$currency] = [
                    'id_currency' => $get_all_currency[$currency],
                    'sale' => $item['saleRateNB'],
                    'buy' => $item['purchaseRateNB'],
                ];
            }

            foreach ($currencies_history as $key => $val)
                CurrencyHistory::create(strtotime($date),intval($val['id_currency']), $val['sale'], $val['buy']);

            print '.';
        }

        return true;
    }
}
