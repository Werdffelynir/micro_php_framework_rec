<?php
/**
 * Created by PhpStorm.
 * User: Werdffelynir
 * Date: 01.08.14
 * Time: 1:10
 */

class ControllerBase extends RecController{

    /** @var string $layout (app/Views/layout/main.php) */
    public $layout = 'main';

    /** @var string $layout (app/Views/main.php) */
    public $partial = 'main';

    /** @var bool $auth авторизация пользователя */
    public $auth = false;

    public function __construct() {
      parent::__construct();
    }

    public function before() {}

    public function init()
    {
      $auth = Request::cookie('auth');

      if($auth)
        $this->auth = $auth;
    }

    /* COMMON FUNCTION */


  /**
   * Возвращает лимитированое количество символов из строки
   *
   * @param string $text  входящая строка
   * @param int    $limit количество выводимых символов
   * @param string $more  строка в конце результата
   * @return string
   */
  public function limitChars($text, $limit=200, $more='')
  {
    $text=  mb_substr($text,0,$limit);
    if(mb_substr($text, mb_strlen($text)-1,1) && mb_strlen($text) == $limit) {
      $textReturn = mb_substr($text,0,mb_strlen($text)-mb_strlen(strrchr($text,' ')));
      if(!empty($textReturn)) {
        return $textReturn.$more;
      }
    }
    return $text;
  }


  /**
   * Возвращает лимитированое количество целых слов из строки
   *
   * @param string $input_text  входящая строка
   * @param int    $limit       количество выводимых слов
   * @param string $end_str     строка в конце результата
   * @return string
   */
  public function limitWords($input_text, $limit = 50, $end_str = '')
  {
    $input_text = strip_tags($input_text);
    $words = explode(' ', $input_text);
    if ($limit < 1 || sizeof($words) <= $limit) {
      return $input_text;
    }
    $words = array_slice($words, 0, $limit);
    $out = implode(' ', $words);
    return $out.$end_str;
  }


} 