<?php

declare(strict_types=1);

namespace PhpQuerySql;

use RuntimeException;
 

const PHPQUERYSQL_TYPE_MYSQL = "dc0c0b20c2005050ae9e1c7faae77047", PHPQUERYSQL_TYPE_MSSQL = "30b3d579b453c3ac42290a65a443f6ad", PHPQUERYSQL_TYPE_POSTGRESql = "082d8c28a47ce77490d67768cbf840de", PHPQUERYSQL_TYPE_ORACLE = "d350d135bbae43d8ce9a64230bc14d2d";
/**
 * Main class
 * 
 * @property engine\builder $Builder
 * 
 * @method PHPQUERYSQL_TYPE_MYSQL|PHPQUERYSQL_TYPE_MSSQL|PHPQUERYSQL_TYPE_POSTGRESql|PHPQUERYSQL_TYPE_ORACLE GetBuilderType()
 * 
 * 
 */
abstract class PhpQuerySql
{

    // public $Builder;
    // private $bt;
    /**
     * Contructor class
     *
     * @param PHPQUERYSQL_TYPE_MYSQL|PHPQUERYSQL_TYPE_MSSQL|PHPQUERYSQL_TYPE_POSTGRESql|PHPQUERYSQL_TYPE_ORACLE $builderType
     */
    public function __construct($builderType)
    {
        if (!in_array($builderType, [PHPQUERYSQL_TYPE_MYSQL, PHPQUERYSQL_TYPE_MSSQL, PHPQUERYSQL_TYPE_POSTGRESql, PHPQUERYSQL_TYPE_ORACLE])) throw new RuntimeException('builder type Undefinied');
        require_once "builder.php";
        $this->__set('Builder', new engine\builder($this));
        $this->__set('bt', $builderType);
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case "GetBuilderType";
                return $this->bt;
            default:
                throw new RuntimeException('Unknown method');
                break;
        endswitch;
    }
    function __set($name, $value): self
    {
        switch ($name):
            case 'bt';
                $this->$name = $value;
                break;
                case 'Builder';
                    $this->$name = $value;
                    break;
            default:
                throw new RuntimeException('Unknown variable');
        endswitch;

        return $this;
    }
}
class PhpQueryMySql extends PhpQuerySql
{
    public function __construct()
    {
        parent::__construct(\PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL);
    }
    public static function Builder()
    {
        return (new self)->Builder;
    }
}

class PhpQueryMsSql extends PhpQuerySql
{
    public function __construct()
    {
        parent::__construct(\PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL);
    }
    public static function Builder()
    {
        return (new self)->Builder;
    }
}
class PhpQueryPOSTGRESql extends PhpQuerySql
{
    public function __construct()
    {
        parent::__construct(\PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql);
    }
    public static function Builder()
    {
        return (new self)->Builder;
    }
}
class PhpQueryOracle extends PhpQuerySql
{
    public function __construct()
    {
        parent::__construct(\PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE);
    }
    public static function Builder()
    {
        return (new self)->Builder;
    }
}
