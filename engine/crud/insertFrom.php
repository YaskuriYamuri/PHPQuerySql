<?php

namespace PhpQuerySql\engine\crud;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "builder.php"]);

use \PhpQuerySql\engine\builder as Builder;

/**
 * Kelas insertFrom
 * 
 * @method self SetValue(string $fieldTo, insertFrom::VALUE_CURRENT_DATE|insertFrom::VALUE_CURRENT_TIME|insertFrom::VALUE_CURRENT_DATETIME|insertFromDirectValue|mixed $fieldFrom)
 * @method self SetFrom(string $tablename)
 * @method self AddWhere(string $field, mixed $value)
 * @method self AddWhereCustom(string $value1, mixed $value2)
 * @method self LogicAnd()
 * @method self LogicOr() 
 * @method self SetIndexStart(?int $value)
 * @method self SetIndexCount(?int $value) * @method Builder GetParent()
 */
class insertFrom
{
    private $items, $parent;
    function __construct(\PhpQuerySql\engine\builder &$parent)
    {
        $this->parent = $parent;
        $this->init();
    }
    function __call($name, array $arguments)
    {
        switch ($name):
            case "SetFrom":
                if (count($arguments) == 1) : $this->tbFrom = &$arguments[0];
                else : throw new insertFromParametersSendInvalidException;
                endif;
                return $this;
                break;
            case "SetValue":
                if (count($arguments) == 2) : $this->items[$arguments[0]] = &$arguments[1];
                else : throw new insertFromParametersSendInvalidException;
                endif;
                return $this;
                break;
            case "AddWhere":
                if (count($arguments) == 2) :
                    $this->where[] = &$arguments;
                    return $this;
                else :
                    throw new insertFromParametersSendInvalidException;
                endif;
                break;
            case "AddWhereCustom":
                if (count($arguments) == 2) :
                    $this->wherenbp[] = &$arguments;
                    return $this;
                else :
                    throw new insertFromParametersSendInvalidException;
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
            case "SetIndexStart":
                if (count($arguments) == 1) :
                    if (is_null($arguments[0])) :   $this->IndexStart = $arguments[0];
                    elseif (!is_integer($arguments[0])) :  throw new insertFromParametersSendInvalidException;
                    else : $this->IndexStart = $arguments[0];
                    endif;
                else :  throw new insertFromParametersSendInvalidException;
                endif;
                return $this;
                break;
            case "SetIndexCount":
                if (count($arguments) == 1) :
                    if (is_null($arguments[0])) :
                        $this->IndexCount = $arguments[0];
                    else :
                        if (is_numeric($arguments[0])) :
                            $this->IndexCount = $arguments[0];
                        else :
                            throw new insertFromParametersSendInvalidException;
                        endif;
                    endif;
                else : throw new insertFromParametersSendInvalidException;
                endif;
                return $this;
                break;
            case "GetParent";
                if (count($arguments) <> 0) throw new insertFromParametersSendInvalidException;
                return $this->parent;
                break;
            case "init":
                $this->items = [];
                $this->tbFrom = null;
                $this->asSource = "a";
                $this->prmPrefix = 'prm_';
                $this->prefixWhere = "where";
                $this->where = [];
                $this->wherenbp = [];
                $this->LogicAnd()->SetIndexStart(null)->SetIndexCount(1);
                break;
            default:
                throw new insertFromMethodUnknownException;
                break;
        endswitch;
    }
    public function PDOBindParam(array &$paramArray)
    {
        $tmp = [];
        foreach ($this->items as $k => &$v) :
            if (!$this->GetParent()->isNonParam($v) && ($v instanceof insertFromDirectValue)) 
            $tmp[":" . $this->prmPrefix . $k] = $v->Value;
        endforeach;
        
        foreach ($this->where as $k => &$v) :
            if (!$this->GetParent()->isNonParam($v[1]))  $tmp[":{$this->prefixWhere}$v[0]$k"] = $v[1];
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    function __toString()
    {
        try {
            $awhere=[];
            $prmOrder =[];
            if (!isset($this->tbFrom) || is_null($this->tbFrom) || empty($this->tbFrom)) throw new \RuntimeException("insertFrom Source From not set");
            if (count($this->items) == 0) throw new \RuntimeException("insertFrom value not set");
            switch ($this->GetParent()->GetParent()->GetBuilderType()):
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                    $prmF = [];
                    $prmV = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "`$key`";
                        switch (true):
                            case $this->GetParent()->isNonParam($val):
                                $prmV[] = $this->GetParent()->nonParam($val, $this->GetParent()->GetParent()->GetBuilderType());
                                break;
                            case $val instanceof insertFromDirectValue:
                                $prmV[] = ":{$this->prmPrefix}{$key}";
                                break;
                            default:
                                $prmV[] = "`{$this->asSource}`.`{$val}`";
                                $prmOrder[] = "`{$this->asSource}`.`{$val}`";
                                break;
                        endswitch;
                     }

                    foreach ($this->where as $key => $val) {
                        $awhere[] = "[{$this->asSource}].[{$val[0]}]=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "[{$val[0]}]={$val[1]}";
                    }

                    $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";
                    $slimit = is_null($this->IndexStart) ? "" : (" LIMIT " . $this->IndexStart . (!is_null($this->IndexCount) ? " , " . $this->IndexCount : ""));

                    return sprintf("INSERT INTO [%s] (%s) SELECT %s FROM %s AS [{$this->asSource}] %s %s;", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV), $this->tbFrom, $swhere, $slimit);
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                    $prmF = [];
                    $prmV = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "[$key]";
                        switch (true):
                            case $this->GetParent()->isNonParam($val):
                                $prmV[] = $this->GetParent()->nonParam($val, $this->GetParent()->GetParent()->GetBuilderType());
                                break;
                            case $val instanceof insertFromDirectValue:
                                $prmV[] = ":{$this->prmPrefix}{$key}";
                                break;
                            default:
                                $prmV[] = "[{$this->asSource}].[{$val}]";
                                $prmOrder[] = "[{$this->asSource}].[{$val}]"; break;
                        endswitch;
                     }

