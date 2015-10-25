<?php /**
 * Форма для обновления производителей
 */ 

$form = $this->beginWidget(
    '\yupe\widgets\ActiveForm',
    [
        'id'                     => 'producer-form',
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
        'label'      => Yii::t('StoreswapModule.main', 'Обновить производителей'),
        'htmlOptions'=> [
        	'class'	=>	'col-xs-3 update-base',
        	'id'	=> 	'updateProducersBtn',
        	'data-url' => Yii::app()->createUrl(
        		'/storeswap/simalandBackend/updateBase',
        		[
        			'class'=>'EProducer'
        		]
        	)
        ]
    ]
); ?>
