<?php
namespace console\controllers;

use common\models\data_level3\DataLevel3;
use yii\console\Controller;

/**
 * Avito controller
 */
class DataLevel3Controller extends Controller
{
    /**
     * Действие "Level3 - Запустить парсер"
     */
    public function actionParser()
    {
        $result = DataLevel3::parser();
        print_r($result);
    }

    /**
     * Действие "Level3 - Запустить удаление объекта"
     *
     * @param int $id - ИД записи в таблице
     */
    public function actionDelete($id)
    {
        $result = DataLevel3::delete($id);
        print_r($result);
    }

    /**
     * Действие "Level3 - Запустить инициацию удаления объекта"
     */
    public function actionInitDelete()
    {
        $data = DataLevel3::initDelete();
        print_r($data);
    }

}
