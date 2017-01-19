<?php /**
 * Форма для обновления категорий
 */ 

$form = $this->beginWidget(
    '\yupe\widgets\ActiveForm',
    [
        'id'                     => 'category-form',
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
        'label'      => Yii::t('StoreswapModule.main', 'Обновить категории'),
        'htmlOptions'=> [
        	'class'	=>	'col-xs-3 update-base left-btn',
        	'id'	=> 	'updateCategoriesBtn',
        	'data-url' => Yii::app()->createUrl(
        		'/storeswap/simalandBackend/updateBase',
        		[
        			'class'=>'EStoreCategory'
        		]
        	)
        ]
    ]
); ?>
