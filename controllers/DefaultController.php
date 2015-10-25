<?php

//TODO Тут какой то ужас творится. Надо все убрать и оставить нужное.
Yii::import('application.modules.store.models.Product');
class DefaultController extends \yupe\components\controllers\FrontController
{
    public function actions()
    {
        return [
            'outimage' => [
                'class' => 'application.modules.storeswap.controllers.actions.OutImageAction',
            ],
        ];
    }

	public function actionIndex($key)
	{
        if ($key!='df9abe56a3487ba17d53dabf6b1abd9e')
            throw new CHttpException(404, "Not faund");
        
        $db = Yii::app()->db;
        $readableFile = Yii::getPathOfAlias('webroot').$this->module->folderFiles;
        $content = @file_get_contents($readableFile.'/'.$this->module->readFileProducts);

        $errors = [];
        $productId = [];
        echo '<pre>';
        if ($content) {
            foreach (explode("\n", $content) as $id => $string) { // разбитие по строке

                $record = new DataRecording;
                $record->db = $db;
                $record->startLine(++$id, $string);

                if (!empty($record->errors))
                    print_r($record->errors);
                else
                    $productId[] = $record->productId;

            }
            $criteria = new CDbCriteria();
            $criteria->addCondition('status = :status');
            $criteria->params = [':status' => 1];
            $criteria->addNotInCondition('id', $productId);
            $model = Product::model()->findAll($criteria);
            foreach ($model as $key => $value) {
                $value->status = 2;
                $value->save();
            }

        }
        // $this->render('index');
	}

    public function actionHideremnants()
    {
        $readableFile = Yii::getPathOfAlias('webroot').$this->module->folderFiles;
        $content = @file_get_contents($readableFile.'/'.$this->module->readFileProducts);

        $errors = [];
        $productId = [];
        $IdC = [];
        if ($content) {
            foreach (explode("\n", $content) as $id => $string) {
                $attr = explode(";", $string);
                $IdC[] = $attr[2];
            }
            $criteria = new CDbCriteria;
            $criteria->select = 'id';
            $criteria->compare('id_1c', $IdC, true);
            $products = Product::model()->findAll($criteria);
            foreach ($products as $key => $value) {
                $productId[] = $value->id;
            }
            $criteria = new CDbCriteria();
            $criteria->addCondition('status = :status');
            $criteria->params = [':status' => 1];
            $criteria->addNotInCondition('id', $productId);
            $model = Product::model()->findAll($criteria);
            foreach ($model as $key => $value) {
                $value->status = 2;
                $value->save();
            }
        }
    }

    public function actionUpdateImage()
    {
        $criteria = new CDbCriteria;
        $criteria->addCondition('image is not null');
        $products = Product::model()->findAll($criteria);
        $errors = [];
        foreach ($products as $key => $model) {
            $data = @file_get_contents($model->prevFile);
            $res = @imagecreatefromstring($data);
            if (!is_resource($res)) {
                $model->image = null;
                $model->save();
                if (file_exists($model->prevFile) && !is_dir($model->prevFile)) {
                    unlink($model->prevFile);
                    $errors[] = $model->prevFile;
                }
            }
        }
        print_r($errors);
    }

    public function actionUpdateCategory()
    {
        // echo '<pre>';
        $db = Yii::app()->db;
        $readableFile = Yii::getPathOfAlias('webroot').$this->module->folderFiles;
        $content = @file_get_contents($readableFile.'/'.$this->module->readFileCategory);
        $data = [];
        echo '<pre>';
        if ($content) {
            foreach (explode("\n", $content) as $id => $string) {                            // разбитие по строке
                $arr = explode(';', $string);
                if (trim($arr[3])!='') {
                    $data[$arr[2]] = [
                        'id'=>$arr[2],
                        'parent_id'=>trim($arr[0]),
                        'name'=>$arr[3]
                    ];
                }
            }
            $obj = new TreeRecording;
            $obj->setData($data);
            $obj->saveData();
            print_r($obj);
        }
    }

    public function actionUpdateUrl()
    {
        $db = Yii::app()->db;
        $all = $db->createCommand()->select('id, name')->from('{{store_category}}')->queryAll();
        foreach ($all as $key => $value) {
            $db->createCommand()->update('{{store_category}}', [
                'alias'=>$this->convert($value['name'])
            ], 'id=:id', [':id'=>$value['id']]);
        }
    }

    public function convert($str)
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

    public function actionTestRead($size)
    {
        $readableFile = Yii::getPathOfAlias('webroot').$this->module->folderFiles;
        $content = @file_get_contents($readableFile.'/'.$this->module->readFileProducts);

        if ($content) {

            echo '<pre>';
            foreach (explode("\n", $content) as $key => $row) {             // разбитие по строке
                $row = trim($row, $this->module->columnSeparator."\r");
                echo '№' . ++$key . ' ' . $row."\r";
                $rowArray = explode($this->module->columnSeparator, $row);  // разбитие строки на массив
                print_r($rowArray);
                if ($key===(int)$size)
                    break;
            }
            echo '</pre>';
        }
    }
}