<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Level2 - Обработка данных';

?>
<h2><?= $this->title ?></h2>

<?= Html::a('Запустить парсер', Url::to(['data-level2/parser',])) ?>
