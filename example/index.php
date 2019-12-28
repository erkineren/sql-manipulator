<?php

use SqlManipulator\SqlManipulator;
use SqlManipulator\SqlManipulatorException;

require dirname(__DIR__) . '/vendor/autoload.php';

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

echo $manipulator->getSql();