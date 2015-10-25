<?php /**
 * Форма для обновления стран
 */ 

$form = $this->beginWidget(
    '\yupe\widgets\ActiveForm',
    [
        'id'                     => 'country-form',
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
        'label'      => Yii::t('StoreswapModule.main', 'Обновить страны'),
        'htmlOptions'=> [
        	'class'	=>	'col-xs-3 update-base',
        	'id'	=> 	'updateСountryBtn',
        	'data-url' => Yii::app()->createUrl(
        		'/storeswap/simalandBackend/updateBase',
        		[
        			'class'=>'ECountry'
        		]
        	)
        ]
    ]
); ?>
