<?php
/**
* TODO это вообще че?
*/
// namespace application\modules\exchange1c\models;

class FileModel extends CFormModel
{
    /**
     * @var string пудь до папки с файлами каталога 
     */
    public $folderFiles;

    /**
     * @var string имя файла с товарами
     */
    public $readFileProducts;

    /**
     * @var string имя файла с категориями
     */
    public $readFileCategory;


    public function init()
    {
        $this->folderFiles = Yii::app()->getModule('exchange1c')->folderFiles;
        $this->readFileProducts = Yii::app()->getModule('exchange1c')->readFileProducts;
        $this->readFileCategory = Yii::app()->getModule('exchange1c')->readFileCategory;
        return parent::init();
    }

    /**
     * @return string дата обновления файла продуктов
     */
    public function getDateFileProducts()
    {
        $path = Yii::getPathOfAlias('webroot').$this->folderFiles.'/'.$this->readFileProducts;
        return $this->getDateUpdateFileByPath($path);
    }

    /**
     * @return string дата обновления файла категорий
     */
    public function getDateFileCategories()
    {
        $path = Yii::getPathOfAlias('webroot').$this->folderFiles.'/'.$this->readFileCategory;
        return $this->getDateUpdateFileByPath($path);
    }

    /**
     * @return string дата обновления базы продуктов 
     */
    public function getDateDbProducts()
    {
        return null;
    }

    /**
     * @return string дата обновления базы категрий
     */
    public function getDateDbCategories()
    {
        return null;
    }

    /**
     * @return string количество загруженных из 1с изображений
     */
    public function getCountImage()
    {
        $path = Yii::getPathOfAlias('webroot').'/../uploads/in/images';
        return count(scandir($path));
    }

    /**
     * @return string количество изобьражений на сайте
     */
    public function getCountImageSite()
    {
        $path = Yii::getPathOfAlias('webroot').'/uploads/store/product';
        return count(scandir($path));
    }

    /**
     * Возвращает дату модификации файла по пути $path
     * @param string $path путь до файла
     * @return stirng дата модефикации файла
     */
    public function getDateUpdateFileByPath($path, $dateFormat = 'd MMMM yyyy')
    {
        $data = stat($path);
        return Yii::app()->dateFormatter->format($dateFormat, $data['mtime']);
    }

}
?>