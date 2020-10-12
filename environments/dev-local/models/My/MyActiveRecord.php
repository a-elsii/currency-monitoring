<?php
/**
 * Created by PhpStorm.
 * User: maksim
 * Date: 17.04.19
 * Time: 17:26
 */

// namespace app\models\My;


use app\models\DefaultQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use Yii;

class MyActiveRecord extends ActiveRecord
{
    public $file_main_image;

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],

                ],
                'value' => time(),

            ],
        ];
    }

    /**
     * Расширение метода find().
     *
     * @inheritdoc
     * @return DefaultQuery the active query used by this AR class.
     */
    public static function find($isAdmin = false) {
        return new DefaultQuery(get_called_class(), ['isAdmin' => $isAdmin]);
    }

    /**
     * Загрузка файлов изображения
     * @return string
     * @throws yii\base\Exception
     */
    public function upload() {
        $dir_alias = '@uploads/images';

        MyHelper::checkDir(Yii::getAlias('@uploads'));
        MyHelper::checkDir(Yii::getAlias($dir_alias));

        $name_avatar = $this->file_main_image->baseName.'_'.Yii::$app->security->generateRandomString() . '.' . $this->file_main_image->extension;
        $this->file_main_image->saveAs(Yii::getAlias($dir_alias.'/') . $name_avatar);

        // Добовляем миниатюры
//        MyHelper::createThumbnail($name_avatar, 138, 192, Yii::getAlias($dir_alias.'/'));
        return $name_avatar;
    }
}