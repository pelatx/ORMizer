<?php

/**********************
* ORMizer basic usage *
++++++++++++++++++****/

/**********************
*
* 1 - Add your database options to 'Config.php' (only "DBMS = 'mysql'" yet):
*
*   class Config {
*
*       //Database options
*       const DBMS = 'mysql';
*       const DBMS_HOST = 'localhost';
*       const APP_DB = 'your_app_db';
*       const APP_DB_USER = 'your_db_user';
*       const APP_DB_USER_PASS = 'your_user_password';
*   }
*
**********************/

/**********************
*
* 2 - Create your model
*
**********************/

class TestModel {

    private $property1;
    private $property2;
    private $property3;

    function __construct($param1, $param2, $param3) {
        $this->property1 = $param1;
        $this->property2 = $param2;
        $this->property3 = $param3;
    }

    public function testMethod($msg) {
        $result = $msg. ': '.
            $this->property1. ' / '.
            $this->property2. ' / '.
            $this->property3;
        return $result;
    }
}

/**************************
*
* 3 - Include the ORMizer class-autoloader file
*     and setup to use the namespaced ORMizer class
*
***************************/

require('../ORMizer.inc.php');
use ORMizer\ORMizer;

/***************************
*
* 4 - Instantiate your model in the ORMizer way
*
***************************/

$testModel = ORMizer::persist( new TestModel('a', '2', 'c') );
// Or:
// $testModel = ORMizer::_( new TestModel('a', '2', 'c') );

/*
That's all. Now, you have a modified instance of 'TestModel' in
'$testModel' and you can use the ORMizer methods on it.
The database table is not created until you use the 'save()' method.
Then, you'll get a table called 'ormized_TestModel', with as many fields
as properties in your model. Plus a field called 'ormizer_id',
which will be the primary key.
Let's play a little to see how it works.
*/

// Let's look at the current values in memory
echo '<b>TestModel instance in memory and saved to the database:</b></br><pre>';
var_dump($testModel);
echo '</pre></br>';
// Save in the database
$testModel->save();
// Let's alter the properties in memory
$testModel->property1 = '1';
$testModel->property2 = 'f';
$testModel->property3 = '3';
echo '<b>TestModel instance changed in memory (differs from the database):</b></br><pre>';
var_dump($testModel);
echo '</pre></br>';
// At this point, looking at the database, you would see that it still stores
// the values 'a', 'b', 'c'. Although we have varied them in the object in memory.
// Let's get them back from the database to the object in memory.
$testModel->load($testModel->ormizer_id);
echo '<b>TestModel instance after being retrieved from the database:</b></br><pre>';
var_dump($testModel);
echo '</pre></br>';

/*
If you execute this script several times, you will see that each time an object
with different id is saved. You can get all objects saved in the table as follows:
*/

$instances = $testModel->getSavedInstances();
if(!$instances) {
    echo '<b>Nothing saved.</b></br>';
} else {
    echo '<b>All instances in the database:</b></br><pre>';
    var_dump($instances);
    echo '</pre></br>';
}

/*
There is no method on the object to perform selective searches as in sql queries.
On the array obtained, you must be the one who performs the search.
*/
/*
On the other hand, your primitive model is fully functional: you can use your
public methods just as if you had not transformed it with ORMizer.
*/

echo '<b>Using original methods of your model:</b></br>';
echo $testModel->testMethod('Values of properties'). '</br>';
?>
