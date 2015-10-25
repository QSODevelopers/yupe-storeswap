<?php
$this->breadcrumbs = [
    'Обмен 1с' => ['/payment/paymentBackend/index'],
    'Обновить прайс-лист',
];

$this->pageTitle = 'Обновление прайс-листа для пользователей';
?>

<?php 
$url = Yii::app()->createUrl('/backend/exchange1c/exchange/updateprice');
echo CHtml::ajaxButton('Обновить прайс', $url, [
	'beforeSend'=>'function(){
		$(".message").html("<div class=\'alert alert-info\'>Ожидайте..</div>");
	}',
	'success'=>'function(data){
		$(".message").html("<div class=\'alert alert-success\'>"+data+"</div>");
	}',
	'error'=>'function(){
		$(".message").html("<div class=\'alert alert-error\'>Произошла ошибка, попробуйте позднее либо свяжитесь с админами сайта</div>");
	}'
],['class'=>'btn btn-info'])?>
<div class="message">
	
</div>