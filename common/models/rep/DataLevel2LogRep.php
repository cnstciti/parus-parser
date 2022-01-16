<?php
namespace common\models\rep;

use Yii;
use common\models\ar\DataLevel2LogAR;

/**
 *  Репозиторий "Лог обработанных данных"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel2LogRep
{
    public static function batchInsert(array $data) : void
    {
        Yii::$app->db->createCommand()
            ->batchInsert(DataLevel2LogAR::tableName(), ['url', 'status', 'createAt'], $data)
            ->execute();
    }

}