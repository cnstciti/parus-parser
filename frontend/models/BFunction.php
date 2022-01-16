<?php

namespace frontend\models;

use Yii;
//use yii\base\Object;
use yii\helpers\Html;

class BFunction //extends Object
{
    /*
    Необязательный параметр $case: (по умолчанию CASE_LOWER) для перевода ключей в нижний регистр или CASE_UPPER для перевода в верхний регистр
    */
    public static function arrayChangeKeyCase(array $params, string $case = CASE_LOWER) : array
    {
        if (is_array($params)) {

            $params = array_change_key_case($params, $case);

            foreach ($params as $k =>$param) {

                if (is_array($param)) {

                    $params[$k] = self::arrayChangeKeyCase($param, $case);

                }
            }
        }

        return $params;
    }

}