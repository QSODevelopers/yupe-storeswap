<?php
class DataNormalizer extends CApplicationComponent
{
	private $arrayData;
	private $attribute;

	public function normalizeArray(Array $array, $arrayTemplate)
	{
		$normalizeArray = [];
		foreach ($array as $key=>$item) {
			$this->arrayData = $item;
			$normalizeArray[$key] = $this->fillTemplateAttributes($arrayTemplate);
		}
		return $normalizeArray;
	}


	/**
	 * Обрабатывает массив аттрибутов и шаблонов одной модели.
	 * Преобразует шаблон
	 * Устанавливает атрибут и значение включенной модели
	 * @param array $tmpAttr ключ элемента массива атрибут модели, значение шаблон этого атрибута.
	 */
	public function fillTemplateAttributes($tmpAttr)
	{
		$normilizeItem = [];
		foreach ($tmpAttr as $attr=>$tmp) {
			$this->attribute = $attr;
			$span = preg_replace_callback("/{[\w:\[\]]*}/", [$this, 'convertToType'], $tmp);
			$normilizeItem[$attr] = $span;
		}
		return 	$normilizeItem;
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
			"-"=>"-", "–"=>"-", "—"=>"-" ,"("=>"", ")"=>"", "["=>"", "]"=>"", "="=>"-", "+"=>"-",
			"*"=>"", "?"=>"", "\""=>"", "'"=>"", "&"=>"", "%"=>"", "#"=>"", "@"=>"",
			"!"=>"", ";"=>"", "№"=>"", "^"=>"", ":"=>"", "~"=>"", "\\"=>"", "«"=>"",
			"»"=>"", "`"=>"", "'"=>"",'a'=>'a','b'=>'b','c'=>'c','d'=>'d','e'=>'e',
			'f'=>'f','j'=>'j','h'=>'h','i'=>'i','j'=>'j','k'=>'k','l'=>'l','m'=>'m',
			'n'=>'n','o'=>'o','p'=>'p','q'=>'q','r'=>'r','s'=>'s','t'=>'t','u'=>'u',
			'v'=>'v','w'=>'w','x'=>'x','y'=>'y','z'=>'z','A'=>'a','B'=>'b','C'=>'c',
			'D'=>'d','E'=>'e','F'=>'f','J'=>'j','H'=>'h','I'=>'i','J'=>'j','K'=>'k',
			'L'=>'l','M'=>'m','N'=>'n','O'=>'o','P'=>'p','Q'=>'q','R'=>'r','S'=>'s',
			'T'=>'t','U'=>'u','V'=>'v','W'=>'w','X'=>'x','Y'=>'y','Z'=>'z'
		];
		return strtr($str,$tr);
	}


	public static function purify($str)
	{
		$tr = [
			"&"=>"&amp;"
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

		preg_match_all('/\[[\d\w]*\]/', current($elem), $keys);
		
		if(!empty($keys[0])){
			$arrayDeep = explode('[', current($elem));
			$result = $this->arrayData[current($arrayDeep)];
			foreach ($keys[0] as $arrayDataKey) {
				$result = $result[trim($arrayDataKey,'[]')];
			}
		}elseif (!isset($this->arrayData[current($elem)])){
			return false;
		}else{
			$result = $this->arrayData[current($elem)];
		}


		if($this->attribute=='name'){
			$result = $this->purify($result);
		}

		if($this->attribute=='slug' or $this->attribute=='alias') {
			$result = $this->convert($result);
		}

		if(($this->attribute == 'parent_id')){
			if($result == 0 )
				$result = null;
		}

		if (count($elem) <= 1)
			return $result; // без преобразования типа

		else{
			// нужно преобразовать			
			if ($elem[1]=='float') {
				$result = str_replace([",", " "], [".", ""], $result);
				return $result;
			}
			if ($elem[1]=='round') {
				$result = ceil($result).'.000';
				return $result;
			}
			settype($result, $elem[1]);
			return $result;
		}
	}

}
?>
