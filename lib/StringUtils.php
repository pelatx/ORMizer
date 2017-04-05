<?php

namespace ORMizer;

class StringUtils {

    public static function after($substr, $string) {
        if (strpos($string, $substr) === false)
            return null;
        return substr($string, strpos($string,$substr)+strlen($substr));
    }

    public static function before($substr, $string) {
        if (strpos($string, $substr) === false)
            return $string;
        return substr($string, 0, strpos($string, $substr));
    }

    public static function beforeLast($substr, $string) {
        return substr($string, 0, self::strrevpos($string, $substr));
    }

    public static function between($substr1, $substr2, $string) {
        return self::before($substr2, self::after($substr1, $string));
    }

    public static function strrevpos($instr, $needle) {
        $rev_pos = strpos (strrev($instr), strrev($needle));
        if ($rev_pos===false) return false;
        else return strlen($instr) - $rev_pos - strlen($needle);
    }
}
?>
