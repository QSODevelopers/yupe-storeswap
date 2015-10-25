<?php 
/**
 * Компонент для рабоыт с API сайта sima-land.ru
 */
Yii::import('application.modules.storeswap.components.behaviors.SseHeraldBehavior');

 class SimalandAPI extends CComponent
 {

 	/**
 	 * Количество возвращаемых записей за запрос
 	 * @var integer
 	 */
 	public $perPage = 100;
 	/**
 	 * Пауза между запросами к simaland
 	 * @var integer
 	 */
 	public $curl_pause = 0;

 	/**
 	 * @param float $value
 	 */
 	public function setCurlPause($value){
 		$this->curl_pause = $value;
 		return true;
 	}

 	/**
 	 * Делает запрос через curl
 	 * @param  string $url куда делаем запрос
 	 * @return array      
 	 */
 	private function getInfoFromSimaland($url){
 		$pause = 0;
 		do{
 			sleep($pause);
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$json = curl_exec($curl); // сохранен json
			$pause = $this->curl_pause; //Второй запрос уже повторится с задержкой
		} while(curl_getinfo($curl)['http_code'] != '200');
		curl_close($curl);
		return CJSON::decode($json);
 	}

 	/**
	 * Возвращает массив категорий с заданой страницы
	 * @param  integer $page номер страницы
	 * @return array 		 массив категорий
	 */
	public function getEStoreCategory($page = 1, $level = 1){
		$url = 'https://www.sima-land.ru/api/v2/category?expand=photo&level='.$level.'&per_page='.$this->perPage.'&page='.$page;
		return $this->getInfoFromSimaland($url);
	}

	/**
	 * Возвращает все товары
	 * @param  integer $page номер страницы
	 * @return array 		 массив товаров
	 */
	public function getEProduct($page = 1){
		$url = 'https://www.sima-land.ru/api/v2/item?expand=category_id,photo&per_page='.$this->perPage.'&page='.$page;
		return $this->getInfoFromSimaland($url);
	}

	/**
	 * Возвращает всех производителей
	 * @param  integer $page номер страницы
	 * @return array 		 массив товаров
	 */
	public function getEProducer($page = 1){
		$url = 'https://www.sima-land.ru/api/v2/trademark?per_page='.$this->perPage.'&page='.$page;
		return $this->getInfoFromSimaland($url);
	}

	/**
	 * Возвращает всех стран
	 * @param  integer $page номер страницы
	 * @return array 		 массив товаров
	 */
	public function getECountry($page = 1){
		$url = 'https://www.sima-land.ru/api/v2/country?per_page='.$this->perPage.'&page='.$page;
		return $this->getInfoFromSimaland($url);
	}

	/**
	 * Возвращает все имеющиеся категории в одном массиве
	 * @return array массив категорий
	 */
	public function getAllEStoreCategory(){
		$categoryArray = [];
		$level = 1;
		while(!empty($this->getEStoreCategory(1,$level)['items'])){
			$categoryArray = CMap::mergeArray($categoryArray, $this->getEStoreCategory(1,$level));
			$this->sendSSEMessage('Парсинг записей с Simaland.', count($categoryArray['items']), SseHeraldBehavior::CONTEXT_INFO, 'PARSING_PROCESS');
			$pageCount = $categoryArray['_meta']['pageCount'];
			for ($i = 2 ; $i <= $pageCount; $i++) { 
				$categoryArray = CMap::mergeArray($categoryArray, $this->getEStoreCategory($i,$level));
				$this->sendSSEMessage('Парсинг записей с Simaland.', count($categoryArray['items']), SseHeraldBehavior::CONTEXT_INFO, 'PARSING_PROCESS');
			}
			$level++;
		}
		return $categoryArray;
	}

	/**
	 * Возвращает все имеющиеся товары в одном массиве
	 * @return array массив товаров
	 */
	public function getAllEProduct(){
		$productsArray = $this->getEProduct();
		$pageCount = $productsArray['_meta']['pageCount'];
		$this->sendSSEMessage('Парсинг записей с Simaland.', count($productsArray['items']), SseHeraldBehavior::CONTEXT_INFO, 'PARSING_PROCESS');
		for ($i = 2 ; $i <= $pageCount; $i++) { 
			$productsArray = CMap::mergeArray($productsArray, $this->getEProduct($i));
			$this->sendSSEMessage('Парсинг записей с Simaland.', count($productsArray['items']), SseHeraldBehavior::CONTEXT_INFO, 'PARSING_PROCESS');
		}
		return $productsArray;
	}

	/**
	 * Возвращает всех имеющихся производителей в одном массиве
	 * @return array массив производителей
	 */
	public function getAllEProducer(){
		$produsersArray = $this->getEProducer();
		$pageCount = $produsersArray['_meta']['pageCount'];
		$this->sendSSEMessage('Парсинг записей с Simaland.', count($produsersArray['items']), SseHeraldBehavior::CONTEXT_INFO, 'PARSING_PROCESS');
		for ($i = 2 ; $i <= $pageCount; $i++) { 
			$produsersArray = CMap::mergeArray($produsersArray, $this->getEProducer($i));
			$this->sendSSEMessage('Парсинг записей с Simaland.', count($produsersArray['items']), SseHeraldBehavior::CONTEXT_INFO, 'PARSING_PROCESS');
		}
		return $produsersArray;
	}

	/**
	 * Возвращает всех имеющихся стран в одном массиве
	 * @return array массив производителей
	 */
	public function getAllECountry(){
		$countryArray = $this->getECountry();
		$pageCount = $countryArray['_meta']['pageCount'];
		$this->sendSSEMessage('Парсинг записей с Simaland.', count($countryArray['items']), SseHeraldBehavior::CONTEXT_INFO, 'PARSING_PROCESS');
		for ($i = 2 ; $i <= $pageCount; $i++) { 
			$countryArray = CMap::mergeArray($countryArray, $this->getECountry($i));
			$this->sendSSEMessage('Парсинг записей с Simaland.', count($countryArray['items']), SseHeraldBehavior::CONTEXT_INFO, 'PARSING_PROCESS');
		}
		return $countryArray;
	}

	/**
	 * Возвращает количество категорий
	 * @return integer 
	 */
	public function getEStoreCategoryCount(){
		$url = 'https://www.sima-land.ru/api/v2/category?expand=photo&per_page=10&page=1';
		return $this->getInfoFromSimaland($url)['_meta']['totalCount'];
	}

	/**
	 * Возвращает количество производителей
	 * @return integer 
	 */
	public function getEProducerCount(){
		return $this->getEProducer(1)['_meta']['totalCount'];
	}

	/**
	 * Возвращает количество товаров
	 * @return integer 
	 */
	public function getEProductCount(){
		return $this->getEProduct(1)['_meta']['totalCount'];
	}

	/**
	 * Возвращает количество стран
	 * @return integer 
	 */
	public function getECountryCount(){
		return $this->getECountry(1)['_meta']['totalCount'];
	}

 }
