<?php
class DataRecording extends CApplicationComponent
{
	/**
	 * @var string обрабатываемая строка
	 */
	private $string;

	/**
	 * @var string переключатель модели
	 */
	private $model;

	/**
	 * @var model переключатель модели
	 */
	private $activeModel;

	/**
	 * @var string переключатель атрибута
	 */
	private $attribute;

	/**
	 * @var array преобразованная строка $string
	 */
	public $arrayData;

	public $db;

	/**
	 * @var integer номер строки
	 */
	public $id;

	/**
	 * @var array модели и атрибуты
	 */
	public $modelsAttributes = [];

	/**
	 * @var array Ошибки валидации
	 */
	public $errors = [];

	/**
	 * @var stirng Возвращает статус
	 */
	public $message;

	/**
	 * @var int Идентификатор продуката
	 */
	public $productId;

	/**
	 * Устанавливает свойства
	 * @param integer $id номер строки из файла
	 * @param string $string обрабатываемая строка
	 */
	public function startLine($id, $string)
	{
		$this->string = $string;
		$this->id = $id;
		$this->processLine();
		$this->distributeModels();
	}

	/**
	 * Преобразовывает строку данных в массив, устанавлевает его как свойство
	 */
	private function processLine()
	{
		$string = trim($this->string);
		$this->arrayData = explode(Yii::app()->getModule('storeswap')->columnSeparator, $string);
	}

	/**
	 * Устанавлевает свойство массив моделей
	 */
	public function distributeModels()
	{
		foreach (Yii::app()->getModule('storeswap')->arrayCorrespondences as $modelName=>$tmpAttr) {
			$this->model = $modelName;
			$this->fillTemplateAttributes($tmpAttr);
		}
		$this->saveData();
	}

	/**
	 * Обрабатывает массив аттрибутов и шаблонов одной модели.
	 * Преобразует шаблон
	 * Устанавливает атрибут и значение включенной модели
	 * @param array $tmpAttr ключ элемента массива атрибут модели, значение шаблон этого атрибута.
	 */
	public function fillTemplateAttributes($tmpAttr)
	{
		foreach ($tmpAttr as $attr => $tmp) {
			$this->attribute = $attr;
			$span = preg_replace_callback("/{[\w]*}/", [$this, 'convertToType'], $tmp);
			$this->modelsAttributes[$this->model][$attr] = $span;
		}	
	}

	public function requiredAttributes()
	{
		foreach(Yii::app()->getModule('storeswap')->attributeRequired as $model=>$attrs) {
			foreach ($attrs as $key => $value) {
				if (!isset($this->modelsAttributes[$model][$key]) || $this->modelsAttributes[$model][$key]==''){
					unset($this->modelsAttributes[$model]);
					break;
				}
			}
		}
	}

	/**
	 * Транслит строки
	 * @param string $str необработанная строка
	 * @return string строка в транслите
	 */
	public static function convert($str)
	{
		$tr = [
			"А"=>"a", "Б"=>"b", "В"=>"v", "Г"=>"g", "Д"=>"d", "Е"=>"e", "Ё"=>"e",
			"Ж"=>"j", "З"=>"z", "И"=>"i", "Й"=>"y", "К"=>"k", "Л"=>"l", "М"=>"m",
			"Н"=>"n", "О"=>"o", "П"=>"p", "Р"=>"r", "С"=>"s", "Т"=>"t", "У"=>"u",
			"Ф"=>"f", "Х"=>"h", "Ц"=>"ts", "Ч"=>"ch", "Ш"=>"sh", "Щ"=>"sch", "Ъ"=>"",
			"Ы"=>"i", "Ь"=>"j", "Э"=>"e", "Ю"=>"yu", "Я"=>"ya", "а"=>"a", "б"=>"b",
			"в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ё"=>"e", "ж"=>"j", "з"=>"z",
			"и"=>"i", "й"=>"y", "к"=>"k", "л"=>"l", "м"=>"m", "н"=>"n", "о"=>"o",
			"п"=>"p", "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", "х"=>"h",
			"ц"=>"ts", "ч"=>"ch", "ш"=>"sh", "щ"=>"sch", "ъ"=>"y", "ы"=>"i", "ь"=>"j",
			"э"=>"e", "ю"=>"yu", "я"=>"ya", " "=> "-", "."=> "", "/"=> "-", ","=>"-",
			"-"=>"-", "("=>"", ")"=>"", "["=>"", "]"=>"", "="=>"-", "+"=>"-",
			"*"=>"", "?"=>"", "\""=>"", "'"=>"", "&"=>"", "%"=>"", "#"=>"", "@"=>"",
			"!"=>"", ";"=>"", "№"=>"", "^"=>"", ":"=>"", "~"=>"", "\\"=>""
		];
		return strtr($str,$tr);
	}

	/**
	 * Обрабатывает элемент шаблона
	 * Транслит строки в зависимости от включенного атрибута
	 * @param array $val массив с вхождениями,
	 * значение элемента массива {n} где n integer - ключ для выборки из свойства arrayData
	 * @return mixed данные из свойства arrayData
	 */
	public function convertToType($val)
	{
		$val = $val[0];
		$elem = explode(':', trim($val,'{}'));
		if (!isset($this->arrayData[current($elem)]))
			return false;
		$result = $this->arrayData[current($elem)];

		if($this->attribute=='slug' or $this->attribute=='alias') {
			$result = $this->convert($result);
		}

		if (count($elem) <= 1)
			return $result; // без преобразования типа

		else{
			// нужно преобразовать
			if ($elem[1]=='float') {
				$result = str_replace([",", " "], [".", ""], $result);
				return $result;
			}
			settype($result, $elem[1]);
			return $result;
		}
	}

	/**
	 * Возвращает строку с ошибками валидации
	 */
	public function getStrErrors($errors=null)
	{
		$errors = (is_array($errors)) ? $errors : $this->errors;

		$eStr = CHtml::openTag('ul');
		$eStr .= CHtml::tag('li', [], $this->arrayData[3]);
		$eStr .= CHtml::tag('li', [], $this->arrayData[2]);
		foreach ($errors as $key => $value) {
			$eStr .= CHtml::openTag('li');
				$eStr .= (is_int($key)) ? '' : $key;
				if (is_array($value))
					$eStr .= $this->getStrErrors($value);
				else
					$eStr .= $value;
			$eStr .= CHtml::closeTag('li');
		}
		$eStr .= CHtml::closeTag('ul');
		return $eStr;
	}

	/**
	 * Метод осуществляющий запись в базу
	 */
	public function saveData()
	{

		$this->requiredAttributes();
		$attrs = $this->modelsAttributes;
		$model = $this->activeModel;
		$model->attributes = $attrs[$this->model];
		if ($model->save()) {
			
		}
		else
			$this->errors[] = $model->errors;

		$model->primaryKey = NULL;
		$model->isNewRecord = true;
	}

	public function setModel($model){
		$this->activeModel = $model;
	}
}
?>
