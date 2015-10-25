<?php
Yii::import('application.modules.store.StoreModule');

class EStoreCategory extends StoreCategory
{
    /**
     * Добавление правил для image и id, необходимо для безопасного присвоения
     */
    public function rules()
    {
        return CMap::mergeArray([['id, image', 'safe']],parent::rules());
    }

    /**
     * Сброс поведения модели для добавления ссылки картинки
     */
    public function behaviors()
    {
        return [
            'sortable'             => [
                'class'         => 'yupe\components\behaviors\SortableBehavior',
                'attributeName' => 'sort'
            ]
        ];
    }

    /**
     * Возвращает имя, просто имя
     * @return string
     */
    public function getName(){
        return Yii::t('StoreModule.category', 'Categories');
    }

    public function beforeValidate(){
        //Сброс значения parent_id в NUll, т.к. от APi приходят нули
        if($this->parent_id == 0)
            $this->parent_id = NULL;

        return parent::beforeValidate();
    }


    public function getRecordsArray($template){
        return Yii::app()->db->createCommand()
                ->select($template[0])
                ->from('{{store_category}}')
                ->queryAll();
    }
}