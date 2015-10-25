<?php
/**
* TODO хм. тоже надо привести в порядок, а может оно уже и не нужно вовсе
*/
class DProduct extends CFormModel
{
    public $id = null;
    public $category_id;
    public $sku;
    public $name;
    public $alias;
    public $price;
    public $discount_price;
    public $discount;
    public $short_description;
    public $description;
    public $is_special;
    public $quantity;
    public $in_stock;
    public $status;
    public $create_time;
    public $update_time;
    public $image;
    public $id_1c;

    private $db;

    public function rules()
    {
        return [
            ['name, alias, id_1c', 'required'],
            ['name, description, short_description, alias, price, status, is_special, id_1c', 'filter', 'filter' => 'trim'],
            ['status, is_special, quantity, in_stock, category_id, id_1c', 'numerical', 'integerOnly' => true],
            ['price', 'store\components\validators\NumberValidator'],
            ['name, image', 'length', 'max' => 250],
            ['sku', 'length', 'max' => 100],
            ['alias', 'length', 'max' => 150],
            ['alias', 'yupe\components\validators\YSLugValidator', 'message' => 'Illegal characters in {attribute}'],
            ['alias', 'uniqueValid'],
            // ['status', 'in', 'range' => [0,1,2]],
            // ['is_special', 'in', 'range' => [0, 1]],
        ];
    }

    public function beforeValidate()
    {
        $this->db = Yii::app()->db;
        // Категория
        $category_id = $this->db->createCommand('
            SELECT id
            FROM yupe_store_category
            WHERE id_1c = '.$this->category_id)
        ->queryRow();
        
        if (empty($category_id)) // категории нет в базе
            $this->category_id = '449';
        else // категория присутствует в базе, возвращаем ее идентификатор
            $this->category_id = $category_id['id'];

        // Товар
        $product = $this->db->createCommand()
        ->select('id, alias')
        ->from('{{store_product}}')
        ->where('id_1c=:id', [':id'=>$this->id_1c])
        ->queryRow();

        if (empty($product)) { // товар в базе не существует
            # code...
        }else{ // товар существует
            $this->id = $product['id'];
            $this->alias = $product['alias'];
        }

        // Даты
        $this->create_time = date('Y-m-d h:m:s', time());
        $this->update_time = date('Y-m-d h:m:s', time());

        return parent::beforeValidate();
    }

    public function afterValidate()
    {
        if (!$this->hasErrors()) {
            $path = Yii::getPathOfAlias('webroot').'/../uploads/in/images/'.$this->image;
            $pathStore = Yii::getPathOfAlias('webroot').'/uploads/store/product/'.$this->image;

            if (file_exists($path)) { // Файл доступен по пути
                $data = @file_get_contents($path);
                $res = @imagecreatefromstring($data);
                if (is_resource($res))// Файл коректно открывается
                    if (copy($path, $pathStore)) { // Файл скопировался
                        $this->image = basename($path);
                        @unlink($path); // удалить файл
                    }
                else
                    $this->image = null;
            } else
                $this->image = null;
        }

        return parent::afterValidate();
    }

    public function uniqueValid($attribute,$params)
    {
        if (!$this->id) {
            $product = $this->db->createCommand('
                SELECT id
                FROM yupe_store_product
                WHERE alias = "'.$this->$attribute.'"')
            ->queryRow();

            if (!empty($product))
                $this->alias = $this->alias.rand(1,10000);
        }
        
    }

}
?>