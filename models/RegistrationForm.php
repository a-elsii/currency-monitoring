<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\My\MyHelper;

/**
 * Signup form
 */
class RegistrationForm extends Model
{
    public $type_user = User::TYPE_USER;
    public $email;
    public $username;
    public $password;
    public $password_repeat;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email'], 'trim'],
            [['type_user'], 'number'],
            ['email', 'checkEmailValid'],
            [['email', 'username'], 'string', 'max' => 255],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'message'=>"Passwords don't match" ],
            ['email', 'uniqueEmailValidate'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match"],
        ];
    }

    /**
     * @throws yii\web\HttpException
     */
    public function uniqueEmailValidate()
    {
        $user = User::findByUsername($this->email);
        if ($user)
            MyHelper::badRequest('Error EmailValidate');
    }

    /**
     * Проверяем на волидность email
     *
     * @throws yii\web\HttpException
     */
    public function checkEmailValid()
    {
        if(!preg_match("/[a-z0-9.]+@[a-z]+\.[a-z]+/", $this->email))
            MyHelper::badRequest('Error checkEmailValid');
    }

    /**
     * Signs user up.
     *
     * @return User whether the creating new account was successful and email was sent
     * @throws Yii\base\Exception
     */
    public function signup() {
        if(!$this->validate())
            MyHelper::badRequest(json_encode($this->errors));

        $user = new User();
        $user->type = $this->type_user;

        // Получам юзернейм при регестрации до сабачки
        $user->username = $this->username;
        $user->email = $this->email;
        $user->access_token = $user->generateAccessTokenUser();
        $user->password_reset_token = User::getPasswordResetToken();

        $user->setPassword($this->password);
        $user->generateAuthKey();
        if(!$user->save())
            MyHelper::badSaveRequest($user->errors, 'Error save user');

        return $user;
    }
}