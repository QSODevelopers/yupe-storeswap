<?php

class TreeRecording extends CApplicationComponent
{
    
    private $result;
    private $level = 0;
    public $data;

    public function setData($data)
    {
        $this->data = $data;
        $this->searchRoot();
    }

    /**
     * Поиск корневых элементов
     */
    public function searchRoot()
    {
        foreach ($this->data as $key => $value) {
            if(array_search('', $value)){
                $this->result[$key] = $this->data[$key];
                unset($this->data[$key]);
            }
        }
        $this->search();
    }

    /**
     * Поиск дочерних элементов
     */
    public function search()
    {
        $tmp = [];
        foreach ($this->result as $key => $row) {
            $this->result[$key]['items'] = $this->recursiveSearch($row);
        }
    }

    public function recursiveSearch($row)
    {
        $id = $row['id'];
        $dataArray = [];
        $presence = false;

        foreach($this->data as $key => $data){
            if (array_search($id, $data)){
                unset($this->data[$key]);
                $dataArray[$key] = $data;
                $child = $this->recursiveSearch($data);
                if ($child) {
                    $dataArray[$key]['items'] = $child;
                }
                $presence = true;
            }
        }

        if ($presence)
            return $dataArray;
        else
            return false;
    }

    public function saveData($data = null, $parent_id = null)
    {
        $data = ($data===null) ? $this->result : $data;
        $db = Yii::app()->db;
        foreach ($data as $key => $value) {
            
            $id = (int)$value['id'];
            
            $db->createCommand()->insert('{{store_category}}', [
                'name'=>$value['name'],
                'parent_id'=>$parent_id,
                'alias'=>$value['name'].$id,
                'id_1c'=>$id,
            ]);

            if (isset($value['items'])) {
                $pr = $db->createCommand()
                    ->select('id')
                    ->from('{{store_category}}')
                    ->where(
                        'id_1c=:id', [
                            ':id' => $id
                        ]
                )->queryRow();
                $this->saveData($value['items'], $pr['id']);
            }
        }
    }

}

?>