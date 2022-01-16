<?php
namespace common\models\rep;

use common\models\ar\DataLevel3AR;
use Yii;
use common\models\MainConst;

/**
 *  Репозиторий ""
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel3Rep
{
    const STATUS_LOADED      = 'загружен';
    const STATUS_PUBLISHED   = 'опубликован';
    const STATUS_DELETE      = 'удален';
    const STATUS_HAND_DELETE = 'ручное_удаление';
    const STATUS_STALED      = 'устарел';
    const STATUS_NOT_OUR_OBJECT = 'не наш объект';


    public static function findByUrl(string $url)
    {
        return DataLevel3AR::find()
            ->where(['like', 'url', $url])
            ->asArray()
            //->where(['like', "REPLACE(REPLACE(url, '_', ''), '.', '')", $url])
            ->one();
    }

    public static function updatePrice(int $id, string $price, string $depositPrice)
    {
        $row = DataLevel3AR::find()
            ->where(['$d' => $id])
            ->one();
        $row->newPrice = $price;
        $row->myDepositPrice = $depositPrice;
        $row->save();
    }

    public static function findLastID($lastId)
    {
        return DataLevel3AR::find()
            ->where(['>=', 'id', $lastId])
            ->andWhere(['<>', 'status', self::STATUS_PUBLISHED])      // кроме "опубликован"
            ->andWhere(['<>', 'status', self::STATUS_HAND_DELETE])    // кроме "ручного удаления"
// ???            ->andWhere(['<>', 'status', self::STATUS_DELETE])         // кроме "удален"
            //->andWhere(['site' => 'avito'])
            ->one();
    }

    public static function insert(
        array $data
        /*
        string $latitude,
        string $longitude,
        string $typeHouse,
        //$myObject->typeLand = $ret['typeLand'];
        string $floor,
        string $numberOfFloors,
        //$myObject->myYearOfConstruction = $ret['yearOfConstruction'];
        string $rooms,
        string $totalArea,
        string $kitchenArea,
        string $livingArea,
        //$myObject->landArea = $ret['landArea'];
        //$myObject->myViewWindow = $ret['viewWindow'];
        //$myObject->myBalcony = $ret['balcony'];
        string $address,
        //$myObject->metroStation1 = $ret['metroStation1'];
        //$myObject->metroStationColor1 = $ret['metroStationColor1'];
        //$myObject->metroStation2 = $ret['metroStation2'];
        //$myObject->metroStationColor2 = $ret['metroStationColor2'];
        //$myObject->metroStation3 = $ret['metroStation3'];
        //$myObject->metroStationColor3 = $ret['metroStationColor3'];
        string $description,
        string $price,
        string $depositPrice,
        string $sellerName,
        string $status,
        string $url,
        string $type,
        string $action,
        string $site
        */
    ) : int
    {
        $row                   = new DataLevel3AR;
        $row->latitude         = $data['latitude'];
        $row->longitude        = $data['longitude'];
        $row->type_house       = $data['typeHouse'];
        $row->floor            = $data['floor'];
        $row->number_of_floors = $data['numberOfFloors'];
        $row->rooms            = $data['rooms'];
        $row->total_area       = $data['totalArea'];
        $row->kitchen_area     = $data['kitchenArea'];
        $row->living_area      = $data['livingArea'];
        $row->address          = $data['address'];
        $row->metro_station1   = $data['metroStation1'];
        $row->metro_station2   = $data['metroStation2'];
        $row->metro_station3   = $data['metroStation3'];
        $row->description      = $data['description'];
        $row->price            = $data['price'];
        $row->price_deposit    = $data['depositPrice'];
        $row->seller_name1     = $data['sellerName'];
        $row->status           = $data['status'];
        $row->url              = $data['url'];
        $row->type_object      = $data['type'];
        $row->action_object    = $data['action'];
        $row->site             = $data['site'];
        $row->save();

        return Yii::$app->db->getLastInsertID();
    }

//$row->myPrice1 = $data['price'];
//$myObject->typeLand = $ret['typeLand'];
//$myObject->myYearOfConstruction = $ret['yearOfConstruction'];
//$myObject->landArea = $ret['landArea'];
//$myObject->myViewWindow = $ret['viewWindow'];
//$myObject->myBalcony = $ret['balcony'];

    public static function getRowLastId(int $lastId) : array
    {
        // Ищем только со статусами: загружен, т.к. квартиры редко потом выставляются заново
        return DataLevel3AR::find()
            ->where(['>=', 'id', $lastId])
            ->andWhere(['status' => self::STATUS_LOADED])
            ->asArray()
            ->one();
    }

    public static function getClientRentObjects() : array
    {
        return DataLevel3AR::find()
            ->where([
                'status'         => self::STATUS_LOADED,
                'myActionObject' => self::ACTION_RENT,
            ])
            ->asArray()
            ->all();
    }
