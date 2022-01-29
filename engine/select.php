<?php

namespace PhpQuerySql\engine;

require_once "builder.php";
class select
{
    const SELECT_ORDER_ASC = "ASC", SELECT_ORDER_DESCENDING = "DESC";
    public function __construct(builder $parent)
    {
        $this->parent = $parent;
        $this->field = [];
        $this->where=[];
        $this->LogicAnd()->SetIndexStart(0)->SetIndexCount(null);
    }
    public function GetParent(): builder
    {
        return $this->parent;
    }
    /**
     * Undocumented function
     *
     * @param string      $field
     * @param string|null $alias
     * @param bool        $group
     * @param null|self::SELECT_ORDER_ASC|self::SELECT_ORDER_DESCENDING $sort
     * @return self
     */
    public function SetField(string $field, ?string $alias = null, bool $group = false, ?string $sort = null): self
    {
        $tmp = new selectField;
        $tmp->field = $field;
        $tmp->alias = $alias;
        $tmp->group = $group;
        $tmp->sort = $sort;
        $this->field[$field] = $tmp;
        return $this;
    }
    public function AddWhere(string $field, $value): self
    {
        $this->where[] = [$field, $value];
        return $this;
    }
    public function LogicOr(): self
    {
        $this->logic = " OR ";
        return $this;
    }
    public function LogicAnd(): self
    {
        $this->logic = " AND ";
        return $this;
    }
    public function SetIndexStart(int $value): self
    {
        $this->IndexStart = $value;
        return $this;
    }
    public function SetIndexCount(?int $value): self
    {
        $this->IndexCount = $value;
        return $this;
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
        switch ($this->parent->GetParent()->GetBuilderType()):
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
                throw new \RuntimeException("Insert unknown builder type");
                break;
        endswitch;
    }
}
class selectField
{
    public string $field;
    public ?string $alias = null;
    public bool $group = false;
    public ?string $sort = null;
}
