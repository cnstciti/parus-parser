<?php
namespace backend\controllers;

use yii\web\Controller;
use common\models\data_level3\DataLevel3;

/**
 * DataLevel3 controller
 * Финализация данных
 */
class DataLevel3Controller extends Controller
{
    /**
     * Действие "Level3. Главная страница"
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Действие "Level3 - Запустить парсер"
     *
     * @return mixed
     */
    public function actionParser()
    {
        $data = DataLevel3::parser();

        return $this->render('parser', [
            'data' => $data,
        ]);
    }

    /**
     * Действие "Level3 - Запустить удаление объекта"
     *
     * @return mixed
     */
    public function actionDelete()
    {
        $data = DataLevel3::delete(7);

        return $this->render('delete', [
            'data' => $data,
        ]);
    }

    /**
     * Действие "Level3 - Запустить инициацию удаления объекта"
     *
     * @return mixed
     */
    public function actionInitDelete()
    {
        $data = DataLevel3::initDelete();

        return $this->render('init-delete', [
            'data' => $data,
        ]);
    }

}
