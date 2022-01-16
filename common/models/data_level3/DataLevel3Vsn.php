<?php
namespace common\models\data_level3;

use DiDom\Document;
use common\models\data_level3\exception\GeoException;

use backend\models\MyObject;
use backend\models\PolygonEngine;
use common\models\rep\DataResultRep;
use common\models\ar\DataResultAR;
use common\models\ar\MyObjectAR;
use common\models\rep\MyObjectRep;
use Yii;
use backend\models\PolygonFlat;
use backend\models\PolygonLand;

/**
 *
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel3Vsn extends DataLevel3Base
{
    const CODE_ERROR_MAP = 1;
    const MSG_ERROR_MAP = 'Данные не прочитаны. Ссылка устарела.';


    public static function getGeo(Document $doc) : array
    {
        if (!$doc->first('div.map')) {
            throw new GeoException(self::MSG_ERROR_MAP, self::CODE_ERROR_MAP);
        }
        return [
            'latitude'  => $doc->first('div.map')->attr('data-latitude'),
            'longitude' => $doc->first('div.map')->attr('data-longitude'),
        ];
    }

    private static function _getParamsValue(Document $doc) : array
    {
        return $doc->find('.apartment-desc__item');
    }

    protected static function getTypeHouse(Document $doc) : string
    {
        $params = self::_getParamsValue($doc);
        foreach ($params as $param) {
            $text = $param->text();
            if (strpos($text, 'тип дома') !== false) {
                $text = trim(substr(strstr($text, ':'), 1));
                return mb_substr($text, 0, -4);
            }
        }

        return '';
    }

    protected static function getFloor(Document $doc) : string
    {
        $params = self::_getParamsValue($doc);
        foreach ($params as $param) {
            $text = $param->text();
            if (strpos($text, 'этаж') !== false) {
                return trim(substr(strstr($text, ':'), 1));
            }
        }

        return '';
    }

    protected static function getNumberOfFloors(Document $doc) : string
    {
        $params = self::_getParamsValue($doc);
        foreach ($params as $param) {
            $text = $param->text();
            if (strpos($text, 'этажей') !== false) {
                return trim(substr(strstr($text, ':'), 1));
            }
        }

        return '';
    }

    protected static function getRooms(Document $doc) : string
    {
        $params = self::_getParamsValue($doc);
        foreach ($params as $param) {
            $text = $param->text();
            if (strpos($text, 'комнат') !== false) {
                return trim(substr(strstr($text, ':'), 1));
            }
        }

        return '';
    }

    protected static function getTotalArea(Document $doc) : string
    {
        $params = self::_getParamsValue($doc);
        foreach ($params as $param) {
            $text = $param->text();
            if (strpos($text, 'площадь') !== false) {
                $text = trim(substr(strstr($text, ':'), 1));
                $text = trim(strstr($text, 'м.кв.',true));
                return preg_replace('~[^0-9(.|,)]~Uuis', '', $text);
            }
        }

        return '';
    }

    protected static function getKitchenArea(Document $doc) : string
    {
        return '';
    }

    protected static function getLivingArea(Document $doc) : string
    {
        return '';
    }

    protected static function getAddress(Document $doc) : string
    {
        $params = self::_getParamsValue($doc);
        foreach ($params as $param) {
            $text = $param->text();
            if (strpos($text, 'адрес') !== false) {
                return trim(substr(strstr($text, ':'), 1));
            }
        }

        return '';
    }

    protected static function getDescription(Document $doc) : string
    {
        return $doc->first('.col-xs-12 .h4')->nextSibling()->text();
    }

    protected static function getPrice(Document $doc) : string
    {
        return trim(preg_replace('~[^0-9]~Uuis', '', $doc->first('*[^itemprop=price]')->text()));
    }

    protected static function getDepositPrice(Document $doc) : string
    {
        return '';
    }

    protected static function getSellerName(Document $doc) : string
    {
        return '';
    }

    protected static function getMetroStation1(Document $doc) : string
    {
        return '';
    }

    protected static function getMetroStation2(Document $doc) : string
    {
        return '';
    }

    protected static function getMetroStation3(Document $doc) : string
    {
        return '';
    }

}
