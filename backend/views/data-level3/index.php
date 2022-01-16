<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Level3 - Финализация данных';

?>
<h2><?= $this->title ?></h2>

<?= Html::a('Запустить парсер', Url::to(['data-level3/parser',])) ?>
<br>
<?= Html::a('Запустить удаление объекта', Url::to(['data-level3/delete',])) ?>
