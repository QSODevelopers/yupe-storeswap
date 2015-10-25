<?php
class DataRecorder extends CApplicationComponent
{
	/**
	 * Ошибки валидации последней модели
	 */
	private $errors = '';
	/**
	 * Устанавлевает модель для работы
	 */
	public function distributeModel($modelName)
	{
		$this->model = $modelName;
	}

	/**
	 * Метод осуществляющий удаление записи из базы
	 */
	public function deleteRecord($model, $id)
	{
		$tableName = $model::model()->tableName();
		if(Yii::app()->db->createCommand()->delete($tableName, 'id=:id',[':id'=>$id]))
			return true;
		else{
			$this->setErrorStr(
				[
					'id'=>[
						'Id не найден в Базе.'
					]
				]
			);
			return false;
		}
	}

	/**
	 * Метод осуществляющий запись в базу
	 */
	public function createRecord($model, $attrs)
	{
		$model->primaryKey = NULL;
		$model->isNewRecord = true;
		$model->attributes = $attrs;
		if($model->save())
			return true;
		else{
			$this->setErrorStr($model->errors);
			return false;
		}
	}

	public function updateRecord($model, $attrs)
	{
		$model = $model->findByPk($attrs['id']);
		$model->attributes = $attrs;
		if($model->save())
			return true;
		else{
			$this->setErrorStr($model->errors);
			return false;
		}
	}

	private function setErrorStr($errorsSummary){
		foreach ($errorsSummary as $attr => $errors) {
			foreach ($errors as $key => $string) {
				$this->errors .= $string .'; ';
			}
		}
	}

	public function getErrorStr(){
		$buffer = $this->errors;
		$this->errors = '';
		return $buffer;
	}

}
?>
