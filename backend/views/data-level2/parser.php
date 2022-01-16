<?php
/*
$header                        = 'Доставка Dimex';
$view                          = 'Просмотр данных';
$this->title                   = $header . '. ' . $view;
$this->params['breadcrumbs'][] = [
    'label' => $header,
    'url'   => ['dimex/index'],
];
$this->params['breadcrumbs'][] = $view;
?>
<h2><?= $header . '. ' . $view ?></h2>
<?php
*/
//echo 'row.url: ' . $url;

echo \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
]);