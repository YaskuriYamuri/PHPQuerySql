<?php

namespace PhpQuerySql\engine\schema;

require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "PhpQuerySql.php"]));
/**
 * Kelas generate
 * @method table Table()
 * @method database Database()
 * @method \PhpQuerySql\PhpQuerySql GetParent()
 */
class schema
{
    function __construct(\PhpQuerySql\PhpQuerySql $parent)
    {
        $this->parent = $parent;
        require_once(implode(DIRECTORY_SEPARATOR, ["table.php"]));
        $this->tb = new table($this);
        require_once(implode(DIRECTORY_SEPARATOR, ["database.php"]));
        $this->db = new database($this);
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case "Table":
                if (count($arguments) <> 0) throw new UnknownSchemaMethodCallParamException;
                return $this->tb;
                break;
            case "Database":
                if (count($arguments) <> 0) throw new UnknownSchemaMethodCallParamException;
                return $this->db;
                break;
            case "GetParent":
                if (count($arguments) <> 0) throw new UnknownSchemaMethodCallParamException;
                return $this->parent;
                break;
            default:
                throw new UnknownSchemaMethodCallException($name);
                break;
        endswitch;
    }

    function __debugInfo()
    {
        return ["Table" => $this->Table(), "Database" => $this->Database()];
    }
}
class UnknownSchemaMethodCallException extends \Exception
{
    public function __construct($name)
    {
        parent::__construct("Unknown schema method call " . $name);
    }
}
class UnknownSchemaMethodCallParamException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Unknown schema method call param set");
    }
}
