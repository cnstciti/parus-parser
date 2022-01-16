<?php
namespace common\models\rep;

use common\models\ar\ParamAR;

/**
 * Репозиторий "Параметры парсеров"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class ParamRep
{
    public static function getOne(string $typeDB, string $cityDB, string $actionDB, string $site) : array
    {
        return ParamAR::find()
            ->where(['type' => $typeDB, 'city' => $cityDB, 'action' => $actionDB, 'site' => $site])
            ->asArray()
            ->one();
    }

    public static function setNumPage(int $id, int $numPage) : void
    {
        $row = ParamAR::findOne($id);
        $row->numPage = $numPage;
        $row->save();
    }
}