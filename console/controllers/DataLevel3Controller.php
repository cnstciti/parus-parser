<?php
namespace console\controllers;

use common\models\data_level3\DataLevel3;
use yii\console\Controller;

/**
 * Avito controller
 */
class DataLevel3Controller extends Controller
{
    /**
     * Действие "Level3 - Обогащение данных"
     *
     * @return mixed
     */
    public function actionParser()
    {
        $result = DataLevel3::parser();
        print_r($result);
        //echo 'OK' . PHP_EOL;
    }

    public function actionDelete($id)
    {
        $result = DataLevel3::delete($id);
        print_r($result);
        //echo 'OK' . PHP_EOL;
    }
}
