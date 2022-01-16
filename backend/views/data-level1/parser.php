<?php
/**
 * @var $data array
 * @var $param array
 * @var $result array
 *
 */

use yii\grid\GridView;
use yii\data\ArrayDataProvider;

echo '<pre>';
print_r($data);
/*
echo "<h3>Входные параметры</h3>";
echo "<p>Тип: " . $data['result']['param']['type'] . "</p>";
echo "<p>Город: " . $data['result']['param']['city'] . "</p>";
echo "<p>Действие: " . $data['result']['param']['action'] . "</p>";
echo "<p>Ссылка: " . $data['result']['param']['url'] . "</p>";
echo "<h4>Результаты</h4>";
if ($data['error']['code']) {
    echo '<pre>';
    echo $data['error']['description'];
} else {
    echo "<p>Собрано ссылок: " . count($data['result']['data']) . "</p>";
    $dataProvider = new ArrayDataProvider([
        'allModels' => $data['result']['data'],
        'pagination' => [
            'pageSize' => 500,
        ],
    ]);
    echo GridView::widget([
        'dataProvider' => $dataProvider,
    ]);
}
*/