<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\CalculationsLogs;
use app\models\Logs;
use app\services\ParserService;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ParserController extends Controller
{
    /**
     * @param $file
     * @return void
     */
    public function actionNginx($file)
    {
        if (file_exists($file)) {
            $data = [];
            try {
                $parser = new ParserService($file);
                $data = $parser->parse();
            }catch (\Exception $e){
                echo "Ошибка: ".$e->getMessage(). PHP_EOL;
            }

            if(count($data) > 0){
                Logs::batchInsert($data);
            }
        } else {
            echo "Ошибка: $file файл не найден.". PHP_EOL;
        }
    }
}
