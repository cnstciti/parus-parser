<?php
namespace common\models\ar;

use yii\db\ActiveRecord;

/**
 *  Таблица "Обработанные данные"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel2AR extends ActiveRecord
{
    /**
    * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
    */
    public static function tableName()
    {
        return '{{data_level2}}';
    }
}