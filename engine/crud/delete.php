<?php

namespace PhpQuerySql\engine\crud;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "builder.php"]);
/**
 * Kelas delete
 * @method \PhpQuerySql\engine\builder GetParent()
 * @method self AddWhere(string $field, mixed $value)
 * @method self AddWhereCustom(string $value1, mixed $value2)
 * @method self LogicAnd()
 * @method self LogicOr() 
 */
class delete
{
    public function __construct(\PhpQuerySql\engine\builder $parent)
    {
        $this->parent = $parent;
        $this->init();
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case "GetParent":
                if (count($arguments) == 0) : return $this->parent;
                else : throw new DeleteParametersSendInvalidException;
                endif;
                break;
            case "AddWhere":
                if (count($arguments) == 2) :
                    $this->where[] = &$arguments;
                    return $this;
                else :
                    throw new DeleteParametersSendInvalidException;
                endif;
                break;
            case "AddWhereCustom":
                if (count($arguments) == 2) :
                    $this->wherenbp[] = &$arguments;
                    return $this;
                else :
                    throw new SelectParametersSendInvalidException;
                endif;
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
                $this->prefixWhere = "where";
                $this->where = [];
                $this->wherenbp = [];
                $this->LogicAnd();
                break;
            default:
                throw new DeleteMethodUnknownException;
                break;
        endswitch;
    }
    public function PDOBindParam(array &$paramArray)
    {
        $tmp = [];
        foreach ($this->where as $k => &$v) :
            if (!$this->GetParent()->isNonParam($v[1]))
                $tmp[":" . $this->prefixWhere . $v[0] . $k] = $v[1];
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    public function __toString()
    {
        try {
            if (count($this->where) == 0) throw new \RuntimeException("Update value where not set");
            switch ($this->GetParent()->GetParent()->GetBuilderType()):
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                    $prmW = [];
                    foreach ($this->where as $key => $val) {
                        $prmW[] = "`{$val[0]}`=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }
                    return sprintf("DELETE FROM `%s` WHERE %s;", $this->GetParent()->GetTables(), implode($this->logic, $prmW));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                    $prmW = [];
                    foreach ($this->where as $key => $val) {
                        $prmW[] = "[{$val[0]}]=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }
                    return sprintf("DELETE FROM [%s] WHERE %s;", $this->GetParent()->GetTables(), implode($this->logic, $prmW));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                    $prmW = [];
                    foreach ($this->where as $key => $val) {
                        $prmW[] = "\"{$val[0]}\"=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }
                    return sprintf("DELETE FROM \"%s\" WHERE %s;", $this->GetParent()->GetTables(),  implode($this->logic, $prmW));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                    $prmW = [];
                    foreach ($this->where as $key => $val) {
                        $prmW[] = "\"{$val[0]}\"=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }
                    return sprintf("DELETE FROM \"%s\" WHERE %s;", $this->GetParent()->GetTables(), implode($this->logic, $prmW));
                    break;
                default:
                    throw new \RuntimeException("Delete unknown builder type");
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

class DeleteParametersSendInvalidException extends \Exception
{
    function __construct()
    {
        parent::__construct("Delete Parameter send invalid");
    }
}
class DeleteMethodUnknownException extends \Exception
{
    function __construct()
    {
        parent::__construct("Delete call method unknown");
    }
}
