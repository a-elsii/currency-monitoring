<?php

namespace app\models;

use app\models\My\MyActiveRecord;
use Yii;
use app\models\My\MyHelper;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * This is the model class for table "currency_history".
 *
 * @property int $id Id
 * @property int $id_currency Ид валюты
 * @property int $date date
 * @property float $sale Продажа
 * @property float $buy Покупка
 * @property int $status_view status view
 * @property int $status_del status del
 * @property int $created_at date create
 * @property int $updated_at date update
 * @property int $deleted_at date delete
 *
 * @property Currency $currency
 */
class CurrencyHistory extends MyActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_currency'], 'required'],
            [['id_currency', 'date', 'status_view', 'status_del', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['sale', 'buy'], 'number'],
            [['id_currency'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['id_currency' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_currency' => 'Id Currency',
            'date' => 'Date',
            'sale' => 'Sale',
            'buy' => 'Buy',
            'status_view' => 'Status View',
            'status_del' => 'Status Del',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'id_currency']);
    }

    /**
     * Записиваем в историю валюту
     *
     * @param $date
     * @param $id_currency
     * @param $sale
     * @param $buy
     *
     * @return Currency|array|yii\db\ActiveRecord
     * @throws HttpException
     *
     */
    public static function create($date, $id_currency, $sale, $buy)
    {

        $model = new self();
        $model->id_currency = $id_currency;
        $model->date = $date;
        $model->sale = $sale;
        $model->buy = $buy;

        if(!$model->save()) {
            MyHelper::badSaveRequest($model->errors, 'create');
        }

        return $model;
    }

    /**
     * @return bool
     * @throws HttpException
     */
    public function softDelete() {
        $this->status_del = 1;
        $this->deleted_at = time();
        if(!$this->save())
            MyHelper::badSaveRequest($this->errors, 'Save filed');

        return true;
    }

    /**
     * @return bool
     * @throws HttpException
     */
    public function softRestore() {
        $this->status_del = 0;
        $this->deleted_at = 0;
        if(!$this->save())
            MyHelper::badSaveRequest($this->errors, 'Save filed');

        return true;
    }

    public static function attributeArray($attr, $default = null) {
        $attribute = [

        ];

        if(!isset($attribute[$attr]))
            return null;

        if($default !== null)
            return ArrayHelper::merge(is_array($default) ? $default : [0 => $default], $attribute[$attr]);

        return $attribute[$attr];
    }
}
