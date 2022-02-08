<?php

namespace PhpQuerySql\engine\crud;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "builder.php"]);
/**
 * Kelas insert
 * 
 * @method self SetValue(string $field, insert::VALUE_CURRENT_DATE|insert::VALUE_CURRENT_TIME|insert::VALUE_CURRENT_DATETIME|mixed $value)
 * @method \PhpQuerySql\engine\builder GetParent()
 */
class insert
{
    const VALUE_CURRENT_DATE = "5082092ba49e8de7a776c8d014e393b3", VALUE_CURRENT_TIME = "3a5d7c73312d48bb630303adb03151c6", VALUE_CURRENT_DATETIME = "3fdea6f44d3817f4a96289e64484c987";
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
            if (!in_array($v, [self::VALUE_CURRENT_DATE, self::VALUE_CURRENT_DATETIME, self::VALUE_CURRENT_TIME])) $tmp[":" . $this->prmPrefix . $k] = $v;
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
                    $nonParam = function ($value) {
                        switch ($value):
                            case self::VALUE_CURRENT_DATE:
                                return "CURRENT_DATE()";
                                break;
                            case self::VALUE_CURRENT_TIME:
                                return "CURRENT_TIME()";
                                break;
                            case self::VALUE_CURRENT_DATETIME:
                                return "CURRENT_TIMESTAMP()";
                                break;
                            default:
                                return $value;
                                break;
                        endswitch;
                    };
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "`$key`";
                        $prmV[] = in_array($val, [self::VALUE_CURRENT_DATE, self::VALUE_CURRENT_DATETIME, self::VALUE_CURRENT_TIME]) ? $nonParam($val) : ":" . $this->prmPrefix . $key;
                    }
                    return sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                    $prmF = [];
                    $prmV = [];
                    $nonParam = function ($value) {
                        switch ($value):
                            case self::VALUE_CURRENT_DATE:
                                return "CONVERT(DATE,CURRENT_TIMESTAMP)";
                                break;
                            case self::VALUE_CURRENT_TIME:
                                return "CONVERT(TIME,CURRENT_TIMESTAMP) ";
                                break;
                            case self::VALUE_CURRENT_DATETIME:
                                return "CURRENT_TIMESTAMP";
                                break;
                            default:
                                return $value;
                                break;
                        endswitch;
                    };
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "[$key]";
                        $prmV[] =in_array($val, [self::VALUE_CURRENT_DATE, self::VALUE_CURRENT_DATETIME, self::VALUE_CURRENT_TIME]) ? $nonParam($val) : ":" . $this->prmPrefix . $key;
                    }
                    return sprintf("INSERT INTO [%s] (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                    $prmF = [];
                    $prmV = [];
                    $nonParam = function ($value) {
                        switch ($value):
                            case self::VALUE_CURRENT_DATE:
                                return "CURRENT_DATE";
                                break;
                            case self::VALUE_CURRENT_TIME:
                                return "CURRENT_TIME";
                                break;
                            case self::VALUE_CURRENT_DATETIME:
                                return "CURRENT_TIMESTAMP";
                                break;
                            default:
                                return $value;
                                break;
                        endswitch;
                    };
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "\"$key\"";
                        $prmV[] = in_array($val, [self::VALUE_CURRENT_DATE, self::VALUE_CURRENT_DATETIME, self::VALUE_CURRENT_TIME]) ? $nonParam($val) :":" . $this->prmPrefix . $key;
                    }
                    return sprintf("INSERT INTO \"%s\" (%s) VALUES (%s)", $this->GetParent()->GetTables(), implode(",", $prmF), implode(",", $prmV));
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                    $prmF = [];
                    $prmV = [];
                    foreach ($this->items as $key => $val) {
                        $prmF[] = "\"$key\"";
                        $prmV[] = ":" . $this->prmPrefix . $key;
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
