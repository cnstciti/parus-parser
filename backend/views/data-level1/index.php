<?php
/**
 * @var array $avitoRegions
 * @var array $vsnRegions
 *
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Level1 - чтение из каталога';
?>
<h2><?= $this->title ?></h2>
<h3>Avito.ru</h3>
<div class="row">
    <div class="col-6">
        <div class="card border-warning">
            <div class="card-header border-warning bg-warning text-dark">
                <h3>Квартиры</h3>
            </div>
            <div class="row card-body">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header text-danger">
                            <h4>Аренда</h4>
                        </div>
                        <div class="card-body">

                        <? foreach ($avitoRegions as $region) : ?>

                            <div>

                                <?= Html::a($region['rus'], Url::to([
                                    'data-level1/parser',
                                    'type'   => 'flat',
                                    'city'   => $region['en'],
                                    'action' => 'rent',
                                    'site'   => 'avito',
                                ])) ?>

                            </div>

                        <? endforeach; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="card border-primary">
            <div class="card-header border-primary bg-primary text-white">
                <h3>Комнаты</h3>
            </div>
            <div class="row card-body">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header text-danger">
                            <h4>Аренда</h4>
                        </div>
                        <div class="card-body">

                        <? foreach ($avitoRegions as $region) : ?>

                            <div>

                                <?= Html::a($region['rus'], Url::to([
                                    'data-level1/parser',
                                    'type'   => 'room',
                                    'city'   => $region['en'],
                                    'action' => 'rent',
                                    'site'   => 'avito',
                                ])) ?>

                            </div>

                        <? endforeach; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h3>Vsn.ru</h3>
<div class="row">
    <div class="col-6">
        <div class="card border-warning">
            <div class="card-header border-warning bg-warning text-dark">
                <h3>Квартиры</h3>
            </div>
            <div class="row card-body">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header text-danger">
                            <h4>Аренда</h4>
                        </div>
                        <div class="card-body">

                        <? foreach ($vsnRegions as $region) : ?>

                            <div>

                                <?= Html::a($region['rus'], Url::to([
                                    'data-level1/parser',
                                    'type'   => 'flat',
                                    'city'   => $region['en'],
                                    'action' => 'rent',
                                    'site'   => 'vsn',
                                ])) ?>

                            </div>

                        <? endforeach; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="card border-primary">
            <div class="card-header border-primary bg-primary text-white">
                <h3>Комнаты</h3>
            </div>
            <div class="row card-body">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header text-danger">
                            <h4>Аренда</h4>
                        </div>
                        <div class="card-body">

                        <? foreach ($vsnRegions as $region) : ?>

                            <div>

                                <?= Html::a($region['rus'], Url::to([
                                    'data-level1/parser',
                                    'type'   => 'room',
                                    'city'   => $region['en'],
                                    'action' => 'rent',
                                    'site'   => 'vsn',
                                ])) ?>

                            </div>

                        <? endforeach; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
