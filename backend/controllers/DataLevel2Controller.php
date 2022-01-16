<?php
namespace backend\controllers;

use yii\web\Controller;
use common\models\data_level2\DataLevel2;
use yii\data\ArrayDataProvider;
use Yii;

/**
 * DataLevel2 controller
 * Обработка данных
 */
class DataLevel2Controller extends Controller
{
    /**
     * Действие "Level2. Главная страница"
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Действие "Level2 - Обработка данных"
     *
     * Вход:
     * @param int $num - количество записей для обработки
     *
     * @return mixed
     */
    public function actionParser()
    {
        $request = Yii::$app->getRequest();
        $params  = $request->isPost ? $request->getBodyParams() : $request->get();
        $num = isset($params['num']) ? intval($params['num']) : 10;
        $data = DataLevel2::parser($num);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => [
                'pageSize' => 500,
            ],
        ]);

        return $this->render('parser', [
            'dataProvider' => $dataProvider,
        ]);
    }
/*
    public function actionDelete()
    {
        $query = "
            SELECT COUNT( id ) , url
            FROM `data_result`
            WHERE 1
            GROUP BY url
            HAVING COUNT( id )>1  
        ";

        $ret = \Yii::$app->db->createCommand($query)->queryAll();
        $k=0;
        foreach ($ret as $row) {
            $id = substr($row['url'], -11);

            $query = "
                SELECT * 
                FROM `data_result` 
                WHERE `url` LIKE '%" . $id . "%'
                ORDER BY `data_result`.`id` ASC
            ";
            $double = \Yii::$app->db->createCommand($query)->queryAll();

            $z=0;
            foreach ($double as $d) {
                if ($z) {
                    \Yii::$app->db->createCommand()->delete('data_result', 'id = ' . $d['id'])->execute();
                }
                $z++;
            }
            if ($k++>200) {
                break;
            }
        }
    }
*/
}
