<?php

require_once('ControllerBase.php');

class Administrator extends ControllerBase
{

    public function index($setType = null, $id = null)
    {
        $itemEdit = null;
        $typeEdit = (!empty($_POST['typeEdit']))?$_POST['typeEdit']:null;

        if ($setType == 'edit') {
            $itemEdit = Items::model()->getRecById($id);
            $typeEdit = 'update';
        }

        else if($typeEdit=='update') {

            $id = Request::post('id');
            $isDelete = Request::post('delete');

            if($isDelete=="Delete"){
                Items::model()->deleteRecById($id);
                Request::redirect('admin');
            }

            $data = $this->requestData();
            $itemEdit = Items::model()->updateRec($id, $data);

            if($itemEdit){}
                Request::redirect('admin/edit/'.$id);
        }

        else if($typeEdit=='insert') {

            $data = $this->requestData();
            $itemEdit = Items::model()->insertRec($data);
            $itemLest = Items::model()->database->lastId("items");
            Request::redirect('admin/edit/'.$itemLest);

        }

        else{

            $itemEdit = array('id'=>'','type'=>'blog','title'=>'','text'=>'');
            $typeEdit = 'insert';
        }


        $listEditMenuBlog = Items::model()->getRecAllByAttr("type", "blog");
        $listEditMenuMain = Items::model()->getRecAllByAttr("type", "main");

        $content = $this->renderPartial('chunk/admin', array(
            'listEditMenuBlog' => $listEditMenuBlog,
            'listEditMenuMain' => $listEditMenuMain,
            'typeEdit' => $typeEdit,
            'itemEdit' => $itemEdit,
        ));

        $this->render('admin/main',
            array(
                'title' => '<h1>Administration: edit records</h1>',
                'content' => $content,
            ));
    }


    private function requestData()
    {
        $data['title']   = Request::post('title');
        $data['text']    = Request::post('text');
        $data['time']    = date("d m Y");
        $data['visibly'] = 1;
        $data['type']    = Request::post('type');
        return $data;
    }


}