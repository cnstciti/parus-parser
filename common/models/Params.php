<?php
namespace common\models;

use Yii;

class Params
{
    public static function proxyApi() : string
    {
        return Yii::$app->params['proxyApi'];
    }

    public static function parusApi() : string
    {
        return Yii::$app->params['parusApi'];
    }

    public static function numGapsDelete() : int
    {
        return Yii::$app->params['numGapsDelete'];
    }

}
