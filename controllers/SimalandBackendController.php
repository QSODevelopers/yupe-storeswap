<?php
class SimalandBackendController extends yupe\components\controllers\BackController
{
	/**
	 * Экземпляр объекта API
	 * @var SimalandAPI
	 */
	public $api;
	/**
	 * Экземпляр объекта dataRecorder
	 * @var DataRecorder
	 */
	public $dataRecorder;
	/**
	 * Частота печати в буфер при создании/удалении/обновлении для медленно работающих на фронте
	 * @var integer
	 */
	private $frequency;
	/**
	 * Пауза после отправки при создании/удалении/обновлении для медленно работающих на фронте
	 * @var integer
	 */
	private $pause;

	public function beforeAction($action){

		switch ($action->id) {
			case 'updateBase':
				header("Content-Type: text/event-stream");
				header("Cache-Control: no-cache");
				header("Connection: keep-alive");
				set_time_limit(0);
				break;
			default:
				break;
		}
		return parent::beforeAction($action);
	}

	public function behaviors()
    {
        return [
            'sseHerald'  => [
                'class' => 'application.modules.storeswap.components.behaviors.SseHeraldBehavior',
            ]
        ];
    }

	public function init(){
		$this->api = new SimalandAPI();
		$this->api->setCurlPause(Yii::app()->getModule('storeswap')->api_pause);
		$this->api->attachBehavior('sseHerald','application.modules.storeswap.components.behaviors.SseHeraldBehavior');
		return parent::init();
	}

	public function actionIndex(){
		$this->render('index');
	}

	public function actionUpdateBase($class){
		try{
			$model = new $class();
			$modelName = $model->_CLASS_();
			$countMethod = 'get'.$modelName.'Count';
			$getAllDataMethod = 'getAll'.$modelName;
			$arrayTemplate = Yii::app()->getModule('storeswap')->arrayCorrespondences[$modelName];

			$this->sendSSEMessage('Процесс запущен', 'Объект обнорвления:- '.$model->getName().'. Ожидайте проверки данных в кэше.');
			if(Yii::app()->cache->get('SimaAPI__'.$modelName)===false){
				$this->sendSSEMessage('Данные будут взяты из базы данных SimaLand.', 'Результат будет закэширован на час или до момента очистки кэша.');
				$this->sendSSEMessage('Ожидает парсинга: ', $this->api->$countMethod(), SseHeraldBehavior::CONTEXT_SUCCESS, 'INIT_PARSER');
				$this->sendSSEMessage('Начало парсинга.', $model->getName());
				//Get array from API
				$apiArray = $this->api->$getAllDataMethod();
				Yii::app()->cache->set('SimaAPI__'.$modelName, $apiArray, 3600);
			}else{
				//Get array from cache
				$apiArray = Yii::app()->cache->get('SimaAPI__'.$modelName);
				$this->sendSSEMessage('Взято данных из кэша: ', count($apiArray['items']), SseHeraldBehavior::CONTEXT_SUCCESS,'INIT_PARSER');
				$this->sendSSEMessage('Данные взяты из кэша.', 'При необходимости сравнивать с данными из базы SimaLand, почистите кэш до обновления.', SseHeraldBehavior::CONTEXT_WARNING);
			}
			$this->sendSSEMessage('Парсинг завершен', '', SseHeraldBehavior::CONTEXT_SUCCESS, 'PARSING_END');
			
			//Приведение массива от API к виду в шаблоне
			
			$this->sendSSEMessage('Нормализация к удобному виду', '', SseHeraldBehavior::CONTEXT_INFO, 'CHANGE_STATUS');
			$dataNormalaizer = new DataNormalizer();
			
			$apiArray = $dataNormalaizer->normalizeArray($apiArray['items'], $arrayTemplate);

			$this->sendSSEMessage('Сравнение с данными в базе',  '',  SseHeraldBehavior::CONTEXT_SUCCESS, 'CHANGE_STATUS');

			//Берем схему базы данных для последующего сравнения
			$template = Yii::app()->getModule('storeswap')->getCorrespondencesKeys($modelName);

			//>>>>>>>>>>>>Error here on local machine
			$dbArray = $model->getRecordsArray($template);
				
			$this->sendSSEMessage('Сранение данных.', 'Алгоритм сравнит новые данные с существующими для разгрузки базы данных от ненужных операций.');
			
			//DataDelimiter Компонент для разбора данных, уменьшает количество запросов к базе
			$deleteData = DataDelimiter::itemsForRemove($apiArray, $dbArray);
			$this->sendSSEMessage('Поиск записей для удаления завершен.', count($deleteData), SseHeraldBehavior::CONTEXT_SUCCESS, 'INIT_DELETER');

			$createData = DataDelimiter::itemsForCreate($apiArray, $dbArray);
			$this->sendSSEMessage('Поиск записей для создания завершен.', count($createData), SseHeraldBehavior::CONTEXT_SUCCESS, 'INIT_CREATER');
			
			$updateData = DataDelimiter::itemsForUpdate($apiArray, $dbArray);
			$this->sendSSEMessage('Поиск записей для обновления завершен.', count($updateData), SseHeraldBehavior::CONTEXT_SUCCESS, 'INIT_UPDATER');
			
				
			$this->sendSSEMessage('Записи не претерпевшие никаких изменений: ', '', SseHeraldBehavior::CONTEXT_INFO, 'INIT_UNCHANGER');

			//Оперирование данными
			$this->dataRecorder = new DataRecorder();
			$this->frequency = Yii::app()->getModule('storeswap')->sse_frequency;
			$this->pause = Yii::app()->getModule('storeswap')->sse_pause;
			
			$this->deleteData($deleteData, $model);
			$this->createData($createData, $model);
			$this->updateData($updateData, $model);

			$this->sendSSEMessage('Процесс завершен','', SseHeraldBehavior::CONTEXT_INFO, 'CLOSE');
			Yii::app()->end();
			
		}catch(Exception $e) {
			$this->sendSSEMessage(
				'Ошибка',
				$e->__toString(),
				SseHeraldBehavior::CONTEXT_DANGER,
				'PHP_ERROR'
			);
		}		
	}

