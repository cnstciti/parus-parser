<?php
namespace common\models\data_level3;

use backend\models\MyObject;
use backend\models\PolygonEngine;
use common\models\rep\DataResultRep;
use common\models\ar\DataResultAR;
use common\models\ar\MyObjectAR;
use common\models\rep\MyObjectRep;
use DiDom\Document;
use Yii;
use backend\models\PolygonFlat;
use backend\models\PolygonLand;

/**
 *  "Авито. Каталог. Парсер"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel3Avito
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
        //$result = $ret = [];
        try {
            //$this->_photo($document);
            $geo = self::_getGeo($doc, $dataResult);
            $polygon = self::_getPolygon($dataResult);
            if ($polygon->isCrossesWith($geo['latitude'], $geo['longitude'])) {
                $params       = self::_getParams($doc);
                $address      = self::_getAddress($doc);
                $description  = self::_getDescription($doc);
                $price        = self::_getPrice($doc);
                $depositPrice = self::_getDepositPrice($doc);
                $sellerName   = self::_getSellerName($doc);
                //$status       = MyObjectAR::STATUS_LOADED;
                $url          = $dataResult['url'];
                //$type         = $dataResult['type'];
                //$action       = $dataResult['action'];
                //$site         = $dataResult['site'];
                $object       = MyObjectRep::findByUrl($url);
                if ($object['status'] == MyObjectAR::STATUS_PUBLISHED) {
                    MyObjectRep::updatePrice($object['id'], $price, $depositPrice);
                } else {
                    $data = [
                        'latitude'       => $geo['latitude'],
                        'longitude'      => $geo['longitude'],
                        'typeHouse'      => $params['typeHouse'],
                        'floor'          => $params['floor'],
                        'numberOfFloors' => $params['numberOfFloors'],
                        'rooms'          => $params['rooms'],
                        'totalArea'      => $params['totalArea'],
                        'kitchenArea'    => $params['kitchenArea'],
                        'livingArea'     => $params['livingArea'],
                        'address'        => $address,
                        'description'    => $description,
                        'price'          => $price,
                        'depositPrice'   => $depositPrice,
                        'sellerName'     => $sellerName,
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
                        //$myObject->myYearOfConstruction = $ret['yearOfConstruction'];
                        $params['rooms'],
                        $params['totalArea'],
                        $params['kitchenArea'],
                        $params['livingArea'],
                        //$myObject->landArea = $ret['landArea'];
                        //$myObject->myViewWindow = $ret['viewWindow'];
                        //$myObject->myBalcony = $ret['balcony'];
                        $address,
                        //$myObject->metroStation1 = $ret['metroStation1'];
                        //$myObject->metroStationColor1 = $ret['metroStationColor1'];
                        //$myObject->metroStation2 = $ret['metroStation2'];
                        //$myObject->metroStationColor2 = $ret['metroStationColor2'];
                        //$myObject->metroStation3 = $ret['metroStation3'];
                        //$myObject->metroStationColor3 = $ret['metroStationColor3'];
                        $description,
                        $price,
                        $depositPrice,
                        $sellerName,
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
/*
----- geo
            $latitude = $document->first('div.b-search-map');
            if (!$latitude) {
                $result->status = DataResultAR::STATUS_STALED;
                $result->save();
                return [
                    'error' => 2,
                    'errorMsg' => 'Ссылка устарела',
                    'data' => [
                        'DataResultAR' => $result,
                        'MyObjectAR' => [],
                    ],
                ];
            }
            $latitude = $document->first('div.b-search-map')->attr('data-map-lat');
            $longitude = $document->first('div.b-search-map')->attr('data-map-lon');
----- geo
---- polygon
            if ($result->type == MyObjectAR::TYPE_FLAT || $result->type == MyObjectAR::TYPE_ROOM) {
                $myPolygon = new PolygonEngine($this->_polygonFlat);
            } else {
                $myPolygon = new PolygonEngine($this->_polygonLand);
            }
---- polygon
*/
/*
//            if ($myPolygon->isCrossesWith($latitude, $longitude)) {
                $ret['latitude'] = $latitude;
                $ret['longitude'] = $longitude;
*/
/*
------ params
                $params = $document->find('li.item-params-list-item');
                if (empty($params)) {
                    $params = $document->find('.item-params span');
                }
                foreach ($params as $object) {
                    $text = $object->text();

                    // для квартир и комнат
                    if (!$ret['typeHouse'] && strpos($text, 'Тип дома') !== false) {
                        $ret['typeHouse'] = trim(substr(strstr($text, ':'), 1));
                        continue;
                    }
                    // для загородной недвижимости
                    if (!$ret['typeHouse'] && strpos($text, 'Материал стен') !== false) {
                        $ret['typeHouse'] = trim(substr(strstr($text, ':'), 1));
                        continue;
                    }

                    // для загородной недвижимости
                    if (!$ret['numberOfFloors'] && strpos($text, 'Этажей в доме') !== false) {
                        $ret['numberOfFloors'] = preg_replace('~[^0-9]~Uuis', '', $text);
                        continue;
                    }
                    // для квартир и комнат
                    if (!$ret['floor'] && strpos($text, 'Этаж') !== false) {
                        preg_match('~[:](.*)из~Uuis', $text, $res);
                        if (!empty($res)) {
                            $ret['floor'] = preg_replace('~[^0-9]~Uuis', '', $res[1]);
                        }
                        $ret['numberOfFloors'] = preg_replace('~[^0-9]~Uuis', '', strstr($text, 'из'));
                        continue;
                    }

                    // для квартир
                    if (!$ret['rooms'] && strpos($text, 'Количество комнат') !== false) {
                        if (strpos($text, 'своб. планировка')) {
                            $ret['rooms'] = 'свободная планировка';
                        } else {
                            $ret['rooms'] = trim(substr(strstr($text, ':'), 1));
                        }
                        continue;
                    }
                    // для комнат
                    if (!$ret['rooms'] && strpos($text, 'Комнат в квартире') !== false) {
                        if (strpos($text, '> 9')) {
                            $ret['rooms'] = 'свободная планировка';
                        } else {
                            $ret['rooms'] = trim(substr(strstr($text, ':'), 1));
                        }
                        continue;
                    }
                    // для загородной недвижимости
                    if (!$ret['rooms'] && strpos($text, 'Вид объекта') !== false) {
                        $ret['rooms'] = trim(substr(strstr($text, ':'), 1));
                        continue;
                    }

                    // для квартир
                    if (!$ret['totalArea'] && strpos($text, 'Общая площадь') !== false) {
                        $ret['totalArea'] = preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
                        continue;
                    }
                    // для комнат
                    if (!$ret['totalArea'] && strpos($text, 'Площадь комнаты') !== false) {
                        $ret['totalArea'] = preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
                        continue;
                    }
                    // для загородной недвижимости
                    if (!$ret['totalArea'] && strpos($text, 'Площадь дома') !== false) {
                        $ret['totalArea'] = preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
                        continue;
                    }

                    if (!$ret['livingArea'] && strpos($text, 'Жилая площадь') !== false) {
                        $ret['livingArea'] = preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
                        continue;
                    }
                    if (!$ret['kitchenArea'] && strpos($text, 'Площадь кухни') !== false) {
                        $ret['kitchenArea'] = preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
                        continue;
                    }

                    // для загородной недвижимости
                    if (!$ret['landArea'] && strpos($text, 'Площадь участка') !== false) {
                        $ret['landArea'] = trim(preg_replace('~[^0-9(.|,)]~Uuis', '', $text), '.');
                        continue;
                    }
                    // для участков
                    if (!$ret['landArea'] && strpos($text, 'Площадь') !== false) {
                        $ret['landArea'] = trim(preg_replace('~[^0-9(.|,)]~Uuis', '', $text), '.');
                        continue;
                    }

                    if (!$ret['viewWindow'] && strpos($text, 'Вид из окна') !== false) {
                        $viewWindow = trim(substr(strstr($text, ':'), 1));
                        $viewWindow = explode(', ', $viewWindow);
                        $ret['viewWindow'] = serialize((new ViewWindow)->selectedData($viewWindow));
                        continue;
                    }
                    if (!$ret['balcony'] && strpos($text, 'Балкон или лоджия') !== false) {
                        $ret['balcony'] = trim(substr(strstr($text, ':'), 1));
                        continue;
                    }
                    if (!$ret['yearOfConstruction'] && strpos($text, 'Год постройки') !== false) {
                        $ret['yearOfConstruction'] = trim(substr(strstr($text, ':'), 1));
                        continue;
                    }
                }
------ params
*/
/*
                // для участков
                if (!$ret['typeLand']) {
                    $ret['typeLand'] = $this->_typeLand($document);
                }
*/
/*
                $ret['address'] = $this->_address($document);
*/
/*
                $stationsColor = $document->find('.item-address-georeferences-item-icons_icon');
                if ($stationsColor) {
                    $stations = $document->find('.item-address-georeferences-item');
                    foreach ($stations as $z => $object) {
                        $text = $object->text();
                        switch ($z) {
                            case 0:
                                $ret['metroStation1'] = $text;
                                $ret['metroStationColor1'] = $stationsColor[$z]->attr('style');
                                break;
                            case 1:
                                $ret['metroStation2'] = $text;
                                $ret['metroStationColor2'] = $stationsColor[$z]->attr('style');
                                break;
                            case 2:
                                $ret['metroStation3'] = $text;
                                $ret['metroStationColor3'] = $stationsColor[$z]->attr('style');
                                break;
                        }
                    }
                }
*/
/*
                if ($document->first('.item-description-text')) {
                    $ret['description'] = trim($document->first('.item-description-text')->text());
                }
                if (!$ret['description'] && $document->first('.item-description-html')) {
                    $ret['description'] = trim($document->first('.item-description-html')->text());
                }
                $ret['description'] = $this->_removeEmoji($ret['description']);
*/
/*
                $ret['price']       = preg_replace('~[^0-9]~Uuis', '', $document->first('.js-item-price')->text());
                if ($document->first('.item-price-sub-price')) {
                    $ret['depositPrice'] = preg_replace('~[^0-9]~Uuis', '', $document->first('.item-price-sub-price')->text());
                }
*/
/*
                $ret['sellerName'] = trim($document->first('.seller-info-name')->text());
*/
/*
                $ret['status']     = MyObjectAR::STATUS_LOADED;
                $ret['url']        = $result->url;
                $ret['type']       = $result->type;
                $ret['action']     = $result->action;
                $ret['site']       = $result->site;
*/
/*
                //$url = str_replace( '.', '', str_replace( '_', '', $ret['url']));
                $myObject = MyObjectAR::find()
                    ->where(['like', 'url', $ret['url']])
                    //->where(['like', "REPLACE(REPLACE(url, '_', ''), '.', '')", $url])
                    ->one();

                if (!$myObject) {
                    $myObject = new MyObjectAR;
                }
*/
/*
                // Для опубликованных меняем только цену
//                if ($myObject->status == MyObjectAR::STATUS_PUBLISHED) {
//                    $myObject->newPrice = $ret['price'];
//                } else {
                    $myObject->myLatitude = $ret['latitude'];
                    $myObject->myLongitude = $ret['longitude'];
                    $myObject->myTypeHouse = $ret['typeHouse'];
                    $myObject->typeLand = $ret['typeLand'];
                    $myObject->myFloor = $ret['floor'];
                    $myObject->myNumberOfFloors = $ret['numberOfFloors'];
                    $myObject->myYearOfConstruction = $ret['yearOfConstruction'];
                    $myObject->myRooms = $ret['rooms'];
                    $myObject->myTotalArea = $ret['totalArea'];
                    $myObject->myLivingArea = $ret['livingArea'];
                    $myObject->myKitchenArea = $ret['kitchenArea'];
                    $myObject->landArea = $ret['landArea'];
                    $myObject->myViewWindow = $ret['viewWindow'];
                    $myObject->myBalcony = $ret['balcony'];
                    $myObject->address = $ret['address'];
                    //$myObject->myAddress          = $ret['address'];
                    $myObject->metroStation1 = $ret['metroStation1'];
                    $myObject->metroStationColor1 = $ret['metroStationColor1'];
                    $myObject->metroStation2 = $ret['metroStation2'];
                    $myObject->metroStationColor2 = $ret['metroStationColor2'];
                    $myObject->metroStation3 = $ret['metroStation3'];
                    $myObject->metroStationColor3 = $ret['metroStationColor3'];
                    $myObject->description = $ret['description'];
                    $myObject->myPrice = $ret['price'];
                    $myObject->myDepositPrice = $ret['depositPrice'];
                    $myObject->mySellerName1 = $ret['sellerName'];
                    $myObject->status = $ret['status'];
                    $myObject->url = $ret['url'];
                    $myObject->myTypeObject = $ret['type'];
                    $myObject->myActionObject = $ret['action'];
                    $myObject->site = $ret['site'];
                }
                $myObject->save();

                $result->status = DataResultAR::STATUS_PROCESSED;
                $result->save();
   //         } else {
   //             $result->status = DataResultAR::STATUS_NOT_OUR_OBJECT;
   //             $result->save();
   //         }
        }
*/
    }

    private static function _getGeo(Document $doc, array $dataResult) : array
    {
        if (!$doc->first('div.b-search-map')) {
            DataResultRep::updateStatus($dataResult['id'], DataResultAR::STATUS_STALED);
            throw new \Exception(self::MSG_ERROR_MAP, self::ERROR_MAP);
            /*
            $result->status = DataResultAR::STATUS_STALED;
            $result->save();
            return [
                'error' => 2,
                'errorMsg' => 'Ссылка устарела',
                'data' => [
                    'DataResultAR' => $result,
                    'MyObjectAR' => [],
                ],
            ];
            */
        }
        return [
            'latitude'  => $doc->first('div.b-search-map')->attr('data-map-lat'),
            'longitude' => $doc->first('div.b-search-map')->attr('data-map-lon'),
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
                $floor = self::_getFloor($text);
                $ret['floor'] = $floor['floor'];
                $ret['numberOfFloors'] = $floor['numberOfFloors'];
            }
            if (!$ret['rooms']) {
                $ret['rooms'] = self::_getRooms($text);
            }
            if (!$ret['totalArea']) {
                $ret['totalArea'] = self::_getTotalArea($text);
            }
            if (!$ret['kitchenArea']) {
                $ret['kitchenArea'] = self::_getKitchenArea($text);
            }
            if (!$ret['livingArea']) {
                $ret['livingArea'] = self::_getLivingArea($text);
            }

        }
        $r=0;
        return $ret;
        /*
            // для загородной недвижимости
            if (!$ret['typeHouse'] && strpos($text, 'Материал стен') !== false) {
                $ret['typeHouse'] = trim(substr(strstr($text, ':'), 1));
                continue;
            }
            // для загородной недвижимости
            if (!$ret['numberOfFloors'] && strpos($text, 'Этажей в доме') !== false) {
                $ret['numberOfFloors'] = preg_replace('~[^0-9]~Uuis', '', $text);
                continue;
            }

            // для загородной недвижимости
            if (!$ret['landArea'] && strpos($text, 'Площадь участка') !== false) {
                $ret['landArea'] = trim(preg_replace('~[^0-9(.|,)]~Uuis', '', $text), '.');
                continue;
            }
            // для участков
            if (!$ret['landArea'] && strpos($text, 'Площадь') !== false) {
                $ret['landArea'] = trim(preg_replace('~[^0-9(.|,)]~Uuis', '', $text), '.');
                continue;
            }
            if (!$ret['viewWindow'] && strpos($text, 'Вид из окна') !== false) {
                $viewWindow = trim(substr(strstr($text, ':'), 1));
                $viewWindow = explode(', ', $viewWindow);
                $ret['viewWindow'] = serialize((new ViewWindow)->selectedData($viewWindow));
                continue;
            }
            if (!$ret['balcony'] && strpos($text, 'Балкон или лоджия') !== false) {
                $ret['balcony'] = trim(substr(strstr($text, ':'), 1));
                continue;
            }
            if (!$ret['yearOfConstruction'] && strpos($text, 'Год постройки') !== false) {
                $ret['yearOfConstruction'] = trim(substr(strstr($text, ':'), 1));
                continue;
            }
        */
    }

    private static function _getParamsValue(Document $doc) : array
    {
        $params = $doc->find('li.item-params-list-item');
        if (empty($params)) {
            $params = $doc->find('.item-params span');
        }
        return $params;
    }

    private static function _getTypeHouse(string $text) : string
    {
        if (strpos($text, 'Тип дома') !== false) {
            return trim(substr(strstr($text, ':'), 1));
        }
        return '';
    }

    private static function _getFloor(string $text) : array
    {
        $ret = [];
        if (strpos($text, 'Этаж') !== false) {
            preg_match('~[:](.*)из~Uuis', $text, $res);
            if (!empty($res)) {
                $ret['floor'] = preg_replace('~[^0-9]~Uuis', '', $res[1]);
            }
            $ret['numberOfFloors'] = preg_replace('~[^0-9]~Uuis', '', strstr($text, 'из'));
        }
        return $ret;
    }

    private static function _getRooms(string $text) : string
    {
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
        if (strpos($text, 'Вид объекта') !== false) {
            return trim(substr(strstr($text, ':'), 1));
        }
        return '';
    }

    private static function _getTotalArea(string $text) : string
    {
        // для квартир
        if (strpos($text, 'Общая площадь') !== false) {
            return preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
        }
        // для комнат
        if (strpos($text, 'Площадь комнаты') !== false) {
            return preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
        }
        // для загородной недвижимости
        if (strpos($text, 'Площадь дома') !== false) {
            return preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
        }
        return '';
    }

    private static function _getKitchenArea(string $text) : string
    {
        if (strpos($text, 'Площадь кухни') !== false) {
            return preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
        }
        return '';
    }

    private static function _getLivingArea(string $text) : string
    {
        if (strpos($text, 'Жилая площадь') !== false) {
            return preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
        }
        return '';
    }

    private static function _getAddress(Document $doc) : string
    {
        $ret = trim($doc->first('*[^itemprop=address]')->text());
        if (strpos($ret, "\n") !== false) {
            $ret = substr($ret, 0, strpos($ret, "\n"));
        }
        return trim($ret);
    }

    private static function _getDescription(Document $doc) : string
    {
        $ret = '';
        if ($doc->first('.item-description-text')) {
            $ret = trim($doc->first('.item-description-text')->text());
        }
        if (!$ret && $doc->first('.item-description-html')) {
            $ret = trim($doc->first('.item-description-html')->text());
        }
        return self::_removeEmoji($ret);
    }

    private static function _removeEmoji($value)
    {
        return preg_replace('/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FF})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FE})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FD})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FC})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FB})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6F9}\x{1F910}-\x{1F93A}\x{1F93C}-\x{1F93E}\x{1F940}-\x{1F945}\x{1F947}-\x{1F970}\x{1F973}-\x{1F976}\x{1F97A}\x{1F97C}-\x{1F9A2}\x{1F9B0}-\x{1F9B9}\x{1F9C0}-\x{1F9C2}\x{1F9D0}-\x{1F9FF}]/u', '', $value);
    }

    private static function _getPrice(Document $doc) : string
    {
        return preg_replace('~[^0-9]~Uuis', '', $doc->first('.js-item-price')->text());
    }

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

}
