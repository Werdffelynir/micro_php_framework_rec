<?php

require_once('ControllerBase.php');

class Main extends ControllerBase
{

    public function error404()
    {
        parent::error404();
    }

    public function index()
    {
        $pageHome = Items::model()->pageHome(1);
        $this->render('main',
            array(
                'title' => "<h1>$pageHome[title]</h1>",
                'content' => $pageHome['text'],
            ));
    }

    public function login()
    {
        $tempUsername = 'admin';
        $tempPassword = 'admin';

        if (!$this->auth && !empty($_POST)) {
            $login = Request::post('username', true);
            $password = Request::post('password', true);

            if ($login == $tempUsername && $password == $tempPassword) {
                Request::cookie('auth', 'admin');
                Request::redirect('main');
            }
        }

        if (!$this->auth) {
            $loginForm = $this->renderPartial('chunk/login');
        } else {
            $loginForm = '<h3>Вход состоялся! Редирект через 3 сек.</h3>';
            Request::redirect('', 3);
        }

        $this->render('main',
            array(
                'title' => '<h1>Login</h1>',
                'content' => $loginForm,
            ));
    }

    public function logout()
    {
        Request::cookie('auth', null, -3600);
        Request::redirect('main');
    }

}