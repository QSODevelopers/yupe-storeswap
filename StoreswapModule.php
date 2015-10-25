<?php
/**
 * Storeswap основной класс модуля Storeswap
 *
 * @author UnnamedTeam
 * @link http://none.shit
 * @copyright 2015 UnnamedTeam
 * @package yupe.modules.Storeswap.install
 * @license  BSD
 * @since 0.0.1
 *
 */

use yupe\components\WebModule;

class StoreswapModule extends WebModule
{
	/**
	 * Папка с csv-файлами для парсинга
	 * @var string
	 */
	public $folderFiles          = '/uploads/files/storeswap/csv';
	/**
	 * Файл товаров для парсинга
	 * TODO Файл будет прописываться непосредственно в управляющем контроллере, 
	 * этот останется по умолчанию
	 * @var string
	 */
	public $readFileProducts     = 'goods.csv';
	/**
	 * Файл категорий для парсинга
	 * TODO Файл будет прописываться непосредственно в управляющем контроллере, 
	 * этот останется по умолчанию
	 * @var string
	 */
	public $readFileCategory     = 'category.csv';
	/**
	 * Разделитель внутри файла. Стандарт csv.
	 * TODO убрать за ненадобностью, файл будет прописываться непосредственно в управляющем контроллере
	 * @var string
	 */
	public $columnSeparator      = ';';
	/**
	 * ?????
	 * @var string
	 */
	public $pathOut              = '/uploads/out';
	/**
	 * Путь до папки с ресурсами
	 * @var string
	 */
	public $assetsPath           = 'application.modules.Storeswap.assets';
	/**
	 * «Вместимость» буфера SSE для высокочастотных операций, таких как запись, удаление, обновление
	 * Когда сыпет много, слабые клиенские компы не справляются.
	 * Другими словами количестов записей, и сообщений отправляемых разом.
	 * @var integer
	 */
	public $sse_frequency		 = 20;
	/**
	 * Пауза после накопления буфера, дабы клиент успел разобрать присланную информацию
	 * @var float
	 */
	public $sse_pause		 	 = 1.5;
	/**
	 * Пауза между запросами на сервер Simaland
	 * @var float
	 */
	public $api_pause		 	 = 0.5;
	/**
	 * Шаблоны для нормализации массивов и сопоставления данных между исходными данными и моделью
	 * @var array
	 */
	public $arrayCorrespondences = [
		'EStoreCategory' => [
			'id'          => '{id}',
			'parent_id'   => '{parent_id}',
			'name'        => '{name}',
			'slug'        => '{name}-{id}',
			'image'       => 'http://simaland-st.cdn.ngenix.net/categories/{id}.jpg'
		],
		'EProducer'=> [
            'id'         => '{id}',
			'name'       => '{name}',
			'name_short' => '{name}',
			'slug'       => '{name}-{id}',
		],
		'ECountry'=> [
            'id'         => '{id}',
			'name'       => '{name}',
		],
		'EProduct'=> [
            'id'	        => '{id}',
			'in_stock'	    => '1',
			'status'	    => '1',
			'type_id'	    => '1',
			'producer_id'	=> '{trademark_id}',
			'category_id'	=> '{category_id[0]}',
			'country_id'	=> '{country_id}',
			'name'	        => '{name}',
			'slug'	        => '{name}-{id}',
			'weight'	    => '{weight:round}',
			'price'	    	=> '{price:round}',
			'image' 		=> '{photo[base_url]}',
			'Attrs'=> [
				'in_set_qty' 	 => '{in_set_qty}',
			    'size_text'      => '{size_text}',
			    'prepacking_qty' => '{prepacking_qty}',
			    'unit'           => '{unit}',
			    'is_exclusive'   => '{is_exclusive}',
			    'material_text'  => '{material_text}',
			    'min_qty'        => '{min_qty}',
			    'box_type'       => '{box_type}',
			]	
		],
	];
	/**
	 * Массив собязательными атрибутами,
	 * если они не заполнены то данный массив пропускается и игнорируется нормализаторами
	 * @var array
	 */
	public $attributeRequired = [
		'EStoreCategory' => [
			'name'              => true,
		],
		'EProducer'=> [
            'name_short'        => true,
            'name'              => true,
		],
		'EProduct' => [
			'name'              => true,
			'price'             => true,
		]
	];

	/**
	 * Версия модуля
	 */
	const VERSION = '0.2';

	public function getVersion()
	{
		return self::VERSION;
	}

	public function getDependencies()
	{
		return [
			'store',
		];
	}

	public function getAdminPageLink()
    {
        return '/storeswap/storeswapBackend/index';
    }

	public function getName()
	{
		return Yii::t('StoreswapModule.main', 'Store Swap');
	}

	public function getDescription()
	{
		return Yii::t('StoreswapModule.main', 'The module updates the database of the store unloaded files');
	}

	public function getAuthor()
	{
		return Yii::t('StoreswapModule.main', 'UnnamedTeam');
	}

