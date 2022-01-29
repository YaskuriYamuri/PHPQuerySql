<?php

namespace PhpQuerySql\engine;

require_once "builder.php";
class insert
{
    private $items, $parent;
    function __construct(builder &$parent)
    {
        $this->parent = $parent;
        $this->items = [];
    }
    public function SetValue(string $field,  $value): self
    {
        $this->items[$field] = $value;
        return $this;
    }
    public function GetParent(): builder
    {
        return $this->parent;
    }
    public function PDOBindParam(array &$paramArray): self
    {
        $tmp = [];
        foreach ($this->items as $k => &$v) :
            $tmp[":val$k"] = $v;
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    function __toString(): string
    {
        switch ($this->parent->GetParent()->GetBuilderType()):
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                $prmF = [];
                $prmV = [];
                foreach ($this->items as $key => $val) {
                    $prmF[] = "`$key`";
                    $prmV[] = ":val" . $key;
                }
                return sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                $prmF = [];
                $prmV = [];
                foreach ($this->items as $key=> $val) {
                    $prmF[] = "[$key]";
                    $prmV[] = ":val" . $key;
                }
                return sprintf("INSERT INTO [%s] (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                $prmF = [];
                $prmV = [];
                foreach ($this->items as $key=> $val) {
                    $prmF[] = "\"$key\"";
                    $prmV[] = ":val" . $key;
                }
                return sprintf("INSERT INTO \"%s\" (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                $prmF = [];
                $prmV = [];
                foreach ($this->items as $key=> $val) {
                    $prmF[] = "\"$key\"";
                    $prmV[] = ":val" . $key;
                }
                return sprintf("INSERT INTO \"%s\" (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                break;
            default:
                throw new \RuntimeException("Insert unknown builder type");
                break;
        endswitch;
    }
}
