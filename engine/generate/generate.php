<?php

namespace PhpQuerySql\engine\generate; 
require_once(implode(DIRECTORY_SEPARATOR,[__DIR__,"..","builder.php"]));
/**
 * Kelas generate
 * @method table Table()
 * @method \PhpQuerySql\engine\builder GetParent()
 */
class generate
{
    function __construct(\PhpQuerySql\engine\builder $parent)
    {
        $this->parent = $parent;
        require_once(implode(DIRECTORY_SEPARATOR,["table.php"]));
        $this->tb = new table($this);
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case "Table":
                if (count($arguments) <> 0) throw new UnknownGenerateMethodCallParamException;
                return $this->tb;
                break;
            case "GetParent":
                if (count($arguments) <> 0) throw new UnknownGenerateMethodCallParamException;
                return $this->parent;
                break;
            default:
                throw new UnknownGenerateMethodCallException($name);
                break;
        endswitch;
    }
}
class UnknownGenerateMethodCallException extends \Exception
{
    public function __construct($name)
    {
        parent::__construct("Unknown generate method call " . $name);
    }
}
class UnknownGenerateMethodCallParamException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Unknown generate method call param set");
    }
}
