<?php
namespace console\controllers;

use common\models\data_level1\DataLevel1;
use yii\console\Controller;

/**
 * DataLevel1 controller
 */
class DataLevel1Controller extends Controller
{
    /**
     * Действие "Разбор каталога"
     *
     * @return mixed
     */
    public function actionParser(string $type, string $city, string $action, string $site)
    {
        $params = [
            'type'   => $type,
            'city'   => $city,
            'action' => $action,
            'site'   => $site,
        ];
        $result = DataLevel1::parser($params);
        print_r($result);
        //echo 'OK' . PHP_EOL;
    }

}
