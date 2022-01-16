<?php
namespace common\models\data_level1;

/**
 *  DataLevel1 model
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel1
{
    /**
     * Разбор данных из каталога
     *
     * @return array
     */
    public static function parser(array $params, $isLog=false) : array
    {
        switch ($params['site']) {
            case 'avito': return DataLevel1Avito::parser($params, $isLog);
            case 'vsn':   return DataLevel1Vsn::parser($params, $isLog);
        }
        return [];
    }
}
