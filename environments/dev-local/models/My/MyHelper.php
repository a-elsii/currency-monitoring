<?php

//namespace app\models\My;

use Yii;
use app\models\User;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\web\HttpException;
use yii\imagine\Image;
use Imagine\Image\Box;
use kartik\mpdf\Pdf;
use Google\Authenticator\GoogleAuthenticator;

class MyHelper
{
    const TYPE_USER_UNKNOWN = 0;

    const API_TYPE_STRING = "string";
    const API_TYPE_INTEGER = "integer";
    const API_TYPE_FLOAT = "float";
    const API_TYPE_BOOLEAN = "boolean";
    const API_TYPE_ARRAY = "array";

    const API_METHOD_GET = "GET";
    const API_METHOD_POST = "POST";

    /**
     * Получаем все языки сайта
     * @return array
     */
    public static function getAllLanguages() {
        return Yii::$app->urlManager->languages ?? [];
    }

    /**
     * Полуаем язык на котором сейчас сайт
     * @return string
     */
    public static function getLanguage() {
        return Yii::$app->language;
    }

    /**
     * Возвращает true если это дев
     * @return bool
     */
    public static function isDev() {
        return YII_ENV == "dev";
    }

    /**
     * Проверяем или есть директория если ее нету тогда создаем ее
     * @param $dir
     * @return bool
     */
    public static function checkDir($dir) {
        if(!is_dir($dir))
            mkdir($dir);

        return true;
    }

    /**
     * Функция для возврата ошибки
     * @param $text
     * @throws HttpException
     */
    public static function badRequest($text) {
        throw new HttpException(400, Yii::t('app', $text));
    }

    /**
     * Функция для возврата ошибки и записиваем в логи
     * @param $error
     * @param $function - функция в какой ошибка
     * @param $text
     * @throws HttpException
     */
    public static function badSaveRequest(array $error, $function , $text = 'Save filed') {
        Yii::error("'({$function}) $text: '".PHP_EOL. print_r($error, true), 'error_save');
        self::badRequest($text);
    }

    /**
     * Функци для отправки письма пользователю
     * @param $view
     * @param $data
     * @param $setTo
     * @param $setSubject
     * @return bool
     */
    public static function sendEmail($view, $data, $setTo, $setSubject) {
        Yii::$app->mailer->compose($view, $data)
            ->setFrom([Yii::$app->params['supportEmail'] => 'masterskaya'])
            ->setTo($setTo)
            ->setSubject($setSubject)
            ->send();

        return true;
    }

    /**
     * Получаем пользователя
     * @return User|null
     * @throws HttpException
     * @throws \Throwable
     */
    public static function getUser() {
        $user = User::findOne(Yii::$app->user->getId());
        // получаем токен если нам нужен апи
        $token = Yii::$app->request->getHeaders()->get('access_token');
        if(!$user && $token)
            $user = User::findIdentityByAccessToken($token);

        if(!$user)
            MyHelper::badRequest('user is not authorized');


        return $user;
    }

    /**
     * Функция для оптправки запроса
     * @param $url
     * @param array $data
     * @param string $method
     * @return array|null
     * @throws HttpException
     */
    public static function createRequest($url, $data = [], $method = 'GET') {
        $result_data = null;
        $client = new Client();
        $response = null;
        try {
            $response = $client->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->setData($data)
                ->send();
        } catch (Exception $e) {
            // Если получаем ошибу то выводим ее пользователю
            MyHelper::badRequest($e->getMessage());
        }

        if (isset($response->isOk))
            $result_data = $response->data;

        return $result_data;
    }

    /**
     * Функция для загрузки изображения в формате base64
     * @example MyHelper::uploadBase64(Yii::getAlias('@uploads/passport'), $file,'passport')
     * @param $path - путь для сохранения
     * @param $file - base64 файл
     * @param $prefix - префикс перед названием файла
     * @param $file_name - имя файла
     * @return string
     * @throws yii\base\Exception
     */
    public static function uploadBase64($path, $file, $prefix = '', $file_name = '') {
        if(!$file)
            return '';

        if(!$path)
            return '';

        if($prefix)
            $prefix .= '_';

        if(!is_dir($path))
            mkdir($path);

        $image_parts = explode(";base64,", $file);
        $image_type = '';

        // если нам передают файл мы сохраняем тип файла
        if($file_name)
            $image_type = explode('.', $file_name)[1];

        // если у нас это изображение то сохраняем росширения изображения
        if(preg_match('/image/', $image_parts[0])) {
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
        }


        $image_base64 = base64_decode($image_parts[1]);
        $file_name = $prefix . Yii::$app->security->generateRandomString() . '.' . $image_type;
        $file = $path.'/'.$file_name;
        file_put_contents($file, $image_base64);

        return $file_name;
    }

