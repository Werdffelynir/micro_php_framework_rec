<?php


class Items extends RecModel
{

    /**
     * @var object $database RecModel
     */
    public $database = null;

    /**
     * @param string $className
     * @return mixed
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function init()
    {
        self::setConnection('db', 'sqlite:' . Rec::$pathApp . 'Database\documentation.sqlite');
        $this->database = self::getConnection('db');
    }


    /** Select Records
     * --------------------------------------------------------- */

    public function pageHome($id)
    {
        return $this->database->getById('items', $id);
    }


    public function pageList()
    {
        return $this->database->getAll("items", null, "type='blog'");
    }


    public function getRecById($id)
    {
        return $this->database->getById('items', $id);
    }

    public function getRecByAttr($attr, $attrVal)
    {
        return $this->database->getByAttr('items', $attr, $attrVal);
    }

    public function getRecAllByAttr($attr, $attrVal)
    {
        return $this->database->getAllByAttr('items', $attr, $attrVal);
    }

    public function pageDownload($id)
    {
        return $this->database->getById($id);
    }


    /** Change table item
     * --------------------------------------------------------- */

    public function getListEditMenu()
    {
        return $this->database->getAll("items");
    }


    public function insertRec($data)
    {

        return $this->database->insert(
            "items",
            array("title","text","time","visibly","type"),
            array(
                'title'    =>$data['title'],
                'text'     =>$data['text'],
                'time'     =>$data['time'],
                'visibly'  =>$data['visibly'],
                'type'     =>$data['type'],
            ));
    }

    public function updateRec($id, $data)
    {
        return $this->database->update(
            "items",
            array("title","text","time","visibly","type"),
            array(
                'title'    =>$data['title'],
                'text'     =>$data['text'],
                'time'     =>$data['time'],
                'visibly'  =>$data['visibly'],
                'type'     =>$data['type'],
            ),
            "id=".$id);
    }


    public function deleteRecById($id)
    {
        return $this->database->delete("items","id='".$id."'");
    }

}