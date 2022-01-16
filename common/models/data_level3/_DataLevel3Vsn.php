<?php
namespace common\models\data_level3;

use backend\models\params\ViewWindow;
use backend\models\PolygonEngine;
use common\models\rep\DataResultRep;
use common\models\ar\DataResultAR;
use common\models\ar\MyObjectAR;
use common\models\rep\MyObjectRep;
use DiDom\Document;
use Exception;
use Yii;
use backend\models\PolygonFlat;
use backend\models\PolygonLand;
use backend\models\MyObject;

/**
 *  "Авито. Каталог. Парсер"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel3Vsn
{
    const NO_ERROR = 0;
    const MSG_NO_ERROR = '';
    const ERROR_MAP = 1;
    const MSG_ERROR_MAP = 'Данные не прочитаны. Ссылка устарела.';
    //const ERROR_DIDOM = 2;
    //const MSG_ERROR_DIDOM = 'Ошибка чтения DiDom\Document.';


    /**
     * Возвращает массив ссылок страницы каталога
     *
     * @return array
     */
    public static function parser(Document $doc, array $dataResult) : array
    {
        //$result =$ret = [];

        try {
            //$this->_photo($document);
            $geo = self::_getGeo($doc, $dataResult);
            $polygon = self::_getPolygon($dataResult);
            if ($polygon->isCrossesWith($geo['latitude'], $geo['longitude'])) {
                $params       = self::_getParams($doc);
                //$address      = self::_getAddress($doc);
                $description  = self::_getDescription($doc);
                $price        = self::_getPrice($doc);
                //$depositPrice = self::_getDepositPrice($doc);
                //$sellerName   = self::_getSellerName($doc);
                //$status       = MyObjectAR::STATUS_LOADED;
                $url          = $dataResult['url'];
                ///$type         = $dataResult['type'];
                //$action       = $dataResult['action'];
                //$site         = $dataResult['site'];
                $object       = MyObjectRep::findByUrl($url);
                if ($object['status'] == MyObjectAR::STATUS_PUBLISHED) {
                    MyObjectRep::updatePrice($object['id'], $price, 0);
                } else {
                    $data = [
                        'latitude'       => $geo['latitude'],
                        'longitude'      => $geo['longitude'],
                        'typeHouse'      => $params['typeHouse'],
                        'floor'          => $params['floor'],
                        'numberOfFloors' => $params['numberOfFloors'],
                        'rooms'          => $params['rooms'],
                        'totalArea'      => $params['totalArea'],
                        'kitchenArea'    => '',
                        'livingArea'     => '',
                        'address'        => $params['address'],
                        'description'    => $description,
                        'price'          => $price,
                        'depositPrice'   => '',
                        'sellerName'     => '',
                        'status'         => MyObjectAR::STATUS_LOADED,
                        'url'            => $url,
                        'type'           => $dataResult['type'],
                        'action'         => $dataResult['action'],
                        'site'           => $dataResult['site'],
                    ];
                    MyObject::add($data);
                    /*
                    MyObjectRep::insert(
                        $geo['latitude'],
                        $geo['longitude'],
                        $params['typeHouse'],
                        //$myObject->typeLand = $ret['typeLand'];
                        $params['floor'],
                        $params['numberOfFloors'],
                        //$myObject->myNumberOfFloors = $ret['numberOfFloors'];
                        //$myObject->myYearOfConstruction = $ret['yearOfConstruction'];
                        $params['rooms'],
                        $params['totalArea'],
                        '',
                        '',
                        //$myObject->landArea = $ret['landArea'];
                        //$myObject->myViewWindow = $ret['viewWindow'];
                        //$myObject->myBalcony = $ret['balcony'];
                        $params['address'],
                        //$myObject->metroStation1 = $ret['metroStation1'];
                        //$myObject->metroStationColor1 = $ret['metroStationColor1'];
                        //$myObject->metroStation2 = $ret['metroStation2'];
                        //$myObject->metroStationColor2 = $ret['metroStationColor2'];
                        //$myObject->metroStation3 = $ret['metroStation3'];
                        //$myObject->metroStationColor3 = $ret['metroStationColor3'];
                        $description,
                        $price,
                        '',
                        '',
                        $status,
                        $url,
                        $type,
                        $action,
                        $site
                    );
                    */
                }
                DataResultRep::updateStatus($dataResult['id'], DataResultAR::STATUS_PROCESSED);
            } else {
                DataResultRep::updateStatus($dataResult['id'], DataResultAR::STATUS_NOT_OUR_OBJECT);
            }
            $ret = [
                'error' => [
                    'code'        => self::NO_ERROR,
                    'description' => self::MSG_NO_ERROR,
                ],
                'result' => [
                    'DataResultAR' => $dataResult,
                    'MyObjectAR'   => $object,
                ],
            ];
        } catch (\Exception $e) {
            $ret = [
                'error' => [
                    'code'        => $e->getCode(),
                    'description' => $e->getMessage(),
                ],
                'result' => [],
            ];
        }

        return $ret;
    }

    private static function _getGeo(Document $doc, array $dataResult) : array
    {
        if (!$doc->first('div.map')) {
            DataResultRep::updateStatus($dataResult['id'], DataResultAR::STATUS_STALED);
            throw new \Exception(self::MSG_ERROR_MAP, self::ERROR_MAP);
        }
        return [
            'latitude'  => $doc->first('div.map')->attr('data-latitude'),
            'longitude' => $doc->first('div.map')->attr('data-longitude'),
        ];
    }

    private static function _getPolygon(array $dataResult) : PolygonEngine
    {
        if ($dataResult['type'] == MyObjectAR::TYPE_FLAT || $dataResult['type'] == MyObjectAR::TYPE_ROOM) {
            return new PolygonEngine(PolygonFlat::POLYGON);
        }
        return new PolygonEngine(PolygonLand::POLYGON);
    }

    private static function _getParams(Document $doc) : array
    {
        $ret = [];
        $params = self::_getParamsValue($doc);
        foreach ($params as $param) {
            $text = $param->text();
            if (!$ret['typeHouse']) {
                $ret['typeHouse'] = self::_getTypeHouse($text);
            }
            if (!$ret['floor']) {
                $ret['floor'] = self::_getFloor($text);
            }
            if (!$ret['numberOfFloors']) {
                $ret['numberOfFloors'] = self::_getNumberOfFloors($text);
            }
            if (!$ret['totalArea']) {
                $ret['totalArea'] = self::_getTotalArea($text);
            }
            if (!$ret['address']) {
                $ret['address'] = self::_getAddress($text);
            }
            if (!$ret['rooms']) {
                $ret['rooms'] = self::_getRooms($text);
            }
        }
        $r=0;
        return $ret;
    }

    private static function _getParamsValue(Document $doc) : array
    {
        return $doc->find('.apartment-desc__item');
    }

    private static function _getTypeHouse(string $text) : string
    {
        if (strpos($text, 'тип дома') !== false) {
            $text = trim(substr(strstr($text, ':'), 1));
            $text = mb_substr($text, 0, -4);
            return $text;
        }
        return '';
    }

    private static function _getFloor(string $text) : string
    {
        if (strpos($text, 'этаж') !== false) {
            return trim(substr(strstr($text, ':'), 1));
        }
        return '';
    }

    private static function _getNumberOfFloors(string $text) : string
    {
        if (strpos($text, 'этажей') !== false) {
            return trim(substr(strstr($text, ':'), 1));
        }
        return '';
    }

    private static function _getTotalArea(string $text) : string
    {
        if (strpos($text, 'площадь') !== false) {
            $text = trim(substr(strstr($text, ':'), 1));
            $text = trim(strstr($text, 'м.кв.',true));
            return preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
        }
        return '';
    }

    private static function _getAddress(string $text) : string
    {
        if (strpos($text, 'адрес') !== false) {
            return trim(substr(strstr($text, ':'), 1));
        }
        return '';
    }

    private static function _getRooms(string $text) : string
    {
        /*
        // для квартир
        if (strpos($text, 'Количество комнат') !== false) {
            if (strpos($text, 'своб. планировка')) {
                return 'свободная планировка';
            }
            return trim(substr(strstr($text, ':'), 1));
        }
        // для комнат
        if (strpos($text, 'Комнат в квартире') !== false) {
            if (strpos($text, '> 9')) {
                return 'свободная планировка';
            }
            return trim(substr(strstr($text, ':'), 1));
        }
        // для загородной недвижимости
        */
        if (strpos($text, 'комнат') !== false) {
            return trim(substr(strstr($text, ':'), 1));
        }
        return '';
    }

    private static function _getDescription(Document $doc) : string
    {
        return $doc->first('.col-xs-12 .h4')->nextSibling()->text();
    }

    private static function _getPrice(Document $doc) : string
    {
        return trim(preg_replace('~[^0-9]~Uuis', '', $doc->first('*[^itemprop=price]')->text()));
    }
