<?php
namespace common\models\rep;

use common\models\ar\DataLevel2AR;
use Yii;
use common\models\ar\DataSourceAR;
/**
 *  Репозиторий "Обработанные данные"
 *
 * @author Constantin Ogloblin <cnst@mail.ru>
 * @since 1.0.0
 */
class DataLevel2Rep
{
    const STATUS_LOADED         = 'загружен';
    const STATUS_STALED         = 'устарел';
    const STATUS_NOT_OUR_OBJECT = 'не наш объект';
    const STATUS_PROCESSED      = 'обработан';
    const STATUS_ERROR          = 'ошибка';


    public static function findByUrl(string $url)
    {
        return DataLevel2AR::find()
            ->where(['like', 'url', $url])
            ->asArray()
            ->one();
    }

    public static function findByStatus(string $status) //: array
    {
        return DataLevel2AR::find()
            ->where(['status' => $status])
            ->orderBy('rand()')
//            ->orderBy('createAt ASC')
            ->asArray()
            ->one();
    }

    public static function insert(string $url, string $type, string $action, string $site) : void
    {
        $result         = new DataLevel2AR;
        $result->url    = $url;
        $result->type   = $type;
        $result->action = $action;
        $result->site   = $site;
        $result->status = self::STATUS_LOADED;
        $result->save();
    }

    public static function updateStatus(int $id, string $status) : void
    {
        $row = DataLevel2AR::findOne($id);
        $row->status = $status;
        $row->save();
    }

    public static function updateBlock(int $id, int $value) : void
    {
        $row = DataLevel2AR::findOne($id);
        $row->block = $value;
        $row->save();
    }

}