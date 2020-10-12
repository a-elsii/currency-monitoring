<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Log as siteLog;
use app\models\My\MyHelper;
use yii\console\Controller;
use yii\console\ExitCode;
use InfoLog\Log;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CronController extends Controller
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
     * Получаем ошибки из таблицы log
     *
     * php yii cron/check-log
     * @return int
     * @throws \Exception
     */
    public function actionCheckLog(){
        /** @var siteLog[] $models */
        $models = siteLog::find()
            ->andWhere(['is_new' => true])
            ->all();

        foreach ($models as $model)
        {
            if($model->level == siteLog::LEVEL_4 && $model->category == 'application')
                continue;

            $model->is_new = false;
            if(!$model->save())
                MyHelper::badSaveRequest($model->errors, 'cron CheckSiteLog');

            Log::info([
                'time' => date('d-m-Y H:i:s', $model->log_time),
                'project_name' => '<b><i>' . MyHelper::getProjectName() . '</i></b>',
                'message' => substr($model->message,0, 500),
            ], MyHelper::getIdProject(), "category:{$model->category}");

            print '.';
        }

        return ExitCode::OK;
    }
}
