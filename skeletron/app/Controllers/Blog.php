<?php

require_once('ControllerBase.php');

class Blog extends ControllerBase
{

    public function listdocs()
    {
      $docsItem = Items::model()->getRecById(2);
      $listdocs = Items::model()->pageList();
      $pageContent = $this->renderPartial('chunk/listdocs',array(
            'listdocs'=>$listdocs
          ));

      $this->render('main',
          array(
              'title'=>"<h1>$docsItem[title]</h1>",
              'content'=>$docsItem['text'].'<br><br>'.$pageContent,
          ));
    }


    public function article($id)
    {
      $content = Items::model()->getRecById($id);

      $this->render('main',
          array(
              'title'=>"<h1>$content[title]</h1>",
              'content'=>$content['text'],
          ));
    }


    public function download()
    {
      $content = Items::model()->pageHome(3);

      $this->render('main',
          array(
              'title'=>"<h1>$content[title]</h1>",
              'content'=>$content['text'],
          ));
    }

} 