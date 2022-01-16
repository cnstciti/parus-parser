<?php
namespace common\models\data_level1;

use common\models\rep\ParamRep;
use DiDom\Document;

/**
 *  "VSN. Каталог. Парсер"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel1Vsn extends DataLevel1Base
{

    protected static function getParams(array $param) : array
    {
        return ParamRep::getOne($param['typeDB'], $param['cityDB'], $param['actionDB'], 'vsn');
    }

    protected static function getCatalogURL(string $url, int $numPage) : string
    {
        return $url . $numPage;
    }
/*
    protected static function getCountItems(Document $doc) : int
    {
        return 1;
    }
*/
    protected static function getItems(Document $doc) : array
    {
        $list = $doc->first('.list-unstyled');
        $items = $list->find('.object__data a[class*=header_adv_short]');
        //$items = $list->find('.list-unstyled .object__data a[class*=header_adv_short]');
        return $items;
    }

    protected static function getItemURL($item) : string
    {
        return $item->attr('href');
    }

    protected static function getMaxNumPage() : int
    {
        return 15;
    }

    public static function getRegions() : array
    {
        return [
            ['en' => 'sector1', 'rus' => 'Cектор1',],
            ['en' => 'sector2', 'rus' => 'Cектор2',],
            ['en' => 'sector3', 'rus' => 'Cектор3',],
            ['en' => 'sector4', 'rus' => 'Cектор4',],
            ['en' => 'sector5', 'rus' => 'Cектор5',],
            ['en' => 'sector6', 'rus' => 'Cектор6',],
            ['en' => 'sector7', 'rus' => 'Cектор7',],
            ['en' => 'sector8', 'rus' => 'Cектор8',],
            ['en' => 'sector9', 'rus' => 'Cектор9',],
            ['en' => 'sector10', 'rus' => 'Cектор10',],
            ['en' => 'sector11', 'rus' => 'Cектор11',],
            ['en' => 'sector12', 'rus' => 'Cектор12',],
            ['en' => 'sector13', 'rus' => 'Cектор13',],
            ['en' => 'sector14', 'rus' => 'Cектор14',],
            ['en' => 'sector15', 'rus' => 'Cектор15',],
            ['en' => 'sector16', 'rus' => 'Cектор16',],
            ['en' => 'sector17', 'rus' => 'Cектор17',],
            ['en' => 'sector18', 'rus' => 'Cектор18',],
            ['en' => 'sector19', 'rus' => 'Cектор19',],
            ['en' => 'sector20', 'rus' => 'Cектор20',],
            ['en' => 'sector21', 'rus' => 'Cектор21',],
            ['en' => 'sector22', 'rus' => 'Cектор22',],
            ['en' => 'sector23', 'rus' => 'Cектор23',],
        ];
    }

}
