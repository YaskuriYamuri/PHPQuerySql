<?php

namespace PhpQuerySql\engine;

require_once("builder.php");
/**
 * 
 * @method \PhpQuerySql\engine\builder GetParent()
 * @method self Create(string $SetNewTableName) 
 * @method self AddField($name, $type,null|mixed $length,$default)
 * 
 */
class table
{
    public function __construct(builder &$parent)
    {
        $this->parent = $parent;
        $this->items = [];
        $this->newTableName = "";
    }
    function __call(string $name, array $arguments)
    {
        switch ($name):
            case "Create":
                if (count($arguments) <> 1) throw new UnknownTableMethodCallParamException;
                $this->newTableName = $arguments[0];
                return $this;
                break;
            case "AddField":
                if (count($arguments) <> 4) throw new UnknownTableMethodCallParamException;
                $this->items[$arguments[0]] = $arguments;
                return $this;
                break;
            case "GetParent":
                if (count($arguments) <> 0) throw new UnknownTableMethodCallParamException;
                return $this->parent;
                break;
            default:
                throw new UnknownTableMethodCallException($name);
                break;
        endswitch;
    }
    public function __toString()
    {
        // return  "Query generate Table ".$this->newTableName . var_export($this->items,true);
        switch ($this->parent->GetParent()->GetBuilderType()):
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                $prm = [];
                foreach ($this->items as $key => $val) {
                    if (in_array($val[1], ["varchar", "char", "int", "integer"], false) && is_null($val[2])) throw new \Exception("Need length type of {$val[1]}");
                    $prm[] = " `{$val[0]}` {$val[1]}" . (is_null($val[2]) ? "" : "({$val[2]})") . " DEFAULT " . (is_null($val[3]) ? "null" : (empty($val[3]) ? "''" : $val[3]
                    ));
                }
                return sprintf("CREATE TABLE `%s` (%s);", $this->newTableName, implode(",", $prm));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                $prm = [];
                foreach ($this->items as $key => $val) {
                    if (in_array($val[1], ["varchar", "varbinary", "nvarchar", "char", "int"], false) && is_null($val[2])) throw new \Exception("Need length type of {$val[1]}");
                    $prm[] = " [{$val[0]}] {$val[1]}" . (is_null($val[2]) ? "" : "({$val[2]})") . " DEFAULT " . (is_null($val[3]) ? "null" : (empty($val[3]) ? "''" : $val[3]
                    ));
                }
                return sprintf("CREATE TABLE [%s] (%s);", $this->newTableName, implode(",", $prm));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                $prm = [];
                foreach ($this->items as $key => $val) {
                    if (in_array($val[1], ["varchar", "char", "int"], false) && is_null($val[2])) throw new \Exception("Need length type of {$val[1]}");
                    $prm[] = " \"{$val[0]}\" {$val[1]}" . (is_null($val[2]) ? "" : "({$val[2]})") . " DEFAULT " . (is_null($val[3]) ? "null" : (empty($val[3]) ? "''" : $val[3]
                    ));
                }
                return sprintf("CREATE TABLE \"%s\" (%s);", $this->newTableName, implode(",", $prm));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                $prm = [];
                foreach ($this->items as $key => $val) {
                    if (in_array($val[1], ["varchar", "char", "int"], false) && is_null($val[2])) throw new \Exception("Need length type of {$val[1]}");
                    $prm[] = " \"{$val[0]}\" {$val[1]}" . (is_null($val[2]) ? "" : "({$val[2]})") . " DEFAULT " . (is_null($val[3]) ? "null" : (empty($val[3]) ? "''" : $val[3]
                    ));
                }
                return sprintf("CREATE TABLE \"%s\" (%s);", $this->newTableName, implode(",", $prm));
                break;
            default:
                throw new \RuntimeException("Table unknown builder type");
                break;
        endswitch;
    }

    function __debugInfo()
    {
        try {
            return ["Query" => (string)$this, "Builder Type" => $this->parent->GetParent()->GetBuilderType()];
        } catch (\Exception $ex) {
            return ["Error" => $ex];
        }
    }
}
class UnknownTableMethodCallException extends \Exception
{
    public function __construct($name)
    {
        parent::__construct("Unknown table method call " . $name);
    }
}
class UnknownTableMethodCallParamException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Unknown table method call param set");
    }
}
