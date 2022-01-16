<?php

namespace frontend\controllers;

use frontend\models\BFunction;
use frontend\models\ON;
use yii\web\Controller;
use yii\web\Response;
use Yii;

/**
 * Rent controller
 */
class RentController extends Controller
{
    /**
     * Возвращает список ИД ОН аренды (квартиры + комнаты)
     *
     * Вызов:
     *      GET <SERVER>/rent/list-ids-object
     *
     * Вход:
     *      Нет
     *
     * Выход: array
     *  [
     *      {
     *          'id' => <int> // ИД ОН аренды
     *      }
     *  ]
     */
    public function actionListIdsObject()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ON::listIdsRentObject();
    }

    /**
     * Поиск ОН аренды (квартиры + комнаты)
     *
     * Вызов:
     *      POST <SERVER>/rent/search
     *
     * Вход: array
     *  [
     *      'flat1Amount'  => <int>,     // максимальная стоимость 1-ком. квартиры
     *      'flat2Amount'  => <int>,     // максимальная стоимость 2-ком. квартиры
     *      'flat3Amount'  => <int>,     // максимальная стоимость 3-ком. квартиры
     *      'flat4Amount'  => <int>,     // максимальная стоимость 4-ком. квартиры
     *      'flat5Amount'  => <int>,     // максимальная стоимость 5-ком. квартиры
     *      'flat6Amount'  => <int>,     // максимальная стоимость 6-ком. квартиры
     *      'studioAmount' => <int>,     // максимальная стоимость квартиры-студии
     *      'roomAmount'   => <int>,     // максимальная стоимость комнаты
     *      'polygons'     => array
     *  ]
     *  Не все ключи могут быть переданы
     *
     * Выход: array
     *  [
     *      'error' => [
     *          'code'        => <int>,
     *          'description' => <string>,
     *      ],
     *      'result' => [
     *          {
     *              'id'          => <int>,     // ИД объекта
     *              'type_object' => <enum>,    // Тип объекта
     *              'rooms'       => <enum>,    // Количество комнат
     *              'latitude'    => <string>,  // Географическая широта
     *              'longitude'   => <string>,  // Географическая долгота
     *              'address'     => <string>,  // Адрес
     *              'price'       => <int>,     // Стоимость
     *              'url'         => <string>   // Ссылка на страницу
     *          }
     *      ]
     *  ]
     */
    public function actionSearch()
    {
        $request = Yii::$app->getRequest();
        $params  = $request->isPost ? $request->getBodyParams() : [];
        $params  = BFunction::arrayChangeKeyCase($params);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return ON::searchRent($params);
    }

}
