<?php

namespace PhpQuerySql\engine;

require_once "PhpQuerySql.php";
/**
 * Kelas builder
 * 
 * @property generate\generate $Generate 
 * @property crud\insert $Insert
 * @property crud\delete $Delete
 * @property crud\update $Update
 * @property crud\select $Select
 * 
 * @method self SetTables(string $name)
 * @method string GetTables()
 * @method \PhpQuerySql\PhpQuerySql GetParent()
 */
class builder
{ 
    private $tables, $parent;
    function __construct(\PhpQuerySql\PhpQuerySql &$parent)
    {
        $this->parent = $parent;

        require_once implode(DIRECTORY_SEPARATOR,["crud","insert.php"]);
        $this->Insert  = new crud\insert($this);

        require_once implode(DIRECTORY_SEPARATOR,["crud","delete.php"]);
        $this->Delete  = new crud\delete($this);

        require_once implode(DIRECTORY_SEPARATOR,["crud","select.php"]);
        $this->Select  = new crud\select($this);

        require_once implode(DIRECTORY_SEPARATOR,["crud","update.php"]);
        $this->Update  = new crud\update($this);
         
        require_once implode(DIRECTORY_SEPARATOR,["generate","generate.php"]);
        $this->Generate  = new generate\generate($this);
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
    function __debugInfo()
    {
        return (array)$this;
    }
}

class ParameterSendNotValid extends \Exception
{
    public function __construct()
    {
        parent::__construct("Parameter send not valid");
    }
}
