<?php
/**
 * Created by PhpStorm.
 * User: Werdffelynir
 * Date: 01.08.14
 * Time: 1:10
 */

class ControllerTemplate extends RecController{

    /** @var string $layout (app/Views/layout/main.php) */
    public $layout = 'main';
    /** @var string $layout (app/Views/main.php) */
    public $partial = 'main';


    public function before()
    {

    }

    public function init()
    {

    }

    public function main()
    {
        $this->render('main',array());
    }


    public function pageOne()
    {
        $this->render('main',array());
    }


    public function pageTwo($num)
    {
        $this->render('main',array());
    }

}