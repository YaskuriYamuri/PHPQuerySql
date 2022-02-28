<?php

namespace PhpQuerySql\engine\crud;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "builder.php"]);

/**
 * Multiple insert in single table query
 * @method self AddValue(insertMultipleValue $value)
 * @method insertMultipleValue CreateValues()
 * @method \PhpQuerySql\engine\builder GetParent()
 */
class insertMultiple
{
    private $items, $parent;
    function __construct(\PhpQuerySql\engine\builder &$parent)
    {
        $this->parent = $parent;
        $this->init();
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case "AddValue":
                switch (count($arguments)):
                    case 1:
                        $this->items[] = $arguments[0];
                        break;
                    default:
                        throw new insertMultipleParametersSendInvalidException;
                        break;
                endswitch;
                break;
            case 'init':
                switch (count($arguments)):
                    case 0:
                        $this->items = [];
                        $this->prmPrefix = "prm_";
                        break;
                    default:
                        throw new insertMultipleParametersSendInvalidException;
                        break;
                endswitch;
                break;
            case 'CreateValues':
                switch (count($arguments)):
                    case 0:
                        return new insertMultipleValue($this);
                        break;
                    default:
                        throw new insertMultipleParametersSendInvalidException;
                        break;
                endswitch;
                break;
            case "GetParent";
                if (count($arguments) <> 0) throw new InsertParametersSendInvalidException;
                return $this->parent;
                break;
            default:
                switch (count($arguments)):
                    default:
                        throw new insertMultipleMethodUnknownException;
                        break;
                endswitch;
                break;
        endswitch;
    }
    public function PDOBindParam(array &$paramArray)
    {
        $tmp = [];
        // foreach ($this->items as $k => &$v) :
        //     if (!$this->GetParent()->isNonParam($v)) 
        //     $tmp[":" . $this->prmPrefix . $k] = $v;
        // endforeach;
        $fieldMain  = array_keys($this->items[0]->items);
        foreach ($this->items as $k => $item) : 
            if (!($item instanceof insertMultipleValue)) throw new \Exception("Item invalid type");
            /** @var insertMultipleValue $item */
            if (count($item->items) != count($fieldMain)) throw new \Exception("Item invalid length");
            foreach ($fieldMain as $fk => $fm) :
                $tmp[":{$this->prmPrefix}{$k}{$fm}{$fk}"] = $item->GetValue($fm);
            endforeach; 
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    function __toString()
    {
        try {
            if (count($this->items) == 0) throw new \RuntimeException("Insert Multiple value not set");
            $fieldMain  = array_keys($this->items[0]->items);
            $valuesGroup = [];
            switch ($this->GetParent()->GetParent()->GetBuilderType()):
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                    foreach ($this->items as $k => $item) :
                        $valuesArray = [];
                        if (!($item instanceof insertMultipleValue)) throw new \Exception("Item invalid type");
                        /** @var insertMultipleValue $item */
                        if (count($item->items) != count($fieldMain)) throw new \Exception("Item invalid length");
                        foreach ($fieldMain as $fk => $fm) :
                            $valuesArray[] =
                                $this->GetParent()->isNonParam($item->GetValue($fm)) ?
                                $this->GetParent()->nonParam($item->GetValue($fm), $this->GetParent()->GetParent()->GetBuilderType()) :
                                ":{$this->prmPrefix}{$k}{$fm}{$fk}";
                        endforeach;
                        $valuesGroup[] = "(" . implode(",", $valuesArray) . ")";
                    endforeach;
                    $valuesGroup = implode(",", $valuesGroup);
                    foreach ($fieldMain as &$fm) :
                        $fm = "`{$fm}`";
                    endforeach;
                    $fieldMain = implode(',', $fieldMain);
                    return "INSERT INTO `{$this->GetParent()->GetTables()}` ({$fieldMain}) VALUES {$valuesGroup};";
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                    foreach ($this->items as $k => $item) :
                        $valuesArray = [];
                        if (!($item instanceof insertMultipleValue)) throw new \Exception("Item invalid type");
                        /** @var insertMultipleValue $item */
                        if (count($item->items) != count($fieldMain)) throw new \Exception("Item invalid length");
                        foreach ($fieldMain as $fk => $fm) :
                            $valuesArray[] =
                                $this->GetParent()->isNonParam($item->GetValue($fm)) ?
                                $this->GetParent()->nonParam($item->GetValue($fm), $this->GetParent()->GetParent()->GetBuilderType()) :
                                ":{$this->prmPrefix}{$k}{$fm}{$fk}";
                        endforeach;
                        $valuesGroup[] = "(" . implode(",", $valuesArray) . ")";
                    endforeach;
                    $valuesGroup = implode(",", $valuesGroup);
                    foreach ($fieldMain as &$fm) :
                        $fm = "[{$fm}]";
                    endforeach;
                    $fieldMain = implode(',', $fieldMain);
                    return "INSERT INTO [{$this->GetParent()->GetTables()}] ({$fieldMain}) VALUES {$valuesGroup};";
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                    foreach ($this->items as $k => $item) :
                        $valuesArray = [];
                        if (!($item instanceof insertMultipleValue)) throw new \Exception("Item invalid type");
                        /** @var insertMultipleValue $item */
                        if (count($item->items) != count($fieldMain)) throw new \Exception("Item invalid length");
                        foreach ($fieldMain as $fk => $fm) :
                            $valuesArray[] =
                                $this->GetParent()->isNonParam($item->GetValue($fm)) ?
                                $this->GetParent()->nonParam($item->GetValue($fm), $this->GetParent()->GetParent()->GetBuilderType()) :
                                ":{$this->prmPrefix}{$k}{$fm}{$fk}";
                        endforeach;
                        $valuesGroup[] = "(" . implode(",", $valuesArray) . ")";
                    endforeach;
                    $valuesGroup = implode(",", $valuesGroup);
                    foreach ($fieldMain as &$fm) :
                        $fm = "\"{$fm}\"";
                    endforeach;
                    $fieldMain = implode(',', $fieldMain);
                    return "INSERT INTO \"{$this->GetParent()->GetTables()}\" ({$fieldMain}) VALUES {$valuesGroup};";
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                    foreach ($this->items as $k => $item) :
                        $valuesArray = [];
                        if (!($item instanceof insertMultipleValue)) throw new \Exception("Item invalid type");
                        /** @var insertMultipleValue $item */
                        if (count($item->items) != count($fieldMain)) throw new \Exception("Item invalid length");
                        foreach ($fieldMain as $fk => $fm) :
                            $valuesArray[] =
                                $this->GetParent()->isNonParam($item->GetValue($fm)) ?
                                $this->GetParent()->nonParam($item->GetValue($fm), $this->GetParent()->GetParent()->GetBuilderType()) :
                                ":{$this->prmPrefix}{$k}{$fm}{$fk}";
                        endforeach;
                        $valuesGroup[] = "(" . implode(",", $valuesArray) . ")";
                    endforeach;
                    $valuesGroup = implode(",", $valuesGroup);
                    foreach ($fieldMain as &$fm) :
                        $fm = "\"{$fm}\"";
                    endforeach;
                    $fieldMain = implode(',', $fieldMain);
                    return "INSERT INTO \"{$this->GetParent()->GetTables()}\" ({$fieldMain}) VALUES {$valuesGroup};";
                    break;
                default:
                    throw new \RuntimeException("Insert Multiple unknown builder type");
                    break;
            endswitch;
        } catch (\Exception $th) {
            return $th->getMessage();
        }
    }
    function __debugInfo()
    {
        return [];
    }
}

/**
 * value
 * @method self SetValue(string $field, \PhpQuerySql\engine\builder::VALUE_CURRENT_DATE|\PhpQuerySql\engine\builder::VALUE_CURRENT_TIME|\PhpQuerySql\engine\builder::VALUE_CURRENT_DATETIME|mixed $value)
 * @method mixed GetValue(string $field)
 * @method insertMultiple GetParent()
 */
class insertMultipleValue
{
    function __construct(insertMultiple &$parent)
    {
        $this->parent = $parent;
        $this->items = [];
    }
    function __call($name, $arguments)
    {
        switch ($name):
            case 'SetValue':
                switch (count($arguments)):
                    case 2:
                        $this->items[$arguments[0]] = $arguments[1];
                        return $this;
                        break;
                    default:
                        throw new insertMultipleParametersSendInvalidException;
                        break;
                endswitch;
                break;
            case 'GetValue':
                switch (count($arguments)):
                    case 1:
                        return $this->items[$arguments[0]];
                        return $this;
                        break;
                    default:
                        throw new insertMultipleParametersSendInvalidException;
                        break;
                endswitch;
                break;
            case 'GetParent':
                switch (count($arguments)):
                    case 0:
                        return $this->parent;
                        break;
                    default:
                        throw new insertMultipleParametersSendInvalidException;
                        break;
                endswitch;
                break;
            default:
                throw new insertMultipleMethodUnknownException;
                break;
                break;
        endswitch;
    }
    function __toString()
    {
        return implode(",", $this->items);
    }
}


class insertMultipleParametersSendInvalidException extends \Exception
{
    function __construct()
    {
        parent::__construct("insertMultiple Parameter send invalid");
    }
}
class insertMultipleMethodUnknownException extends \Exception
{
    function __construct()
    {
        parent::__construct("insertMultiple call method unknown");
    }
}
