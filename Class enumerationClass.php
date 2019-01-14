<?php

class enumerationClass{

	var $elements = array();

	function __construct(){}
  
	function add($element, $onlyAddIfNotAlreadyContained = false) {

		if(strlen(trim($element)) == 0 || $element == '""' || $element == "''")
			return;

		if($onlyAddIfNotAlreadyContained == false || !in_array($element, $this->elements))
			$this->elements[count($this->elements)] = $element;

	}

	function count() {

		return count($this->elements);

	}
  
	// For backward compatibility
		
	function display($enumerator = ', ', $concatenator = ' and ', $ucfirst = false){

		$string = '';

		for($i=0; $i<count($this->elements); $i++) {

		  $string .= trim($this->elements[$i]) . '@ ';

		}

		if(strstr($string, '@ '))
		  $string = substr($string, 0, -2);
	  
		if(strstr($string, '@ '))
		  $string = substr_replace($string, $concatenator, strrpos($string, '@'), 2);
		
		$string = str_replace('@ ', $enumerator, $string);

		if($ucfirst)
		  echo ucfirst($string);
		else
		  echo $string;    

	}

	function getEnumeration($enumerator = ', ', $concatenator = ' and ', $ucfirst = false) {

		$string = '';
		
		for($i=0; $i<count($this->elements); $i++) {

			$string .= trim($this->elements[$i]) . '@ ';

		}

		if(strstr($string, '@ '))
			$string = substr($string, 0, -2);
		
		if(strstr($string, '@ '))
			$string = substr_replace($string, $concatenator, strrpos($string, '@'), 2);
		
		$string = str_replace('@ ', $enumerator, $string);

		if($ucfirst)
			return ucfirst($string);
		else
			return $string;    

	}

	function getElement($i) {

		$string = '';
		
		$string .= trim($this->elements[$i]);

		if($ucfirst)
			return ucfirst($string);
		else
			return $string;  
		
	}

}

?>