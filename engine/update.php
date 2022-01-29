<?php

namespace PhpQuerySql\engine;

require_once "builder.php";
class update
{
    public function __construct(builder &$parent)
    {
        $this->parent = $parent;
        $this->LogicAnd();
    }
    public function GetParent(): builder
    {
        return $this->parent;
    }
    public function SetValue(string $field, $value): self
    {
        $this->items[$field] = $value;
        return $this;
    }
    public function AddWhere(string $field, $value): self
    {
        $this->where[] = [$field,$value];
        return $this;
    }
    public function LogicOr(): self
    {
        $this->logic = " OR ";
        return $this;
    }
    public function LogicAnd(): self
    {
        $this->logic = " AND ";
        return $this;
    }
    public function PDOBindParam(array &$paramArray): self
    {
        $tmp = [];
        foreach ($this->items as $k => &$v) :
            $tmp[":set$k"] = $v;
        endforeach;
        foreach ($this->where as $k => &$v) :
            $tmp[":where$v[0]$k"] = $v[1];
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    public function __toString(): string
    {
        # UPDATE tb SET field=:setvalue where field=:wherevalue
        switch ($this->parent->GetParent()->GetBuilderType()):
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                $prmF = [];
                $prmW = [];
                foreach ($this->items as $key  => $val) {
                    $prmF[] = "`$key`= :set$key"; 
                }
                foreach ($this->where as $key => $val) {
                    $prmW[] = "`$val[0]`=:where" .$val[0]. $key;
                }
                return sprintf("UPDATE `%s` SET %s WHERE %s", $this->GetParent()->GetTables(), implode(",", $prmF), implode($this->logic, $prmW));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                $prmF = [];
                $prmW = [];
                foreach ($this->items as $key => $val) {
                    $prmF[] = "[$key]= :set$key"; 
                }
                foreach ($this->where as $key => $val) {
                    $prmW[] = "[$val[0]]=:where" .$val[0]. $key;
                }
                return sprintf("UPDATE [%s] SET %s WHERE %s", $this->GetParent()->GetTables(), implode(",", $prmF), implode($this->logic, $prmW));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                $prmF = [];
                $prmW = [];
                foreach ($this->items as $key => $val) {
                    $prmF[] = "\"$key\"= :set$key"; 
                }
                foreach ($this->where as $key => $val) {
                    $prmW[] = "\"$val[0]\"=:where" .$val[0]. $key;
                }
                return sprintf("UPDATE \"%s\" SET %s WHERE %s", $this->GetParent()->GetTables(), implode(",", $prmF), implode($this->logic, $prmW));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                $prmF = [];
                $prmW = [];
                foreach ($this->items as $key => $val) {
                    $prmF[] = "\"$key\"= :set$key"; 
                }
                foreach ($this->where as $key => $val) {
                    $prmW[] = "\"$val[0]\"=:where" .$val[0]. $key;
                }
                return sprintf("UPDATE \"%s\" SET %s WHERE %s", $this->GetParent()->GetTables(), implode(",", $prmF), implode($this->logic, $prmW));
                break;
            default:
                throw new \RuntimeException("Insert unknown builder type");
                break;
        endswitch;
    }
}
