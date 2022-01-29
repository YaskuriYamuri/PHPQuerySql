<?php

namespace PhpQuerySql\engine;
 require_once "PhpQuerySql.php";
/**
 * Undocumented class
 */
class builder
{
    public $Insert,$Update,$Delete,$Select;
    private $tables,$parent;
    function __construct(\PhpQuerySql\PhpQuerySql &$parent)
    {
        require_once "insert.php";
        $this->parent = $parent;
        $this->Insert  = new insert($this);

        require_once "delete.php";
        $this->Delete  = new delete($this);

        require_once "select.php";
        $this->Select  = new select($this);

        require_once "update.php";
        $this->Update  = new update($this);
    }
    function SetTables(string $name):self{
        $this->tables=$name;
        return $this;
    }
    function GetTables():string{
        return $this->tables;
    }
    function GetParent():\PhpQuerySql\PhpQuerySql{
        return $this->parent;
    }
}
