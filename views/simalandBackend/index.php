<?php 
    $assetsPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias($this->module->assetsPath), true, -1, YII_DEBUG);
    Yii::app()->getClientScript()->registerCssFile($assetsPath. '/css/storeswap.css');
    Yii::app()->getClientScript()->registerScriptFile($assetsPath. '/js/js.additional.js');
    Yii::app()->getClientScript()->registerScriptFile($assetsPath. '/js/jquery.sseconsole.js');
    Yii::app()->getClientScript()->registerScriptFile($assetsPath. '/js/storeswap.js');
 ?>
<?php
$this->breadcrumbs = [
    Yii::t('StoreswapModule.main', 'Варианты обновлений'),
    Yii::t('StoreswapModule.main', 'Simaland API'),
];

$this->pageTitle = Yii::t('StoreswapModule.main', 'Обновления каталога при помощи Simaland API');

$this->menu = [
    [
        'label' => Yii::t('StoreswapModule.main', 'Обновить каталог из 1С'),
        // 'url'   => ['/backend/storeswap/exchange/update'],
        'icon'  => "fa fa-play",
    ],
    [
        'label' => Yii::t('StoreswapModule.main', 'Обновить изображения из 1С'),
        // 'url'   => ['/backend/storeswap/exchange/images'],
        'icon'  => "fa fa-picture-o",
    ],
    [
        'label' => Yii::t('StoreswapModule.main', 'Обновление прайс-листа'),
        // 'url'   => ['/backend/storeswap/exchange/userprice'],
        'icon'  => "fa fa-file-excel-o",
    ],
    [
        'label' => Yii::t('StoreswapModule.main', 'Simaland API'),
        'url'   => ['/backend/storeswap/simaland/index'],
        'icon'  => "fa fa-share-alt",
    ]
];
?>
<div class="page-header">
    <h1>
        <?php echo Yii::t('StoreswapModule.main', 'Обновления каталога при помощи Simaland API'); ?>
        <small><?php echo Yii::t('StoreswapModule.main', 'управление обновлениями'); ?></small>
    </h1>
</div>
<div class="col-xs-9">
    <div class="row">
        <div class="panel panel-default">
          <div class="panel-body">
              <?php echo $this->renderPartial('_cateroryForm'); ?>
              <?php echo $this->renderPartial('_producerForm'); ?>
              <?php echo $this->renderPartial('_countryForm'); ?>
              <?php echo $this->renderPartial('_productForm'); ?>
          </div>
        </div>
    </div>
    <div class="row">
        <div class="panel panel-default console">
            <div class="panel-heading">
                <div class="console-status pull-left">Статус: <span id="consoleStatus">-</span></div>
                <div class="alerts pull-right" id='consoleBundle'></div>
            </div>
            <div class="panel-body">
                <div class="console-body" id="consoleBody"></div>
                <div class="progress progress-striped active" id="consoleProgress"></div>
                <div class="bundle-styler"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <button class="btn btn-default" id="clearConsole">Очистить консоль</button>    
        <button class="btn btn-default flushAction" method="cacheFlush" href="/backend/ajaxflush?method=1">Очистить кеш</button>
    </div>
</div>
