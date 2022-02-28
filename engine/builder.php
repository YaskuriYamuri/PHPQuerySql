<?php

namespace PhpQuerySql\engine;

require_once "PhpQuerySql.php";
/**
 * Kelas builder
 * 
 * @property generate\generate $Generate 
 * @property crud\insertMultiple $InsertMultiple
 * @property crud\insertFrom $InsertFrom
 * @property crud\insert $Insert
 * @property crud\delete $Delete
 * @property crud\update $Update
 * @property crud\select $Select
 * 
 * @method self SetTables(string $name)
 * @method string GetTables()
 * @method \PhpQuerySql\PhpQuerySql GetParent()
 * @method string nonParam(string $value,\PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL|\PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL|\PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql|\PhpQuerySql\PHPQUERYSQL $builderType)
 * @method bool isNonParam($value)
 * @method self Reset()
 */
class builder
{

    const VALUE_CURRENT_DATE = "5082092ba49e8de7a776c8d014e393b3", VALUE_CURRENT_TIME = "3a5d7c73312d48bb630303adb03151c6", VALUE_CURRENT_DATETIME = "3fdea6f44d3817f4a96289e64484c987";

    private $tables, $parent;
    function __construct(\PhpQuerySql\PhpQuerySql &$parent)
    {
        $this->parent = $parent;

        require_once implode(DIRECTORY_SEPARATOR, ["crud", "insertMultiple.php"]);
        $this->InsertMultiple  = new crud\insertMultiple($this);

        require_once implode(DIRECTORY_SEPARATOR, ["crud", "insertFrom.php"]);
        $this->InsertFrom  = new crud\insertFrom($this);

        require_once implode(DIRECTORY_SEPARATOR, ["crud", "insert.php"]);
        $this->Insert  = new crud\insert($this);

        require_once implode(DIRECTORY_SEPARATOR, ["crud", "delete.php"]);
        $this->Delete  = new crud\delete($this);

        require_once implode(DIRECTORY_SEPARATOR, ["crud", "select.php"]);
        $this->Select  = new crud\select($this);

        require_once implode(DIRECTORY_SEPARATOR, ["crud", "update.php"]);
        $this->Update  = new crud\update($this);

        require_once implode(DIRECTORY_SEPARATOR, ["generate", "generate.php"]);
        $this->Generate  = new generate\generate($this);
    }
    /**
     * call class dynamic
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|void
     */
    function __call($name, $arguments)
    {
        switch ($name):
            case 'SetTables':
                if (count($arguments) == 1) :
                    $this->tables = $arguments[0];
                    return $this;
                else :
                    throw new ParameterSendNotValid();

                endif;
                break;
            case 'GetTables':
                if (count($arguments) == 0) :
                    return $this->tables;
                else :
                    throw new ParameterSendNotValid();
                endif;
                break;
            case 'GetParent':
                if (count($arguments) == 0) :
                    return $this->parent;
                else :
                    throw new ParameterSendNotValid();
                endif;
                break;
            case "isNonParam":
                switch (count($arguments)):
                    case 1:
                        return in_array($arguments[0], [self::VALUE_CURRENT_DATE, self::VALUE_CURRENT_DATETIME, self::VALUE_CURRENT_TIME], true);
                        break;
                    default:
                        throw new ParameterSendNotValid();
                endswitch;
                break;
            case 'nonParam':
                if (count($arguments) == 2) :
                    $value = $arguments[0];
                    $builderType = $arguments[1];
                    switch ($builderType):
                        case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
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
                            break;

                        case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
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
                            break;

                        case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
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
                            break;

                        case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                            switch ($value):

                                default:
                                    throw new \Exception("Error Builder Please contact developer");
                                    break;
                            endswitch;
                            break;

                        default:
                            return $value;
                            break;
                    endswitch;
                else :
                    throw new ParameterSendNotValid();
                endif;
                break;
            case "Reset":
                $this->Select->init();
                $this->InsertMultiple->init();
                $this->InsertFrom->init();
                $this->Insert->init();
                $this->Update->init();
                $this->Delete->init();
                return $this;
                break;
            default:
        endswitch;
    }
    function __debugInfo()
    {
        return (array)$this;
    }
}

class ParameterSendNotValid extends \Exception
{
    public function __construct()
    {
        parent::__construct("Parameter send not valid");
    }
}
