<?php

namespace PhpQuerySql\engine;

require_once "PhpQuerySql.php";
/**
 * Kelas builder
 * 
 * @property table $Table
 * 
 * @method self SetTables(string $name)
 * @method string GetTables()
 * @method \PhpQuerySql\PhpQuerySql GetParent()
 */
class builder
{
    public $Insert, $Update, $Delete, $Select;
    private $tables, $parent;
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
        
        require_once "table.php";
        $this->Table  = new table($this);
    }
    /**
     * call class dynamic
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|void
     */
    function __call($name, $arguments)
    {
        switch ($name):
            case 'SetTables':
                if (count($arguments) == 1) :
                    $this->tables = $arguments[0];
                    return $this;
                else :
                    throw new ParameterSendNotValid();

                endif;
                break;
            case 'GetTables':
                if (count($arguments) == 0) :
                    return $this->tables;
                else :
                    throw new ParameterSendNotValid();
                endif;
                break;
            case 'GetParent':
                if (count($arguments) == 0) :
                    return $this->parent;
                else :
                    throw new ParameterSendNotValid();
                endif;
                break;
            default:
        endswitch;
    }
    // function SetTables(string $name): self
    // {
    //     $this->tables = $name;
    //     return $this;
    // }
    // function GetTables(): string
    // {
    //     return $this->tables;
    // }
    // function GetParent(): \PhpQuerySql\PhpQuerySql
    // {
    //     return $this->parent;
    // }
}

class ParameterSendNotValid extends \Exception
{
    public function __construct()
    {
        parent::__construct("Parameter send not valid");
    }
}
