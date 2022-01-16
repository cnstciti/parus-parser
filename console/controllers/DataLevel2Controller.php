<?php
namespace console\controllers;

use common\models\data_level2\DataLevel2;
use yii\console\Controller;

/**
 * DataLevel2 controller
 */
class DataLevel2Controller extends Controller
{
    /**
     * Действие "Level2 - Обработка данных"
     *
     * @return mixed
     */
    public function actionParser($num=10)
    {
        $result = DataLevel2::parser($num);
        print_r($result);
        //echo 'OK' . PHP_EOL;
    }

}
