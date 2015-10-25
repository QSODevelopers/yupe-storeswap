<?php
//TODO Тут какой то ужас творится. Надо все убрать и оставить нужное.
Yii::import('application.modules.storeswap.components.ExcelCreator');

class ExchangeBackendController extends yupe\components\controllers\BackController
{

    

    public function actions()
    {
        return [
            'outimage' => [
                'class' => 'application.modules.storeswap.controllers.actions.OutImageAction',
                'redirect'=>true,
            ],
        ];
    }

    public function actionHelp()
    {
        $this->render('help');
    }
    
    public function actionUpdate()
    {
        set_time_limit(10000);
        $model = new FileModel;
        $this->render('update', [
            'model'=>$model,
        ]);
    }

    public function actionImages()
    {
        $model = new FileModel;

        $this->render('images', [
            'model'=>$model
        ]);
    }

    public function actionStart()
    {
        set_time_limit(10000);
        header("Content-Type: text/event-stream");
        header("Content-Encoding: none; ");
        header("Cache-Control: no-cache");
        header("Access-Control-Allow-Origin: *");

        ob_start();

        echo ":" . str_repeat(" ", 2048) . "\n"; // 2 kB padding for IE
        echo "retry: 2000\n";

        //-----------------------------
        $readableFile = Yii::getPathOfAlias('webroot').$this->module->folderFiles;
        $content = @file_get_contents($readableFile.'/'.$this->module->readFileProducts);

        if ($content) {
            foreach (explode("\n", $content) as $id => $string) {
                $array = explode(';', $string);
                
                $record = new DataRecording;
                $record->startLine(++$id, $string);
                $this->message($id, $record);
            }
        }
    }

    public function message($id, $record)
    {
        echo "id: " . $id . PHP_EOL;
        echo "data: <p><span class='label label-{$record->message}' title='{$record->getStrErrors()}'>{$record->message}</span> " . $record->arrayData['Product']['name'] . '</p>' . PHP_EOL;
        echo PHP_EOL . str_repeat(" ", 1024 * 64); // Заполняется ответ пробелами, необходимо для корректного вывода буфера

        ob_flush();
        flush();
    }


    public function actionUserprice(){
        $this->render('userprice');
    }

    public function actionUpdateprice(){
        $creator = new ExcelCreator();
        
        if ($creator->createExcelFile('price.xlsx'))
            echo 'Прайс успешно обновлен';
    }
}   
?>