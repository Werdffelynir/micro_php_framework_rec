<?php

class RecPDO extends PDO { 
    
    public function __construct($config,$name,$password){ 
        parent::__construct($config,$name,$password);
    }

} 