<?php
Yii::import('application.modules.store.StoreModule');

class EProduct extends Product
{

    public $Attrs = null;
    /**
     * Добавление правил для image и id, необходимо для безопасного присвоения
     */
    public function rules()
    {
        return CMap::mergeArray([['id, image, Attrs', 'safe']], parent::rules());
    }

    /**
     * Сброс поведения модели для добавления ссылки картинки
     */
    public function behaviors()
    {
        return [
            'eavAttr' => [
                'class' => 'application.modules.store.components.behaviors.AttributesBehavior',
                'tableName' => '{{store_product_attribute_eav}}',
                'entityField' => 'product_id',
                'preload' => false
            ],
        ];
    }

    /**
     * Возвращает имя, просто имя
     * @return string
     */
    public function getName(){
        return Yii::t('StoreModule.store', 'Product');
    }

    public function afterSave(){
        $this->updateEavAttributes($this->Attrs);
    }

    /**
     * Возращает массив из базы
     * @param  array $template массив строк для запросов
     * @return array          
     */
    public function getRecordsArray($template){

        //Возращает список всех категорий
        $db_array =  Yii::app()->db->createCommand()
                    ->select($template[0])
                    ->from('{{store_product}}')
                    ->queryAll();

        //Прокручиваем каждый товар в массиве для добавления к нему атрибутов
        foreach ($db_array as $key => $value) {
            //Получаем все атрибуты товара
            $attr_array = Yii::app()->db->createCommand()
                        ->from('{{store_product_attribute_eav}}')
                        ->where('product_id=:id', [':id'=>$value['id']])
                        ->queryAll();

            //Cоздаем структуру массива заполненого NULLами
            $template_keys = array_fill_keys(explode(',', $template['Attrs']), NULL);
            //Удалим последний эллемнт, он пустой
            array_pop($template_keys);

            //Присваиваем структуру нашему массиву
            $db_array[$key]['Attrs'] = $template_keys;
            //Наполняем значениями
            foreach ($attr_array as $attr_key => $attr_value) {
                $db_array[$key]['Attrs'][$attr_value['attribute']] = $attr_value['value'];
            }
        }    
        
        return $db_array;
    }


}