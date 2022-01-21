<?php
namespace common\models\rep;

use Yii;
use common\models\ar\DataLevel1AR;

/**
 *  Репозиторий "Загруженные данные"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel1Rep
{
    public static function batchInsert(array $data) : void
    {
        Yii::$app->db->createCommand()
            ->batchInsert(DataLevel1AR::tableName(), ['url', 'type', 'action', 'site',], $data)
            ->execute();
    }

    public static function findOne()// : array
    {
        return DataLevel1AR::find()
            ->orderBy('createAt ASC')
            ->asArray()
            ->one();
    }

    public static function deleteAllByUrl(string $url) : void
    {
        DataLevel1AR::deleteAll(['like', 'url', $url]);
    }

}