    /**
     * Парсим дату и возвращаем время начала аренды и время окончания аренды
     * @param $datetime - пример (1548432000_1548439200)
     * @return array
     */
    public static function parseDatetimeToRent(string $datetime) {
        $datetime_array = explode('_', $datetime);

        return [
            'start_rent' => intval($datetime_array[0] ?? 0),
            'end_rent' => intval($datetime_array[1] ?? 0),
        ];
    }

    /**
     * Функция которая проверяет вхождения времени
     * мы проверяем время начала работы и окончания если в этот промежуток попадает аренда то мы выводим true иначе false
     * @param $unix_from - время начала работы
     * @param $unix_to - время окончания работы
     * @param $time_start_rent - время начала аренды
     * @param $time_end_rent - время окончания аренды
     * @param $is_debug - дебаг времени
     * @return bool
     */
    public static function checkTimeRent($unix_from, $unix_to, $time_start_rent, $time_end_rent, $is_debug = false) {
        if($is_debug)
            print ("unix_from -> ". date('Y-m-d H:i:s', $unix_from) ." >= start_rent -> ". date('Y-m-d H:i:s', $time_start_rent) ." && unix_to -> ". date('Y-m-d H:i:s', $unix_to) ." <= end_rent -> ". date('Y-m-d H:i:s', $time_end_rent) ."" . PHP_EOL);

        if(
            ($unix_from >= $time_start_rent && $unix_to <= $time_end_rent)
            || ($time_start_rent >= $unix_from && $unix_to <= $time_end_rent && $time_start_rent < $unix_to)
            || ($unix_from >= $time_start_rent && $unix_to >= $time_end_rent && $time_end_rent > $unix_from && $time_end_rent < $unix_to)
        )
            return true;

        return false;
    }

    /**
     * Функция для получения DateTime
     *
     * @param $timestamp integer
     * @param $format string
     * @return DateTime
     * @throws \Exception
     */
    public static function getDateTime($timestamp, $format = 'Y-m-d H:i:s') {
        return new DateTime(date($format, $timestamp));
    }

    /**
     * Возвращает полный урл для изображений
     *
     * @param $image
     * @param $width
     * @param $height
     * @return string
     */
    public static function getImageUrl($image, $width = 0, $height = 0) {
        $image_name = $image;
        if($width && $height)
        {
            $explode_image = explode('.', $image);
            $image_name = "{$explode_image[0]}-{$width}x{$height}.{$explode_image[1]}";
        }

        return Url::to(["/assets/uploads/images/{$image_name}"], true);
    }

    /**
     * Функция для создания миниатюр
     *
     * @param string $path          - полный путь к изображению
     * @param $image                - названия изображения
     * @param $width                - ширина
     * @param $height               - высота
     * @param bool $is_resize       - изменить размер
     * @param int $quality          - качество изображения
     * @return bool
     */
    public static function createThumbnail($image, $width, $height, $path = '@uploads/images/', $is_resize = true, $quality = 70)
    {
        // Если у нас нету директории где сохранять, то сохраняем в папку images
        if(!$path)
            $path = Yii::getAlias($path);

        // Проверяем или есть директория если нету то создаем
        self::checkDir($path);

        // Полный путь к изображению
        $path_image = $path.$image;

        // разделяем изображения на названия и тип
        $name_image = explode('.', $image);
        $name_thumbnail_image = $path . $name_image[0] . "-{$width}x{$height}" . '.' . $name_image[1];

        if($is_resize)
        {
            Image::thumbnail($path_image, $width, $height)
                ->resize(new Box($width,$height))
                ->save($name_thumbnail_image, ['quality' => $quality]);
            return true;
        }

        Image::thumbnail($path_image, $width, $height)
            ->save($name_thumbnail_image, ['quality' => $quality]);

        return true;
    }


    /**
     * Функция для вывода шаблона pdf
     *
     * @param string $template - шаблон
     * @param array $data - данные
     * @return mixed
     * @throws HttpException
     */
    public static function renderPdf($template = 'default', $data = [])
    {
        $html = self::getTemplatePdf($template, $data);
        if(!$html)
            self::badRequest('Ошибка шаблона');

        $pdf = Yii::$app->pdf;
        $pdf->content = $html;
        return $pdf->render();
    }

