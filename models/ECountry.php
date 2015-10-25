<?php
Yii::import('application.modules.store.StoreModule');

class ECountry extends Country
{
    /**
     * Добавление правил для image и id, необходимо для безопасного присвоения
     */
    public function rules()
    {
        return CMap::mergeArray([['id, name', 'safe']], parent::rules());
    }

    /**
     * Сброс поведения модели для добавления ссылки картинки
     */
    public function behaviors()
    {
        return [
        ];
    }

    /**
     * Возвращает имя, просто имя
     * @return string
     */
    public function getName(){
        return Yii::t('StoreModule.store', 'Country');
    }

    public function getRecordsArray($template){
        return Yii::app()->db->createCommand()
                ->select($template[0])
                ->from('{{store_country}}')
                ->queryAll();
    }


}