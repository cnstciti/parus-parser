<?php

namespace frontend\models;

use Yii;
use common\models\rep\DataLevel3Rep;
use common\models\PolygonEngine;

/**
 * класс "Объект недвижимости"
 */
class ON
{
    public static function listIdsRentObject() : array
    {
        return DataLevel3Rep::listIdsRentObject();
    }

    public static function searchRent(array $params) : array
    {
        //Yii::error($params, 'searchRent');
        $objects = DataLevel3Rep::searchRent($params);
        //return $objects;
        $ret = [];
        foreach ($objects as $object) {
            foreach ($params['polygons'] as $polygon) {
                $p = new PolygonEngine($polygon);
                //Yii::error($object['id'], 'before/searchRent');
                if ($p->isCrossesWith($object['latitude'], $object['longitude'])) {
                    //Yii::error($object['id'], 'in/searchRent');
                    $ret[] = $object;
                }
            }
        }

        return $ret;
    }

    public static function delete(int $id) : void
    {
        DataLevel3Rep::delete($id);
    }

    public static function countRentObject() : array
    {
        return [
            'flatPublished' => DataLevel3Rep::countFlatPublishedRent(),
            'flatLoaded'    => DataLevel3Rep::countFlatLoadedRent(),
            'roomPublished' => DataLevel3Rep::countRoomPublishedRent(),
            'roomLoaded'    => DataLevel3Rep::countRoomLoadedRent(),
        ];
    }

}
