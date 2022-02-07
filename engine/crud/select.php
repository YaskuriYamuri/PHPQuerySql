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
 * @method self AddWhere(string $field, mixed $value)
 * @method self LogicAnd()
 * @method self LogicOr() 
 * @method self SetIndexStart(int $value)
 * @method self SetIndexCount(?int $value)
 */
class select
{ 
    public function __construct(\PhpQuerySql\engine\builder $parent)
    {
        $this->parent = $parent;
        $this->field = [];
        $this->where = [];
        $this->LogicAnd()->SetIndexStart(0)->SetIndexCount(null);
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
                if (count($arguments) == 2) :
                    $this->where[] = &$arguments;
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
                if (count($arguments) == 1) : if (!is_integer($arguments[0])) throw new SelectParametersSendInvalidException;
                    $this->IndexStart = $arguments[0];
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
            default:
                throw new SelectMethodUnknownException;
                break;
        endswitch;
    }
    public function PDOBindParam(array &$paramArray): self
    {
        $tmp = [];
        foreach ($this->where as $k => &$v) :
            $tmp[":where$v[0]$k"] = $v[1];
        endforeach;
        $paramArray = $tmp;
        return $this;
    }
    public function __toString(): string
    {
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
                foreach ($this->where as $key => $val) {
                    $awhere[] = "`$val[0]`=:where" . $val[0] . $key;
                }
                $sfield = implode(",", $afield);
                $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";;
                $sgroup = count($agroupby) > 0 ? "GROUP BY " . implode(",", $agroupby) : "";
                $sorder = count($aorderby) > 0 ? "ORDER BY " . implode(",", $aorderby) : "";
                $slimit = $this->IndexStart . (!is_null($this->IndexCount) ? "," . $this->IndexCount : "");
                return sprintf("SELECT %s FROM `%s` %s %s %s LIMIT %s", $sfield, $this->GetParent()->GetTables(), $swhere, $sgroup, $sorder, $slimit);
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
                    $awhere[] = "[$val[0]]=:where" . $val[0] . $key;
                }
                $sfield = implode(",", $afield);
                $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";;
                $sgroup = count($agroupby) > 0 ? "GROUP BY " . implode(",", $agroupby) : "";
                $sorder = count($aorderby) > 0 ? "ORDER BY " . implode(",", $aorderby) : "";
                $slimit = "OFFSET " . $this->IndexStart . " ROWS" . (!is_null($this->IndexCount) ? " FETCH NEXT " . $this->IndexCount . " ROWS ONLY "  : "");
                return sprintf("SELECT %s FROM [%s] %s %s %s LIMIT %s", $sfield, $this->GetParent()->GetTables(), $swhere, $sgroup, $sorder, $slimit);
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
                    $awhere[] = "\"$val[0]\"=:where" . $val[0] . $key;
                }
                $sfield = implode(",", $afield);
                $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";;
                $sgroup = count($agroupby) > 0 ? "GROUP BY " . implode(",", $agroupby) : "";
                $sorder = count($aorderby) > 0 ? "ORDER BY " . implode(",", $aorderby) : "";
                $slimit = "OFFSET " . $this->IndexStart . " ROWS" . (!is_null($this->IndexCount) ? " FETCH NEXT " . $this->IndexCount . " ROWS ONLY "  : "");
                return sprintf("SELECT %s FROM \"%s\" %s %s %s %s", $sfield, $this->GetParent()->GetTables(), $swhere, $sgroup, $sorder, $slimit);
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
                    $awhere[] = "\"$val[0]\"=:where" . $val[0] . $key;
                }
                $sfield = implode(",", $afield);
                $swhere = count($awhere) > 0 ? "WHERE " . implode($this->logic, $awhere) : "";;
                $sgroup = count($agroupby) > 0 ? "GROUP BY " . implode(",", $agroupby) : "";
                $sorder = count($aorderby) > 0 ? "ORDER BY " . implode(",", $aorderby) : "";
                $slimit = "OFFSET " . $this->IndexStart . " ROWS" . (!is_null($this->IndexCount) ? " FETCH NEXT " . $this->IndexCount . " ROWS ONLY "  : "");
                return sprintf("SELECT %s FROM \"%s\" %s %s %s %s", $sfield, $this->GetParent()->GetTables(), $swhere, $sgroup, $sorder, $slimit);
                break;
            default:
                throw new \RuntimeException("Select unknown builder type");
                break;
        endswitch;
    }
    function __debugInfo()
    {
        try {
            $param =[];
             $this->PDOBindParam($param );
            return ["Query" => (string)$this,"Param"=>$param, "Builder Type" => $this->GetParent()->GetParent()->GetBuilderType()];
        } catch (\Exception $ex) {
            return ["Error" => $ex];
        }
    }
}
class selectField
{
    public string $field;
    public ?string $alias = null;
    public bool $group = false;
    public ?string $sort = null;
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
