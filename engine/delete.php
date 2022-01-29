<?php

namespace PhpQuerySql\engine;
require_once "builder.php";
class delete{ 
    public function __construct(builder $parent) {
        $this->parent= $parent;
        $this->LogicAnd();
    } 
    public function GetParent(): builder
    {
        return $this->parent;
    }
    public function AddWhere(string $field,$value):self{
        $this->where[] = [$field,$value];
        return $this;
    } 
    public function LogicOr():self{
        $this->logic = " OR ";
        return $this;
    }
    public function LogicAnd():self{
        $this->logic = " AND ";
        return $this;
    }
    public function PDOBindParam(array &$paramArray): self
    {
        $tmp = []; 
        foreach ($this->where as $k => &$v) :
            $tmp[":where$v[0]$k"] = $v[1];
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    public function __toString():string
    {
        switch ($this->parent->GetParent()->GetBuilderType()):
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                $prmW = []; 
                foreach ($this->where as $key => $val) {
                    $prmW[] = "`$val[0]`=:where" .$val[0]. $key;
                }
                return sprintf("DELETE FROM `%s` WHERE %s", $this->GetParent()->GetTables(), implode($this->logic, $prmW));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                $prmW = []; 
                foreach ($this->where as $key => $val) {
                    $prmW[] = "[$val[0]]=:where" .$val[0]. $key;
                }
                return sprintf("DELETE FROM [%s] WHERE %s", $this->GetParent()->GetTables(),implode($this->logic, $prmW));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                $prmW = []; 
                foreach ($this->where as $key => $val) {
                    $prmW[] = "\"$val[0]\"=:where" .$val[0]. $key;
                }
                return sprintf("DELETE FROM \"%s\" WHERE %s", $this->GetParent()->GetTables(),  implode($this->logic, $prmW));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                $prmW = []; 
                foreach ($this->where as $key => $val) {
                    $prmW[] = "\"$val[0]\"=:where" .$val[0]. $key;
                }
                return sprintf("DELETE FROM \"%s\" WHERE %s", $this->GetParent()->GetTables(),implode($this->logic, $prmW));
                break;
            default:
                throw new \RuntimeException("Insert unknown builder type");
                break;
        endswitch;
    }
}