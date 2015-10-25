<?php
$this->breadcrumbs = [
    'Обмен 1с' => ['/payment/paymentBackend/index'],
    'Изображения 1с',
];

$this->pageTitle = 'Управление изображениями';
?>

<div id="yw1" class="collapse in" style="height: auto;">

<div class="panel-group" id="accordion">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#yw2">
                <i class="fa fa-fw fa-pencil"></i> Изображения</a>
                <span class="badge alert-info">Изображений из 1с <?php echo $model->getCountImage(); ?> шт.</span>
                <span class="badge alert-success">На сайте 0 шт.</span>
                <span class="badge alert-danger">0</span>
            </h4>
        </div>


        <div id="yw2" class="panel-collapse collapse in" style="height: auto;">
            <div class="panel-body">
                <?php echo CHtml::link('Выгрузить информацию о изображениях в 1с', ['/exchange1c/exchangeBackend/outimage'], ['class'=>'btn btn-success']); ?>
            </div>
        </div>
    </div>
</div>
</div>