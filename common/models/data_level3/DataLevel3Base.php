<?php
namespace common\models\data_level3;

use DiDom\Document;
use common\models\data_level3\exception\GeoException;
use common\models\data_level3\exception\BaseException;
use common\models\MainConst;
use common\models\PolygonEngine;
use common\models\PolygonFlat;
use common\models\PolygonLand;
use common\models\rep\DataLevel2Rep;
use common\models\rep\DataLevel3Rep;
use common\models\rep\DataLevel3LogRep;
use common\models\Parus;

/**
 *
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
abstract class DataLevel3Base
{
    const CODE_NO_ERROR = 0;
    const MSG_NO_ERROR = '';


    abstract public static function getGeo(Document $doc) : array;
    abstract protected static function getTypeHouse(Document $doc) : string;
    abstract protected static function getFloor(Document $doc) : string;
    abstract protected static function getNumberOfFloors(Document $doc) : string;
    abstract protected static function getRooms(Document $doc) : string;
    abstract protected static function getTotalArea(Document $doc) : string;
    abstract protected static function getKitchenArea(Document $doc) : string;
    abstract protected static function getLivingArea(Document $doc) : string;
    abstract protected static function getAddress(Document $doc) : string;
    abstract protected static function getDescription(Document $doc) : string;
    abstract protected static function getPrice(Document $doc) : string;
    abstract protected static function getDepositPrice(Document $doc) : string;
    abstract protected static function getSellerName(Document $doc) : string;
    abstract protected static function getMetroStation1(Document $doc) : string;
    abstract protected static function getMetroStation2(Document $doc) : string;
    abstract protected static function getMetroStation3(Document $doc) : string;


    final public static function parser(Document $doc, array $dataLevel2, $isLog=false) : array
    {
        try {
            $geo     = static::getGeo($doc);
            $price   = static::getPrice($doc);
            $polygon = self::_getPolygon($dataLevel2['type']);

            if ($polygon->isCrossesWith($geo['latitude'], $geo['longitude']) && $price >= 10000) {
                // ???????? ???????????? ?? ?????????? ???????? ???????????????????????? ?? ?????????????????? >= 10 ??.??.
                DataLevel2Rep::updateStatus($dataLevel2['id'], DataLevel2Rep::STATUS_PROCESSED);
                $status = DataLevel3Rep::STATUS_LOADED;
            } else {
                // ?????? ???? "??????" ????????????
                DataLevel2Rep::updateStatus($dataLevel2['id'], DataLevel2Rep::STATUS_NOT_OUR_OBJECT);
                $status = DataLevel3Rep::STATUS_NOT_OUR_OBJECT;
            }

            $dataLevel3 = DataLevel3Rep::findByUrl($dataLevel2['url']);
            if ($dataLevel3['status'] == DataLevel3Rep::STATUS_PUBLISHED) {
                DataLevel3Rep::updatePrice($dataLevel3['id'], $price, static::getDepositPrice($doc));
            } else {
                $insertData = [
                    'latitude'       => $geo['latitude'],
                    'longitude'      => $geo['longitude'],
                    'typeHouse'      => static::getTypeHouse($doc),
                    'floor'          => static::getFloor($doc),
                    'numberOfFloors' => static::getNumberOfFloors($doc),
                    'rooms'          => static::getRooms($doc),
                    'totalArea'      => static::getTotalArea($doc),
                    'kitchenArea'    => static::getKitchenArea($doc),
                    'livingArea'     => static::getLivingArea($doc),
                    'address'        => static::getAddress($doc),
                    'metroStation1'  => static::getMetroStation1($doc),
                    'metroStation2'  => static::getMetroStation2($doc),
                    'metroStation3'  => static::getMetroStation3($doc),
                    'description'    => static::getDescription($doc),
                    'price'          => $price,
                    'depositPrice'   => static::getDepositPrice($doc),
                    'sellerName'     => static::getSellerName($doc),
                    'status'         => $status,
                    'url'            => $dataLevel2['url'],
                    'type'           => $dataLevel2['type'],
                    'action'         => $dataLevel2['action'],
                    'site'           => $dataLevel2['site'],
                ];
                $dataLevel3Id = DataLevel3Rep::insert($insertData);
                if ($status == DataLevel3Rep::STATUS_LOADED) {
                    // ???????????? ?????????????? ???? ???????????????? "????????????????" ???????????????????? ???????????????? ????????????
                    Parus::clientRentAddOn($dataLevel3Id);
                }
                if ($isLog) {
                    //$date = new \DateTime();
                    DataLevel3LogRep::insert([
                        'level2_id' => $dataLevel2['id'],
                        'level3_id' => $dataLevel3Id,
                        'url' => $dataLevel2['url'],
                        //'createdAt' => $date->format('Y-m-d H:i:s'),
                    ]);
                }
            }
/*
if ($polygon->isCrossesWith($geo['latitude'], $geo['longitude'])) {
    //$status       = MyObjectAR::STATUS_LOADED;
    //$type         = $dataResult['type'];
    //$action       = $dataResult['action'];
    //$site         = $dataResult['site'];
    if ($object['status'] == MyObjectAR::STATUS_PUBLISHED) {
        MyObjectRep::updatePrice($object['id'], $price, $depositPrice);
    } else {

        MyObject::add($data);
*/
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
/*
                }
                DataResultRep::updateStatus($dataResult['id'], DataResultAR::STATUS_PROCESSED);
            } else {
                DataResultRep::updateStatus($dataResult['id'], DataResultAR::STATUS_NOT_OUR_OBJECT);
            }
*/
            $ret = [
                'error' => [
                    'code'        => self::CODE_NO_ERROR,
                    'description' => self::MSG_NO_ERROR,
                ],
                'result' => [
                    'dataLevel2' => $dataLevel2,
                    'dataLevel3' => $dataLevel3,
                    'insertData' => $insertData,
                    'status'     => $status,
                ],
            ];
        } catch (GeoException $e) {
            DataLevel2Rep::updateStatus($dataLevel2['id'], DataLevel2Rep::STATUS_STALED);
            $ret = [
                'error' => [
                    'code'        => $e->getCode(),
                    'description' => $e->getMessage(),
                ],
                'result' => [],
            ];
        } catch (BaseException $e) {
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

    private static function _getPolygon(string $type) : PolygonEngine
    {
        if ($type == MainConst::TYPE_FLAT || $type == MainConst::TYPE_ROOM) {
            return new PolygonEngine(PolygonFlat::POLYGON);
        }
        return new PolygonEngine(PolygonLand::POLYGON);
    }

        /*
            // ?????? ???????????????????? ????????????????????????
            if (!$ret['typeHouse'] && strpos($text, '???????????????? ????????') !== false) {
                $ret['typeHouse'] = trim(substr(strstr($text, ':'), 1));
                continue;
            }
            // ?????? ???????????????????? ????????????????????????
            if (!$ret['numberOfFloors'] && strpos($text, '???????????? ?? ????????') !== false) {
                $ret['numberOfFloors'] = preg_replace('~[^0-9]~Uuis', '', $text);
                continue;
            }

            // ?????? ???????????????????? ????????????????????????
            if (!$ret['landArea'] && strpos($text, '?????????????? ??????????????') !== false) {
                $ret['landArea'] = trim(preg_replace('~[^0-9(.|,)]~Uuis', '', $text), '.');
                continue;
            }
            // ?????? ????????????????
            if (!$ret['landArea'] && strpos($text, '??????????????') !== false) {
                $ret['landArea'] = trim(preg_replace('~[^0-9(.|,)]~Uuis', '', $text), '.');
                continue;
            }
            if (!$ret['viewWindow'] && strpos($text, '?????? ???? ????????') !== false) {
                $viewWindow = trim(substr(strstr($text, ':'), 1));
                $viewWindow = explode(', ', $viewWindow);
                $ret['viewWindow'] = serialize((new ViewWindow)->selectedData($viewWindow));
                continue;
            }
            if (!$ret['balcony'] && strpos($text, '???????????? ?????? ????????????') !== false) {
                $ret['balcony'] = trim(substr(strstr($text, ':'), 1));
                continue;
            }
            if (!$ret['yearOfConstruction'] && strpos($text, '?????? ??????????????????') !== false) {
                $ret['yearOfConstruction'] = trim(substr(strstr($text, ':'), 1));
                continue;
            }
        */

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
        if (strpos($title, '??????') !== false) {
            return '??????';
        }
        if (strpos($title, '??????') !== false) {
            return '??????';
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