	private function deleteData($deleteData, $model){
		$this->sendSSEMessage( 'Данные удаляются', '', SseHeraldBehavior::CONTEXT_INFO, 'CHANGE_STATUS');
		$messages = [];
		//Init index
		$index = 1;
		foreach ($deleteData as $item) {
			if ($this->dataRecorder->deleteRecord($model, $item['id'])){
				$messages[] = [
					'title'=> 'Запись №'.$item['id'].' удалена.',
					'message'=>$item['name'],
					'context' => SseHeraldBehavior::CONTEXT_SUCCESS
				];
			}else{
				$messages[] = [
					'title'=> 'Произошла ошибка при удалении №'.$item['id'],
					'message'=>'Запись '.$item['name'].' не удалена. Причины: '.$this->dataRecorder->getErrorStr(),
					'context' => SseHeraldBehavior::CONTEXT_WARNING
				];
			}
			if($index % $this->frequency == 0 || $index == count($deleteData)){
				$this->sendSSEMessage( count($messages), $messages, SseHeraldBehavior::CONTEXT_INFO, 'DELETER_IN_PROGRESS');
				sleep($this->pause);
				$messages = [];
			}
			$index++;
		}
	}

	private function createData($createData, $model){
		$this->sendSSEMessage( 'Данные записываются', '', SseHeraldBehavior::CONTEXT_INFO, 'CHANGE_STATUS');
		$messages = [];
		//Init index
		$index = 1;
		foreach ($createData as $key => $item) {
			if ($this->dataRecorder->createRecord($model,$item)){
				$messages[] = [
					'title'=> 'Созданна №'.$item['id'],
					'message'=>'Запись '.$item['name'].' создана.',
					'context' => SseHeraldBehavior::CONTEXT_SUCCESS
				];
			}else{
				$messages[] = [
					'title'=> 'Произошла ошибка при создании №'.$item['id'],
					'message'=>'Запись '.$item['name'].' не создана. Причины: '.$this->dataRecorder->getErrorStr(),
					'context' => SseHeraldBehavior::CONTEXT_WARNING
				];
			}
			if($index % $this->frequency == 0 || $index == count($createData)){
				$this->sendSSEMessage( count($messages), $messages, SseHeraldBehavior::CONTEXT_INFO, 'CREATER_IN_PROGRESS');
				sleep($this->pause);
				$messages = [];
			}
			$index++;
		}
	}

	private function updateData($updateData, $model){
		$this->sendSSEMessage( 'Данные обновляются', '', SseHeraldBehavior::CONTEXT_INFO, 'CHANGE_STATUS');
		$messages = [];
		//Init index
		$index = 1;
		foreach ($updateData as $key => $item) {
			if ($this->dataRecorder->updateRecord($model,$item)){
				$messages[] = [
					'title'=> 'Обновлена №'.$item['id'],
					'message'=>'Запись '.$item['name'].' обновлена.',
					'context' => SseHeraldBehavior::CONTEXT_SUCCESS
				];
			}else{
				$messages[] = [
					'title'=> 'Произошла ошибка при обновлении №'.$item['id'],
					'message'=>'Запись '.$item['name'].' не обновлена. Причины: '.$this->dataRecorder->getErrorStr(),
					'context' => SseHeraldBehavior::CONTEXT_WARNING
				];
			}
			if($index % $this->frequency == 0 || $index == count($updateData)){
				$this->sendSSEMessage( count($messages), $messages, SseHeraldBehavior::CONTEXT_INFO, 'UPDATER_IN_PROGRESS');
				sleep($this->pause);
				$messages = [];
			}
			$index++;
		}
	}
}