	public function getAuthorEmail()
	{
		return Yii::t('StoreswapModule.main', 'max100491@mail.ru, konstantin24121@gmail.com');
	}

	public function getIcon()
	{
		return "glyphicon glyphicon-sort";
	}

	public function getNavigation()
	{
		return [
			[
				'label' => Yii::t('StoreswapModule.main', 'Обновить каталог из 1С'),
				'url'   => ['/backend/storeswap/exchange/update'],
				'icon'  => "fa fa-play",
			],
			[
				'label' => Yii::t('StoreswapModule.main', 'Обновление прайс-листа'),
				'url'   => ['/backend/storeswap/exchange/userprice'],
				'icon'  => "fa fa-file-excel-o",
			],
			[
				'label' => Yii::t('StoreswapModule.main', 'Simaland API'),
				'url'   => ['/backend/Storeswap/simaland/index'],
				'icon'  => "fa fa-share-alt",
			],
			[
				'label' => Yii::t('StoreswapModule.main', 'Справка'),
				'url'   => ['/backend/storeswap/exchange/help'],
				'icon'  => "fa fa-fw fa-question-circle",
			]
		];
	}

	public function checkSelf()
	{
		$messages = [];

		$readableFile = Yii::getPathOfAlias('webroot');
		$readableFile = $readableFile.$this->folderFiles;

		$readFileProducts = $readableFile.'/'.$this->readFileProducts;
		$readFileCategory = $readableFile.'/'.$this->readFileCategory;

		if (!file_exists($readableFile)) {
			$messages[WebModule::CHECK_ERROR][] = [
				'type'    => WebModule::CHECK_ERROR,
				'message' => 'Директория '.$readableFile.' не существует'
			];
		}
		
		if (!file_exists($readFileProducts)) {
			$messages[WebModule::CHECK_ERROR][] = [
				'type'    => WebModule::CHECK_ERROR,
				'message' => 'Файл '.$readFileProducts.' не существует'
			];
		}

		if (!file_exists($readFileCategory)) {
			$messages[WebModule::CHECK_ERROR][] = [
				'type'    => WebModule::CHECK_NOTICE,
				'message' => 'Файл '.$readFileCategory.' не существует'
			];
		}

		return isset($messages[WebModule::CHECK_ERROR]) ? $messages : true;
	}

	public function getCategory()
    {
        return Yii::t('StoreswapModule.main', 'Integrators');
    }


	public function getEditableParams()
	{
		return [
			'folderFiles',
			'readFileProducts',
			'readFileCategory',
			'columnSeparator',
			'sse_frequency',
			'sse_pause',
			'api_pause',
		];
	}

	public function getEditableParamsGroups()
    {
        return [
            'main'      => [
                'label' => Yii::t('StoreswapModule.main', 'Главные настройки'),
                'items' => [
                    'adminMenuOrder',
                ]
            ],
            'sse'      => [
                'label' => Yii::t('StoreswapModule.main', 'Настройки SSE-глашетея'),
                'items' => [
                    'sse_frequency',
					'sse_pause',
                ]
            ],
            'csv'      => [
                'label' => Yii::t('StoreswapModule.main', 'Настройки CSV-парсера'),
                'items' => [
                    'folderFiles',
					'readFileProducts',
					'readFileCategory',
					'columnSeparator',
                ]
            ],
            'api'      => [
                'label' => Yii::t('StoreswapModule.main', 'Настройки API'),
                'items' => [
                    'api_pause',
                ]
            ],
        ];
    }

	public function getParamsLabels()
	{
		return [
			'adminMenuOrder'    =>  Yii::t('StoreswapModule.main', 'Порядок следования в меню'),
			'folderFiles'       =>	'Папка с csv-файлами для парсинга',
			'readFileProducts'  =>	'Файл товаров для парсинга',
			'readFileCategory'  =>	'Файл категорий для парсинга',
			'columnSeparator'   =>	'Разделитель внутри файла (Не рекомендуется менять)',
			'sse_frequency'		=>	'«Вместимость» буфера SSE для высокочастотных операций',
			'sse_pause'	        =>	'Пауза после накопления буфера',
			'api_pause'	        =>	'Пауза между запросами на Simaland',
		];
	}
	
	public function init()
	{
		$this->setImport(array(
			'storeswap.models.*',
			'storeswap.components.*',
		));
		parent::init();
	}

	/**
	 * Возвращает ключи шаблона для шаблонизации запросов к базе
	 * @param  string $templateName имя шаблона
	 * @return string               
	 */
	public function getCorrespondencesKeys($templateName)
	{	
		$template = $this->arrayCorrespondences[$templateName];
		$keys = [];
		$root = '';
		foreach ($template as $root_key => $root_value) {
			if(gettype($root_value) == 'string'){
				$keys[0] .= $root_key.', '; 
			}else{
				foreach ($root_value as $key => $value) {
					$keys[$root_key] .= $key.','; 
				}
			}
		}
		return $keys;
	}
}
