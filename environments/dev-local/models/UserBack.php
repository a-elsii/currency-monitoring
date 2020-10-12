<?php

// namespace app\models;

use app\models\My\MyActiveRecord;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;

/**
 * This is the model class for table "user".
 *
 * @property int $id Код
 * @property string $username логин пользователя
 * @property string $avatar Аватар
 * @property string $name Имя пользователя
 * @property string $surname Фамилия пользователя
 * @property string $email Емейл
 * @property string $access_code Код доступа
 * @property string $password Пароль
 * @property string $auth_key Ключ авторизации
 * @property string $full_name ФИО
 * @property string $role Роль
 * @property int $type Тип
 * @property int $status_del Статус удаления
 * @property int $created_at Дата создания
 * @property int $updated_at Дата изменения
 * @property int $deleted_at Дата удаления
 */
class UserBack extends MyActiveRecord implements IdentityInterface
{
    /**
     * @var $avatar UploadedFile
     */
    public $avatar_file;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password', 'username'], 'required'],
            [['avatar'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            [['type', 'status_del', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['email', 'full_name'], 'string', 'max' => 255],
            [['access_code'], 'string', 'max' => 25],
            [['password', 'role'], 'string', 'max' => 32],
            [['surname','name'], 'trim'],
            [['auth_key'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'avatar' => Yii::t('app', 'avatar'),
            'username' => Yii::t('app', 'username'),
            'name' => Yii::t('app', 'name'),
            'surname' => Yii::t('app', 'surname'),
            'email' => Yii::t('app', 'Email'),
            'access_code' => Yii::t('app', 'Access Code'),
            'password' => Yii::t('app', 'Password'),
            'full_name' => Yii::t('app', 'Full Name'),
            'role' => Yii::t('app', 'Role'),
            'type' => Yii::t('app', 'Type'),
            'status_del' => Yii::t('app', 'Status Del'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
        ];
    }

//    public function afterFind()
//    {
//        if($this->avatar)
//            $this->avatar_file = $this->avatar;
//
//        parent::afterFind();
//        return true;
//    }

    public function beforeSave($insert)
    {
        if(!parent::beforeSave($insert))
            return false;

        if ($this->isNewRecord)
            $this->auth_key = Yii::$app->security->generateRandomString();

        return true;
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

    public static function HashPassword($password)
    {
        return md5( 'd1cc18f961deaee'.md5($password).'89511c1663fbcaaa5');
    }

    public function getId() {
        return $this->id;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === self::HashPassword($password);
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     */
    public function upload()
    {
        if(!is_dir(Yii::getAlias('@uploads/avatar')))
            mkdir(Yii::getAlias('@uploads/avatar'));

        $name_avatar = $this->avatar_file->baseName.'_'.Yii::$app->security->generateRandomString() . '.' . $this->avatar_file->extension;
        $this->avatar_file->saveAs(Yii::getAlias('@uploads/avatar/') . $name_avatar);
        return $name_avatar;
    }

}
