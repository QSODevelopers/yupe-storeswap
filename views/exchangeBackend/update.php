<?php
$this->breadcrumbs = [
    'Обмен 1с' => ['/payment/paymentBackend/index'],
    'Обновление каталога',
];

$this->pageTitle = 'Обновление каталога';
?>

<div class="row">
    <div class="col-md-6">
        <h3>Инфомация</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Операции</th>
                    <th>База сайта</th>
                    <th>Выгрузка 1с</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Дата обновления товаров</td>
                    <td><span class="label label-success"><?php echo $model->getDateDbProducts(); ?></span></td>
                    <td><span class="label label-info"><?php echo $model->getDateFileProducts(); ?></span></td>
                </tr>
                <tr>
                    <td>Дата обновления категорий</td>
                    <td><span class="label label-success"><?php echo $model->getDateDbCategories(); ?></span></td>
                    <td><span class="label label-info"><?php echo $model->getDateFileCategories(); ?></span></td>
                </tr>
                <tr>
                    <td>Количество изобьражений</td>
                    <td><span class="label label-success"><?php echo $model->getCountImageSite(); ?> шт.</span></td>
                    <td><span class="label label-info"><?php echo $model->getCountImage(); ?> шт.</span></td>
                </tr>
            </tbody>
        </table>

        <form>
            <input type="text" name="number" id="number">
        </form>
    </div>
    <div class="col-md-6">
        <h3>Поток</h3>
        <div class="stream-container" style="height: 340px; border: 1px solid #ddd; overflow: auto;">
        </div>
    </div>
</div>

<hr>

<?php echo CHtml::link('Запустить обновление', ['/backend/exchange1c/exchange/start'], ['class'=>'btn btn-success', 'id'=>'start']); ?>

<script type="text/javascript">
    $('#start').on('click', function(){
        var source = new EventSource(this.href);
        source.addEventListener('message', function(e) {
          $('.stream-container').prepend(e.data);
        }, false);

        source.addEventListener('open', function(e) {
            console.log('Соединение открыто');
        }, false);

        source.addEventListener('error', function(e) {
            if (e.eventPhase == EventSource.CLOSED) {
                source.close();
            }
        }, false);
        return false;
    })
</script>