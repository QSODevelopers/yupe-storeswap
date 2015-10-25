<?php
/**
* TODO хм. тоже надо привести в порядок
*/
Yii::import('application.modules.store.models.Product');

class OutImageAction extends CAction
{
    public $redirect = false;

    public function run()
    {
        $model = Product::model()->findAll('image is not null');

        $module = Yii::app()->getModule('exchange1c');

        $basePath = Yii::app()->uploadManager->getBasePath(); // путь до папки uploads public_html
        $path = Yii::getPathOfAlias('webroot').'/..'.$module->pathOut; // путь до папки out 1c

        $string = "Код товара;Имя файла;Размер в байтах;Время последней модификации\n"; // Описание информации

        foreach ($model as $key => $value) {
            $file = $basePath.'/'.$value->uploadPath.'/'.$value->image;
            if (file_exists($file)) {
                $info = stat($file);
                $nameImage = $value->image;
                $code = substr($value->image, 0, 11);
                $size = $info['size'];
                $mtime = $info['mtime'];
                
                $string .= "{$code};{$nameImage};{$size};{$mtime}\n";
            }
        }

        file_put_contents($path.'/images.csv', $string);

        if ($this->redirect)
            Yii::app()->getController()->redirect(['/exchange1c/exchangeBackend/images']);

        echo $string;
    }
}

?>