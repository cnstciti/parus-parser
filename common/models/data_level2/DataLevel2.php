<?php
namespace common\models\data_level2;

use common\models\rep\DataLevel1Rep;
use common\models\rep\DataLevel2Rep;
use common\models\rep\DataLevel2LogRep;

/**
 *  Level 2
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel2
{
    /**
     * Обработка данных Level 1 и перенос на Level 2
     *
     * @param int $num - количество записей для обработки
     * @return array
     *  [
     *      {
     *          'url'      => <string>,
     *          'status'   => <string>,
     *          'createAt' => <string>,
     *      }
     *  ]
     */
    public static function parser(int $num, $isLog=false) : array
    {
        $ret = [];
        for ($i=0; $i<$num; ++$i) {
            if ($dataLevel1 = DataLevel1Rep::findOne()) {
                // ищем в DataLevel2 объект с адресом $dataLevel1->url
                $dataLevel2 = DataLevel2Rep::findByUrl($dataLevel1['url']);

                if (empty($dataLevel2)) {
                    // если объект не найден
                    // создаем новый
                    DataLevel2Rep::insert($dataLevel1['url'], $dataLevel1['type'], $dataLevel1['action'], $dataLevel1['site']);
                    $status   = 'new';
                    $createAt = $dataLevel1['createAt'];
                } elseif ($dataLevel2['status'] == DataLevel2Rep::STATUS_STALED) {
                    // если статус Устарел
                    DataLevel2Rep::updateStatus($dataLevel2['id'], DataLevel2Rep::STATUS_LOADED);
                    $status   = 'update';
                    $createAt = $dataLevel2['createAt'];
                } else {
                    $status   = 'isExist';
                    $createAt = $dataLevel2['createAt'];
                }
                $ret[] = [
                    'url'      => $dataLevel1['url'],
                    'status'   => $status,
                    'createAt' => $createAt,
                ];
                DataLevel1Rep::deleteAllByUrl($dataLevel1['url']);
            }
        }
        if ($isLog) {
            DataLevel2LogRep::batchInsert($ret);
        }

        return $ret;
    }

}
