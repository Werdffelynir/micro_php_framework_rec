<?php
define('START_TIMER',microtime(true));

include "../source/Rec.php";
$R = new Rec('app');

$R->setApplicationName("Web Application Rec");

/** Added controllers and url requests */
$R->urlDefault('Main');
$R->urlNotFound('Main/error404');


/**
 * Alias to controllers and roles
 *      urlAdd(' Class/Method ', ' url/{!p}/{n} ')
 *
 *  {n} {!n} number [0-9]
 *  {w} {!w} words [a-z]
 *  {p} {!p} symbol [a-z0-9_-]
 *
 * Заметка: следить чтобы при создании экземпляра класса Class не вызывал одноименный себе конструктор
 */

$R->urlAdd('Main', 'main');
$R->urlAdd('Main/login', 'login/{p}');
$R->urlAdd('Main/logout', 'logout');

$R->urlAdd('Blog/listdocs', 'docs');
$R->urlAdd('Blog/article', 'doc/{!n}');
$R->urlAdd('Blog/download', 'download');


$R->run();