<?php

namespace PhpQuerySql\engine\crud;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "builder.php"]);
/**
 * Kelas update
 * 
 * @method self SetValue(string $field, mixed $value)
 * @method self AddWhere(string $field, mixed $value)
 * @method \PhpQuerySql\engine\builder GetParent()
 * @method self LogicOr()
 * @method self LogicAnd()
 */
class update
{
    public function __construct(\PhpQuerySql\engine\builder &$parent)
    {
        $this->parent = $parent;
        $this->init();
    }

    function __call($name, array $params)
    {
        switch ($name):
            case "SetValue":
                if (count($params) == 2) :
                    $this->items[(string)$params[0]] = &$params[1];
                    return $this;
                else :
                    throw new UpdateParametersSendInvalidException;
                endif;
                break;
            case "AddWhere":
                if (count($params) == 2) :
                    $this->where[] = &$params;
                    return $this;
                else :
                    throw new UpdateParametersSendInvalidException;
                endif;
                break;
            case "GetParent";
                if (count($params) <> 0) throw new UpdateParametersSendInvalidException;
                return $this->parent;
                break;
            case "LogicOr":
                $this->logic = " OR ";
                return $this;
                break;
            case "LogicAnd":
                $this->logic = " AND ";
                return $this;
                break;
case "init":
    $this->items = [];
    $this->where = [];
    $this->prefixSet = "set";
    $this->prefixWhere = "where";
    $this->LogicAnd();
    break;
            default:
                throw new UpdateMethodUnknownException;
                break;
        endswitch;
    }
    public function PDOBindParam(array &$paramArray)
    {
        $tmp = [];
        foreach ($this->items as $k => &$v) :
            if (!$this->GetParent()->isNonParam($v)) $tmp[":{$this->prefixSet}{$k}"] = $v;
        endforeach;
        foreach ($this->where as $k => &$v) :
            if (!$this->GetParent()->isNonParam($v[1])) $tmp[":{$this->prefixWhere}{$v[0]}{$k}"] = $v[1];
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    public function __toString()
    {
        try {
            if (count($this->items) == 0) throw new \RuntimeException("Update value set not set");
            if (count($this->where) == 0) throw new \RuntimeException("Update value where not set");
            # UPDATE tb SET field=:setvalue where field=:wherevalue
            switch ($this->GetParent()->GetParent()->GetBuilderType()):
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                    $prmF = [];
                    $prmW = [];

                    foreach ($this->items as $key  => $val) {
                        $prmF[] = "`$key`=" . ($this->GetParent()->isNonParam($val) ? $this->GetParent()->nonParam($val, $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixSet}$key");
                    }
                    foreach ($this->where as $key => $val) {
                        $prmW[] = "`$val[0]`=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    return sprintf("UPDATE `%s` SET %s WHERE %s;", $this->GetParent()->GetTables(), implode(",", $prmF), implode($this->logic, $prmW));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                    $prmF = [];
                    $prmW = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "[$key]= " . ($this->GetParent()->isNonParam($val) ? $this->GetParent()->nonParam($val, $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixSet}$key");
                    }
                    foreach ($this->where as $key => $val) {
                        $prmW[] = "[$val[0]]=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    return sprintf("UPDATE [%s] SET %s WHERE %s;", $this->GetParent()->GetTables(), implode(",", $prmF), implode($this->logic, $prmW));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                    $prmF = [];
                    $prmW = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "\"$key\"= " . ($this->GetParent()->isNonParam($val) ? $this->GetParent()->nonParam($val, $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixSet}$key");
                    }
                    foreach ($this->where as $key => $val) {
                        $prmW[] = "\"$val[0]\"=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    return sprintf("UPDATE \"%s\" SET %s WHERE %s;", $this->GetParent()->GetTables(), implode(",", $prmF), implode($this->logic, $prmW));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                    $prmF = [];
                    $prmW = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "\"$key\"= " . ($this->GetParent()->isNonParam($val) ? $this->GetParent()->nonParam($val, $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixSet}$key");
                    }
                    foreach ($this->where as $key => $val) {
                        $prmW[] = "\"$val[0]\"=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    return sprintf("UPDATE \"%s\" SET %s WHERE %s;", $this->GetParent()->GetTables(), implode(",", $prmF), implode($this->logic, $prmW));
                    break;
                default:
                    throw new \RuntimeException("Update unknown builder type");
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
class UpdateParametersSendInvalidException extends \Exception
{
    function __construct()
    {
        parent::__construct("Update Parameter send invalid");
    }
}
class UpdateMethodUnknownException extends \Exception
{
    function __construct()
    {
        parent::__construct("Update call method unknown");
    }
}
