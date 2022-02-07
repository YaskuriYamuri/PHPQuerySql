<?php

namespace PhpQuerySql\engine\schema;

/**
 * Kelas skema
 * @method string Show()
 * @method string Info(string $tbName)
 *  @method schema GetParent()
 */
class table
{
    public function __construct(schema $parent)
    {
        $this->parent = $parent;
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case "Show":
                if (count($arguments) <> 0) throw new UnknownSchemaTableMethodCallParamException;
                switch ($this->GetParent()->GetParent()->GetBuilderType()):
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                        return "SHOW TABLES;";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                        return "SELECT [TABLE_NAME] FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_TYPE] = 'BASE TABLE' AND [TABLE_SCHEMA]=SCHEMA_NAME() AND [TABLE_CATALOG]=DB_NAME(); ";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                        return "SELECT \"table_name\" FROM \"information_schema\".\"tables\" WHERE \"table_type\"='BASE TABLE' AND \"table_catalog\" =current_database() AND \"table_schema\" = current_schema();";
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
                if (count($arguments) <> 1) throw new UnknownSchemaTableMethodCallParamException; 
                switch ($this->GetParent()->GetParent()->GetBuilderType()):
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                        return "SELECT * FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`=DATABASE() AND `TABLE_TYPE` ='BASE TABLE' AND `TABLE_NAME` = '{$arguments[0]}';";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                        return "SELECT *  FROM [INFORMATION_SCHEMA].[TABLES] WHERE  [TABLE_SCHEMA]=SCHEMA_NAME() AND [TABLE_CATALOG]=DB_NAME() AND [TABLE_NAME] = '{$arguments[0]}';";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                        return "SELECT * FROM \"information_schema\".\"tables\" WHERE \"table_catalog\" =current_database() AND \"table_schema\" = current_schema() AND \"table_name\" = '{$arguments[0]}';";
                        break;
                    case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                        throw new \RuntimeException("Show Schema table type under proccess");
                        break;
                    default:
                        throw new \RuntimeException("Schema table unknown builder type");
                        break;
                endswitch;
                break;
            case "GetParent":
                if (count($arguments) <> 0) throw new UnknownSchemaTableMethodCallParamException;
                return $this->parent;
                break;
            default:
                throw new UnknownSchemaTableMethodCallException($name);
                break;
        endswitch;
    }
}

class UnknownSchemaTableMethodCallException extends \Exception
{
    public function __construct($name)
    {
        parent::__construct("Unknown schema table method call " . $name);
    }
}
class UnknownSchemaTableMethodCallParamException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Unknown schema table method call param set");
    }
}