    /**
     * Функция для скачивания шаблона pdf
     *
     * @param string $filename - файл
     * @param string $template - шаблон
     * @param array $data - данные
     * @return mixed
     * @throws HttpException
     */
    public function downloadPdf($filename, $template = 'default', $data = [])
    {
        if(!$filename)
            $filename = 'pdf-' . date('d-m-Y', time()) . '.pdf';

        $html = self::getTemplatePdf($template, $data);
        if(!$html)
            self::badRequest('Ошибка шаблона');

        /** @var Pdf $pdf */
        $pdf = Yii::$app->pdf; // or new Pdf();
        $mpdf = $pdf->api; // fetches mpdf api
        $mpdf->WriteHtml($html); // call mpdf write html
        echo $mpdf->Output($filename, 'D'); // call the mpdf api output as needed
        return true;
    }

    /**
     * Функция для сохранения шаблона pdf
     *
     * @param string $filename - файл
     * @param string $template - шаблон
     * @param array $data - данные
     * @return mixed
     * @throws HttpException
     */
    public static function savePdf($filename, $template = 'default', $data = [])
    {
        if(!$filename)
            $filename = 'pdf-' . date('d-m-Y', time()) . '.pdf';

        $html = self::getTemplatePdf($template, $data);
        if(!$html)
            self::badRequest('Ошибка шаблона');

        /** @var Pdf $pdf */
        $pdf = Yii::$app->pdf; // or new Pdf();
        $mpdf = $pdf->api; // fetches mpdf api
        $mpdf->WriteHtml($html); // call mpdf write html
        $mpdf->Output("assets/uploads/pdf_files/{$filename}", Pdf::DEST_FILE); // call the mpdf api output as needed
        return true;
    }

    /**
     * Функция для возврата в админку иконки
     *
     * @param boolean $is_check
     * @return string
     */
    public static function isCheckBoolGetIconForAdmin( bool $is_check) {
        $icon = '<i class="text-red icon fa fa-ban"></i>';
        if($is_check)
            $icon = '<i class="text-green icon fa fa-check"></i>';
        return $icon;
    }

    /**
     * Выводим апи на страницу из файла
     *
     * @return mixed
     */
    public static function getDocumentationApi() {
        return include ("api-documentation-array.php");
    }

    /**
     * Функция для получения шаблона в html
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    public static function getTemplatePdf($template = 'default', $data = [])
    {
        return Yii::$app->controller->renderPartial("/templates_pdf/{$template}", $data);
    }

    /**
     * Получить все куки
     *
     * @return array
     */
    public static function getAllCookie()
    {
        if(!isset(getallheaders()['Cookie']))
            return [];

        $headerCookies = explode('; ', getallheaders()['Cookie']);
        $cookies = array();
        foreach ($headerCookies as $itm) {
            if(!$itm)
                continue;

            list($key, $val) = explode('=', $itm, 2);
            $cookies[$key] = $val;
        }

        return $cookies;
    }

//    /**
//     * Генерировать secret для авторизации черезе google authenticator
//     * Нужно сохранять у пользователя $secret
//     *
//     * У пользователя нужно сгенерить $secret и сохранить
//     * потом вывести изображение
//     *
//     * @return bool
//     * @throws HttpException
//     */
//    public function generateGoogleAuthSecret()
//    {
//        $g = new GoogleAuthenticator();
//        $secret = $g->generateSecret();
//        $user->google_auth_secret = $secret;
//        if(!$this->save())
//            MyHelper::badSaveRequest($this->errors, 'generateGoogleAuthSecret', 'error save');
//
//        return true;
//    }

    /**
     * Получить Qr-code для авторизации гугла
     *
     * @param $secret
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function getQrCodeGoogleAuth($secret, $width = 150, $height = 150)
    {
        $name_project = 'cng.casino';
        return "https://chart.googleapis.com/chart?cht=qr&chl=otpauth://totp/{$name_project}%3Fsecret%3D{$secret}&chs={$width}x{$height}&chld=L|0";
    }

    /**
     * Проверяем код в гугл авторизации
     *
     * @param $secret
     * @param $code
     * @return bool
     */
    public static function checkCodeGoogleAuth($secret, $code)
    {
        $g = new GoogleAuthenticator();
        if($g->checkCode($secret, $code))
            return true;
        return false;
    }

    /**
     * Получаем ид проекта
     *
     * @return mixed|string
     */
    public static function getIdProject()
    {
        return Yii::$app->params['id_project_support'] ?? '777';
    }

    /**
     * Получаем Project name
     *
     * @return mixed|string
     */
    public static function getProjectName()
    {
        return Yii::$app->params['project_name'] ?? AA_DOMAIN;
    }
}
