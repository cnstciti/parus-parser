<?php
namespace common\models\rep;

use Yii;
use common\models\ar\DataLevel3LogAR;

/**
 *  Репозиторий "Лог обработанных данных"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel3LogRep
{
    public static function insert(array $data) : void
    {
        $row            = new DataLevel3LogAR;
        $row->level2_id = $data['level2_id'];
        $row->level3_id = $data['level3_id'];
        $row->url       = $data['url'];
        $row->save();
    }

}