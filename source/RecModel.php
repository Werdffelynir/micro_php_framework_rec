<?php



/**
 *
 */
class RecModel
{
  public  static $db = null;
  private static $dbhGroup = null;
  public  static $connectionCount = 0;
  private static $_models;

  public function __construct()
  {
      $this->init();
  }


  public function init(){}


  /**
   * Метод универсального доступу к моделя, метод переписываеться в создаваемых маделях
   * обезательно
   *
   * @param   string  $className  Имя класса модели
   * @return  mixed
   */
  public static function model($className = __CLASS__)
  {
    if (isset(self::$_models[$className])) {
      return self::$_models[$className];
    } else {
      $model = self::$_models[$className] = new $className();
      return $model;
    }
  }


  /**
   * Установка соединение и проверка соединения
   *
   * <pre>
   * Пример:
   * // Создание первого основного подключения
   * $connect='mysql:host=localhost;dbname=myDatabaseName';
   * $user='root';
   * $pass = '';
   * $DB1Cheack = Model::setConnection('mysql1', $connect, $user, $pass);
   * if(!$DB1Cheack) die('Not Connect to database!');
   *
   *
   * // Создание второго подключения
   * $connect='sqlite:D:\server\domains\experement.loc\litemvc\docs\DataBase\database.db';
   * $DB2Cheack = Model::setConnection('sqlite', $connect);
   * if(!$DB2Cheack) die('Not Connect to database!');
   *
   * <pre>
   *
   * @param string $connectionName Имя соединения, обезательно если подключений несколько,
   *                               обращение к подключеню по его имене через метод
   *                               getConnection(name)
   *
   * @param string $config         Строка конфигурации соединения. Например:<br><br>
   *                               # MS SQL Server и Sybase через PDO_DBLIB<br>
   *                                     "mssql:host=localhost;dbname=my_batabase"<br>
   *                                     "sybase:host=localhost;dbname=my_batabase"<br>
   *
   *                               # MySQL через PDO_MYSQL<br>
   *                                     "mysql:host=localhost;dbname=my_batabase"<br>
   *
   *                               # SQLite<br>
   *                                     "sqlite:my/database/path/database.db"<br>
   *
   *                               # Oracle<br>
   *                                     "oci:dbname=//dev.mysite.com:1521/orcl.mysite.com;charset=UTF8"<br>
   * @param string $name           Имя логин к базе данных
   * @param string $password       Пароль к базе данных
   * @return bool
   */
  public static function setConnection($connectionName='db',$config,$name='root',$password='')
  {

    $db = new Model($config,$name,$password);

    $db->tableName = $connectionName;

    if($db != null){
      self::$connectionCount+=1;
      self::$dbhGroup[$connectionName] = $db;
      return true;
    } else
      return false;
  }

  /**
   * Возвращает установленное соединение
   *
   * <pre>
   * Пример:
   *
   * // Способ выборки статический с основного соединения
   * $products = Model::$db->query('select * from products where buyPrice>100')->all();
   *
   * // Способ выборки первого соединения
   * $DB1 = $Model::getConnection('mysql');
   * $products = $DB1->query('select * from products where buyPrice>100')->all();
   *
   * // Способ выборки с второго соединения
   * $DB2 = $Model::getConnection('sqlite');
   * $result = $DB2->query('SELECT * FROM pages WHERE id>10')->all();
   * <pre>
   *
   * @param  string       $name     Имя соединение, при условии что оно существует
   * @return bool|object
   */
  public static function getConnection($name=null)
  {
    if(self::$dbhGroup==null) return false;

    if($name==null || count(self::$dbhGroup)==1){
      $db = array_values(self::$dbhGroup);
      self::$db = $db[0];
      return $db[0];
    }else
      if(!empty(self::$dbhGroup[$name])){
        return self::$dbhGroup[$name];
      } else
        return false;
  }
}



/**
 * 
 */
class Model
{
	public static $DB = null;
	public static $STH = null;

	  public $sql = null;
	  public $tableName = null;
    public $config = null;
    public $name = null;
    public $password = null;

