# SQL Manipulator
SQL string manipulator for PHP
Manipulate SQL statements dynamically, regardless of whether the keywords are sorted or "AND / OR" at the beginning.

## Install
```shell script
composer require erkineren/sql-manipulator
```

## Example
##### Example Code
```php
// SQL before manipulation
$sql = "SELECT sa.id FROM s_articles sa WHERE sa.active = 1";

$manipulator = new SqlManipulator($sql);

try {
    $manipulator
        ->addWhere("AND (sa.id = 5 OR sa.name LIKE '%al-%')")
        ->addWhere("OR sas.name = 'test'")
        ->addJoin("JOIN s_articles_details sad ON sad.articleID = sa.id")
        ->addJoin("JOIN s_articles_supplier sas ON sas.id = sa.supplierID")
        ->addSelectColumn('sad.ordernumber, COUNT(sad.id) as variant_count, sas.name')
        ->addSelectColumn("(SELECT id*2 FROM s_articles WHERE id = sa.id) as subq")
        ->addHaving("variant_count > 1")
        ->addGroupBy("sa.id");
} catch (SqlManipulatorException $e) {
    echo $e->getMessage();
}

// SQL after manipulation
echo $manipulator->getSql();
```

##### Example Output
```sql
SELECT sa.id, sad.ordernumber, COUNT(sad.id) AS variant_count, sas.name, (SELECT id * 2 FROM s_articles WHERE id = sa.id) AS subq FROM s_articles sa INNER JOIN s_articles_details sad ON sad.articleID = sa.id INNER JOIN s_articles_supplier sas ON sas.id = sa.supplierID WHERE sa.active = 1 AND (sa.id = 5 OR sa.name LIKE '%al-%') OR sas.name = 'test' GROUP BY sa.id HAVING variant_count > 1
```