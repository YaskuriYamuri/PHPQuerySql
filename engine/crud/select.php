<?php

namespace PhpQuerySql\engine\crud;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "builder.php"]);
/**
 * Kelas select
 * 
 * @method \PhpQuerySql\engine\builder GetParent()
 * @method self SetField(string $field)
 * @method self SetField(string $field, ?string $alias = null)
 * @method self SetField(string $field, ?string $alias = null, bool $group = false)
 * @method self SetField(string $field, ?string $alias = null, bool $group = false, null|\PhpQuerySql\PhpQuerySql::ORDERBY_ASCENDING|\PhpQuerySql\PhpQuerySql::ORDERBY_DESCENDING $sort = null)
 * @method self AddWhere(\PhpQuerySql\engine\BetweenWhere $param)
 * @method self AddWhere(string $field, mixed $value)
 * @method self AddWhereCustom(string $value1, mixed $value2)
 * @method self LogicAnd()
 * @method self LogicOr() 
 * @method self SetIndexStart(?int $value)
 * @method self SetIndexCount(?int $value)
 */
class select
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
                else : throw new SelectParametersSendInvalidException;
                endif;
                break;
            case "SetField":
                $tmp = new selectField;
                if (count($arguments) == 1) :
                    $tmp->field = $arguments[0];
                elseif (count($arguments) == 2) :
                    $tmp->field = $arguments[0];
                    $tmp->alias = $arguments[1];
                elseif (count($arguments) == 3) :
                    $tmp->field = $arguments[0];
                    $tmp->alias = $arguments[1];
                    $tmp->group = $arguments[2];
                elseif (count($arguments) == 4) :
                    $tmp->field = $arguments[0];
                    $tmp->alias = $arguments[1];
                    $tmp->group = $arguments[2];
                    $tmp->sort = $arguments[3];
                else : throw new SelectParametersSendInvalidException;
                endif;
                $this->field[$arguments[0]] = $tmp;
                return $this;
                break;
            case "AddWhere":
                switch (count($arguments)):
                    case 1:
                        # \PhpQuerySql\engine\BetweenWhere
                        $this->where[] = &$arguments[0];
                        break;
                    case 2:
                        $this->where[] = &$arguments;
                        break;
                    default:
                        new SelectParametersSendInvalidException;
                        break;
                endswitch;
                return $this;
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
            case "SetIndexStart":
                if (count($arguments) == 1) :
                    if (is_null($arguments[0])) :   $this->IndexStart = $arguments[0];
                    elseif (!is_integer($arguments[0])) :  throw new SelectParametersSendInvalidException;
                    else : $this->IndexStart = $arguments[0];
                    endif;
                else :  throw new SelectParametersSendInvalidException;
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
                            throw new SelectParametersSendInvalidException;
                        endif;
                    endif;
                else : throw new SelectParametersSendInvalidException;
                endif;
                return $this;
                break;
            case "init":
                $this->field = [];
                $this->where = [];
                $this->wherenbp = [];
                $this->LogicAnd()->SetIndexStart(null)->SetIndexCount(1);
                break;
            default:
                throw new SelectMethodUnknownException;
                break;
        endswitch;
    }
    public function PDOBindParam(array &$paramArray)
    {
        $tmp = [];
        foreach ($this->where as $k => &$v) :
            if ($v instanceof \PhpQuerySql\engine\BetweenWhere) :
                $tmp[":where{$v->field}val1_{$k}"] = $v->value1;
                $tmp[":where{$v->field}val2_{$k}"] = $v->value2;
            elseif (!$this->GetParent()->isNonParam($v[1])) :  $tmp[":where$v[0]$k"] = $v[1];
            endif;
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    public function __toString()
    {
        try {
            $afield = [];
            $agroupby = [];
            $aorderby = [];
            $awhere = [];
            switch ($this->GetParent()->GetParent()->GetBuilderType()):
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MYSQL:
                    # SELECT * FROM tb as a WHERE tb.field=:wherefield GROUP BY gb1 ORDER BY ob1 LIMIT 0 
                    if (count($this->field) > 0) :
                        /** @var selectField $fselect */
                        foreach ($this->field as $k => &$fselect) :
                            if ($fselect->group) $agroupby[] = $fselect->field;
                            if (!is_null($fselect->sort)) $aorderby[] = $fselect->field . " " . $fselect->sort;
                            $afield[] = !is_null($fselect->alias) ? "" . $fselect->field . " AS `" . $fselect->alias . "`" : "" . $fselect->field . " AS `" . $fselect->field . "`";
                        endforeach;
                    else :
                        $afield[] = "*";
                    endif;
                    foreach ($this->where as $key => &$val) {
                        if ($val instanceof \PhpQuerySql\engine\BetweenWhere) :
                            $awhere[] = "`{$val->field}` BETWEEN " . ($this->GetParent()->isNonParam($val->value1) ? $this->GetParent()->nonParam($val->value1, $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val->field}val1_{$key}") . " AND " . ($this->GetParent()->isNonParam($val->value2) ? $this->GetParent()->nonParam($val->value2, $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val->field}val2_{$key}");
                        else :
                            $awhere[] = "`$val[0]`=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":where" . $val[0] . $key);
                        endif;
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }
                    $sfield = implode(",", $afield);
                    $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";;
                    $sgroup = count($agroupby) > 0 ? "GROUP BY " . implode(",", $agroupby) : "";
                    $sorder = count($aorderby) > 0 ? "ORDER BY " . implode(",", $aorderby) : "";
                    $slimit = is_null($this->IndexStart) ? "" : (" LIMIT " . $this->IndexStart . (!is_null($this->IndexCount) ? " , " . $this->IndexCount : ""));
                    return sprintf("SELECT %s FROM `%s` %s %s %s %s;", $sfield, $this->GetParent()->GetTables(), $swhere, $sgroup, $sorder, $slimit);
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_MSSQL:
                    # SELECT * FROM tb as a WHERE tb.field=:wherefield GROUP BY gb1 ORDER BY ob1 LIMIT 0 
                    if (count($this->field) > 0) :
                        /** @var selectField $fselect */
                        foreach ($this->field as $k => &$fselect) :
                            if ($fselect->group) $agroupby[] = $fselect->field;
                            if (!is_null($fselect->sort)) $aorderby[] = $fselect->field . " " . $fselect->sort;
                            $afield[] = !is_null($fselect->alias) ? "[" . $fselect->alias . "]=" . $fselect->field . "" : "[" . $fselect->field . "]=" . $fselect->field . "";
                        endforeach;
                    else :
                        $afield[] = "*";
                    endif;
                    foreach ($this->where as $key => $val) {
                        if ($val instanceof \PhpQuerySql\engine\BetweenWhere) :
                            $awhere[] = "[{$val->field}] BETWEEN " . ($this->GetParent()->isNonParam($val->value1) ? $this->GetParent()->nonParam($val->value1, $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val->field}val1_{$key}") . " AND " . ($this->GetParent()->isNonParam($val->value2) ? $this->GetParent()->nonParam($val->value2, $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val->field}val2_{$key}");
                        else :
                            $awhere[] = "[{$val[0]}]=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val[0]}{$key}");
                        endif;
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }
                    $sfield = implode(",", $afield);
                    $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";;
                    $sgroup = count($agroupby) > 0 ? "GROUP BY " . implode(",", $agroupby) : "";
                    $sorder = count($aorderby) > 0 ? "ORDER BY " . implode(",", $aorderby) : "";
                    $slimit = is_null($this->IndexStart) ? "" : (" OFFSET " . $this->IndexStart . " ROWS " . (!is_null($this->IndexCount) ? " FETCH NEXT " . $this->IndexCount . " ROWS ONLY "  : ""));
                    return sprintf("SELECT %s FROM [%s] %s %s %s %s;", $sfield, $this->GetParent()->GetTables(), $swhere, $sgroup, $sorder, $slimit);
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_POSTGRESql:
                    if (count($this->field) > 0) :
                        /** @var selectField $fselect */
                        foreach ($this->field as $k => &$fselect) :
                            if ($fselect->group) $agroupby[] = $fselect->field;
                            if (!is_null($fselect->sort)) $aorderby[] = $fselect->field . " " . $fselect->sort;
                            $afield[] = !is_null($fselect->alias) ? "" . $fselect->field . " AS \"" . $fselect->alias . "\"" : "" . $fselect->field . " AS \"" . $fselect->field . "\"";
                        endforeach;
                    else :
                        $afield[] = "*";
                    endif;
                    foreach ($this->where as $key => $val) {
                        if ($val instanceof \PhpQuerySql\engine\BetweenWhere) :
                            $awhere[] = "\"{$val->field}\" BETWEEN " . ($this->GetParent()->isNonParam($val->value1) ? $this->GetParent()->nonParam($val->value1, $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val->field}val1_{$key}") . " AND " . ($this->GetParent()->isNonParam($val->value2) ? $this->GetParent()->nonParam($val->value2, $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val->field}val2_{$key}");
                        else :
                        $awhere[] = "\"$val[0]\"=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val[0]}{$key}"); endif;
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }
                    $sfield = implode(",", $afield);
                    $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";;
                    $sgroup = count($agroupby) > 0 ? "GROUP BY " . implode(",", $agroupby) : "";
                    $sorder = count($aorderby) > 0 ? "ORDER BY " . implode(",", $aorderby) : "";
                    $slimit = is_null($this->IndexStart) ? "" : (" OFFSET " . $this->IndexStart . " ROWS " . (!is_null($this->IndexCount) ? " FETCH NEXT " . $this->IndexCount . " ROWS ONLY "  : ""));
                    return sprintf("SELECT %s FROM \"%s\" %s %s %s %s;", $sfield, $this->GetParent()->GetTables(), $swhere, $sgroup, $sorder, $slimit);
                    break;
                case \PhpQuerySql\PHPQUERYSQL_TYPE_ORACLE:
                    if (count($this->field) > 0) :
                        /** @var selectField $fselect */
                        foreach ($this->field as $k => &$fselect) :
                            if ($fselect->group) $agroupby[] = $fselect->field;
                            if (!is_null($fselect->sort)) $aorderby[] = $fselect->field . " " . $fselect->sort;
                            $afield[] = !is_null($fselect->alias) ? "" . $fselect->field . " AS \"" . $fselect->alias . "\"" : "" . $fselect->field . " AS \"" . $fselect->field . "\"";
                        endforeach;
                    else :
                        $afield[] = "*";
                    endif;
                    foreach ($this->where as $key => $val) {
                        if ($val instanceof \PhpQuerySql\engine\BetweenWhere) :
                            $awhere[] = "\"{$val->field}\" BETWEEN " . ($this->GetParent()->isNonParam($val->value1) ? $this->GetParent()->nonParam($val->value1, $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val->field}val1_{$key}") . " AND " . ($this->GetParent()->isNonParam($val->value2) ? $this->GetParent()->nonParam($val->value2, $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val->field}val2_{$key}");
                        else :
                        $awhere[] = "\"$val[0]\"=" . ($this->GetParent()->isNonParam($val[1]) ? $this->GetParent()->nonParam($val[1], $this->GetParent()->GetParent()->GetBuilderType()) : ":where{$val[0]}{$key}");
                        endif;
                    }
                    foreach ($this->wherenbp as $key => $val) {
                        $awhere[] = "$val[0]=$val[1]";
                    }
                    $sfield = implode(",", $afield);
                    $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";;
                    $sgroup = count($agroupby) > 0 ? "GROUP BY " . implode(",", $agroupby) : "";
                    $sorder = count($aorderby) > 0 ? "ORDER BY " . implode(",", $aorderby) : "";
                    $slimit = is_null($this->IndexStart) ? "" : (" OFFSET " . $this->IndexStart . " ROWS " . (!is_null($this->IndexCount) ? " FETCH NEXT " . $this->IndexCount . " ROWS ONLY "  : ""));
                    return sprintf("SELECT %s FROM \"%s\" %s %s %s %s;", $sfield, $this->GetParent()->GetTables(), $swhere, $sgroup, $sorder, $slimit);
                    break;
                default:
                    throw new \RuntimeException("Select unknown builder type");
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

/**
 * 
 * @property string $field
 * @property null|string $alias
 * @property bool $group
 * @property null|string $sort
 */
class selectField
{
    public  $field;
    public  $alias = null;
    public  $group = false;
    public  $sort = null;
}

class SelectParametersSendInvalidException extends \Exception
{
    function __construct()
    {
        parent::__construct("Select Parameter send invalid");
    }
}
class SelectMethodUnknownException extends \Exception
{
    function __construct()
    {
        parent::__construct("Select call method unknown");
    }
}