	public function __construct($config,$name,$password)
	{
		try {
			$this->init($config,$name,$password);
		} catch (PDOException $e) {
		    echo 'Подключение не удалось: ' . $e->getMessage();
		}
	}


    /**
     * Инициализация PDO
     *
     * @param string    $config     
     * @param string    $name
     * @param string    $password
    */
    private function init($config,$name,$password)
    {
        self::$DB = new RecPDO($config,$name,$password);
        $this->config = $config;
        $this->name = $name;
        $this->password = $password;
    }


	public function __get($name)
	{
		if($name=='db')
			return self::$DB;
	}


    /**
     * Слипой не безопасный метод выполнения запросов
     *
     * @param string    $sql    SQL запрос
     * @return mixed            Колчество затронутых строк
    */
    public function exec($sql)
    {
        $this->checkConnect();	
        $this->sql = $sql;

        try {
            $count = self::$DB->exec($sql);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        if(!$count) $this->checkConnect();
        return $count;
    }


    /**
     * Базовый метод запросов к базе данных.
     * Использует стандартный метод execute() через обертку, принимает sql запрос,
     * или если указан второй параметр происходит выполнение через метод prepare()
     * возвращает екземпляр обекта
     *
     * Запросы осуществляються:
     * <pre>
     * ->query( "INSERT INTO blog (title, article, date) values (:title, :article, :date)",
     *      array(
     *          'title' => $title,
     *          'article' => $article,
     *          'date' => time()
     *          )
     *      )
     *
     * ->query( "INSERT INTO blog (title, article, date) values (?, ?, ?)",
     *      array(
     *          $title,
     *          $article,
     *          $date
     *          )
     *      )
     * ->query( "SELECT title, article, date FROM blog WHERE id=:id",
     *      array('id'=> '215')
     *      )
     * ->row()
     * ->all()
     * <pre>
     * @param string $sql   Принимает открытый SQL запрос или безопасный
     * @param array  $data  Значения для безопасного запроса
     * @return $this        Возвращает екземпляр обекта
     */
    public function query($sql, array $data = null)
    {
        $this->checkConnect();
        $this->sql = $sql;

        if (is_null($data)) {
            self::$STH = self::$DB->prepare($sql);
            if(!self::$STH)
				$this->checkConnect(true);	
            self::$STH->execute();
        } else {
            self::$STH = self::$DB->prepare($sql);
            if(!self::$STH)
				$this->checkConnect(true);	
	        self::$STH->execute($data);
        }
        return $this;
    }


    /**
     * Извлечь строку с запроса
     *
     * Выберает типы: assoc, class, obj
     * 
     * @param  string   $type   использует FETCH_ASSOC, FETCH_CLASS, и FETCH_OBJ.
     * @return mixed
     */
	public function row($type = 'assoc')
    {
        if ($type == "assoc") self::$STH->setFetchMode(RecPDO::FETCH_ASSOC);
        if ($type == "obj") self::$STH->setFetchMode(RecPDO::FETCH_OBJ);
        if ($type == "class") self::$STH->setFetchMode(RecPDO::FETCH_CLASS);
        return self::$STH->fetch();
    }


    /**
     * Извлечь несколько строк
     *
     * @param  $type
     * @return array
     */
    public function all($type = 'assoc')
    {
        if ($type == "assoc") self::$STH->setFetchMode(RecPDO::FETCH_ASSOC);
        if ($type == "obj") self::$STH->setFetchMode(RecPDO::FETCH_OBJ);
        if ($type == "class") self::$STH->setFetchMode(RecPDO::FETCH_CLASS);

        $result = array();
        while ($rows = self::$STH->fetch()) {
            $result[] = $rows;
        };
        return $result;
    }


    /**
     * Обертка INSERT
     * <pre>
     * ->insert("pages", array("title","link","content","datetime","author"),
     *      array(
     *          'title'     =>'SOME TITLE',
     *          'link'      =>'SOME LINK',
     *          'content'   =>'SOME CONTENT',
     *          'datetime'  =>'SOME DATETIME',
     *          'author'    =>'SOME AUTHOR',
     *      ));
     * С генерирует SQL запрос:
     * "INSERT INTO pages (title,link,content,datetime,author)
     *      VALUES (:title,:link,:content,:datetime,:author)"
     * и подставит необходимые значения.
     * </pre>
     *
     * @param $table - Имя таблицы
     * @param array $dataColumn - Масив названий колонок для обновлеия
     * @param array $dataValue - Массив значений для установленных $dataColumn
     * @return bool
     */
    public function insert($table, array $dataColumn, array $dataValue)
    {
        if (count($dataColumn) == count($dataValue)) {
            $constructSql = "INSERT INTO " . $table . " (";
            $constructSql .= implode(", ", $dataColumn);
            $constructSql .= ") VALUES (";
            $constructSql .= ':' . implode(", :", $dataColumn);
            $constructSql .= ")";

            self::$STH = self::$DB->prepare($constructSql);
            $resultInsert = self::$STH->execute($dataValue);
            return $resultInsert;
        } else {
            die("Количество полей не соответствует количеству значений!");
        }
    }


    /**
     * Метод обертка UPDATE
     * 
     * <pre>
     * ->update( 'table', array('column'),array('data'), 'id=50' || array('id=:id', array('id'=>50)) );
     * 
     * ->update(
     *      "pages", 
     *      array("type","link","category","title","content","datetime","author"),
     *      array(
     *          'type'     =>'SOME DATA TITLE',
     *          'link'     =>'SOME DATA LINK',
     *          'category' =>'SOME DATA CATEGORY',
     *          'title'    =>'SOME DATA TITLE',
     *          'content'  =>'SOME DATA CONTENT',
     *          'datetime' =>'SOME DATA TIME',
     *          'author'   =>'SOME DATA AUTHOR',
     *          ),
     *      "id=13"
     *  );
     *
     * ->update(
     *      "pages", 
     *      array("type","link","category","title","content","datetime","author"),
     *      array(
     *          'type'     =>'SOME DATA TITLE',
     *          'link'     =>'SOME DATA LINK',
     *          'category' =>'SOME DATA CATEGORY',
     *          'title'    =>'SOME DATA TITLE',
     *          'content'  =>'SOME DATA CONTENT',
     *          'datetime' =>'SOME DATA TIME',
     *          'author'   =>'SOME DATA AUTHOR',
     *      ),
     *      array("id=:updId AND title=:updTitle", array('updId'=>13, 'updTitle'=>'SOME TITLE'))
     *  );
     * Сгенерирует: "UPDATE pages SET title=:title, type=:type, link=:link, category=:category, subcategory=:subcategory, content=:content, datetime=:datetime WHERE id=:updId AND title=:updTitle;"
     * </pre>
     *
     * @param $table - Имя таблицы
     * @param array $dataColumn - Масив названий колонок для обновлеия
     * @param array $dataValue - Массив значений для установленных $dataColumn
     * @param $where - определение, строка НЕ безопасно "id=$id", или безопасный вариант array( "id=:updId", array('updId'=>$id))
     * @return bool
     */
    public function update($table, array $dataColumn, array $dataValue, $where = null)
    {
        if (count($dataColumn) == count($dataValue)) {
            $constructSql = "UPDATE " . $table . " SET ";

            for ($i = 0; $i < count($dataColumn); $i++) {
                if ($i < count($dataColumn) - 1) {
                    $constructSql .= $dataColumn[$i] . "=:" . $dataColumn[$i] . ", ";
                } else {
                    $constructSql .= $dataColumn[$i] . "=:" . $dataColumn[$i] . " ";
                }
            }

            if (is_string($where)) {
                $constructSql .= " WHERE " . $where;
            } elseif (is_array($where) AND is_array($where[1])) {
                $constructSql .= " WHERE " . $where[0];
                $dataValue = array_merge($dataValue, $where[1]);
            }

            self::$STH = self::$DB->prepare($constructSql);
            $resultUpdate = self::$STH->execute($dataValue);

            return $resultUpdate;
        } else {
            die("Количество полей не соответствует количеству значений!");
        }
    }


    /**
     * Обертка удаления
     *
     * <pre>
     * Например:
     * ->delete( 'table', 'key=val' || array('key=:key', array('key'=>val));
     * ->delete('Users','id=21');
     * ->delete('Users', array('id=:id', array('id'=>'21'));
     * </pre>
     *
     * @param string $table    название таблицы
     * @param string $where    Часть запроса SQL where
     * @return mixed
     */
    public function delete($table,  $where = null)
    {
        $dataValue = null;

        $constructSql = "DELETE FROM " . $table;
        
        if (is_string($where)) {
            $constructSql .= " WHERE " . $where;
        } elseif (is_array($where) AND is_array($where[1])) {
            $constructSql .= " WHERE " . $where[0];
            $dataValue = $where[1];
        }

        self::$STH = self::$DB->prepare($constructSql);
        $resultUpdate = self::$STH->execute($dataValue);

        return $resultUpdate;
    }



    private function checkConnect($checkSTH=false)
    {
        if (self::$DB == null) die("Connection with DataBase closed!");

        if($checkSTH)
        	if (self::$STH == null)  die('Error SQL string! Check your query string, error can be names :<br><span style="color:red">'.$this->sql.'</span>');
    }


    /**
     * Выбирает все записи с указанной таблицы.
     * Если указан второй аргумент выбирает только те поля что вказаны в нем
     *
     * <pre>
     * Например:
     *
     * ->getAll("table");
     *
     * ->getAll("table", "title, content, author");
     *
     * ->getAll("table", array(
     *      "title",
     *      "content",
     *      "author"
     * ));
     *
     * </pre>
     *
     * @param string            $tbl    название таблицы
     * @param null|string|array $select если string через запятую, выберает указаные поля,
     *                                  если array по значених выберает указаные
     * @param string            $where  Часть запроса SQL where
     * @param string            $order  Часть запроса SQL order
     * @return mixed
     */
    public function getAll($tbl, $select = null, $where = '', $order='')
    {
        $sql = '';
        if ($select == null) {
            $sql = "SELECT * FROM " . $tbl;
        } elseif (is_string($select)) {
            $sql = "SELECT " . $select . " FROM " . $tbl;
        } elseif (is_array($select)) {
            $column = implode(", ", $select);
            $sql = "SELECT " . $column . " FROM " . $tbl;
        }
        $sql .= (!empty($where)) ? ' WHERE ' . $where : '';
        $sql .= (!empty($order)) ? ' ORDER BY ' . $order : '';
        return $this->query($sql)->all();
    }


    /**
     * Выберает все с указаной таблицы по id
     * <pre>
     * Например:
     *
     * ->getById("table", 215);
     *
     * ->getById("table", 215, "title, content, author");
     *
     * ->getById("table", 215, array(
     *      "title",
     *      "content",
     *      "author"
     * ));
     *
     * </pre>
     * @param $tbl      название таблицы
     * @param $id       id записи
     * @param $select (string|array) если string через запятую, выберает указаные,
     *                               если array по значених выберает указаные
     * @return mixed
     */
    public function getById($tbl, $id, $select = null)
    {
        if ($select == null) {
            $sql = "SELECT * FROM " . $tbl . " WHERE id='" . $id . "'";
        } elseif (is_string($select)) {
            $sql = "SELECT " . $select . " FROM " . $tbl . " WHERE id='" . $id . "'";
        } elseif (is_array($select)) {
            $column = implode(", ", $select);
            $sql = "SELECT " . $column . " FROM " . $tbl . " WHERE id='" . $id . "'";
        }

        return $this->query($sql)->row();
    }


    /**
     * Выберает одну запись с указаной таблицы по названию колонки
     *
     * <pre>
     * Например:
     *
     * ->getByAttr("table", "column", "column_value");
     *
     * ->getByAttr("table", "column", "column_value", "title, content, author");
     *
     * ->getByAttr("table", "column", "column_value", array(
     *      "title",
     *      "content",
     *      "author"
     * ));
     *
     * ->getByAttr("table", "column", "column_value", null, "AND link='my_link'");
     *
     * </pre>
     *
     * @param string            $tbl        название таблицы
     * @param string            $attr       название колонки
     * @param string            $attrVal    значение в колонке
     * @param string|array      $select     если string через запятую, выберает указаные, если array по значених выберает указаные
     * @param string            $andWhere   AND WHERE
     * @return array
     */
    public function getByAttr($tbl, $attr, $attrVal, $select = null, $andWhere=null)
    {
        $setWhere = ($andWhere!=null) ? $andWhere : '';

        if ($select == null) {
            $sql = "SELECT * FROM " . $tbl . " WHERE " . $attr . "='" . $attrVal . "' ".$setWhere." ";
        } elseif (is_string($select)) {
            $sql = "SELECT " . $select . " FROM " . $tbl . " WHERE " . $attr . "='" . $attrVal . "' ".$setWhere."";
        } elseif (is_array($select)) {
            $column = implode(", ", $select);
            $sql = "SELECT " . $column . " FROM " . $tbl . " WHERE " . $attr . "='" . $attrVal . "' ".$setWhere."";
        }

        return $this->query($sql)->row();
    }


    /**
     * Выберает все с указаной таблицы по названию колонки
     *
     * <pre>
     * Например:
     *
     * ->getAllByAttr("table", "column", "column_value");
     *
     * ->getAllByAttr("table", "column", "column_value", "title, content, author");
     *
     * ->getAllByAttr("table", "column", "column_value", array(
     *      "title",
     *      "content",
     *      "author"
     * ));
     *
     * </pre>
     * @param string        $tbl        Таблица
     * @param string        $attr       По атрибуту, колонке
     * @param string        $attrVal    Значение $attr по которому делается поиск
     * @param string        $andWhere
     * @param string|array  $select     Поля что  нужно выбрать
     *                                      если string через запятую, выберает указаные,
     *                                      если array по значених выберает указаные
     * @return mixed
     */
    public function getAllByAttr($tbl, $attr, $attrVal, $select=null, $andWhere=null)
    {
        $setWhere = ($andWhere!=null) ? $andWhere : '';
        
        if ($select == null) {
            $sql = "SELECT * FROM " . $tbl . " WHERE " . $attr . "='" . $attrVal . "' ".$setWhere." ";
        } elseif (is_string($select)) {
            $sql = "SELECT " . $select . " FROM " . $tbl . " WHERE " . $attr . "='" . $attrVal . "' ".$setWhere." ";
        } elseif (is_array($select)) {
            $column = implode(",", $select);
            $sql = "SELECT " . $column . " FROM " . $tbl . " WHERE " . $attr . "='" . $attrVal . "' ".$setWhere." ";
        }
        return $this->query($sql)->all();
    }


    /**
     * Подсчет количества записй в таблице
     *
     * @param string    $tbl     таблица
     * @param string    $where   
     * @return bool|numeric
     */
    public function countRows($tbl, $where=null)
    {
        if($where!=null)
            $where = 'WHERE '.$where;

        $result = $this->query("SELECT COUNT(*) as counter FROM $tbl ".$where)->all();

        if(isset($result[0]['counter']))
            return $result[0]['counter'];
        else
            return false;
    }


    /**
     * Определение последнего ID в таблице
     *
     * @param string    $tbl    таблица
     * @param string    $primaryKey     имя столбца для подсчета
     * @return numeric
     */
    public function lastId($tbl, $primaryKey ='id')
    {
        $result = $this->query("SELECT $primaryKey FROM $tbl ORDER BY $primaryKey DESC ")->all();
        if(isset($result[0][$primaryKey]))
            return $result[0][$primaryKey];
        else
            return 0;
    }


    /**
     * Закрыть соединение
     */
    public function close()
    {
        self::$DB = null;
        unset(self::$DB);
    }


    /**
     *  Реконект
     * @return [type] [description]
     */
    public function reset()
    {
        self::$STH = null;
        self::$DB = null;
        
        try {
            $this->init($this->config,$this->name,$this->password);
        } catch (PDOException $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
        }
    }


}

