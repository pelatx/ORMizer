<?php

namespace ORMizer;

/**
 * Classe genèrica validar dades.
 *
 * @author Jose A. Martínez
 *
 * Se li passen mínima i màxima llargaria permesa de l'string i
 * els caracters que seran vàlids, a més de l'string de dades mateix.
 */

class Validator {

	private $valid_chars;
	private $min_length;
	private $max_length;

	/**
	 * Constructor de la classe.
	 *
	 * @param int $min_length
	 * @param int $max_length
	 * @param string $valid_chars
	 */
	function __construct($min_length, $max_length, $valid_chars='alphanumeric') {
		$this->min_length = $min_length;
		$this->max_length = $max_length;
		$this->setChars($valid_chars);
	}

	/**
	 * Funció que realitza efectivament la comprovació.
	 * Retorna verdader si l'string compleix totes les condicions
	 * o fals en cas contrari.
	 *
	 * @return true or false
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

	public function setLength($min, $max) {
		$this->min_length = $min;
		$this->max_length = $max;
	}
}
?>
