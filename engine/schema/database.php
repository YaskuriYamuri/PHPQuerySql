<?php
namespace PhpQuerySql\engine\schema;
/**
 * Kelas skema
 * 
 * @method string Show()
 * @method string Info($name)
 * 
 *  @method schema GetParent()
 */
class database{
    public function __construct(schema $parent ) {
        $this->parent = $parent;
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case "Show":
                if (count($arguments) <> 0) throw new UnknownSchemaTableMethodCallParamException;
                switch ($this->GetParent()->GetParent()->GetBuilderType()):
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                        return "SHOW DATABASES;";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                        return "SELECT [name] FROM [sys].[databases];";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                        return "SELECT \"datname\" FROM \"pg_database\";";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                        throw new \RuntimeException("Show Schema table type under proccess");
                        return "";
                        break;
                    default:
                        throw new \RuntimeException("Schema table unknown builder type");
                        break;
                endswitch;
                break;
            case "Info":
                if (count($arguments) <> 1) throw new UnknownSchemaDatabaseMethodCallParamException;    
                switch ($this->GetParent()->GetParent()->GetBuilderType()):
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                        return "SELECT * FROM `information_schema`.`SCHEMATA` WHERE SCHEMA_NAME= '{$arguments[0]}';";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                        return "SELECT  * FROM  [information_schema].[SCHEMATA] WHERE [SCHEMA_NAME]=SCHEMA_NAME() AND  [CATALOG_NAME]='{$arguments[0]}';";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                        return "SELECT * FROM information_schema.schemata WHERE \"catalog_name\" = '{$arguments[0]}' AND \"schema_name\" = current_schema();";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                        throw new \RuntimeException("Show Schema table type under proccess");
                        break;
                    default:
                        throw new \RuntimeException("Schema table unknown builder type");
                        break;
                endswitch;
                break;
                break;
            case "GetParent":
                if (count($arguments) <> 0) throw new UnknownSchemaDatabaseMethodCallParamException;
                return $this->parent;
                break;
            default:
                throw new UnknownSchemaDatabaseMethodCallException($name);
                break;
        endswitch;
    }
}

class UnknownSchemaDatabaseMethodCallException extends \Exception
{
    public function __construct($name)
    {
        parent::__construct("Unknown Schema database method call " . $name);
    }
}
class UnknownSchemaDatabaseMethodCallParamException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Unknown Schema database method call param set");
    }
}
