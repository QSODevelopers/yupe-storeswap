<?php
class DataDelimiter extends CApplicationComponent
{
	/**
	 * Возвращает элементы отсутствующие в новом массиве и присутствующие в оригинальном
	 * @param  Array  $newArray    новые данные
	 * @param  Array  $originArray текущие данные
	 * @return Array             
	 */
	public static function itemsForRemove(Array $newArray, Array $originArray){
		return array_udiff($originArray, $newArray,  function($a, $b){
			if ($a['id'] < $b['id']) {
		        return -1;
		    } elseif ($a['id'] > $b['id']) {
		        return 1;
		    } else {
		        return 0;
		    }
		});
	}

	/**
	 * Возвращает элементы отсутствующие в базе и присутствующие в ответе от API
	 * @param  Array  $newArray    новые данные
	 * @param  Array  $originArray текущие данные
	 * @return Array             
	 */
	public static function itemsForCreate(Array $newArray, Array $originArray){
		return self::itemsForRemove($originArray, $newArray);
	}

	/**
	 * Возвращает элементы изменившиеся с последнего обновления
	 * @param  Array  $newArray    новые данные
	 * @param  Array  $originArray текущие данные
	 * @return Array             
	 */
	public static function itemsForUpdate(Array $newArray, Array $originArray){
		return array_uintersect($newArray, $originArray, function($a, $b){
			if ($a['id'] < $b['id']) {
		        return -1;
		    } elseif ($a['id'] > $b['id']) {
		        return 1;
		    } else {
		    	if($a != $b)
			        return 0;
			    else
			    	return 1;
		    }
		});
	}
}