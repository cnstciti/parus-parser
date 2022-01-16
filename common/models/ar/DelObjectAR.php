<?php
namespace common\models\ar;

use yii\db\ActiveRecord;

/**
 *  "Таблица "
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DelObjectAR extends ActiveRecord
{

    /**
    * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
    */
    public static function tableName()
    {
        return '{{del_object}}';
    }
}