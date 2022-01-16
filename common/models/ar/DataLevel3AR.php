<?php
namespace common\models\ar;

use Yii;
use yii\db\ActiveRecord;

/**
 *  "Таблица финальных объектов"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel3AR extends ActiveRecord
{


    /**
    * @return string название таблицы, сопоставленной с этим ActiveRecord-классом.
    */
    public static function tableName()
    {
        return '{{data_level3}}';
    }
/*
    public function getPrice() {
        return Yii::$app->formatter->asPrice($this->myPrice);
    }

    public function getType() {
        switch ($this->myRooms) {
            case 'дома':      return 'Дом';
            case 'дачи':      return 'Дача';
            case 'таунхаусы': return 'Таунхаус';
            case 'коттеджи':  return 'Коттедж';
        }

        return $this->myRooms;
    }

    public function getRealAddress() {
        if ($this->myAddress) {
            return $this->myAddress;
        }

        return $this->address;
    }

    public function attributeLabels() {
        return [
            'price'       => 'Цена',
            'realAddress' => 'Адрес',
            'type'        => 'Тип',
        ];
    }
*/
}