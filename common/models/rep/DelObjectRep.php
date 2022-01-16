<?php
namespace common\models\rep;

use common\models\ar\DelObjectAR;

/**
 *  Репозиторий ""
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DelObjectRep
{
    //const ID = 1;

    public static function findOne($id)
    {
        return DelObjectAR::findOne($id);
    }

    public static function setLastId(int $maxId, int $lastId, $id) : void
    {
        $row = self::findOne($id);
        if ($lastId < $maxId) {
            $row->lastId = $lastId;
        } else {
            $row->lastId = 0;
        }
        $row->save();
    }

}