                    foreach ($this->where as $key => $val) {
                        $awhere[] = "[{$this->asSource}].[{$val[0]}]=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }

                    $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";
                    $slimit = is_null($this->IndexStart) ? "" : (" ORDER BY ".implode(",",$prmOrder)." OFFSET " . $this->IndexStart . " ROWS " . (!is_null($this->IndexCount) ? " FETCH NEXT " . $this->IndexCount . " ROWS ONLY "  : ""));
                  
                    return sprintf("INSERT INTO [%s] (%s) SELECT %s FROM [%s] AS [{$this->asSource}] %s %s;", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV), $this->tbFrom, $swhere, $slimit);
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                    $prmF = [];
                    $prmV = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "\"$key\"";
                        switch (true):
                            case $this->GetParent()->isNonParam($val):
                                $prmV[] = $this->GetParent()->nonParam($val, $this->GetParent()->GetParent()->GetBuilderType());
                                break;
                            case $val instanceof insertFromDirectValue:
                                $prmV[] = ":{$this->prmPrefix}{$key}";
                                break;
                            default:
                                $prmV[] = "\"{$this->asSource}\".\"{$val}\"";
                                $prmOrder[] = "\"{$this->asSource}\".\"{$val}\"";  break;
                        endswitch;
                     }

                    foreach ($this->where as $key => $val) {
                        $awhere[] = "\"{$this->asSource}\".\"{$val[0]}\"=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":{$this->prefixWhere}" . $val[0] . $key);
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "{$val[0]}={$val[1]}";
                    }

                    $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";
                    $slimit = is_null($this->IndexStart) ? "" : (" ORDER BY ".implode(",",$prmOrder)." OFFSET " . $this->IndexStart . " ROWS " . (!is_null($this->IndexCount) ? " FETCH NEXT " . $this->IndexCount . " ROWS ONLY "  : ""));

                    return sprintf("INSERT INTO \"%s\" (%s) SELECT (%s) FROM \"%s\" AS \"{$this->asSource}\" %s %s;", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV), $this->tbFrom, $swhere, $slimit);
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                    $prmF = [];
                    $prmV = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "\"$key\"";
                        $prmV[] = $this->GetParent()->isNonParam($val) ? $this->GetParent()->nonParam($val, $this->GetParent()->GetParent()->GetBuilderType()) : ":" . $this->prmPrefix . $key;
                    }
                    return sprintf("INSERT INTO \"%s\" (%s) VALUES (%s);", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                    break;
                default:
                    throw new \RuntimeException("insertFrom unknown builder type");
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
class insertFromDirectValue
{
    public $Value;
    public function __construct($value)
    {
        $this->Value = $value;
    }
}
class insertFromParametersSendInvalidException extends \Exception
{
    function __construct()
    {
        parent::__construct("insertFrom Parameter send invalid");
    }
}
class insertFromMethodUnknownException extends \Exception
{
    function __construct()
    {
        parent::__construct("insertFrom call method unknown");
    }
}
