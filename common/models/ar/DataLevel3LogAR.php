<?php
namespace common\models\ar;

use yii\db\ActiveRecord;

/**
 *  Таблица "Лог обработанных данных"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel3LogAR extends ActiveRecord
{
    /**
    * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
    */
    public static function tableName()
    {
        return '{{data_level3_log}}';
    }
}