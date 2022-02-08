<?php

namespace PhpQuerySql\engine\generate;

require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "generate.php"]));
/**
 * 
 * @method \PhpQuerySql\engine\generate\generate GetParent()
 * @method self Create(string $SetNewTableName)   
 * @method self AddField($name, $type,null|mixed $length,bool $nullable = true,mixed $default =table::DEFAULT_NO_DEFAULT)
 * @method self SetPrimary(string $field);
 * @method self SetComment(string $KomentarTabel);
 * 
 */
class table
{
    const DEFAULT_NO_DEFAULT = "1071d6ce5ca467754e479ce6912aa37c";
    public function __construct(\PhpQuerySql\engine\generate\generate &$parent)
    {
        $this->parent = $parent;
        $this->items = [];
        $this->newTableName = "";
        $this->setPrimary = null;
        $this->setComment = null;
    }
    function __call($name, array $arguments)
    {
        switch ($name):
            case "Create":
                if (count($arguments) <> 1) throw new UnknownTableMethodCallParamException;
                $this->newTableName = $arguments[0];
                return $this;
                break;
            case "SetPrimary":
                if (count($arguments) <> 1) throw new UnknownTableMethodCallParamException;
                $this->setPrimary = $arguments[0];
                return $this;
                break;
            case "SetComment":
                if (count($arguments) <> 1) throw new UnknownTableMethodCallParamException;
                $this->setComment = $arguments[0];
                return $this;
                break;
            case "AddField": 
                switch (count($arguments)):
                    case 3:
                        $arguments[3] = true;
                        $arguments[4] = self::DEFAULT_NO_DEFAULT;
                        $this->items[$arguments[0]] = $arguments;
                        break;
                    case 4:
                        $arguments[4] = self::DEFAULT_NO_DEFAULT;
                        $this->items[$arguments[0]] = $arguments;
                        break;
                    case 5:
                        $this->items[$arguments[0]] = $arguments;
                        break;
                    default:
                        throw new UnknownTableMethodCallParamException;
                        break;
                endswitch;
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

        switch ($this->GetParent()->GetParent()->GetParent()->GetBuilderType()):
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                $prm = [];
                // die(var_dump($this->items));
                foreach ($this->items as $key => $val) {
                    if (in_array($val[1], ["varchar", "char", "int", "integer"], false) && is_null($val[2])) throw new \Exception("Need length type of {$val[1]}");
                    $prm[] = " `{$val[0]}` {$val[1]}" .
                        (is_null($val[2]) ? "" : "({$val[2]})") .
                        ($val[3] == true ? " NULL " : " NOT NULL ") .
                        ($val[4] != self::DEFAULT_NO_DEFAULT ? (is_null($val[4]) ? "DEFAULT null " : (empty($val[4]) ? "DEFAULT ''" : "DEFAULT " . $val[4])) : "");
                }
                if (!is_null($this->setPrimary)) $prm[] = "CONSTRAINT `PK_{$this->newTableName}_{$this->setPrimary}` PRIMARY KEY (`{$this->setPrimary}`)";
                return sprintf("CREATE TABLE `%s` (%s) %s;", $this->newTableName, implode(",", $prm), (is_null($this->setComment) ? "" : "COMMENT '" . str_replace("'", "\'", $this->setComment) . "'"));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                $prm = [];
                foreach ($this->items as $key => $val) {
                    if (in_array($val[1], ["varchar", "varbinary", "nvarchar", "char", "int"], false) && is_null($val[2])) throw new \Exception("Need length type of {$val[1]}");
                    $prm[] = " [{$val[0]}] {$val[1]}" .
                        (is_null($val[2])  ? "" : (in_array(strtolower($val[1]), ['int', 'integer']) ? "" : "({$val[2]})")) .
                        ($val[3] == true ? " NULL " : " NOT NULL ") .
                        ($val[4] != self::DEFAULT_NO_DEFAULT ? (is_null($val[4]) ? "DEFAULT null " : (empty($val[4]) ? "DEFAULT ''" : "DEFAULT " . $val[4])) : "");
                }
                return sprintf("CREATE TABLE [%s] (%s);", $this->newTableName, implode(",", $prm));
                break;
            case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                $prm = [];
                foreach ($this->items as $key => $val) {
                    if (in_array($val[1], ["varchar", "char", "int"], false) && is_null($val[2])) throw new \Exception("Need length type of {$val[1]}");
                    $prm[] = " \"{$val[0]}\" {$val[1]}" .
                        (is_null($val[2])  ? "" : (in_array(strtolower($val[1]), ['int', 'integer']) ? "" : "({$val[2]})")) .
                        ($val[3] == true ? " NULL " : " NOT NULL ") .
                        ($val[4] != self::DEFAULT_NO_DEFAULT ? (is_null($val[4]) ? "DEFAULT null " : (empty($val[4]) ? "DEFAULT ''" : "DEFAULT " . $val[4])) : "");
                }
                if (!is_null($this->setPrimary)) $prm[] = "CONSTRAINT \"PK_{$this->newTableName}_{$this->setPrimary}\" PRIMARY KEY (\"{$this->setPrimary}\")";
                return sprintf("CREATE TABLE \"%s\" (%s) %s;", $this->newTableName, implode(",", $prm), (is_null($this->setComment) ? "" : "; COMMENT ON TABLE \"{$this->newTableName}\" IS '" . str_replace("'", "''", $this->setComment) . "'"));
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
            return ["Query" => (string)$this, "Builder Type" => $this->GetParent()->GetParent()->GetParent()->GetBuilderType()];
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
    public function __construct($name)
    {
        parent::__construct("Unknown table method call {$name}");
    }
}
