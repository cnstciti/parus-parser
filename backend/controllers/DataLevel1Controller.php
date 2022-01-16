<?php
namespace backend\controllers;

use common\models\data_level1\DataLevel1;
use common\models\data_level1\DataLevel1Avito;
use common\models\data_level1\DataLevel1Vsn;
use yii\web\Controller;
use Yii;

/**
 * DataLevel1 controller
 * Первичный сбор данных с сайтов
 *
 */
class DataLevel1Controller extends Controller
{
    /**
     * Действие "Level1. Список парсеров"
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $avitoRegions = DataLevel1Avito::getRegions();
        $vsnRegions   = DataLevel1Vsn::getRegions();

        return $this->render('index', [
             'avitoRegions' => $avitoRegions,
             'vsnRegions'   => $vsnRegions,
        ]);
    }

    /**
     * Действие "Level1. Разбор каталога"
     *
     * Вход:
     *  [
     *      'type'   => <string>,   // тип каталога ОН (flat | room | house | land)
     *      'city'   => <string>,   // населенный пункт (klim | vosk | msk | pod | sher | vid | znam)
     *      'action' => <string>,   // сделка каталога ОН (sell | rent)
     *      'site'   => <string>,   // сайт (avito | vsn)
     *  ]
     *
     * @return mixed
     */
    public function actionParser()
    {
        $request = Yii::$app->getRequest();
        $params  = $request->isPost ? $request->getBodyParams() : $request->get();

        $data = DataLevel1::parser($params);

        return $this->render('parser', [
            'data'  => $data,
        ]);
    }

}
