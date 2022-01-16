<?php
namespace common\models\data_level1;

use common\models\rep\ParamRep;
use DiDom\Document;

/**
 *  "Авито. Каталог. Парсер"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel1Avito extends DataLevel1Base
{
    protected static function getParams(array $param) : array
    {
        return ParamRep::getOne($param['typeDB'], $param['cityDB'], $param['actionDB'], 'avito');
    }

    protected static function getCatalogURL(string $url, int $numPage) : string
    {
        return sprintf($url, $numPage);
    }
/*
    protected static function getCountItems(Document $doc) : int
    {
        $ret = 0;
        //$str = $doc->first('*[^data-=page-title/count]');
        if ($str = $doc->first('*[^data-=page-title/count]')) {
            $ret = intval($str->text());
        }

        return $ret;
    }
*/
    protected static function getItems(Document $doc) : array
    {
        $serp = $doc->first('*[^data-=catalog-serp]');
        if (!empty($serp)) {
            $items = $serp->find('a[^data-=item-title]');
            if (count($items)) {
                return $items;
            }
        }

        return [];
    }

    protected static function getItemURL($item) : string
    {
        return 'https://www.avito.ru' . $item->attr('href');
    }

    protected static function getMaxNumPage() : int
    {
        return 100;
    }

    public static function getRegions() : array
    {
        return [
            ['en' => 'msk',  'rus' => 'Москва',],

            ['en' => 'klim', 'rus' => 'Климовск',],
            ['en' => 'vosk', 'rus' => 'Воскресенское',],
            ['en' => 'pod',  'rus' => 'Подольск',],
            ['en' => 'sher', 'rus' => 'Щербинка',],
            ['en' => 'vid',  'rus' => 'Видное',],
            ['en' => 'znam', 'rus' => 'Знамя Октября',],
            ['en' => 'dom',  'rus' => 'Домодедово',],
            ['en' => 'komm', 'rus' => 'Коммунарка',],
            ['en' => 'kon',  'rus' => 'Константиново',],
            ['en' => 'lv',   'rus' => 'Львовский',],
            ['en' => 'tr',   'rus' => 'Троицк',],
            ['en' => 'shl',  'rus' => 'Шишкин лес',],
            ['en' => 'vat',  'rus' => 'Ватутинки',],

            ['en' => 'bal',  'rus' => 'Балашиха',],
            ['en' => 'zhel', 'rus' => 'Железнодорожный',],
            ['en' => 'reut', 'rus' => 'Реутов',],
            ['en' => 'lyub', 'rus' => 'Люберцы',],
            ['en' => 'kras', 'rus' => 'Красково',],
            ['en' => 'kot',  'rus' => 'Котельники',],
            ['en' => 'byk',  'rus' => 'Быково',],
            ['en' => 'ilin', 'rus' => 'Ильинский',],
            ['en' => 'zhuk', 'rus' => 'Жуковский',],
            ['en' => 'krat', 'rus' => 'Кратово',],
            ['en' => 'st_k', 'rus' => 'Старая Купавна',],
            ['en' => 'okt',  'rus' => 'Октябрьский',],
            ['en' => 'ud',   'rus' => 'Удельная',],
            ['en' => 'ram',  'rus' => 'Раменское',],
        ];
    }

}
