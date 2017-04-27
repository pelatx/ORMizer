<?php

namespace ORMizer;

/**
 * Generic class to validate data.
 * We can set the max and min string length and the allowed characters.
 */
class Validator {

    private $valid_chars;
    private $min_length;
    private $max_length;

    /**
	 * Class constructor.
	 *
	 * @param int      $min_length
	 * @param int      $max_length
	 * @param string   $valid_chars
	 */
    function __construct($min_length, $max_length, $valid_chars='alphanumeric') {
        $this->min_length = $min_length;
        $this->max_length = $max_length;
        $this->setChars($valid_chars);
    }

    /**
     * Effectively checks the string.
     * @param  string  $data String to be validated.
     * @return boolean True if is valid.
     */
    public function validate($data) {
        //Comprovem que és una cadena
        if(!is_string($data))
            return false;
        //Comprovem que la longitud sigui correcta.
        if(strlen($data) < $this->min_length or strlen($data) > $this->max_length)
            return false;
        //Comprovem que no hi ha caracters invàlids.
        for($i=0;$i<strlen($data);$i++) {
            if(strpos($this->valid_chars,substr($data,$i,1)) === false)
                return false;
        }
        //Si tot ha anat bé.
        return true;
    }

    /**
     * Sets allowed chars to be used in the validation.
     * @param string $valid_chars One of the preconfigured options or a custom set of characters.
     */
    public function setChars($valid_chars) {
        switch($valid_chars) {
            case 'alphanumeric':
                $this->valid_chars = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ0123456789_-';
                break;
            case 'email':
                $this->valid_chars = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ0123456789_-@.';
                break;
            case 'password':
                $this->valid_chars = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ0123456789_-@#$+';
                break;
            case 'numeric':
                $this->valid_chars = '0123456789';
                break;
            default:
                $this->valid_chars = $valid_chars;
        }
    }

    /**
     * Sets the max and min allowed length to be used in the validation.
     * @param [[Type]] $min [[Description]]
     * @param [[Type]] $max [[Description]]
     */
    public function setLength($min, $max) {
        $this->min_length = $min;
        $this->max_length = $max;
    }
}
?>
