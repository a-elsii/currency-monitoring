<?php

namespace app\models;

use app\models\My\MyHelper;
use Yii;
use app\models\My\MyActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * This is the model class for table "currency".
 *
 * @property int $id Id
 * @property string $key name
 * @property string $name name
 * @property int $status_view status view
 * @property int $status_del status del
 * @property int $created_at date create
 * @property int $updated_at date update
 * @property int $deleted_at date delete
 */
class Currency extends MyActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status_view', 'status_del', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['key', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'name' => 'Name',
            'status_view' => 'Status View',
            'status_del' => 'Status Del',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Создания валюты
     *
     * @param $key
     * @param $name
     * @return Currency|array|\yii\db\ActiveRecord
     * @throws HttpException
     *
     */
    public static function create($key, $name)
    {
        $model = self::find()
            ->byCurrencyKey($key)
            ->byName($name)
            ->one();

        if($model)
            return $model;

        $model = new self();
        $model->key = $key;
        $model->name = $name;

        if(!$model->save())
            MyHelper::badSaveRequest($model->errors, 'create');

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

    public static function getAllCurrency()
    {
        $result = [];
        /** @var self[] $models */
        $models = self::find()->all();
        foreach ($models as $item)
            $result[$item->key] = $item->id;

        return $result;
    }
}
