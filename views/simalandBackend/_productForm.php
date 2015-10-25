<?php /**
 * Форма для обновления товаров
 */ 

$form = $this->beginWidget(
    '\yupe\widgets\ActiveForm',
    [
        'id'                     => 'product-form',
        'enableAjaxValidation'   => false,
        'enableClientValidation' => true,
    ]
); ?>


<?php $this->endWidget(); ?>
<?php $this->widget(
    'bootstrap.widgets.TbButton',
    [
        'buttonType' => 'submit',
        'context'    => 'primary',
        'label'      => Yii::t('StoreswapModule.main', 'Обновить товары'),
        'htmlOptions'=> [
        	'class'	=>	'col-xs-3 update-base right-btn',
        	'id'	=> 	'updateProducersBtn',
        	'data-url' => Yii::app()->createUrl(
        		'/storeswap/simalandBackend/updateBase',
        		[
        			'class'=>'EProduct'
        		]
        	)
        ]
    ]
); ?>
