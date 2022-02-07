<?php

declare(strict_types=1);

namespace PhpQuerySql;

const PHPQUERYSQL_TYPE_MYSQL = "dc0c0b20c2005050ae9e1c7faae77047", PHPQUERYSQL_TYPE_MSSQL = "30b3d579b453c3ac42290a65a443f6ad", PHPQUERYSQL_TYPE_POSTGRESql = "082d8c28a47ce77490d67768cbf840de", PHPQUERYSQL_TYPE_ORACLE = "d350d135bbae43d8ce9a64230bc14d2d";
/**
 * Main class
 * 
 * @property engine\builder $Builder  
 * @property engine\schema\schema $Schema
 * 
 * @method PHPQUERYSQL_TYPE_MYSQL|PHPQUERYSQL_TYPE_MSSQL|PHPQUERYSQL_TYPE_POSTGRESql|PHPQUERYSQL_TYPE_ORACLE GetBuilderType()
 * 
 
 */
abstract class PhpQuerySql
{

    const TABLE_DATATYPE_VARCHAR = 1, TABLE_DATATYPE_INT = 2, TABLE_DATATYPE_DATE = 3, TABLE_DATATYPE_TIME = 4, TABLE_DATATYPE_DATETIME = 5, ORDERBY_ASCENDING = "ASC", ORDERBY_DESCENNDING = "DESC";
    /**
     * Contructor class
     *
     * @param PHPQUERYSQL_TYPE_MYSQL|PHPQUERYSQL_TYPE_MSSQL|PHPQUERYSQL_TYPE_POSTGRESql|PHPQUERYSQL_TYPE_ORACLE $builderType
     */
    public function __construct($builderType)
    {
        if (!in_array($builderType, [PHPQUERYSQL_TYPE_MYSQL, PHPQUERYSQL_TYPE_MSSQL, PHPQUERYSQL_TYPE_POSTGRESql, PHPQUERYSQL_TYPE_ORACLE])) throw new \RuntimeException('builder type Undefinied');
        require_once "builder.php";
        $this->Builder = new engine\builder($this);
        $this->bt = $builderType;

        require_once implode(DIRECTORY_SEPARATOR, ["schema", "schema.php"]);
        $this->Schema = new engine\schema\schema($this);
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case "GetBuilderType";
                return $this->bt;
                break;
            default:
                throw new \RuntimeException('Unknown method');
                break;
        endswitch;
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
    public static function Schema()
    {
        return (new self)->Schema;
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
    public static function Schema()
    {
        return (new self)->Schema;
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
    public static function Schema()
    {
        return (new self)->Schema;
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
    public static function Schema()
    {
        return (new self)->Schema;
    }
}
