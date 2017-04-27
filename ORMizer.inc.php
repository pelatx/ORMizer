<?php

/**
 * Automatic classloader for ORMizer.
 * @param string $class Class to find.
 */
function ORMizer_autoload($class) {
    $name_array = explode('\\', $class);
    if(count($name_array) === 2 && $name_array[0] === 'ORMizer') {
        if($name_array[1] === 'Config') {
            $class_path = __DIR__. DIRECTORY_SEPARATOR. $name_array[1]. '.php';
        }elseif(strpos($name_array[1], 'Adapter') !== false) {
            $class_path = __DIR__. DIRECTORY_SEPARATOR. 'lib'. DIRECTORY_SEPARATOR. 'DBAdapters'. DIRECTORY_SEPARATOR. $name_array[1]. '.php';
        }else {
            $class_path = __DIR__. DIRECTORY_SEPARATOR. 'lib'. DIRECTORY_SEPARATOR. $name_array[1]. '.php';
        }
        require $class_path;
    }
}

spl_autoload_register('ORMizer_autoload');

?>
