<?php

//namespace ORMizer;
include('../ORMizer.inc.php');

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

//$db_manager = DBManager::instance();
/*
echo $db_manager::INTEGER. '</br>';
echo $db_manager::BLOB. '</br>';
echo '<pre>';
var_dump($db_manager->type_mapping);
echo '</pre>';
$props = array(
    'ormizer_id'    => 'ididididididididid',
    'title'         => 'el envolo',
    'author'        => 'tu mismo',
    'year'          => 1967
);
*/
//$db_manager->insertRow('ormized_Book', $props);
/*
$row = $db_manager->getRow('ormized_TypeTest', 'ormizer_id', '1234567890123456');
$rows = $db_manager->getAll('ormized_TypeTest');
$table_desc = $db_manager->getTableDescription('ormized_TypeTest');
*/
/*
echo '<pre>';
var_dump($table_desc);
echo '</pre></br>';
*/
/*
function castFetchedRow($row, $table_desc, $db_manager) {
    for($i = 0; $i < count($row); $i++) {
        $field = $table_desc[$i]['field'];
        $type = $table_desc[$i]['type'];
        foreach($db_manager->type_mapping as $relational => $php) {
            if ($type === $relational) {
                if($php === $db_manager::INTEGER || $php === $db_manager::DECIMAL) {
                    $row[$field] += 0;
                }
            }
        }
    }
    return $row;
}

function castFetchedRows($rows, $table_desc, $db_manager) {
    // Test if we got a single row or an array of rows
    $bidimensional = false;
    foreach($rows as $row) {
        if(is_array($row)) $bidimensional =  true;
    }

    if($bidimensional) {
        $updated_rows = array();
        foreach($rows as $row) {
            $updated_rows[] = castFetchedRow($row, $table_desc, $db_manager);
        }
    }else {
        $updated_rows = castFetchedRow($rows, $table_desc, $db_manager);
    }
    return $updated_rows;
}
*/
/*
$updated_rows = $db_manager->castFetchedRows($rows, $table_desc, $db_manager);
echo '<pre>';
var_dump($updated_rows);
echo '</pre></br>';
*/

use ORMizer\ORMizer;

class TypeTest {

    private $timestamp;
    private $float;
    private $array;

    function __construct() {}
}

$type_test = ORMizer::persist(new TypeTest());
echo '<pre>';
var_dump($type_test);
echo '</pre></br>';
$type_test->load('1234567890123456');
echo '<pre>';
var_dump($type_test);
echo '</pre></br>';

$all = $type_test->getSavedInstances();
echo '<pre>';
var_dump($all);
echo '</pre></br>';
?>
