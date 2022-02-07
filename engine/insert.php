<?php

namespace PhpQuerySql\engine;

require_once "builder.php";
/**
 * Kelas insert
 * 
 * @method self SetValue(string $field, mixed $value)
 * @method builder GetParent()
 */
class insert
{
    private $items, $parent;
    function __construct(builder &$parent)
    {
        $this->parent = $parent;
        $this->items = [];
        $this->prmPrefix="prm_";
    }
    function __call(string $name, array $params)
    {
        switch ($name):
            case "SetValue":
                if (count($params) == 2) :
                    $this->items[$params[0]] = &$params[1];
                    return $this;
                else :
                    throw new InsertParametersSendInvalidException;
                endif;
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
    public function PDOBindParam(array &$paramArray): self
    {
        $tmp = [];
        foreach ($this->items as $k => &$v) :
            $tmp[":".$this->prmPrefix.$k] = $v;
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    function __toString(): string
    {
        if(count($this->items)==0) throw new \RuntimeException("Insert value not set");
        switch ($this->parent->GetParent()->GetBuilderType()):
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                $prmF = [];
                $prmV = [];
                foreach ($this->items as $key => $val) {
                    $prmF[] = "`$key`";
                    $prmV[] = ":".$this->prmPrefix . $key;
                }
                return sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                $prmF = [];
                $prmV = [];
                foreach ($this->items as $key => $val) {
                    $prmF[] = "[$key]";
                    $prmV[] = ":".$this->prmPrefix . $key;
                }
                return sprintf("INSERT INTO [%s] (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                $prmF = [];
                $prmV = [];
                foreach ($this->items as $key => $val) {
                    $prmF[] = "\"$key\"";
                    $prmV[] = ":".$this->prmPrefix . $key;
                }
                return sprintf("INSERT INTO \"%s\" (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                $prmF = [];
                $prmV = [];
                foreach ($this->items as $key => $val) {
                    $prmF[] = "\"$key\"";
                    $prmV[] = ":".$this->prmPrefix . $key;
                }
                return sprintf("INSERT INTO \"%s\" (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                break;
            default:
                throw new \RuntimeException("Insert unknown builder type");
                break;
        endswitch;
    }
    function __debugInfo()
    {
        try {
            $param =[];
             $this->PDOBindParam($param );
            return ["Query" => (string)$this,"Param"=>$param, "Builder Type" => $this->parent->GetParent()->GetBuilderType()];
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