/*
    public static function setStatusDelete(int $id) : string
    {
        $comment             = 'Удален. Автоматическая обработка. Страницы уже не существует';
        $row                 = DataLevel3AR::findOne($id);
        $row->status         = self::STATUS_DELETE;
        $row->comment_parser = $comment;
        $row->update_at      = date("Y-m-d H:i:s");
        $row->save();

        return $comment;
    }
*/
    public static function setStatusLoaded(int $id) : string
    {
        $comment             = 'Загружен. Автоматическая обработка. Обновление (карта существует)';
        $row                 = DataLevel3AR::findOne($id);
        $row->status         = self::STATUS_LOADED;
        $row->comment_parser = $comment;
        $row->update_at      = date("Y-m-d H:i:s");
        $row->save();

        return $comment;
    }

    public static function setStatusStaled(int $id) : string
    {
        $comment             = 'Устарел. Автоматическая обработка. Не нашли карту на странице';
        $row                 = DataLevel3AR::findOne($id);
        $row->status         = self::STATUS_STALED;
        $row->comment_parser = $comment;
        $row->update_at      = date("Y-m-d H:i:s");
        $row->save();

        return $comment;
    }

    public static function getMaxId() : int
    {
        return DataLevel3AR::find()->max('id');
    }

    public static function delete(int $id) : void
    {
        $row         = DataLevel3AR::findOne($id);
        $row->status = self::STATUS_HAND_DELETE;
        $row->save();
    }

    public static function listIdsRentObject() : array
    {
        return DataLevel3AR::find()
            ->select(['id'])
            ->where([
                'status'        => self::STATUS_LOADED,
                'action_object' => MainConst::ACTION_RENT,
            ])
            ->asArray()
            ->all();
    }

    public static function searchRent(array $params) : array
    {
        $conditions = [
            'conditionFlat1'  => '',
            'conditionFlat2'  => '',
            'conditionFlat3'  => '',
            'conditionFlat4'  => '',
            'conditionFlat5'  => '',
            'conditionFlat6'  => '',
            'conditionStudio' => '',
            'conditionRoom'   => '',
        ];

        if (isset($params['flat1amount']) && $params['flat1amount']) {
            $conditions['conditionFlat1'] = "(type_object = '" . MainConst::TYPE_FLAT . "' and rooms='1' and price<=" . $params['flat1amount'] . ")";
        }
        if (isset($params['flat2amount']) && $params['flat2amount']) {
            $conditions['conditionFlat2'] = "(type_object = '" . MainConst::TYPE_FLAT . "' and rooms='2' and price<=" . $params['flat2amount'] . ")";
        }
        if (isset($params['flat3amount']) && $params['flat3amount']) {
            $conditions['conditionFlat3'] = "(type_object = '" . MainConst::TYPE_FLAT . "' and rooms='3' and price<=" . $params['flat3amount'] . ")";
        }
        if (isset($params['flat4amount']) && $params['flat4amount']) {
            $conditions['conditionFlat4'] = "(type_object = '" . MainConst::TYPE_FLAT . "' and rooms='4' and price<=" . $params['flat4amount'] . ")";
        }
        if (isset($params['flat5amount']) && $params['flat5amount']) {
            $conditions['conditionFlat5'] = "(type_object = '" . MainConst::TYPE_FLAT . "' and rooms='5' and price<=" . $params['flat5amount'] . ")";
        }
        if (isset($params['flat6amount']) && $params['flat6amount']) {
            $conditions['conditionFlat6'] = "(type_object = '" . MainConst::TYPE_FLAT . "' and rooms='6' and price<=" . $params['flat6amount'] . ")";
        }
        if (isset($params['studioamount']) && $params['studioamount']) {
            $conditions['conditionStudio'] = "(type_object = '" . MainConst::TYPE_FLAT . "' and rooms='студия' and price<=" . $params['studioamount'] . ")";
        }
        if (isset($params['roomamount']) && $params['roomamount']) {
            $conditions['conditionRoom'] = "(type_object = '" . MainConst::TYPE_ROOM . "' and price<=" . $params['roomamount'] . ")";
        }

        $where = '';
        foreach ($conditions as $condition) {
            if ($condition) {
                if ($where) {
                    $where .= ' or ' . $condition;
                } else {
                    $where .= $condition;
                }
            }
        }
/*
        //для отладки
        echo '<pre>';
        print_r($params);
        print_r($conditions);
        $query = DataLevel3AR::find()
            ->select(['id', 'type_object', 'rooms', 'latitude', 'longitude', 'address', 'price', 'url'])
            ->where([
                'status'        => self::STATUS_LOADED,
                'action_object' => MainConst::ACTION_RENT,
            ])
            ->andWhere($where);
        var_dump($where);
        var_dump($query->prepare(\Yii::$app->db->queryBuilder)->createCommand()->rawSql);
*/
        if ($where) {
            return DataLevel3AR::find()
                ->select(['id', 'type_object', 'rooms', 'latitude', 'longitude', 'address', 'price', 'url'])
                ->where([
                    'status'        => self::STATUS_LOADED,
                    'action_object' => MainConst::ACTION_RENT,
                ])
                ->andWhere($where)
                ->asArray()
                ->all();
        }

        return [];
    }

    public static function countFlatPublishedRent() : int
    {
        return DataLevel3AR::find()
            ->where([
                'type_object'   => MainConst::TYPE_FLAT,
                'status'        => self::STATUS_PUBLISHED,
                'action_object' => MainConst::ACTION_RENT,
            ])
            ->count();
    }

    public static function countFlatLoadedRent() : int
    {
        return DataLevel3AR::find()
            ->where([
                'type_object'   => MainConst::TYPE_FLAT,
                'status'        => self::STATUS_LOADED,
                'action_object' => MainConst::ACTION_RENT,
            ])
            ->count();
    }

    public static function countRoomPublishedRent() : int
    {
        return DataLevel3AR::find()
            ->where([
                'type_object'   => MainConst::TYPE_ROOM,
                'status'        => self::STATUS_PUBLISHED,
                'action_object' => MainConst::ACTION_RENT,
            ])
            ->count();
    }

    public static function countRoomLoadedRent() : int
    {
        return DataLevel3AR::find()
            ->where([
                'type_object'   => MainConst::TYPE_ROOM,
                'status'        => self::STATUS_LOADED,
                'action_object' => MainConst::ACTION_RENT,
            ])
            ->count();
    }

}