<?php
namespace common\models\rep;

use Yii;
use common\models\ar\DataLevel1LogAR;

/**
 *  Репозиторий "Лог загруженных данных"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel1LogRep
{
    public static function batchInsert(array $data) : void
    {
        Yii::$app->db->createCommand()
            ->batchInsert(DataLevel1LogAR::tableName(), ['url', 'result'], $data)
            ->execute();
    }

}