/*
    private static function _getDepositPrice(Document $doc) : string
    {
        $ret = '';
        if ($doc->first('.item-price-sub-price')) {
            $ret = preg_replace('~[^0-9]~Uuis', '', $doc->first('.item-price-sub-price')->text());
        }
        return $ret;
    }

    private static function _getSellerName(Document $doc) : string
    {
        return trim($doc->first('.seller-info-name')->text());
    }

/*
    private static function _getObject(string $url)
    {
        $object = ObjectRep::findByUrl($url);
        if (empty($object)) {
            $object = new ObjectAR;
            $object->save();
        }
        return $object;
    }
*/

/*
    private function _typeLand(Document $document)
    {
        $title = $document->first('.title-info-title-text');
        if (strpos($title, 'ИЖС') !== false) {
            return 'ИЖС';
        }
        if (strpos($title, 'СНТ') !== false) {
            return 'СНТ';
        }

        return '';
    }

    private function _photo(Document $document)
    {
        $photos = $document->first('.gallery-extended-imgs-wrapper')->html();
        preg_match_all('~(data-url=")(.*)(["])~Uuis', $photos, $res);

        foreach ($res[2] as $photo) {
            $destFile = $this->_getRandomFileName(Yii::getAlias('@photoPathAvito'), 'jpeg');
            copy($photo, $destFile);
        }

        return '';
    }

    public function _getRandomFileName(string $path, string $extension='') : string
    {
        $extension = $extension ? '.' . $extension : '';
        $path = $path ? $path . '/' : '';

        do {
            $name = md5(microtime() . rand(0, 9999));
            $file = $path . $name . $extension;
        } while (file_exists($file));

        return $file;
    }
*/
}
