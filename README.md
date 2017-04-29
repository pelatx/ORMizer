# ORMizer
Simple but functional PHP ORM based on Active Record. Focused on being extremely easy to use.

## Why?
There are many excellent choices out there. Professional and well tested frameworks that do this in a perfect way (surely better than this tool). But ...

- I needed a simple tool for small projects.
- I greatly appreciate the ease of use. ORMizer does not involve the learning curve of a framework.
- I like intuitive approaches, although this means fewer options.
- I love spending my free time coding. My wife does not like it so much, I think. :-D

## Features
- Transparency of the entire access layer to DDBB.
- Configurable type casting.
- An object transformed by ORMizer retains all its original functionality: you can use its public methods.
- You can define an alias for an object when you create it and retrieve it using the alias.
- You can preload all objects with aliases and make them available in global scope.

## Usage
#### 1 - Edit `Config.php` with your database settings:
```
class Config {

    //Database options
    const DBMS = 'mysql'; // Must be the PDO string for the Database Management System
    const DBMS_HOST = 'localhost';
    const DBMS_PORT = ''; // Leave empty for default port
    const APP_DB = 'ormzr_tests';
    const APP_DB_USER = 'ormzr_tests_user';
    const APP_DB_USER_PASS = 'ormzr_tests_user_pass';
}
```
#### 2 - Include the ORMizer autoloader:
```
include("ORMizer.inc.php");
```
#### 3 - Use the ORMizer class and namespace in your script:
```
use ORMizer\ORMizer;
```
#### Now, you are ready to:

Make an object persistent:
```
$original = new Original("arg1", "arg2");
$persistent = ORMizer::persist($original);
```
Save it into database:
```
$persistent->save();
```
Load from database:
```
$persitent->load($ormizer_id);
```
Delete from the database:
```
$persistent->delete();
```
Retrieve all objects of same class saved in the database:
```
$all = $persistent->getSavedInstances();
```

More in the [examples](https://github.com/pelatx/ORMizer/tree/master/examples) included.