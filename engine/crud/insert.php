<?php

namespace PhpQuerySql\engine\crud;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "builder.php"]);
use \PhpQuerySql\engine\builder as CBuilder;
/**
 * Kelas insert
 * 
 * @method self SetValue(string $field, insert::VALUE_CURRENT_DATE|insert::VALUE_CURRENT_TIME|insert::VALUE_CURRENT_DATETIME|mixed $value)
 * @method \PhpQuerySql\engine\builder GetParent()
 */
class insert
{ 
    private $items, $parent;
    function __construct(\PhpQuerySql\engine\builder &$parent)
    {
        $this->parent = $parent;
        $this->items = [];
        $this->prmPrefix = "prm_";
    }
    function __call($name, array $params)
    {
        switch ($name):
            case "SetValue":
                if (count($params) == 2) : $this->items[$params[0]] = &$params[1];
                else : throw new InsertParametersSendInvalidException;
                endif;
                return $this;
                break;
            case "GetParent";
                if (count($params) <> 0) throw new InsertParametersSendInvalidException;
                return $this->parent;
                break;
            default:
                throw new InsertMethodUnknownException;
                break;
        endswitch;
    }
    public function PDOBindParam(array &$paramArray)
    {
        $tmp = [];
        foreach ($this->items as $k => &$v) :
            if (!$this->GetParent()->isNonParam($v)) $tmp[":" . $this->prmPrefix . $k] = $v;
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    function __toString()
    {
        try {
            if (count($this->items) == 0) throw new \RuntimeException("Insert value not set");
            switch ($this->GetParent()->GetParent()->GetBuilderType()):
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                    $prmF = [];
                    $prmV = []; 
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "`$key`";
                        $prmV[] =  $this->GetParent()->isNonParam($val) ? $this->GetParent()->nonParam($val,$this->GetParent()->GetParent()->GetBuilderType()) : ":" . $this->prmPrefix . $key;
                    }
                    return sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                    $prmF = [];
                    $prmV = []; 
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "[$key]";
                        $prmV[] = $this->GetParent()->isNonParam($val) ? $this->GetParent()->nonParam($val,$this->GetParent()->GetParent()->GetBuilderType()) : ":" . $this->prmPrefix . $key;
                    }
                    return sprintf("INSERT INTO [%s] (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                    $prmF = [];
                    $prmV = []; 
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "\"$key\"";
                        $prmV[] = $this->GetParent()->isNonParam($val)? $this->GetParent()->nonParam($val,$this->GetParent()->GetParent()->GetBuilderType()) :":" . $this->prmPrefix . $key;
                    }
                    return sprintf("INSERT INTO \"%s\" (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                    $prmF = [];
                    $prmV = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "\"$key\"";
                        $prmV[] = $this->GetParent()->isNonParam($val)? $this->GetParent()->nonParam($val,$this->GetParent()->GetParent()->GetBuilderType()) :":" . $this->prmPrefix . $key;
                    }
                    return sprintf("INSERT INTO \"%s\" (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                    break;
                default:
                    throw new \RuntimeException("Insert unknown builder type");
                    break;
            endswitch;
        } catch (\Exception $th) {
            return $th->getMessage();
        }
    }
    function __debugInfo()
    {
        try {
            $param = [];
            $this->PDOBindParam($param);
            return ["Query" => (string)$this, "Param" => $param, "Builder Type" => $this->GetParent()->GetParent()->GetBuilderType()];
        } catch (\Exception $ex) {
            return ["Error" => $ex];
        }
    }
}
class InsertParametersSendInvalidException extends \Exception
{
    function __construct()
    {
        parent::__construct("Insert Parameter send invalid");
    }
}
class InsertMethodUnknownException extends \Exception
{
    function __construct()
    {
        parent::__construct("Insert call method unknown");
    }
}
