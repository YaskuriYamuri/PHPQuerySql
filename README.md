
# PHPQuerySql

# Builder
Main class of builder query
~~~php
use PhpQuerySql\PhpQueryMsSql as Mssql;

use PhpQuerySql\PhpQueryMySql as Mysql;

use PhpQuerySql\PhpQueryPOSTGRESql as PostGreSql;

use PhpQuerySql\PhpQueryOracle as Oracle;

~~~
## Tabel
Main class of build basic table
### Dumps information about a variable
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
var_dump( db::Builder()->Generate->Table()->Create("demo")->AddField("id","integer",11,null)->AddField("fistname","varchar",10,"")->AddField("lastname","varchar",10,""));
~~~
### Simple generate
~~~php
use PhpQuerySql\PhpQueryMySql as db; 
print(db::Builder()->Generate->Table()->Create("demo")->AddField("id","integer",11,null)->AddField("fistname","varchar",10,"")->AddField("lastname","varchar",10,"")); 
~~~
## Insert
Main class of build basic insert 
### Dumps information about a variable
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
var_dump( db::Builder()->SetTables("demo")->Insert->SetValue("id",1)->SetValue("fistname","albert")->SetValue("lastname","einstein")->PDOBindParam($param));
~~~
### Simple generate
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
echo  db::Builder()->SetTables("demo")->Insert->SetValue("id",1)->SetValue("fistname","albert")->SetValue("lastname","einstein")->PDOBindParam($param);
echo "\n<br>\n";
var_dump($param);
~~~
## Select
Main class of build basic select
### Dumps information about a variable
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
var_dump( db::Builder()->SetTables("demo")->Select->SetField("fistname","fname")->SetField("lastname","lname",false,\PhpQuerySql\engine\select::SELECT_ORDER_DESCENDING)->LogicOr()->AddWhere("id",2)->AddWhere("id",3)->PDOBindParam($param));
~~~
### Simple generate
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
echo  db::Builder()->SetTables("demo")->Select->SetField("fistname","fname")->SetField("lastname","lname",false,\PhpQuerySql\engine\select::SELECT_ORDER_DESCENDING)->LogicOr()->AddWhere("id",2)->AddWhere("id",3)->PDOBindParam($param);
echo "\n<br>\n";
var_dump($param);
~~~
## Update
Main CLass of build basic update
### Dumps information about a variable
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
 var_dump (db::Builder()->SetTables("demo")->Update->SetValue("fistname","isaac")->SetValue("lastname","newton")->LogicOr()->AddWhere("id",2)->AddWhere("id",3)->PDOBindParam($param));
~~~
### Simple generate
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
echo  db::Builder()->SetTables("demo")->Update->SetValue("fistname","isaac")->SetValue("lastname","newton")->LogicOr()->AddWhere("id",2)->AddWhere("id",3)->PDOBindParam($param);
echo "\n<br>\n";
var_dump($param);
~~~
## Delete
Main class of build basic Delete
### Dumps information about a variable
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
var_dump( db::Builder()->SetTables("demo")->Delete->AddWhere("id",3)->AddWhere("id",2)->PDOBindParam($param));
~~~
### Simple generate
~~~php
use PhpQuerySql\PhpQueryMySql as db;
$param=[];
echo  db::Builder()->SetTables("demo")->Delete->AddWhere("id",3)->AddWhere("id",2)->PDOBindParam($param);
echo "\n<br>\n";
var_dump($param);
~~~
## Schema Database
Main class of build basic query schema
### Show
~~~php
use PhpQuerySql\PhpQueryMySql as db; 
print(db::Schema()->Database()->Show());
~~~
### Detail
~~~php
use PhpQuerySql\PhpQueryMySql as db;
print(db::Schema()->Database()->Info('db_example'))
~~~
## Schema Tabel
Main class of build basic query schema
### Show
~~~php
use PhpQuerySql\PhpQueryMySql as db; 
print(db::Schema()->Table()->Show());
~~~
### Detail
~~~php
use PhpQuerySql\PhpQueryMySql as db;
print(db::Schema()->Table()->Info('tb_example'))
~~~