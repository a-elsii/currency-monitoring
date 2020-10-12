<?php
/**
 * @link http://phe.me
 * @copyright Copyright (c) 2014 Pheme
 * @license MIT http://opensource.org/licenses/MIT
 */

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UrlManager;

/**
 * @author Aris Karageorgos <aris@phe.me>
 */
class I18nUrlManager extends UrlManager
{

    public static $currentLanguage;
    /**
     * @var array Supported languages
     */
    public $languages;
    public $langpath='';

    /**
     * @var array Language aliases
     */
    public $aliases = [];

    /**
     * @var bool Whether to display the source app language in the URL
     */
    public $displaySourceLanguage = false;

    /**
     * @var string Parameter used to set the language
     */
    public $languageParam = 'lang';


    public function setLang($lang){
        self::$currentLanguage = $lang;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (is_callable($this->languages)) {
            $this->languages = call_user_func($this->languages);
        }
        if (empty($this->languages)) {
            $this->languages = [Yii::$app->language];
        }
        parent::init();
    }

    /**
     * Parses the URL and sets the language accordingly
     * @param \yii\web\Request $request
     * @return array|bool
     */
    public function parseRequest($request)
    {
        if ($this->enablePrettyUrl) {
            $pathInfo = $request->getPathInfo();
            $language = explode('/', $pathInfo)[0];
            $locale = ArrayHelper::getValue($this->aliases, $language, $language);
            if (in_array($language, $this->languages)) {
                $request->setPathInfo(substr_replace($pathInfo, '', 0, (strlen($language) + 1)));
                Yii::$app->language = $locale;
                static::$currentLanguage = $language;
            }
        } else {
            $params = $request->getQueryParams();
            $route = isset($params[$this->routeParam]) ? $params[$this->routeParam] : '';
            if (is_array($route)) {
                $route = '';
            }
            $language = explode('/', $route)[0];
            $locale = ArrayHelper::getValue($this->aliases, $language, $language);
            if (in_array($language, $this->languages)) {
                $route = substr_replace($route, '', 0, (strlen($language) + 1));
                $params[$this->routeParam] = $route;
                $request->setQueryParams($params);
                Yii::$app->language = $locale;
                static::$currentLanguage = $language;
            }
        }

//        if(static::$currentLanguage && isset(Yii::$app->user->identity)){
//            Yii::$app->user->identity->lang=static::$currentLanguage;
//            Yii::$app->user->identity->save();
//        }elseif(isset(Yii::$app->user->identity)){
//            Yii::$app->user->identity->lang='en';
//            Yii::$app->user->identity->save();
//        }

        if(Yii::$app->language !='ru'){
            $this->langpath = '/'.Yii::$app->language;
        }

        return parent::parseRequest($request);
    }

    /**
     * Adds language functionality to URL creation
     * @param array|string $params
     * @return string
     */
    public function createUrl_($params)
    {

        $params = (array)$params;

        if (array_key_exists($this->languageParam, $params)) {
            $lang = $params[$this->languageParam];
            if ((($lang !== Yii::$app->sourceLanguage && ArrayHelper::getValue($this->aliases, $lang) !== Yii::$app->sourceLanguage)
                    || $this->displaySourceLanguage) && !empty($lang)
            ) {
                $params[0] = $lang . '/' . ltrim($params[0], '/');
            }
            unset($params[$this->languageParam]);
        } else {
            if (Yii::$app->language !== Yii::$app->sourceLanguage || $this->displaySourceLanguage) {
                $params[0] = static::$currentLanguage . '/' . ltrim($params[0], '/');
            }
        }
        return parent::createUrl($params);
    }


    public function createUrl($params)
    {

        $lang = '';

        if ((Yii::$app->language !== Yii::$app->sourceLanguage || $this->displaySourceLanguage) && static::$currentLanguage) {
            $lang = static::$currentLanguage . '/';
        }
        if (array_key_exists($this->languageParam, $params)) {
            if($params[$this->languageParam] != 'ru'){
                $lang = $params[$this->languageParam];
            }else{
                $lang = '';
            }
            unset($params[$this->languageParam]);
        }

        $params = (array)$params;
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#'], $params[$this->routeParam]);

        $route = trim($params[0], '/');
        unset($params[0]);

        $baseUrl = $this->showScriptName || !$this->enablePrettyUrl ? $this->getScriptUrl() : $this->getBaseUrl();

        if ($this->enablePrettyUrl) {
            $cacheKey = $route . '?';
            foreach ($params as $key => $value) {
                if ($value !== null) {
                    $cacheKey .= $key . '&';
                }
            }

            $url = $this->getUrlFromCache($cacheKey, $route, $params);

            if ($url === false) {
                $cacheable = true;
                foreach ($this->rules as $rule) {
                    /* @var $rule UrlRule */
                    if (!empty($rule->defaults) && $rule->mode !== UrlRule::PARSING_ONLY) {
                        // if there is a rule with default values involved, the matching result may not be cached
                        $cacheable = false;
                    }
                    if (($url = $rule->createUrl($this, $route, $params)) !== false) {
                        if ($cacheable) {
                            $this->setRuleToCache($cacheKey, $rule);
                        }
                        break;
                    }
                }
            }

            if ($url !== false) {
                if (strpos($url, '://') !== false) {
                    if ($baseUrl !== '' && ($pos = strpos($url, '/', 8)) !== false) {
                        return substr($url, 0, $pos) . $baseUrl . substr($url, $pos) . $anchor;
                    } else {
                        return $url . $baseUrl . $anchor;
                    }
                } elseif (strpos($url, '//') === 0) {
                    if ($baseUrl !== '' && ($pos = strpos($url, '/', 2)) !== false) {
                        return substr($url, 0, $pos) . $baseUrl . substr($url, $pos) . $anchor;
                    } else {
                        return $url . $baseUrl . $anchor;
                    }
                } else {
                    $url = ltrim($url, '/');
                    if($lang && !strstr($lang, '/')){
                        $url = '/'.$url;
                    }

                    return "$baseUrl/{$lang}{$url}{$anchor}";
                }
            }

            if ($this->suffix !== null) {
                $route .= $this->suffix;
            }
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $route .= '?' . $query;
            }

            $route = ltrim($route, '/');


            return "$baseUrl/{$lang}{$route}{$anchor}";
        } else {
            $url = "$baseUrl?{$this->routeParam}=" . urlencode($route);
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $url .= '&' . $query;
            }

            return $url . $anchor;
        }
    }


}
