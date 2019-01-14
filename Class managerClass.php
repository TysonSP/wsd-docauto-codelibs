<?php

#define('__ERROR_REPORTING', 'NORMAL');
#define('__ERROR_REPORTING', 'BUFFERED');

$errorMessageString = '';
function triggerError($message, $level, $type = 'general') {

	global $errorMessageString;

	switch(__ERROR_REPORTING) {
	
		case 'BUFFERED':
			$errorMessageString .= '<error><type>' . $type . '</type><message>' . $message . '</message></error>';
		break;
		
		case 'NORMAL':
		default:
			trigger_error($message, $level);
		break;
		
	}

}

function flushErrorMessages() {

	global $errorMessageString;

	if(strlen(trim($errorMessageString)) > 0)
		trigger_error('<errors>' . $errorMessageString . '</errors>', E_USER_ERROR);

}

class spellOutClass {

	var $number, $ones, $tens, $triplets;

	function __construct() {
  
  	$this->ones = array(
        "",
        " one",
        " two",
        " three",
        " four",
        " five",
        " six",
        " seven",
        " eight",
        " nine",
        " ten",
        " eleven",
        " twelve",
        " thirteen",
        " fourteen",
        " fifteen",
        " sixteen",
        " seventeen",
        " eighteen",
        " nineteen"
	);
    
	$this->tens = array(
        "",
        "",
        " twenty",
        " thirty",
        " forty",
        " fifty",
        " sixty",
        " seventy",
        " eighty",
        " ninety"
	);

	$this->triplets = array(
        "",
        " thousand",
        " million",
        " billion",
        " trillion",
        " quadrillion",
        " quintillion",
        " sextillion",
        " septillion",
        " octillion",
        " nonillion"
	);

  }

	function spellOutNumber($number, $language) {
	
		$this->number = $number;
		
		switch($language) {
		
			case 'German':
			case 'german':
				return $this->__spellOutGerman();
				
			case 'English':
			case 'english':
				return $this->__spellOutEnglish();
		
			default:
				triggerError('Invalid language specified for spellOutClass (permitted options are English and German)' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		
		}
	
	}
	
	function __spellOutGerman() {

		$zahl = $this->number;
	
		$zahlennamen = array();
		$ziffern = array();
		$zehner = array();
		$max = 0; 

		$zahlennamen =  array (
			1000000000  =>  " Milliarde",
			"e_1000000000"  =>  "n",
			1000000 =>  " Million",
			"e_1000000" =>  "en",
			1000  =>  " Tausend",
			1 =>  "",
		);

		foreach($zahlennamen as $key => $value) {
			if(is_int($key)) {
				$max = ($key > $max) ? $key : $max;
			}
		} 

		$ziffern = array (
			"null", "eins", "zwei", "drei", "vier", "f" . chr(252) . "nf", "sechs", "sieben", "acht", "neun", "zehn", "elf", "zw" . chr(246) . "lf",
		);

		$zehner = array (
			1  =>  "zehn",
			2  =>  "zwanzig",
			3  =>  "drei" . chr(223) . "ig",
			4  =>  "vierzig",
			5  =>  "f" . chr(252) . "nfzig",
			6  =>  "sechzig",
			7  =>  "siebzig",
			8  =>  "achtzig",
			9  =>  "neunzig",
		); 

		if($zahl >= $max * 1000) return false;
			
		if($zahl == 0 || $zahl == 1) return $ziffern[$zahl];
		$string = ($zahl < 0) ? "Minus " : "";
			
		$current = ($zahl < 0) ? $zahl * -1 : $zahl;
		$zahl_in_worten = "";

		$first = true;

		foreach($zahlennamen as $key => $text) {
			if($current >= $key && is_int($key)) {
				$zw = "";
				$bw = "";
					
				if($current >= $key * 2) {
					if($key > 1000) {
						$zw = $text . $zahlennamen["e_{$key}"];
					} else {
						$zw = $text;
					}
				} else {
					$zw = $text ." ";
				}
					
				$block = (int) ($current / $key);
				if($block == 1) {
					if($key > 1000) {
						$bw = "eine";
					} else {
						$bw = "ein";
					}
				} else {
					if ($block >= 100 && $block < 200) {
						$bw = "einhundert";
					} elseif($block >= 200) {
						$bw = $ziffern[substr($block, 0 ,1)] . "hundert";
					}
					$rest = $block % 100;
					if($rest > 0) {
						$bw.= ($block >= 100) ? "und" : ( ($first) ? "" : "und ");
						if($rest <= count($ziffern) - 1) {
							$bw.= $ziffern[$rest];
						} else {
							if(substr($rest, 1, 1) > 0) {
								$bw.= $ziffern[substr($rest, 1, 1)] . ( ( substr($rest, 0, 1) > 1) ? "und" : "" );
							}
							$bw.= $zehner[substr($rest, 0, 1)];
						}
					}
				}
					
				$zahl_in_worten.= "{$bw} {$zw} ";
				$current = substr($current, strlen($block));
				$first = false;
			}
		}
		
		$zahl_in_worten = str_replace("  ", " ", $zahl_in_worten);
		return trim( $string . $zahl_in_worten );
	}


	// recursive fn, converts three digits per pass
	function __convertTri($num, $tri) {

		// chunk the number, ...rxyy
		$r = (int) ($num / 1000);
		$x = ($num / 100) % 10;
		$y = $num % 100;

		// init the output string
		$str = "";

		// do hundreds
		if ($x > 0)
		 $str = $this->ones[$x] . " hundred";

		// do ones and tens
		if ($y < 20)
		 $str .= $this->ones[$y];
		else
		 $str .= $this->tens[(int) ($y / 10)] . $this->ones[$y % 10];

		// add triplet modifier only if there
		// is some output to be modified...
		if ($str != "")
		 $str .= $this->triplets[$tri];

		// continue recursing?
		if ($r > 0)
		 return $this->__convertTri($r, $tri+1).$str;
		else
		 return $str;
	}

	function __spellOutEnglish() {
	
		$num = $this->number;

		$num = (int) $num;    // make sure it's an integer

		if ($num < 0)
		return "negative".$this->__convertTri(-$num, 0);

		if ($num == 0)
		return "zero";

		if($num == $this->number)		
			return trim($this->__convertTri($num, 0));
		else
			return trim($this->__convertTri($num, 0)) . ' and a half';
	}

}

class elementClass {

	const __BOOLEAN = 'BOOLEAN';
	const __DATE = 'DATE';
	const __NUMBER = 'NUMBER';
	const __PERCENTAGE = 'PERCENTAGE';
	const __STRING = 'STRING';
	const __OBJECT = 'OBJECT';

	private $name;
	
	private $type;

	private $value;

	private $numberFormat;

	private $isComplexType = false;
	private $isRange = false;
	private $rangeLowerEndValue;
	private $rangeUpperEndValue;
	private $defaultValue;
	private $defaultFormatString;
	private $defaultLanguage;
	private $defaultSeparator;
	
	function __clone($v) {
		if(is_array($v)) {
			$clone = array();
			foreach($v as $key => $val)
				$clone[$key] = $val;
		} else
			$clone = $v;
		return $clone;
	}
    
	function __construct($name, $type, $value, $parameters = NULL) {

		$this->name = $name;
		$this->type = $type;
		
		switch($this->type) {
		
			case elementClass::__BOOLEAN:
				$this->value = $value;
				break;

			case elementClass::__DATE:
				if(strlen(trim($value)) == 0)
					$this->value = NULL;
				else
					$this->value = round($value / 86400.0) * 86400;
				break;
				
			case elementClass::__NUMBER:
				$v = $this->__clone($value);
				
				if(!is_array($v))
					if(strlen(trim($v)) == 0)
						$this->value = NULL;
					else
						if(is_numeric($v)) {
							$this->value = $v;
							if(array_key_exists('divide', $parameters))
								if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
									$this->value /= $parameters['divide'];
								else
									triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
							if(array_key_exists('multiply', $parameters))
								if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
									$this->value *= $parameters['multiply'];
								else
									triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
							if(array_key_exists('add', $parameters))
								if(is_numeric($parameters['add']) && $parameters['add'] != 0)
									$this->value += $parameters['add'];
								else
									triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
						} else
							triggerError('elementClass: Improperly formatted number passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
						
				if(is_array($v)) {

					// The code probably never goes into this branch - but there's some doc auto versions where it's possible
					
					if(!array_key_exists('Range?', $v) && array_key_exists('Value', $v)) {
						if(strlen(trim($v['Value'])) == 0)
							$this->value = NULL;
						else
							if(is_numeric($v['Value'])) {
								$this->value = $v['Value'];
								if(array_key_exists('divide', $parameters))
									if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
										$this->value /= $parameters['divide'];
									else
										triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('multiply', $parameters))
									if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
										$this->value *= $parameters['multiply'];
									else
										triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('add', $parameters))
									if(is_numeric($parameters['add']) && $parameters['add'] != 0)
										$this->value += $parameters['add'];
									else
										triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
							} else
								triggerError('elementClass: Improperly formatted percentage passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($v['Value'], true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
					} else {				
						$this->isComplexType = true;
					
						// If lower or upper end is provided, we assume this is a range
					
						if($v['Lower end'] != NULL || $v['Upper end'] != NULL)
							 $this->isRange = true;
						else
							 $this->isRange = false;
			
						// But if we have a range parameter and it's set to false, then we assume this isn't a range
			
						if(array_key_exists('Range?', $v) && !$v['Range?']['Yes'])
							$this->isRange = false;

						if($this->isRange) {
							
							if(strlen(trim($v['Lower end'])) == 0)
								$this->rangeLowerEndValue = NULL;
							else {
								$this->rangeLowerEndValue = $v['Lower end'];
								
								if(array_key_exists('divide', $parameters))
									if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
										$this->rangeLowerEndValue /= $parameters['divide'];
									else
										triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('multiply', $parameters))
									if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
										$this->rangeLowerEndValue *= $parameters['multiply'];
									else
										triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('add', $parameters))
									if(is_numeric($parameters['add']) && $parameters['add'] != 0)
										$this->rangeLowerEndValue += $parameters['add'];
									else
										triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

							}
							if(strlen(trim($v['Upper end'])) == 0)
								$this->rangeUpperEndValue = NULL;
							else {
								$this->rangeUpperEndValue = $v['Upper end'];

								if(array_key_exists('divide', $parameters))
									if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
										$this->rangeUpperEndValue /= $parameters['divide'];
									else
										triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);								
								if(array_key_exists('multiply', $parameters))
									if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
										$this->rangeUpperEndValue *= $parameters['multiply'];
									else
										triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);								
								if(array_key_exists('add', $parameters))
									if(is_numeric($parameters['add']) && $parameters['add'] != 0)
										$this->rangeUpperEndValue += $parameters['add'];
									else
										triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);								
							}
							
						} else
							if(strlen(trim($v['Value'])) == 0)
								$this->value = NULL;
							else {
								$this->value = $v['Value'];
								
								if(array_key_exists('divide', $parameters))
									if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
										$this->value /= $parameters['divide'];
									else
										triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('multiply', $parameters))
									if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
										$this->value *= $parameters['multiply'];
									else
										triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('add', $parameters))
									if(is_numeric($parameters['add']) && $parameters['add'] != 0)
										$this->value += $parameters['add'];
									else
										triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
							}
					}
				}
				
				$this->numberFormat = elementClass::__NUMBER;
				break;

			case elementClass::__PERCENTAGE:
				$v = $this->__clone($value);
				
				if(!is_array($v))
					if(strlen(trim($v)) == 0)
						$this->value = NULL;
					else
						if(is_numeric($v)) {
							$this->value = $v / 100.0;

							if(array_key_exists('divide', $parameters))
								if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
									$this->value /= $parameters['divide'];
								else
									triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
							if(array_key_exists('multiply', $parameters))
								if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
									$this->value *= $parameters['multiply'];
								else
									triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
							if(array_key_exists('add', $parameters))
								if(is_numeric($parameters['add']) && $parameters['add'] != 0)
									$this->value += $parameters['add'];
								else
									triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

						} else
							triggerError('elementClass: Improperly formatted percentage passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
						
				if(is_array($v)) {
				
					// The code probably never goes into this branch - but there's some doc auto versions where it's possible
					
					if(!array_key_exists('Range?', $v) && array_key_exists('Value', $v)) {
						if(strlen(trim($v['Value'])) == 0)
							$this->value = NULL;
						else
							if(is_numeric($v['Value'])) {

								$this->value = $v['Value'] / 100.0;
								if(array_key_exists('divide', $parameters))
									if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
										$this->value /= $parameters['divide'];
									else
										triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('multiply', $parameters))
									if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
										$this->value *= $parameters['multiply'];
									else
										triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('add', $parameters))
									if(is_numeric($parameters['add']) && $parameters['add'] != 0)
										$this->value += $parameters['add'];
									else
										triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
							} else
								triggerError('elementClass: Improperly formatted percentage passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($v['Value'], true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
					} else {
						$this->isComplexType = true;
					
						// If lower or upper end is provided, we assume this is a range
					
						if($v['Lower end'] != NULL || $v['Upper end'] != NULL)
							 $this->isRange = true;
						else
							 $this->isRange = false;
			
						// But if we have a range parameter and it's set to false, then we assume this isn't a range
			
						if(array_key_exists('Range?', $v) && !$v['Range?']['Yes'])
							$this->isRange = false;
					
						if($this->isRange) {
							
							if(strlen(trim($v['Lower end'])) == 0)
								$this->rangeLowerEndValue = NULL;
							else {
								$this->rangeLowerEndValue = $v['Lower end'] / 100.0;
								
								if(array_key_exists('divide', $parameters))
									if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
										$this->rangeLowerEndValue /= $parameters['divide'];
									else
										triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('multiply', $parameters))
									if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
										$this->rangeLowerEndValue *= $parameters['multiply'];
									else
										triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('add', $parameters))
									if(is_numeric($parameters['add']) && $parameters['add'] != 0)
										$this->rangeLowerEndValue += $parameters['add'];
									else
										triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
							}
							
							if(strlen(trim($v['Upper end'])) == 0)
								$this->rangeUpperEndValue = NULL;
							else {
								$this->rangeUpperEndValue = $v['Upper end'] / 100.0;

								if(array_key_exists('divide', $parameters))
									if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
										$this->rangeUpperEndValue /= $parameters['divide'];
									else
										triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('multiply', $parameters))
									if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
										$this->rangeUpperEndValue *= $parameters['multiply'];
									else
										triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('add', $parameters))
									if(is_numeric($parameters['add']) && $parameters['add'] != 0)
										$this->rangeUpperEndValue += $parameters['add'];
									else
										triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								
							}
							
						} else
							if(strlen(trim($v['Value'])) == 0)
								$this->value = NULL;
							else {
								$this->value = $v['Value'] / 100.0;

								if(array_key_exists('divide', $parameters))
									if(is_numeric($parameters['divide']) && $parameters['divide'] != 0)
										$this->value /= $parameters['divide'];
									else
										triggerError('elementClass: Invalid divide parameter (' . $parameters['divide'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('multiply', $parameters))
									if(is_numeric($parameters['multiply']) && $parameters['multiply'] != 0)
										$this->value *= $parameters['multiply'];
									else
										triggerError('elementClass: Invalid multiply parameter (' . $parameters['multiply'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
								if(array_key_exists('add', $parameters))
									if(is_numeric($parameters['add']) && $parameters['add'] != 0)
										$this->value += $parameters['add'];
									else
										triggerError('elementClass: Invalid add parameter (' . $parameters['add'] . ') passed to elementClass::__construct() - element name: ' . $name . ' / element value: ' . print_r($value, true) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

							}
					}
				}
				$this->numberFormat = elementClass::__PERCENTAGE;
				break;

			case elementClass::__STRING:
				$this->value = $value;
				break;
				
			case elementClass::__OBJECT:
				$this->value = $value;
				break;

		}
				
		$this->defaultValue = NULL;
		$this->defaultFormat = NULL;
		$this->defaultPrelimFormat = NULL;
		$this->defaultSeparator = '-';
		$this->defaultLanguage = 'en';
		
		return $this->name;
	
	}
	
	
	
	function validate($permittedValues) {
	
		if(!is_array($permittedValues))
			triggerError('managerClass: Second argument passed to managerClass::validate not an array' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		$valid = false;
	
		foreach($permittedValues as $permittedValue) {
		
			if($this->value == $permittedValue)
				$valid = true;
		
		}
	
		if($valid || $this->value == NULL)
			return true;
		else
			return false;
	
	}
	
	function setDefaultValue($defaultValue) {
	
		$this->defaultValue = $defaultValue;
	
	}
	
	function setDefaultRangeSeparator($defaultSeparator) {
	
		$this->defaultSeparator = $defaultSeparator;
	
	}
	
	function setDefaultFormat($defaultFormat) {

		$this->defaultFormat = $defaultFormat;
	
	}

	function setDefaultLanguage($defaultLanguage) {

		$this->defaultLanguage = $defaultLanguage;
	
	}
	
	function setDefaultPrelimFormat($defaultPrelimFormat) {
	
		$this->defaultPrelimFormat = $defaultPrelimFormat;
	
	}

	function getElementType() {
	
		return $this->type;
	
	}
	
	function __convertNumberFromAbsoluteToPercentage($number) {

		return $number;
	
	}
	
	function __convertNumberFromPercentageToAbsolute($number) {

		return $number;
	
	}
	
	function superscriptOrdinals($string) {

		$string = str_replace('1st', wsd_raw_rtf('1\super st\nosupersub'), $string);
		$string = str_replace('nd', wsd_raw_rtf('\super nd\nosupersub'), $string);
		$string = str_replace('rd', wsd_raw_rtf('\super rd\nosupersub'), $string);
		$string = str_replace('th', wsd_raw_rtf('\super th\nosupersub'), $string);

		return $string;
		
	}

	function __superscript_rtmsm($string) {

		$string = str_replace('(TM)', wsd_raw_rtf('\super TM \nosupersub'), $string);

		if(substr($string , strlen($string) - 2, 2) == 'TM')
			$string = str_replace('TM', wsd_raw_rtf('\super TM \nosupersub'), $string);

		$string = str_replace('(SM)', wsd_raw_rtf('\super SM \nosupersub'), $string);

		if(substr($string , strlen($string) - 2, 2) == 'SM')
			$string = str_replace('SM', wsd_raw_rtf('\super SM \nosupersub'), $string);

		$string = str_replace('(R)', wsd_raw_rtf('\super '. chr(92) . chr(39) . 'ae \nosupersub'), $string);
		$string = str_replace(chr(174), wsd_raw_rtf('\super '. chr(92) . chr(39) . 'ae \nosupersub'), $string);

		return $string;

	}	
	
	function __getRawValue($value) {

		return $value;

	}
		
	function __translateDate($date, $language) {

		switch($language) {
		
			case 'de':
			break;
			
			case 'en':
			break;
			
			case 'frLong':
				$date = str_replace('January', 'janvier', $date);
				$date = str_replace('February', 'février', $date);
				$date = str_replace('March', 'mars', $date);
				$date = str_replace('April', 'avril', $date);
				$date = str_replace('May', 'mai', $date);
				$date = str_replace('June', 'juin', $date);
				$date = str_replace('July', 'juillet', $date);
				$date = str_replace('August', 'août', $date);
				$date = str_replace('September', 'septembre', $date);	
				$date = str_replace('October', 'octobre', $date);
				$date = str_replace('November', 'novembre', $date);
				$date = str_replace('December', 'décembre', $date);
			break;
			
			case 'frShort':
			case 'fr':
				$date = str_replace('January', 'janv.', $date);
				$date = str_replace('February', 'févr.', $date);
				$date = str_replace('March', 'mars', $date);
				$date = str_replace('April', 'avril', $date);
				$date = str_replace('May', 'mai', $date);
				$date = str_replace('June', 'juin', $date);
				$date = str_replace('July', 'juil.', $date);
				$date = str_replace('August', 'août', $date);
				$date = str_replace('September', 'sept.', $date);	
				$date = str_replace('October', 'oct.', $date);
				$date = str_replace('November', 'nov.', $date);
				$date = str_replace('December', 'déc.', $date);				
			break;
			
			default:
				triggerError('elementClass: Invalid or missing language (' . print_r($language, true) . ') in call to elementClass::translateDate' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		
		}
	
		return $date;

	}
	
	function __getValue($documentType, $value, $options = NULL) {

		if($options != NULL && !is_array($options))
			triggerError('Invalid options passed to elementClass::getValue' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		if($options != NULL && array_key_exists('formatString', $options) && $options['formatString'][0] != NULL)
			$formatString = $options['formatString'];
		else
			$formatString = NULL;
						
		if($options != NULL && array_key_exists('language', $options) && $options['language'] != NULL)
			$language = $options['language'];
		else
			$language = $this->defaultLanguage;

		if($options != NULL && array_key_exists('displayNumberFormat', $options))
			$displayNumberFormat = $options['displayNumberFormat'];
		else
			$displayNumberFormat = $this->numberFormat;

		if($options != NULL && array_key_exists('percentagePrefix', $options))
			$percentagePrefix = $options['percentagePrefix'];
		else
			$percentagePrefix = '';
			
		if($options != NULL && array_key_exists('fallbackFormatString', $options))
			$fallbackFormatString = $options['fallbackFormatString'];
		else
			$fallbackFormatString = NULL;
			
		if($options != NULL && array_key_exists('fallbackValue', $options))
			$fallbackValue = $options['fallbackValue'];
		else
			$fallbackValue = NULL;
			
		if($formatString != NULL && !is_array($formatString))
			triggerError('elementClass: Invalid formatString specified in call to elementClass:getValue' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		switch($this->type) {
		
			case elementClass::__BOOLEAN:
				return $value;
			
			case elementClass::__DATE:
				if(strlen(trim($value)) == 0 || $value == NULL)
					if($this->defaultValue != NULL)
						return $this->defaultValue;
					else
						return $fallbackValue;
				else
					if($formatString != NULL)
						if($options['superscriptOrdinals'])
							return $this->__translateDate($this->superscriptOrdinals(date($formatString[0], $value)), $language);
						else
							return $this->__translateDate(date($formatString[0], $value), $language);
					else
						if($this->defaultFormat != NULL)
							if($options['superscriptOrdinals'])
								return $this->__translateDate($this->superscriptOrdinals(date($documentType == 'Final' || $this->defaultPrelimFormat == NULL ? $this->defaultFormat[0] : $this->defaultPrelimFormat[0], $value)), $language);
							else
								return $this->__translateDate(date($documentType == 'Final' || $this->defaultPrelimFormat == NULL ? $this->defaultFormat[0] : $this->defaultPrelimFormat[0], $value), $language);
						else
							if($fallbackFormatString != NULL)
								if($options['superscriptOrdinals'])
									return $this->__translateDate($this->superscriptOrdinals(date($fallbackFormatString[0], $value)), $language);
								else
									return $this->__translateDate(date($fallbackFormatString[0], $value), $language);
							else
								triggerError('elementClass: No formatString specified in call to elementClass::getValue and no default date formatString available' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
				break;
		
			case elementClass::__NUMBER:
			case elementClass::__PERCENTAGE:
				if($value === NULL)
					if($this->defaultValue !== NULL)
						return $this->defaultValue . ($this->type == elementClass::__PERCENTAGE ? $percentagePrefix . '%' : '');
					else
						return $fallbackValue . ($this->type == elementClass::__PERCENTAGE ? $percentagePrefix . '%' : '');
				else
					if($formatString != NULL) {
						if(substr($formatString[0], -3) == '.##')
							if(round($this->__getRawValue($value), 5) != round($this->__getRawValue($value)))
								$formatString[0] = substr($formatString[0], 0, -3) . '.00';
						if(substr($formatString[0], -4) == '.###')
							if(round($this->__getRawValue($value), 5) != round($this->__getRawValue($value)))
								$formatString[0] = substr($formatString[0], 0, -4) . '.00';
						if(is_array($formatString))
							return wsd_decimal_format(round(($this->type == elementClass::__PERCENTAGE && substr($formatString[0], -1) == '!' ? $this->__getRawValue($value) * 100.0 : $this->__getRawValue($value)), 7),  ($this->type == elementClass::__PERCENTAGE && substr($formatString[0], -1) != '%' && substr($formatString[0], -1) != '!' ? $formatString[0] . $percentagePrefix . '%' : str_replace('!', '', $formatString[0])), $formatString[1]);
						else
							return wsd_decimal_format(round(($this->type == elementClass::__PERCENTAGE && substr($formatString[0], -1) == '!' ? $this->__getRawValue($value) * 100.0 : $this->__getRawValue($value)), 7), ($this->type == elementClass::__PERCENTAGE && substr($formatString[0], -1) != '%' && substr($formatString[0], -1) != '!' ? $formatString[0] . $percentagePrefix . '%' : str_replace('!', '', $formatString[0])));
					} else
						if($this->defaultFormat != NULL)
							if(is_array($this->defaultFormat))
								return wsd_decimal_format(round($this->__getRawValue($value), 7), $documentType == 'Final' || $this->defaultPrelimFormat == NULL ? $this->defaultFormat[0] . ($this->type == elementClass::__PERCENTAGE && substr($this->defaultFormat[0], -1) != '%' ? $percentagePrefix . '%' : '') : $this->defaultPrelimFormat[0] . ($this->type == elementClass::__PERCENTAGE && substr($this->defaultPrelimFormat[0], -1) != '%' ? $percentagePrefix . '%' : ''), $documentType == 'Final' || $this->defaultPrelimFormat == NULL ? $this->defaultFormat[1] : $this->defaultPrelimFormat[1]);
							else
								return wsd_decimal_format(round($this->__getRawValue($value), 7), $this->defaultFormat[0] . ($this->type == elementClass::__PERCENTAGE && substr($this->defaultFormat[0], -1) != '%' ? $percentagePrefix . '%' : ''));
						else
							if($fallbackFormatString != NULL)
								if(is_array($fallbackFormatString))
									return wsd_decimal_format(round($this->__getRawValue($value), 7), $fallbackFormatString[0] . ($this->type == elementClass::__PERCENTAGE && substr($fallbackFormatString[0], -1) != '%' ? $percentagePrefix . '%' : ''), $fallbackFormatString[1]);
								else
									return wsd_decimal_format(round($this->__getRawValue($value), 7), $fallbackFormatString[0]. ($this->type == elementClass::__PERCENTAGE && substr($fallbackFormatString[0], -1) != '%' ? $percentagePrefix . '%' : ''));
							else
								triggerError('elementClass: No formatString specified in call to elementClass::getValue and no default number formatString available' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
				break;

			case elementClass::__STRING:
				if(strlen(trim($value)) == 0 || $value == NULL)
					if($this->defaultValue != NULL)
						return $this->defaultValue;
					else
						return $fallbackValue;
				else
					return $this->__superscript_rtmsm($value);
				break;

			case elementClass::__OBJECT:
				return $value;
				
		}
	
	}
	
	function __getSpelledOutValue($value, $displayNumberFormat) {
	
		$spellOut = new spellOutClass();
	
		switch($displayNumberFormat) {
		
			case elementClass::__NUMBER:
				switch($this->numberFormat) {
				
					case elementClass::__NUMBER:
						$spelledOutNumber = $spellOut->spellOutNumber($value, 'english');
						
					case elementClass::__PERCENTAGE:
						$spelledOutNumber = $spellOut->spellOutNumber($this->__convertNumberFromPercentageToAbsolute($value), 'english');
				
				}
				break;
				
			case elementClass::__PERCENTAGE:
				switch($this->numberFormat) {
				
					case elementClass::__NUMBER:
						$spelledOutNumber = $spellOut->spellOutNumber($this->__convertNumberFromAbsoluteToPercentage($value), 'english');
						
					case elementClass::__PERCENTAGE:
						$spelledOutNumber = $spellOut->spellOutNumber($value, 'english');
				
				}
			
			default:
				$spelledOutNumber = $spellOut->spellOutNumber($value, 'english');

		}

		return $spelledOutNumber;
		
	}

	function getRawValue($displayNumberFormat) {
	
		switch($this->type) {
		
			case elementClass::__BOOLEAN:
				return $this->__getRawValue($this->value);

			case elementClass::__DATE:
				return $this->__getRawValue($this->value);

			case elementClass::__NUMBER:
			case elementClass::__PERCENTAGE:
			
				if(!$this->isComplexType)
				
					return $this->__getRawValue($this->value);
					
				else {
				
					if(!$this->isRange) {
					
						return $this->__getRawValue($this->value);
					
					} else {
					
						return $this->__getRawValue($this->rangeLowerEndValue) . ' - ' . $this->__getRawValue($this->rangeUpperEndValue);
			
					}
				
				}
				
			case elementClass::__STRING:
				return $this->__getRawValue($this->value);
		
			case elementClass::__OBJECT:
				return $this->value;

		}
			
	}

	function getValue($documentType, $options = NULL) {
	
		if($options != NULL && array_key_exists('displayBrackets', $options))
			$displayBrackets = $options['displayBrackets'];
		else
			$displayBrackets = FALSE;
			
		if($options != NULL && array_key_exists('rangeSeparator', $options))
			$rangeSeparator = $options['rangeSeparator'];
		else
			$rangeSeparator = $this->defaultSeparator;
						
		if($options != NULL && array_key_exists('displayBracketsForEachRangeValueIndividually', $options))
			$displayBracketsForEachRangeValueIndividually = $options['displayBracketsForEachRangeValueIndividually'];
		else
			$displayBracketsForEachRangeValueIndividually = true;
	
		switch($this->type) {
		
			case elementClass::__BOOLEAN:
				return $this->__getValue($documentType, $this->value, $options);

			case elementClass::__DATE:
				return (($displayBrackets) ? '[' : '') . $this->__getValue($documentType, $this->value, $options) . (($displayBrackets) ? ']' : '');

			case elementClass::__NUMBER:
			case elementClass::__PERCENTAGE:
			
				if(!$this->isComplexType)
				
					return str_replace($percentagePrefix . '%]', ']' . $percentagePrefix . '%', ($displayBrackets ? '[' : '') . $this->__getValue($documentType, $this->value, $options) . ($displayBrackets ? ']' : ''));
					
				else {
				
					if(!$this->isRange) {
					
						return str_replace($percentagePrefix . '%]', ']' . $percentagePrefix . '%', ($displayBrackets ? '[' : '') . $this->__getValue($documentType, $this->value, $options) . ($displayBrackets ? ']' : ''));
					
					} else {

						if($this->rangeUpperEndValue != NULL)
							
							switch($displayBracketsForEachRangeValueIndividually) {
							
								case true:
									return str_replace($percentagePrefix . '%]', ']' . $percentagePrefix . '%', ($displayBrackets ? '[' : '') . $this->__getValue($documentType, $this->rangeLowerEndValue, $options) . ($displayBrackets ? ']' : '') . ' ' . $rangeSeparator . ' ' . ($displayBrackets ? '[' : '') . $this->__getValue($documentType, $this->rangeUpperEndValue, $options) . ($displayBrackets ? ']' : ''));
								break;
								
								case false:
									return ($displayBrackets ? '[' : '') . $this->__getValue($documentType, $this->rangeLowerEndValue, $options) . ' ' . $rangeSeparator . ' ' . $this->__getValue($documentType, $this->rangeUpperEndValue, $options) . ($displayBrackets ? ']' : '');
								break;
							
							}
							
						else
						
							return str_replace($percentagePrefix . '%]', ']' . $percentagePrefix . '%', 'at least ' . ($displayBrackets ? '[' : '') . $this->__getValue($documentType, $this->rangeLowerEndValue, $options) . ($displayBrackets ? ']' : ''));
									
					}
				
				}
				
			case elementClass::__STRING:
				return $this->__getValue($documentType, $this->value, $options);
		
			case elementClass::__OBJECT:
				return $this->__getValue($documentType, $this->value, $options);
				
		}
	
	}

	function isRange() {

		return $this->isRange;
	
	}
	
	function getRangeLowerEndRawValue() {
	
		if(!$this->isComplexType)
			triggerError('elementClass: elementClass::getRangeLowerEndRawValue called on non-complex field' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		if(!$this->isRange)
			triggerError('elementClass: elementClass::getRangeLowerEndRawValue called on non-range' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		
		return $this->__getRawValue($this->rangeLowerEndValue);
	
	}

	function getRangeLowerEndValue($documentType, $options) {
	
		if(!$this->isComplexType)
			triggerError('elementClass: elementClass::getRangeLowerEndValue called on non-complex field' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		if(!$this->isRange)
			triggerError('elementClass: elementClass::getRangeLowerEndValue called on non-range' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		
		return $this->__getValue($documentType, $this->rangeLowerEndValue, $options);
	
	}
	
	function getRangeUpperEndValue($documentType, $options) {
	
		if(!$this->isComplexType)
			triggerError('elementClass: elementClass::getRangeUpperEndValue called on non-complex field' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		if(!$this->isRange)
			triggerError('elementClass: elementClass::getRangeUpperEndValue called on non-range' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		
		return $this->__getValue($documentType, $this->rangeUpperEndValue, $options);
	
	}
	
	function getRangeUpperEndRawValue() {
	
		if(!$this->isComplexType)
			triggerError('elementClass: elementClass::getRangeUpperEndRawValue called on non-complex field' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		if(!$this->isRange)
			triggerError('elementClass: elementClass::getRangeUpperEndRawValue called on non-range' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		
		return $this->__getRawValue($this->rangeUpperEndValue);
	
	}

	function getSpelledOutValue($displayNumberFormat) {
	
		switch($this->type) {
		
			case elementClass::__NUMBER:
			case elementClass::__PERCENTAGE:
			
				if(!$this->isComplexType)
				
					return $this->__getSpelledOutValue($this->value, $displayNumberFormat);
					
				else {
				
					if(!$this->isRange) {
					
						return $this->__getSpelledOutValue($this->value, $displayNumberFormat);
					
					} else {
					
						return $this->__getSpelledOutValue($this->rangeLowerEndValue, $displayNumberFormat) . ' - ' . $this->__getSpelledOutValue($this->rangeUpperEndValue, $displayNumberFormat);
			
					}
				
				}
				
			default:
				triggerError('elementClass: Call to elementClass:getSpelledOutValue on non-number' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		
		}
			
	}

}

class managerClass {

	const __PRELIM = 'Prelim';
	const __FINAL = 'Final';

	const __BOOLEAN = elementClass::__BOOLEAN;
	const __DATE = elementClass::__DATE;
	const __NUMBER = elementClass::__NUMBER;
	const __PERCENTAGE = elementClass::__PERCENTAGE;
	const __STRING = elementClass::__STRING;
	const __OBJECT = elementClass::__OBJECT;

	private $elements;

	private $documentType;
	
	private $developmentMode;
	private $defaultValue;
	private $defaultDateFormat;
	private $defaultPrelimDateFormat;
	private $defaultNumberFormat;
	private $defaultPrelimNumberFormat;
	private $defaultRangeSeparator;
	private $displayBracketsForRanges;
	private $displayBracketsForEachRangeValueIndividually;
	private $percentagePrefix = NULL;
	private $defaultLanguage = 'en';
	
	private $rtfClassIsAvailable;
	private $rtf;
	
	function __construct($rtf = NULL) {	
	
		$this->elements = array();
	
		$this->documentType = 'Final';
		$this->defaultValue = '[INSERT]';
		$this->defaultDateFormat = array('F j, Y');
		$this->defaultNumberFormat = array(',##0.00###', NULL);
		$this->defaultPrelimDateFormat = array('F [j], Y');
		$this->defaultPrelimNumberFormat = array(',##0.00###', NULL);
		$this->defaultRangeSeparator = '-';
		$this->displayBracketsForRanges = false;
		$this->displayBracketsForEachRangeValueIndividually = true;
		
		$this->rtfClassIsAvailable = false;

		if($rtf != NULL)
			$this->setRTFClass($rtf);
		
	}
	
	function setRTFClass($rtf) {
	
		$this->rtfClassIsAvailable = true;
		$this->rtf = $rtf;
		
	
	}
	
	function setDocumentType($documentType) {
	
		switch($documentType) {
		
			case managerClass::__PRELIM:
				$this->documentType = 'Prelim';
				break;
				
			case managerClass::__FINAL:
				$this->documentType = 'Final';
				break;
				
			default:
				triggerError('managerClass: Invalid document type provided in call to managerClass::setDocumentType' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		}
	
	}
	
	
	function developmentMode($developmentMode = true) {
		$this->developmentMode = $developmentMode;
	}

	function setDefaultValue($defaultValue) {
	
		if($this->rtfClassIsAvailable)
			$this->defaultValue = $this->rtf->highlight($defaultValue, 'yellow');
		else
			$this->defaultValue = $defaultValue;
		
	}
	
	function setDefaultRangeSeparator($defaultRangeSeparator) {
	
		$this->defaultRangeSeparator = $defaultRangeSeparator;
		
	}
	
	function setDefaultDateFormat($defaultDateFormat) {
	
		$this->defaultDateFormat = array($defaultDateFormat);
		
	}

	function setDefaultLanguage($defaultLanguage) {
	
		$this->defaultLanguage = $defaultLanguage;
		
	}
	
	function setDefaultPrelimDateFormat($defaultPrelimDateFormat) {
	
		$this->defaultPrelimDateFormat = array($defaultPrelimDateFormat);
		
	}
	
	function setDefaultNumberFormat($defaultNumberFormat, $region = NULL) {
	
		$this->defaultNumberFormat = array($defaultNumberFormat, $region);
	
	}

	function setDefaultPrelimNumberFormat($defaultNumberFormat, $region = NULL) {
	
		$this->defaultPrelimNumberFormat = array($defaultNumberFormat, $region);
	
	}
	
	function setDefaultPrelimNumberFormat($defaultPrelimNumberFormat, $region = NULL) {
	
		$this->defaultPrelimNumberFormat = array($defaultPrelimNumberFormat, $region);
	
	}

	function setPercentagePrefix($percentagePrefix) {
	
		$this->percentagePrefix = $percentagePrefix;
	
	}
	
	function __addElement($elementName, $elementType, $elementValue, $parameters = NULL) {
	
		$this->elementName = $elementName;
		
		switch($elementType) {
		
			case elementClass::__BOOLEAN:
			case elementClass::__DATE:
			case elementClass::__NUMBER:
			case elementClass::__PERCENTAGE:
			case elementClass::__STRING:
			case elementClass::__OBJECT:
				break;
				
			default:
				triggerError('managerClass: No or invalid type provided in call to managerClass:__addElement - elementName: "' . $elementName . '" / elementType: "' . $elementType . '" / elementValue: "' . $elementValue . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		
		}
		
		$this->elements[$this->elementName] = new elementClass($elementName, $elementType, $elementValue, $parameters);
		
	}
	
	function addElement($elementName, $elementType, $elementValue, $parameters = NULL) {
	
		if(strlen(trim($elementName)) == 0)
			triggerError('managerClass: No name provided in call to managerClass::addElement' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		if(array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Multiple addition of element of same name not permitted, elementname: ' . $elementName . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		$this->__addElement($elementName, $elementType, $elementValue, $parameters);
	
	}

	function addAndReplaceElement($elementName, $elementType, $elementValue) {
	
		if(strlen(trim($elementName)) == 0)
			triggerError('managerClass: No name provided in call to managerClass::addAndReplaceElement' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		if(array_key_exists($elementName, $this->elements))
			unset($this->elements[$elementName]);
			
		$this->addElement($elementName, $elementType, $elementValue);
	
	}

	function replaceElement($elementName, $elementType, $elementValue) {
	
		$this->addAndReplaceElement($elementName, $elementType, $elementValue);
	
	}

	function validateElement($elementName, $permittedValues, $throwErrorIfValidationFails = true) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:validateElement refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		if($this->elements[$elementName]->validate($permittedValues))
			return true;
		else
			if($throwErrorIfValidationFails)
				triggerError('managerClass: Element "' . $elementName . '" failed validation - permitted values: ' . implode(', ', $permittedValues) . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			else
				return false;
	
	}

	function validate($value, $permittedValues) {
	
		if(!is_array($permittedValues))
			triggerError('managerClass: Second argument passed to managerClass::validate not an array' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		$valid = false;
	
		foreach($permittedValues as $permittedValue) {
		
			if($value == $permittedValue)
				$valid = true;
		
		}
	
		if($valid || $this->value == NULL)
			return true;
		else
			return false;
	
	}

	function setElementDefaultValue($elementName, $defaultValue) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:setElementDefaultValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		$this->elements[$elementName]->setDefaultValue($defaultValue);
		
	}
	
	function setElementDefaultFormat($elementName, $defaultFormatStringString, $defaultFormatStringModifier = NULL) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:setElementDefaultFormat refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		$defaultFormatString = array($defaultFormatStringString, $defaultFormatStringModifier);
	
		$this->elements[$elementName]->setDefaultFormat($defaultFormatString);
		
	}
	
	function setElementDefaultLanguage($elementName, $defaultLanguage) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:setElementDefaultLanguage refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
				
		$this->elements[$elementName]->setDefaultLanguage($defaultLanguage);
		
	}
		
	function setElementDefaultPrelimFormat($elementName, $defaultPrelimFormatString, $defaultPrelimFormatStringModifier = NULL) {

		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:setElementDefaultPrelimFormat refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		$defaultPrelimFormat = array($defaultPrelimFormatString, $defaultPrelimFormatStringModifier);

		$this->elements[$elementName]->setDefaultPrelimFormat($defaultPrelimFormat);
		
	}
	
	function setValueForDisplayBracketsForRanges($option) {
	
		$this->displayBracketsForRanges = $option;
	
	}
	
	function setValueForDisplayBracketsForEachRangeValueIndividually($option) {
	
		$this->displayBracketsForEachRangeValueIndividually = $option;
	
	}
	
	function __getElementValue($elementClassMethodName, $elementName, $options = NULL) {
	
		if($options != NULL && !is_array($options))
			triggerError('Invalid options passed to elementClass::getElementValue' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		if($options != NULL && array_key_exists('formatString', $options))
			$formatString = $options['formatString'];
		else
			$formatString = NULL;
			
		if($options != NULL && array_key_exists('language', $options))
			$language = $options['language'];
		else
			$language = $this->defaultLanguage;

		if($this->isElementRange($elementName) && $this->displayBracketsForRanges)
			$displayBrackets = true;
		else
			$displayBrackets = false;
			
		if($options != NULL && array_key_exists('displayBrackets', $options))
			$displayBrackets = $options['displayBrackets'];
			
		if($this->isElementRange($elementName) && $this->displayBracketsForEachRangeValueIndividually)
			$displayBracketsForEachRangeValueIndividually = true;
		else
			$displayBracketsForEachRangeValueIndividually = false;
		
		if($this->isElementRange($elementName) && $options != NULL && array_key_exists('displayBracketsForEachRangeValueIndividually', $options))
			$displayBracketsForEachRangeValueIndividually = $options['displayBracketsForEachRangeValueIndividually'];
			
		if($options != NULL && array_key_exists('rangeSeparator', $options))
			$rangeSeparator = $options['rangeSeparator'];
		else
			$rangeSeparator = $this->defaultRangeSeparator;
			
		if($options != NULL && array_key_exists('formatStringModifier', $options))
			$formatStringModifier = $options['formatStringModifier'];
		else
			$formatStringModifier = NULL;

		$format = array($formatString, $formatStringModifier);
	
		if($options != NULL && array_key_exists('displayNumberFormat', $options))
			$displayNumberFormat = $options['displayNumberFormat'];
		else
			$displayNumberFormat = NULL;
			
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		switch($this->elements[$elementName]->getElementType()) {
		
			case elementClass::__BOOLEAN:
				return $this->elements[$elementName]->{$elementClassMethodName}($this->documentType, NULL);
			
			case elementClass::__DATE:
				return $this->elements[$elementName]->{$elementClassMethodName}($this->documentType, array(
					'formatString' => array($formatString, $formatStringModifier),
					'fallbackFormatString' => $this->documentType == 'Final' || $this->defaultPrelimDateFormat == NULL ? $this->defaultDateFormat : $this->defaultPrelimDateFormat,
					'fallbackValue' => $this->defaultValue,
					'language' => $language,
					'superscriptOrdinals' => $options['superscriptOrdinals'] ? true : false));
				
			case elementClass::__NUMBER:
			case elementClass::__PERCENTAGE:
				return $this->elements[$elementName]->{$elementClassMethodName}($this->documentType, array(
					'formatString' => array($formatString, $formatStringModifier),
					'fallbackFormatString' => $this->documentType == 'Final' || $this->defaultPrelimNumberFormat == NULL  ? $this->defaultNumberFormat : $this->defaultPrelimNumberFormat,
					'percentagePrefix' => $this->percentagePrefix,
					'fallbackValue' => $this->defaultValue,
					'displayNumberFormat' => $displayNumberFormat,
					'displayBrackets' => $displayBrackets,
					'rangeSeparator' => $rangeSeparator,
					'displayBracketsForEachRangeValueIndividually' => $displayBracketsForEachRangeValueIndividually));

			case elementClass::__STRING:
				return $this->elements[$elementName]->{$elementClassMethodName}($this->documentType, array(
					'fallbackValue' => $this->defaultValue));

			case elementClass::__OBJECT:
				return $this->elements[$elementName]->{$elementClassMethodName}($this->documentType);

		}
		
	}
	
	function isElementRange($elementName, $ignoreIfElementDoesntExist = false) {

		if($ignoreIfElementDoesntExist && !$this->doesElementExist($elementName))
			return false;
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:isElementRange refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		try {
			
			return $this->elements[$elementName]->isRange();
		
		} catch (Exception $e) {
			
			trigger_error('Attempt to access non-existing element: "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		}
	
	}
	
	function getElementDefaultFormat($elementName) {
	
	
		return $this->elements[$elementName]->defaultFormat[0];
	
	}
	
	function getElementType($elementName) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementType refers to non-existing element "' . $elementName . '"' . print_r(debug_backtrace(), true), E_USER_ERROR);
	
		switch($this->elements[$elementName]->getElementType()) {
	
			case elementClass::__BOOLEAN:
				return '__BOOLEAN';
			
			case elementClass::__DATE:
				return '__DATE';
				
			case elementClass::__NUMBER:
				return '__NUMBER';
				
			case elementClass::__PERCENTAGE:
				return '__PERCENTAGE';
				
			case elementClass::__STRING:
				return '__STRING';

			case elementClass::__OBJECT:
				return '__OBJECT';
				
			default:
				triggerError('managerClass: Unknown element type in managerClass:getElementType', E_USER_ERROR);
				
		}
	
	}
	
	function getElementRawValue($elementName, $displayFormat = NULL) {

		if(!$this->doesElementExist($elementName))
			return false;
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementRawValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		return $this->elements[$elementName]->getRawValue($displayFormat);
	
	}

	function getElementValue($elementName, $options = NULL) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		return $this->__getElementValue('getValue', $elementName, $options);
	
	}
	
	function getElement($elementName, $options = NULL) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElement refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
		
		if(strstr($elementName,'terminology')){		
			
			if($this->developmentMode){
				return $this->getElementValue($elementName, $options) . ' [' . debug_backtrace()[0]['line'] . ']';
			}else{
				return $this->getElementValue($elementName, $options);
			}
		}else{
			return $this->getElementValue($elementName, $options);
		}
	
	}

	function getElementRangeMidpointRawValue($elementName, $displayFormat = NULL) {
	
		if(!$this->doesElementExist($elementName))
			return false;
		
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElement refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		if($this->isElementRange($elementName))
			if($this->getElementRangeUpperEndRawValue($elementName) === NULL)
				return round($this->getElementRangeLowerEndRawValue($elementName), 7);				
			else
				return round(($this->getElementRangeLowerEndRawValue($elementName) + $this->getElementRangeUpperEndRawValue($elementName)) / 2.0, 7);
		else
			return $this->elements[$elementName]->getRawValue($displayFormat);

	}

	function getElementMidpointValue($elementName, $options = NULL) {

		return $this->getElementRangeMidpointValue($elementName, $options);

	}
	
	function getElementRangeMidpointValue($elementName, $options = NULL) {
	
		if(!$this->doesElementExist($elementName))
			return false;
		
		if($options === NULL)
			$options = array();
		if(!array_key_exists('formatString', $options)) {
			$tmp_options = array('formatString'=>($this->getElementDefaultFormat($elementName) == NULL?(($this->documentType == 'Final' || $this->defaultPrelimNumberFormat == NULL ) ? $this->defaultNumberFormat[0] : $this->defaultPrelimNumberFormat[0]):$this->getElementDefaultFormat($elementName)) . ($this->getElementType($elementName) =='__PERCENTAGE'?$percentagePrefix . '%':''));
			$options = array_merge($options, $tmp_options);
		}

		if($this->isElementRange($elementName))
			if($this->getElementRangeUpperEndRawValue($elementName) === NULL)
				return $this->displayNumber($this->getElementRangeLowerEndRawValue($elementName), $options);
			else
				return $this->displayNumber(($this->getElementRangeLowerEndRawValue($elementName) + $this->getElementRangeUpperEndRawValue($elementName)) / 2.0, $options);
		else
			return $this->getElementValue($elementName, $options);
			
	}

	function getElementRawMidpointValue($elementName, $displayFormat = NULL) {
	
		return $this->getElementRangeMidpointRawValue($elementName, $displayFormat);
	
	}
	
	function checkElement($elementName) {
	
		if(!array_key_exists($elementName, $this->elements))
			return false;
			
		return true;
	
	}

	function getElementRangeLowerEndRawValue($elementName, $displayFormat = NULL) {
	
		if(!$this->doesElementExist($elementName))
			return false;
		
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementRangeLowerEndRawValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		if($this->isElementRange($elementName))
			return $this->elements[$elementName]->getRangeLowerEndRawValue($displayFormat);
		else
			return $this->elements[$elementName]->getRawValue($displayFormat);
	
	}

	function getElementRangeLowerEndValue($elementName, $options = NULL) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementRangeLowerEndValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		if($this->isElementRange($elementName))
			return $this->__getElementValue('getRangeLowerEndValue', $elementName, $options);
		else
			return $this->__getElementValue('getValue', $elementName, $options);
	
	}

	function getElementRangeUpperEndRawValue($elementName, $displayFormat = NULL) {
	
		if(!$this->doesElementExist($elementName))
			return false;
		
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementRangeUpperEndRawValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		if($this->isElementRange($elementName))
			return $this->elements[$elementName]->getRangeUpperEndRawValue($displayFormat);
		else
			return $this->elements[$elementName]->getRawValue($displayFormat);
	
	}

	function getElementRangeUpperEndValue($elementName, $options = NULL) {
	
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementRangeUpperEndValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		if($this->isElementRange($elementName))
			return $this->__getElementValue('getRangeUpperEndValue', $elementName, $options);
		else
			return $this->__getElementValue('getValue', $elementName, $options);
	
	}

	function getElementSpelledOutValue($elementName, $displayFormat = NULL) {
	
		if(!$this->doesElementExist($elementName))
			return false;
		
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementSpelledOutValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		return $this->elements[$elementName]->getSpelledOutValue($displayFormat);
	
	}

	function getElementOrdinalValue($elementName, $displayFormat = NULL) {
	
		if(!$this->doesElementExist($elementName))
			return false;
		
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementSpelledOutValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		$string = floor($this->elements[$elementName]->getRawValue($displayFormat));
				
		switch(substr($string, strlen($string)-1)) {
		
			case 1:
				if($string < 19 && $string > 10)
					$string = $string . wsd_raw_rtf('\super th\nosupersub ');
				else
					$string = $string . wsd_raw_rtf('\super st\nosupersub ');
			break;

			case 2:
				if($string < 19 && $string > 10)
					$string = $string . wsd_raw_rtf('\super th\nosupersub ');
				else
					$string = $string . wsd_raw_rtf('\super nd\nosupersub ');
			break;

			case 3:
				if($string < 19 && $string > 10)
					$string = $string . wsd_raw_rtf('\super th\nosupersub ');
				else
					$string = $string . wsd_raw_rtf('\super rd\nosupersub ');
			break;
			
			default:
				$string = $string . wsd_raw_rtf('\super th\nosupersub ');
			break;

		}
	
		return $string;
	
	}

	function getElementSpelledOutOrdinalValue($elementName, $displayFormat = NULL) {
	
		if(!$this->doesElementExist($elementName))
			return false;
		
		if(!array_key_exists($elementName, $this->elements))
			triggerError('managerClass: Call to managerClass:getElementSpelledOutValue refers to non-existing element "' . $elementName . '"' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
	
		$string = floor($this->elements[$elementName]->getRawValue($displayFormat));
				
		switch($string) {
		
			case 1:
				$string = 'first';
			break;

			case 2:
				$string = 'second';
			break;

			case 3:
				$string = 'third';
			break;
			
			case 4:
				$string = 'fourth';
			break;

			case 5:
				$string = 'fifth';
			break;

			case 6:
				$string = 'sixth';
			break;

			case 7:
				$string = 'seventh';
			break;

			case 8:
				$string = 'eighth';
			break;

			case 9:
				$string = 'ninth';
			break;

			case 10:
				$string = 'tenth';
			break;
			
			case 11:
				$string = 'eleventh';
			break;

			case 12:
				$string = 'twelfth';
			break;

			case 13:
				$string = 'thirteenth';
			break;

			case 14:
				$string = 'fourteenth';
			break;

			case 15:
				$string = 'fifteenth';
			break;

			case 16:
				$string = 'sixteenth';
			break;

			case 17:
				$string = 'seventeenth';
			break;
			
			case 18:
				$string = 'eighteenth';
			break;

			case 19:
				$string = 'nineteenth';
			break;

			case 20:
				$string = 'twentieth';
			break;

			case 21:
				$string = 'twenty-first';
			break;
			
			case 22:
				$string = 'twenty-second';
			break;
			
			case 23:
				$string = 'twenty-third';
			break;
			
			case 24:
				$string = 'twenty-fourth';
			break;
			
			case 25:
				$string = 'twenty-fifth';
			break;
			
			case 26:
				$string = 'twenty-sixth';
			break;
			
			case 27:
				$string = 'twenty-seventh';
			break;
			
			case 28:
				$string = 'twenty-eighth';
			break;
			
			case 29:
				$string = 'twenty-ninth';
			break;
			
			case 30:
				$string = 'thirtieth';
			break;
			
			case 31:
				$string = 'thirty-first';
			break;
			
			case 32:
				$string = 'thirty-second';
			break;
			
			case 33:
				$string = 'thirty-third';
			break;
			
			case 34:
				$string = 'thirty-fourth';
			break;
			
			case 35:
				$string = 'thirty-fifth';
			break;
	
			case 36:
				$string = 'thirty-sixth';
			break;
			
			case 37:
				$string = 'thirty-seventh';
			break;
			
			case 38:
				$string = 'thirty-eighth';
			break;
			
			case 39:
				$string = 'thirty-ninth';
			break;
			
			default:
				$string = $string . wsd_raw_rtf('\super th\nosupersub ');
			
		}
	
		return $string;
	
	}
	
	function doesElementExist($elementName) {
		return array_key_exists($elementName, $this->elements);
	}
	
	function cloneElement($elementName, $clone) {
	
		$this->elements[$clone] = $this->elements[$elementName];
	
	}
	
	function displayString($string, $options = NULL) {
	
		$this->addAndReplaceElement('__dummy', managerClass::__STRING, $string);
		return $this->getElement('__dummy', $options);
	
	}
	
	function displayNumber($number, $options = NULL) {
	
		$this->addAndReplaceElement('__dummy', managerClass::__NUMBER, $number);
		return $this->getElement('__dummy', $options);
	
	}

	function displayNumberRawValue($number, $options = NULL) {
	
		$this->addAndReplaceElement('__dummy', managerClass::__NUMBER, $number);
		return $this->getElementRawValue('__dummy', $options);
	
	}
	
	function displaySpelledOutNumber($number, $options = NULL) {
	
		$this->addAndReplaceElement('__dummy', managerClass::__NUMBER, $number);
		return $this->getElementSpelledOutValue('__dummy', $options);
	
	}

	function displayOrdinalNumber($number, $options = NULL) {

		if($number == '[INSERT]')
			return '[INSERT]';
	
		$this->addAndReplaceElement('__dummy', managerClass::__NUMBER, $number);
		return $this->getElementOrdinalValue('__dummy', $options);
	
	}

	function displaySpelledOutOrdinalNumber($number, $options = NULL) {

		if($number == '[INSERT]')
			return '[INSERT]';
	
		$this->addAndReplaceElement('__dummy', managerClass::__NUMBER, $number);
		return $this->getElementSpelledOutOrdinalValue('__dummy', $options);
	
	}
	
	function displayPercentage($percentage, $options = NULL) {
	
		$this->addAndReplaceElement('__dummy', managerClass::__PERCENTAGE, $percentage * 100.0);
		return $this->getElement('__dummy', $options);
	
	}

	function displayDate($date, $options = NULL) {
	
		$this->addAndReplaceElement('__dummy', managerClass::__DATE, $date);
		return $this->getElement('__dummy', $options);
	
	}

	function getSpelledOutValue($number) {

		$this->addAndReplaceElement('__dummy', managerClass::__NUMBER, $number);
		return $this->getElementSpelledOutValue('__dummy');
	
	}
	
	function getTermInMonths($end = NULL, $round = true, $roundingDecimals = 0) {
	
		if($end === NULL)
			$end = $this->getElementRawValue('Maturity date');

		$start = $this->getElementRawValue('Trade date');
		
		if(is_numeric($start) && is_numeric($end))
			switch($round) {

				case false:
					return ($end - $start) / 86400.0 / 30.5;
				break;
				
				case true:
					return round(($end - $start) / 86400.0 / 30.5, $roundingDecimals);
				break;
				
			}
		else
			return $this->defaultValue;
		
	}

	function getTermInYears($end = NULL, $round = true, $roundingDecimals = 0) {
	
		if($end === NULL)
			$end = $this->getElementRawValue('Maturity date');
	
		$start = $this->getElementRawValue('Trade date');
	
		if(is_numeric($start) && is_numeric($end))
			switch($round) {

				case false:
					return ($end - $start) / 86400.0 / 365.0;
				break;
			
				case true:
					return round(($end - $start) / 86400.0 / 365.0, $roundingDecimals);
				break;
			}
		else
			return $this->defaultValue;
		
	}
	
	function getTermInDays($end = NULL, $round = true, $roundingDecimals = 0) {
	
		if($end === NULL)
			$end = $this->getElementRawValue('Maturity date');
	
		$start = $this->getElementRawValue('Trade date');
	
		if(is_numeric($start) && is_numeric($end))
			switch($round) {

				case false:
					return ($end - $start) / 86400.0;
				break;
			
				case true:
					return round(($end - $start) / 86400.0, $roundingDecimals);
				break;
			}
		else
			return $this->defaultValue;
		
	}
	
	function getTermAsNoun($end = $this->getElementRawValue('Maturity date'), $spellOut = false, $switchOver = 18) {
	
		if($this->getTermInMonths($end) <= $switchOver)
		
			if($this->getTermInMonths($end, true, 1) == 1) {
			
				switch($this->defaultLanguage) {
				
					case 'en':
					default:
						if($spellOut)
							return 'one month';
						else
							return '1 month';
						
					case 'fr':
						if($spellOut)
							return 'un mois';
						else
							return '1 mois';
					
				}
				
			} else {

				switch($this->defaultLanguage) {
				
					case 'en':
					default:
						if($spellOut)
							return $this->displaySpelledOutNumber($this->getTermInMonths($end, true, 0)) . ' months';
						else
							return $this->getTermInMonths($end, true, 0) . ' months';
						
					case 'fr':
						if($spellOut)
							return $this->displaySpelledOutNumber($this->getTermInMonths($end, true, 0)) . ' mois';
						else
							return $this->getTermInMonths($end, true, 0) . ' mois';
								
				}
				
			}
			
		else
			if($this->getTermInYears($end, true, 1) == 1) {
				switch($this->defaultLanguage) {
				
					case 'en':
					default:
						if($spellOut)
							return 'one year';
						else
							return '1 year';
						
					case 'fr':
						if($spellOut)
							return 'un an';
						else
							return '1 an';
					
				}
				
			} else {
			
				switch($this->defaultLanguage) {
				
					case 'en':
					default:
						if($spellOut)
							return $this->displaySpelledOutNumber($this->getTermInYears($end, true, 1)) . ' years';
						else
							return $this->getTermInYears($end, true, 1) . ' years';
						
					case 'fr':
						if($spellOut)
							return $this->displaySpelledOutNumber($this->getTermInYears($end, true, 1)) . ' ans';
						else
							return $this->getTermInYears($end, true, 1) . ' ans';
					
				}
			
			}
	}
	
	function getSpelledOutTermAsNoun($end = $this->getElementRawValue('Maturity date'), $switchOver = 18) {
	
		if($this->getTermInMonths($end) <= $switchOver)
			if($this->getTermInMonths($end) == 1)
				return $this->getSpelledOutValue('1') .  ' month';
			else
				return $this->getSpelledOutValue($this->getTermInMonths($end)) . ' months';
		else
			if($this->getTermInYears($end) == 1)
				return $this->getSpelledOutValue('1') .  ' year';
			else
				return $this->getSpelledOutValue($this->getTermInYears($end)) . ' years';
	}
		
	function getTermAsAdjective($end = $this->getElementRawValue('Maturity date')) {
	
		if($this->getTermInMonths($end) <= 18) {
			
			if($this->getTermInMonths($end, true, 1) == 1) {
				
				switch($this->defaultLanguage) {
				
					case 'en':
					default:
						return '1-month';
						
					case 'fr':
						return '1-mois';
					
				}
			
			} else {
				
				switch($this->defaultLanguage) {
				
					case 'en':
					default:
						return $this->getTermInMonths($end, true, 0) . '-month';
						
					case 'fr':
						return $this->getTermInMonths($end, true, 0) . '-mois';
					
				}
			}
		} else {
			
			if($this->getTermInYears($end, true, 1) == 1) {
				
				switch($this->defaultLanguage) {
				
					case 'en':
					default:
						return '1-year';
						
					case 'fr':
						return '1-an';
					
				}

			} else {
				
				switch($this->defaultLanguage) {
				
					case 'en':
					default:
						return $this->getTermInYears($end, true, 1) . '-year';
						
					case 'fr':
						return $this->getTermInYears($end, true, 1) . '-ans';
					
				}
			}
				
		}
		
	}

	function getSpelledOutTermAsAdjective($end = $this->getElementRawValue('Maturity date')) {
	
		if($this->getTermInMonths($end) <= 18)
			if($this->getTermInMonths($end) == 1)
				return $this->getSpelledOutValue('1') . '-month';
			else
				return $this->getSpelledOutValue($this->getTermInMonths($end)) . '-month';
		else
			if($this->getTermInYears($end) == 1)
				return $this->getSpelledOutValue('1') . '-year';
			else
				return $this->getSpelledOutValue($this->getTermInYears($end)) . '-year';
	}
	
	function getTermAsOrdinalExpression($end = $this->getElementRawValue('Maturity date')) {
	
		$termInWords = $this->getSpelledOutValue($this->getTermInYears($end));
		
		$lastHyphen = strrpos($termInWord, '-');
		$lastSpace = strrpos($termInWord, ' ');
		
		if($lastHyphen !== false || $lastSpace !== false) {
			$finalNumberBegins = max(0, $lastHyphen, $lastSpace) + 1;
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('one', 'first', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('two', 'second', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('three', 'third', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('four', 'fourth', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('five', 'fifth', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('two', 'second', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('six', 'sixth', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('seven', 'seventh', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('eight', 'eighth', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('nine', 'ninth', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('ten', 'tenth', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('eleven', 'eleventh', substr($termInWords, $finalNumberBegins));
			$termInWords = substr($termInWords, 0, $finalNumberBegins - 1) . str_replace('twelve', 'twelfth', substr($termInWords, $finalNumberBegins));
		} else {
			$finalNumberBegins = 0;
			$termInWords = str_replace('one', 'first', $termInWords);
			$termInWords = str_replace('two', 'second', $termInWords);
			$termInWords = str_replace('three', 'third', $termInWords);
			$termInWords = str_replace('four', 'fourth', $termInWords);
			$termInWords = str_replace('five', 'fifth', $termInWords);
			$termInWords = str_replace('six', 'sixth', $termInWords);
			$termInWords = str_replace('seven', 'seventh', $termInWords);
			$termInWords = str_replace('eight', 'eighth', $termInWords);
			$termInWords = str_replace('nine', 'ninth', $termInWords);
			$termInWords = str_replace('ten', 'tenth', $termInWords);
			$termInWords = str_replace('eleven', 'eleventh', $termInWords);
			$termInWords = str_replace('twelve', 'twelfth', $termInWords);
		}
		
		if($this->getTermInMonths($end) < 12)
			return 'the ' . $termInWords . ' month';
		else
			return 'the ' . $termInWords . ' year';

	}
	
	function getAPY($number, $term) {
	
		return pow(1 + $number, $term) - 1;
	
	}

	function getAPY1($number, $term) {
	
		return pow($number, 1 / $term) - 1;
	
	}

	function getAPY2($number, $decimal = true, $roundToTheNearestYear = true, $applyFormatting = true, $plusOne = true, $lastDate = null) {
		
		if($lastDate == null){
		
			if($plusOne){
				
				if($applyFormatting)
					if($roundToTheNearestYear)
						if($decimal)
							return round((pow($number + 1, 1 / $this->getTermInYears()) - 1) * 100, 2) . '%';
						else
							return round((pow($number / 100 + 1, 1 / $this->getTermInYears()) - 1) * 100, 2) . '%';
					else
						if($decimal)
							return round((pow($number + 1, 1 / $this->getTermInYears(null,false)) - 1) * 100, 2) . '%';
						else
							return round((pow($number / 100 + 1, 1 / $this->getTermInYears(null,false)) - 1) * 100, 2) . '%';
				else
					if($roundToTheNearestYear)
						if($decimal)
							return round((pow($number + 1, 1 / $this->getTermInYears()) - 1) * 100, 2);
						else
							return round((pow($number / 100 + 1, 1 / $this->getTermInYears()) - 1) * 100, 2);
					else
						if($decimal)
							return round((pow($number + 1, 1 / $this->getTermInYears(null,false)) - 1) * 100, 2);
						else
							return round((pow($number / 100 + 1, 1 / $this->getTermInYears(null,false)) - 1) * 100, 2);
			
			
			}else{
			
				if($applyFormatting)
					if($roundToTheNearestYear)
						if($decimal)
							return round((pow($number, 1 / $this->getTermInYears()) - 1) * 100, 2) . '%';
						else
							return round((pow($number / 100, 1 / $this->getTermInYears()) - 1) * 100, 2) . '%';
					else
						if($decimal)
							return round((pow($number, 1 / $this->getTermInYears(null,false)) - 1) * 100, 2) . '%';
						else
							return round((pow($number / 100, 1 / $this->getTermInYears(null,false)) - 1) * 100, 2) . '%';
				else
					if($roundToTheNearestYear)
						if($decimal)
							return round((pow($number, 1 / $this->getTermInYears()) - 1) * 100, 2);
						else
							return round((pow($number / 100, 1 / $this->getTermInYears()) - 1) * 100, 2);
					else
						if($decimal)
							return round((pow($number, 1 / $this->getTermInYears(null,false)) - 1) * 100, 2);
						else
							return round((pow($number / 100, 1 / $this->getTermInYears(null,false)) - 1) * 100, 2);
			
			}
			
		}else{
		
			if($plusOne){
				
				if($applyFormatting)
					if($roundToTheNearestYear)
						if($decimal)
							return round((pow($number + 1, 1 / $this->getTermInYears($lastDate)) - 1) * 100, 2) . '%';
						else
							return round((pow($number / 100 + 1, 1 / $this->getTermInYears($lastDate)) - 1) * 100, 2) . '%';
					else
						if($decimal)
							return round((pow($number + 1, 1 / $this->getTermInYears($lastDate,false)) - 1) * 100, 2) . '%';
						else
							return round((pow($number / 100 + 1, 1 / $this->getTermInYears($lastDate,false)) - 1) * 100, 2) . '%';
				else
					if($roundToTheNearestYear)
						if($decimal)
							return round((pow($number + 1, 1 / $this->getTermInYears($lastDate)) - 1) * 100, 2);
						else
							return round((pow($number / 100 + 1, 1 / $this->getTermInYears($lastDate)) - 1) * 100, 2);
					else
						if($decimal)
							return round((pow($number + 1, 1 / $this->getTermInYears($lastDate,false)) - 1) * 100, 2);
						else
							return round((pow($number / 100 + 1, 1 / $this->getTermInYears($lastDate,false)) - 1) * 100, 2);
			
			
			}else{
			
				if($applyFormatting)
					if($roundToTheNearestYear)
						if($decimal)
							return round((pow($number, 1 / $this->getTermInYears($lastDate)) - 1) * 100, 2) . '%';
						else
							return round((pow($number / 100, 1 / $this->getTermInYears($lastDate)) - 1) * 100, 2) . '%';
					else
						if($decimal)
							return round((pow($number, 1 / $this->getTermInYears($lastDate,false)) - 1) * 100, 2) . '%';
						else
							return round((pow($number / 100, 1 / $this->getTermInYears($lastDate,false)) - 1) * 100, 2) . '%';
				else
					if($roundToTheNearestYear)
						if($decimal)
							return round((pow($number, 1 / $this->getTermInYears($lastDate)) - 1) * 100, 2);
						else
							return round((pow($number / 100, 1 / $this->getTermInYears($lastDate)) - 1) * 100, 2);
					else
						if($decimal)
							return round((pow($number, 1 / $this->getTermInYears($lastDate,false)) - 1) * 100, 2);
						else
							return round((pow($number / 100, 1 / $this->getTermInYears($lastDate,false)) - 1) * 100, 2);
			
			}
		
		}
	
	}
	
	function getAPYDays1($number, $term) {
	
		return pow($number, 365 / $term) - 1;
	
	}

	function getRomanNumeral($input_arabic_numeral='') {

    if ($input_arabic_numeral == '') { $input_arabic_numeral = date("Y"); } // DEFAULT OUTPUT: THIS YEAR 
    $arabic_numeral            = intval($input_arabic_numeral); 
    $arabic_numeral_text    = "$arabic_numeral"; 
    $arabic_numeral_length    = strlen($arabic_numeral_text); 

    if (!ereg('[0-9]', $arabic_numeral_text)) { return false; } 

    if ($arabic_numeral > 4999) { return false; } 

    if ($arabic_numeral < 1) { return false; } 

    if ($arabic_numeral_length > 4) { return false; } 

    $roman_numeral_units    = $roman_numeral_tens        = $roman_numeral_hundreds        = $roman_numeral_thousands        = array(); 
    $roman_numeral_units[0]    = $roman_numeral_tens[0]    = $roman_numeral_hundreds[0]    = $roman_numeral_thousands[0]    = ''; // NO ZEROS IN ROMAN NUMERALS 

    $roman_numeral_units[1]='i'; 
    $roman_numeral_units[2]='ii'; 
    $roman_numeral_units[3]='iii'; 
    $roman_numeral_units[4]='iv'; 
    $roman_numeral_units[5]='v'; 
    $roman_numeral_units[6]='vi'; 
    $roman_numeral_units[7]='vii'; 
    $roman_numeral_units[8]='viii'; 
    $roman_numeral_units[9]='ix'; 

    $roman_numeral_tens[1]='x'; 
    $roman_numeral_tens[2]='xx'; 
    $roman_numeral_tens[3]='xxx'; 
    $roman_numeral_tens[4]='xl'; 
    $roman_numeral_tens[5]='l'; 
    $roman_numeral_tens[6]='lx'; 
    $roman_numeral_tens[7]='lxx'; 
    $roman_numeral_tens[8]='lxxx'; 
    $roman_numeral_tens[9]='xc'; 

    $roman_numeral_hundreds[1]='c'; 
    $roman_numeral_hundreds[2]='cc'; 
    $roman_numeral_hundreds[3]='ccc'; 
    $roman_numeral_hundreds[4]='cd'; 
    $roman_numeral_hundreds[5]='d'; 
    $roman_numeral_hundreds[6]='dc'; 
    $roman_numeral_hundreds[7]='dcc'; 
    $roman_numeral_hundreds[8]='dccc'; 
    $roman_numeral_hundreds[9]='cm'; 

    $roman_numeral_thousands[1]='m'; 
    $roman_numeral_thousands[2]='mm'; 
    $roman_numeral_thousands[3]='mmm'; 
    $roman_numeral_thousands[4]='mmmm'; 

    if ($arabic_numeral_length == 3) { $arabic_numeral_text = "0" . $arabic_numeral_text; } 
    if ($arabic_numeral_length == 2) { $arabic_numeral_text = "00" . $arabic_numeral_text; } 
    if ($arabic_numeral_length == 1) { $arabic_numeral_text = "000" . $arabic_numeral_text; } 

    $anu = substr($arabic_numeral_text, 3, 1); 
    $anx = substr($arabic_numeral_text, 2, 1); 
    $anc = substr($arabic_numeral_text, 1, 1); 
    $anm = substr($arabic_numeral_text, 0, 1); 

    $roman_numeral_text = $roman_numeral_thousands[$anm] . $roman_numeral_hundreds[$anc] . $roman_numeral_tens[$anx] . $roman_numeral_units[$anu]; 
		return ($roman_numeral_text); 
	
	}
	
	function optimizeCrossReferences($string) {
	
		$crossReferenceStart = strpos($string, '"');
		$crossReferenceEnd = strpos($string, chr(8212));
		$crossReference = substr($string, $crossReferenceStart, $crossReferenceEnd - $crossReferenceStart);
		
		$string = substr($string, 0, $crossReferenceEnd) . str_replace($crossReference, '"', substr($string, $crossReferenceEnd));
	
		return $string;
	
	}

	function __calculateOID($comparableYield, $notional, $timeDiff) {
		return $comparableYield * $notional * $timeDiff;
	}
	
	function calculateOIDProjectedPaymentSchedule($denomination, $comparableYield, $issueDate, $maturityDate) {
	
		if($maturityDate < $issueDate || $issueDate == NULL || $maturityDate == NULL)
			triggerError('managerClass: Call to managerClass:calculateOIDProjectedPaymentSchedule() didn\'t pass sensible issue date and maturity date', E_USER_ERROR);
	
		$OIDArray = array();
		$OIDArrayCounter = 0;

		$OIDYearCount = 0;

		$endOfFirstCalendarYear = round(wsd_mktime(0, 0, 0, 12, 31, wsd_date_format($issueDate, 'yyyy')) / 86400.0) * 86400;
		$endOfLastCalendarYear = round(wsd_mktime(0, 0, 0, 12, 31, wsd_date_format($maturityDate, 'yyyy') - 1) / 86400.0) * 86400;

		$notional = $denomination;
		$lastOID = $totalOID = 0;

		if($issueDate != $endOfFirstCalendarYear) {
			$totalOID += $lastOID;
			$startDate = $issueDate;
			$endDate = $endOfFirstCalendarYear;
			$timeDiff = ($endDate - $startDate) / 86400.0 / 365.0;
			$lastOID = $this->__calculateOID($comparableYield, $notional, $timeDiff);
			$OIDYearCount++;    
		}

		for($i=$endOfFirstCalendarYear + 86400; $i<$endOfLastCalendarYear; $i = round(wsd_mktime(0, 0, 0, 1, 1, wsd_date_format($i, 'yyyy') + 1) / 86400.0) * 86400) {
			array_push($OIDArray['Schedule'], array('Start date' => $startDate, 'End date' => $endDate, 'Year' => wsd_date_format($issueDate, 'yyyy') + $OIDArrayCounter++, 'OID' => round($lastOID, 2)));
			$totalOID += round($lastOID, 2);
			$notional += round($lastOID, 2);
			$startDate = $i;
			$endDate = round(wsd_mktime(0, 0, 0, 1, 1, wsd_date_format($i, 'yyyy') + 1) / 86400.0) * 86400 - 86400;
			$timeDiff = 1;
			$lastOID = $this->__calculateOID($comparableYield, $notional, $timeDiff);
			$OIDYearCount++;    
		}

		if($maturityDate != $endOfLastCalendarYear) {
			array_push($OIDArray['Schedule'], array('Start date' => $startDate, 'End date' => $endDate, 'Year' => wsd_date_format($issueDate, 'yyyy') + $OIDArrayCounter++, 'OID' => round($lastOID, 2)));
			$totalOID += round($lastOID, 2);
			$notional += round($lastOID, 2);
			$startDate = $i;
			$endDate = $maturityDate;
			$timeDiff = ($endDate - $startDate) / 86400.0 / 365.0;
			$lastOID = $this->__calculateOID($comparableYield, $notional, $timeDiff);
			$OIDYearCount++;    
		}

		array_push($OIDArray['Schedule'], array('Start date' => $startDate, 'End date' => $endDate, 'Year' => wsd_date_format($issueDate, 'yyyy') + $OIDArrayCounter, 'OID' => round($lastOID, 2)));
		$totalOID += round($lastOID, 2);
		$finalOIDYear = wsd_date_format($issueDate, 'yyyy') + $OIDArrayCounter;
		$OIDIncome = round($lastOID, 2);

		$OIDArray['Final payment'] = round($denomination + $totalOID, 2);
		
		$this->OIDArray = $OIDArray;

	}

	function getOIDProjectedPaymentSchedule() {
	
		return $this->OIDArray['Schedule'];
	
	}

	function getOIDFinalPayment() {
	
		return $this->OIDArray['Final payment'];
	
	}
	
	function resetDisclosure() {
	
		$this->disclosureStyles = array();
		$this->disclosuredSafteyCount = 0;
		
		
	}
	
	function startDisclosure() {
	
		$this->disclosure = array();
		$this->disclosureCount = 0;
		$this->disclosureItemSymbolCount = array();
		$this->disclosuredSafteyCount = 0;
		
		
	}
	
	function endDisclosure() {

		$this->disclosureCount = 0;
		$this->disclosuredSafteyCount = 0;
		
	
	}
	
	function setDisclosureStyle($style, $level) {
	
		$this->disclosureStyles[$style]['Level'] = $level;
	
	}
	
	function setDisclosureSymbol($style, $symbol, $prefix, $postfix) {
	
		if(!array_key_exists($style, $this->disclosureStyles))
			triggerError('managerClass:: reference to undefined style in call to managerClass::setDisclosureSymbol' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
			
		$this->disclosureStyles[$style]['Symbol'] = $symbol;
		$this->disclosureStyles[$style]['Prefix'] = $prefix;
		$this->disclosureStyles[$style]['Postfix'] = $postfix;
	
	}

	function addDisclosure($style, $text) {

		if(!array_key_exists($style, $this->disclosureStyles))
			triggerError('managerClass:: reference to undefined style in call to managerClass::setDisclosureSymbol' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		$count = count($this->disclosure);
		
		$this->disclosure[$count]['Style'] = $style;
		$this->disclosure[$count]['Text'] = $text;
			
	}
	
	function areThereMoreDisclosureItems() {
		
		if($this->disclosuredSafteyCount++ <= 1000){
			
			if($this->disclosureCount < count($this->disclosure)){
				
				$this->disclosureDisplayed = false;
				
				return true;
			
			}else{
				
				return false;
	
			}

			
		}else{
			
			trigger_error('ManagerClass : Disclosure Has Exceeded Saftey Count : ' . $this->disclosuredSafteyCount . ' Diclosure Identifier : ' . $this->disclosureCount . ' : ' . print_r($this->disclosure[$this->disclosureCount],  true) , E_USER_ERROR);
			
		}
		
	}
	
	function getDisclosureItemStyle() {
		
		$this->disclosureDisplayed = true;
		
		return $this->disclosure[$this->disclosureCount]['Style'];
	
	}
	
	function getDisclosureItemSymbol() {
	
		$index = $this->disclosureCount;
		$level = $this->disclosureStyles[$this->disclosure[$index]['Style']]['Level'];
		
		if($index == 0)
			$this->disclosureItemSymbolCount[$level] = 0;
		else
			if($this->disclosureStyles[$this->disclosure[$index - 1]['Style']]['Level'] < $this->disclosureStyles[$this->disclosure[$index]['Style']]['Level'] && $this->disclosureStyles[$this->disclosure[$index - 1]['Style']]['Level'] != -1)
				$this->disclosureItemSymbolCount[$level] = 0;

		if($this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol'] == 'a' || $this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol'] == 'A')
			return $this->disclosureStyles[$this->disclosure[$index]['Style']]['Prefix'] . chr(ord($this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol']) + $this->disclosureItemSymbolCount[$level]++) . $this->disclosureStyles[$this->disclosure[$index]['Style']]['Postfix'];
			
		if($this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol'] == 'x' || $this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol'] == 'X')
			return $this->disclosureStyles[$this->disclosure[$index]['Style']]['Prefix'] . chr(ord($this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol']) + $this->disclosureItemSymbolCount[$level]++) . $this->disclosureStyles[$this->disclosure[$index]['Style']]['Postfix'];
			
		if($this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol'] == '1')
			return $this->disclosureStyles[$this->disclosure[$index]['Style']]['Prefix'] . ($this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol'] + $this->disclosureItemSymbolCount[$level]++) . $this->disclosureStyles[$this->disclosure[$index]['Style']]['Postfix'];
			
		if($this->disclosureStyles[$this->disclosure[$index]['Style']]['Symbol'] == 'i')
			return $this->disclosureStyles[$this->disclosure[$index]['Style']]['Prefix'] . $this->getRomanNumeral(1 + $this->disclosureItemSymbolCount[$level]++) . $this->disclosureStyles[$this->disclosure[$index]['Style']]['Postfix'];		
	
	}
	
	function getDisclosureItem() {
	
		$string = '';
	
		if($this->disclosureStyles[$this->disclosure[$this->disclosureCount]['Style']]['Symbol'] != '')
			$string .= $this->getDisclosureItemSymbol() . wsd_raw_rtf('\tab ');
	
		$string .= $this->disclosure[$this->disclosureCount++]['Text'];
		
		return $string;
	
	}

	function getDisclosureItemRaw() {
	
		return $this->disclosure[$this->disclosureCount++]['Text'];
		
	}
	
	function formatCurrencyAmount($string) {
	
		$string = str_replace('US$-', '-US$', $string);
		$string = str_replace('CA$-', '-CA$', $string);
		$string = str_replace('$-', '-$', $string);
		$string = str_replace('€-', '-€', $string);
		$string = str_replace('£-', '-£', $string);
	
		return $string;
	
	}
	
	function calculateDates($vField, $dateName, $startDate = $this->getElementRawValue('Trade date'), $endDate = $this->getElementRawValue('Maturity date'), $stub = null) {

		global $manager, $holidayChecker;
		
		switch($vField['Specification']) {
		
			case 'Specify dates by interval':
			
				if($stub == null)
					$dates = new eventsClass($startDate, $endDate, $vField['Dates (specified by interval)']['Interval']);
				else
					$dates = new eventsClass($startDate, $endDate, $vField['Dates (specified by interval)']['Interval'],$stub);
					
				$dates->setEventDateCalendarDay($vField['Dates (specified by interval)']['Calendar day']);
				
				if($vField['Dates (specified by interval)']['Interval'] == 'Bi-weekly')
					$dates->setEventDateCalendarDay($vField['Dates (specified by interval)']['Calendar day 2'], 2);
				
				$dates->calculateEventDates();
				
				$this->addElement($dateName . ' indicative', managerClass::__STRING, $dates->getIndicativeEventDates());
				$this->addElement($dateName, managerClass::__STRING, $dates->getEventDates());
				$this->addElement($dateName . ' ordinal', managerClass::__STRING, $dates->getEventDates(true));
				$datesList = $dates->createListOfEventDates();
				
				//Check for Final Date
				switch($dateName){
					
					case 'Observation dates':
					case 'Review dates':
						
						if($this->doesElementExist('Final valuation date') && $this->getElementRawValue('Final valuation date') != NULL)
							$datesList[] = $this->getElementRawValue('Final valuation date');
						
					break;
					
					case 'Payment dates':
					
						if($this->doesElementExist('Maturity date') && $this->getElementRawValue('Maturity date') != NULL)
							$datesList[] = $this->getElementRawValue('Maturity date');
						
					break;
					
				}
				
				$datesList = array_unique($datesList);
				
				//Calculate array of observation/payment dates
				switch($dateName){
					
					//Observation dates
					case 'Review dates':
					case 'Observation dates':
					case 'Valuation dates':
					case 'Coupon observation dates':
					case 'Autocall observation dates':
						
						$adjustedDatesList = array();
					
						foreach($datesList as $date) {
							
							$item = $holidayChecker->adjustForWeekendsAndHolidays($date);
							
							if($dateName == 'Coupon observation dates')
								$item = $holidayChecker->moveDate($item, - $this->getElement('Business days count'));
							
							$adjustedDatesList[] = $item;
						
						}
						
						//Enumerating Holidays for debugging dates
						$adjustedDatesListHolidays = array();
						$indexCounter = 0;
						foreach($datesList as $date) {
							
							$date = $holidayChecker->adjustForWeekendsAndHolidays($date);
							
							if($dateName == 'Coupon observation dates')
								$item = $holidayChecker->moveDate($item, - $this->getElement('Business days count'));
							
							$tempValue = $holidayChecker->adjustForWeekendsAndHolidays($date);
						
							$adjustedDatesListHolidays[$indexCounter]['unadjusted date'] = $date;
							$adjustedDatesListHolidays[$indexCounter]['unadjusted date formatted'] = date('F j, Y',$date);
							$adjustedDatesListHolidays[$indexCounter]['adjusted date'] = $tempValue;
							$adjustedDatesListHolidays[$indexCounter]['adjusted date foramtted'] = date('F j, Y',$tempValue);
							$adjustedDatesListHolidays[$indexCounter]['difference'] = $holidayChecker->getListHolidaysAndWeekendsBetweenTwoDates($date,$tempValue,false);
							$adjustedDatesListHolidays[$indexCounter]['difference formatted'] = $holidayChecker->getListHolidaysAndWeekendsBetweenTwoDates($date,$tempValue,true);
							
							$indexCounter++;
						}
						
					break;
					
					//Payment dates
					case 'Payment dates':
					case 'Coupon payment dates':
					case 'Interest payment dates':
					case 'Redemption dates':
					case 'Early redemption dates':
					case 'Interest reset dates':
						
						$adjustedDatesList = array();
						foreach($datesList as $date) 
							$adjustedDatesList[] = $holidayChecker->adjustForWeekendsAndHolidays($date);
						
						//Enumerating Holidays for debugging dates
						$adjustedDatesListHolidays = array();
						$indexCounter = 0;
						foreach($datesList as $date) {
							
							$tempValue = $holidayChecker->adjustForWeekendsAndHolidays($date);
						
							$adjustedDatesListHolidays[$indexCounter]['unadjusted date'] = $date;
							$adjustedDatesListHolidays[$indexCounter]['unadjusted date formatted'] = date('F j, Y',$date);
							$adjustedDatesListHolidays[$indexCounter]['adjusted date'] = $tempValue;
							$adjustedDatesListHolidays[$indexCounter]['adjusted date foramtted'] = date('F j, Y',$tempValue);
							$adjustedDatesListHolidays[$indexCounter]['difference'] = $holidayChecker->getListHolidaysAndWeekendsBetweenTwoDates($date,$tempValue,false);
							$adjustedDatesListHolidays[$indexCounter]['difference formatted'] = $holidayChecker->getListHolidaysAndWeekendsBetweenTwoDates($date,$tempValue,true);
							
							$indexCounter++;
						}
					
					break;
					
				}
				
				$this->addElement($dateName . ' list', managerClass::__OBJECT, $adjustedDatesList);
				$this->addElement($dateName . ' list adjusted', managerClass::__OBJECT, $adjustedDatesListHolidays);
				$this->addElement($dateName . ' list unadjusted', managerClass::__OBJECT, $datesList);
				
			break;
			
			case 'Specify dates individually':
				$datesList = array();
				$enumeration1 = new enumerationClass();
				$enumeration2 = new enumerationClass();
				$enumeration3 = new enumerationClass();

				foreach($vField['Dates (specified individually)'] as $date) {
				
					array_push($datesList, round($date['Date'] / 86400.0) * 86400);
					
					if(count($vField['Dates (specified individually)']) == 1) {
					
						$enumeration1->add($this->displayDate($date, array('formatString' => '     F Y')));
						$enumeration2->add($this->displayDate($date, array('formatString' => 'j F Y')));
						$enumeration3->add($this->displayDate($date, array('formatString' => 'F Y')));
						
					}
					
				}
				
				$this->addElement($dateName . ' list', managerClass::__OBJECT, $datesList);
				$this->addElement($dateName . ' list unadjusted', managerClass::__OBJECT, $datesList);
				
				$date = $datesList[0];
				
				if(count($vField['Dates (specified individually)']) == 0) {
					
					triggerError('Not dates of type "' . $dateName . '" provided' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);
				
				}
				
				if(count($vField['Dates (specified individually)']) == 1) {
					
					$this->addElement($dateName . ' indicative', managerClass::__STRING, $enumeration1->getEnumeration());
					$this->addElement($dateName, managerClass::__STRING, $enumeration2->getEnumeration());
					$this->addElement($dateName . ' ordinal', managerClass::__STRING, 'the ' . $this->displayDate($date, array('formatString' => 'jS')) . ' day of ' . $enumeration3->getEnumeration());
					
				}
				
				if(count($vField['Dates (specified individually)']) > 1) {
				
					$date1 = $datesList[0];
					$date2 = $datesList[1];
					switch($period = round(($date2 - $date1) / 86400.0 / 15.25)) {
						
						case 1:
							$interval = 'Bi-weekly';
						
						case 2:
							$interval = 'Monthly';
						break;
						
						case 6:
							$interval = 'Quarterly';
						break;
						
						case 12:
							$interval = 'Semi-annually';
						break;

						case 24:
							$interval = 'Annually';
						break;
						
						default:
							triggerError('Invalid period length for ' . $dateName . ' (appears to be ' . $period . ' months)', E_USER_ERROR);
						
					}
					
					$dates = new eventsClass($startDate, $endDate, $interval);
					$dates->setEventDateCalendarDay(date('j', $datesList[0]));
					if($vField['Dates (specified by interval)']['Interval'] == 'Bi-weekly')
						$dates->setEventDateCalendarDay(date('j', $datesList[1], 2));
					$dates->calculateEventDates();

					$this->addElement($dateName . ' indicative', managerClass::__STRING, $dates->getIndicativeEventDates());
					$this->addElement($dateName, managerClass::__STRING, $dates->getEventDates());
					$this->addElement($dateName . ' ordinal', managerClass::__STRING, $dates->getEventDates(true));

				}
				
			break;
		
		}
		
		
		if(count($datesList) >= 2){
			
			$date1 = $datesList[0];
			$date2 = $datesList[1];
			$this->addElement($dateName . ' interval applies', managerClass::__BOOLEAN, true);
		
		}else{
			
			$this->addElement($dateName . ' interval applies', managerClass::__BOOLEAN, false);
			
		}
		
		
		if($this->getElement($dateName . ' interval applies')) {
		
			switch($period = round(($date2 - $date1) / 86400.0 / 30.5)) {
			
				case 1:
					$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, 'monthly');
					$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, 'monthly');
					$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, 'month');
					$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, 'one month');
					$this->addElement($dateName . ' APY factor', managerClass::__STRING, 12);
				break;

				case 3:
					$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, 'quarterly');
					$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, 'quarterly');
					$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, 'quarter');
					$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, 'three months');
					$this->addElement($dateName . ' APY factor', managerClass::__STRING, 4);
				break;
			
				case 6:
					$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, 'semi-annual');
					$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, 'semi-annually');
					$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, 'six-month period');
					$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, 'six months');
					$this->addElement($dateName . ' APY factor', managerClass::__STRING, 2);
				break;

				case 12:
					$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, 'annual');
					$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, 'annually');
					$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, 'year');
					$this->addElement($dateName . ' APY factor', managerClass::__STRING, 1);
					$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, 'twelve months');
				break;
					
				default:
					triggerError('Invalid period length for ' . $dateName . ' (appears to be ' . $period . ' months)', E_USER_ERROR);

			}

		} else {
		
			$this->addElement($dateName . ' APY factor', managerClass::__STRING, 12 / round(($datesList[0] - $this->getElementRawValue('Trade date')) / 86400.0 / 30.5));
		
			$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, '');
			$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, '');
			$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, '');
			$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, '');
			
		}
	}

	function calculateDatesBasedOnDatesList($listOfDates, $dateName) {

		$datesList = array();
		$enumeration1 = new enumerationClass();
		$enumeration2 = new enumerationClass();
		foreach($listOfDates as $date) {
			array_push($datesList, round($date / 86400.0) * 86400);
			$enumeration1->add($this->displayDate($date, array('formatString' => '     F Y')));
			$enumeration2->add($this->displayDate($date, array('formatString' => 'j F Y')));
		}
		$this->addElement($dateName . ' indicative', managerClass::__STRING, $enumeration1->getEnumeration());
		$this->addElement($dateName, managerClass::__STRING, $enumeration2->getEnumeration());
		$this->addElement($dateName . ' list', managerClass::__OBJECT, $datesList);

		if(count($datesList) >= 2) {
			$date1 = $datesList[0];
			$date2 = $datesList[1];
			$this->addElement($dateName . ' interval applies', managerClass::__BOOLEAN, true);
		} else
			$this->addElement($dateName . ' interval applies', managerClass::__BOOLEAN, false);
			
		if($this->getElement($dateName . ' interval applies')) {

			switch($period = round(($date2 - $date1) / 86400.0 / 30.5)) {
			
				case 1:
					$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, 'monthly');
					$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, 'monthly');
					$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, 'month');
					$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, 'one month');
					$this->addElement($dateName . ' APY factor', managerClass::__STRING, 12);
				break;

				case 3:
					$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, 'quarterly');
					$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, 'quarterly');
					$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, 'quarter');
					$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, 'three months');
					$this->addElement($dateName . ' APY factor', managerClass::__STRING, 4);
				break;
			
				case 6:
					$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, 'semi-annual');
					$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, 'semi-annually');
					$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, 'six-month period');
					$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, 'six months');
					$this->addElement($dateName . ' APY factor', managerClass::__STRING, 2);
				break;

				case 12:
					$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, 'annual');
					$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, 'annually');
					$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, 'year');
					$this->addElement($dateName . ' APY factor', managerClass::__STRING, 1);
					$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, 'twelve months');
				break;
					
				default:
					triggerError('Invalid period length for ' . $dateName . ' (appears to be ' . $period . ' months)', E_USER_ERROR);

			}

		} else {
		
			$this->addElement($dateName . ' APY factor', managerClass::__STRING, 12 / round(($datesList[0] - $this->getElementRawValue('Trade date')) / 86400.0 / 30.5));
		
			$this->addElement($dateName . ' frequency (adjective)', managerClass::__STRING, '');
			$this->addElement($dateName . ' frequency (adverb)', managerClass::__STRING, '');
			$this->addElement($dateName . ' frequency (noun)', managerClass::__STRING, '');
			$this->addElement($dateName . ' frequency (interval)', managerClass::__STRING, '');
			
		}
	
	}
	
	function days360($startDate, $endDate) {
 
	/*Previous version:
	 return (360 * (date('Y', $laterDate) - date('Y', $earlierDate)) + 30 * (date('n', $laterDate) - date('n', $earlierDate)) + (date('j', $earlierDate) == 31 ? ((date('j', $laterDate) - date('j', $earlierDate))+1) : (date('j', $laterDate) - date('j', $earlierDate))));

	//return (360 * ($laterYear - $earlierYear) + 30 * ($laterDate - $earlierDate) + ($laterDay - $earlierDay));
	*/
	 
	 /*
	 adjusted to excel 360 function, using the US method by default, will calculate as follow:
	 US Method:

	 >If start date is last day of month it is set to 30th of that month
	 >If end date is last day of month, then:
	 >>If start date is last day of month the end date is set to the 1st of the following month
	 >>Otherwise, the end date is set to 30th of that month
	 -----
	 
	 */
	 
	 $count = 0;
	 
	 if($startDate >= $endDate)
	 triggerError('Day count problem', E_USER_ERROR);
	 
	 $startDate = round($startDate / 86400.0) * 86400;
	 $endDate = round($endDate / 86400.0) * 86400;

	 // Determine if start date is last calendar day of the month
	 
	 $startDateIsLastCalendarDayOfMonth = false;
	 if(date('j', $startDate+ 86400) == 1)
	 $startDateIsLastCalendarDayOfMonth = true;

	 // Skip the first day
	 
	 $startDate += 86400;
	 
	 for($i = $startDate; $i <= $endDate; $i+=86400) {
	 
	 // Only count up to the 30th calendary day of the month
	 
	 if(date('j', $i) <= 30)
	 $count++;
	 
	 // Add missing days for February, taking into account leap years
	 
	 if(date('j', $i) == 1 && date('n', $i) == 3)
	 $count += 30 - date('j', $i - 86400);

	 //echo date('j', $i) . ' / ' . $count . '<br>'; 
	 
	 }
	 
	 // If the end date is the last calendar day of the month, we count one extray but only if the start date is not the last calendar day of the month
	 
	 if(!$startDateIsLastCalendarDayOfMonth && date('j', $i - 86400) == 31)
	 $count++;

	 return $count;
	 
	 }

	function addDisclosure($style, $text) {

		if(!array_key_exists($style, $this->disclosureStyles))
			triggerError('managerClass:: reference to undefined style in call to managerClass::setDisclosureSymbol' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 1000), E_USER_ERROR);

		$count = count($this->disclosure);
		
		$this->disclosure[$count]['Style'] = $style;
		$this->disclosure[$count]['Text'] = $text;
			
	}

	function eliminateRTFCodes($string) {
	
		$string = str_replace(wsd_raw_rtf('\super TM \nosupersub'), '(TM)', $string);
		
		$string = str_replace(wsd_raw_rtf('\super SM \nosupersub'), '(SM)', $string);

		$string = str_replace(wsd_raw_rtf('\super '. chr(92) . chr(39) . 'ae \nosupersub'), '(R)', $string);
		
		return $string;
		
	}

	function doesWordStartWithVowel($word) {
	
		$dummy = strtolower(substr($word, 0, 1));
		
		if($dummy == 'a' || $dummy == 'e' || $dummy == 'i' || $dummy == 'o' || $dummy == 'u')
			return true;
		else
			return false;
	
	}
	
	function lowerCaseFirstLetter($word,$firstWords = null){
    
		if($firstWords != null){
		 
			if(is_array($firstWords)){
				
				foreach($firstWords as $firstWord)
					if(substr($word, 0, (strlen($firstWord))) == $firstWord)
						return strtolower(substr($word, 0, 1)) . substr($word, 1);
					
			}else{	   
					
				if(substr($word, 0, (strlen($firstWords))) == $firstWords)
					return strtolower(substr($word, 0, 1)) . substr($word, 1);
			
			}
			
		}else{
				
			return strtolower(substr($word, 0, 1)) . substr($word, 1);
			
		}
			
		return $word;
		
	}
	
	
	function removeFirstWord($word,$firstWords = null){
    
		if($firstWords != null){
		 
			if(is_array($firstWords)){
				
				foreach($firstWords as $firstWord)
					if(substr($word, 0, strlen($firstWord) + 1) == $firstWord . ' ')
						return substr($word, strlen($firstWord) + 1);
					
			}else{	   
					
				if(substr($word, 0, strlen($firstWords) + 1) == $firstWords . ' ')
					return substr($word, strlen($firstWords) + 1);
			
			}
			
		}else{
				
			trigger_error('ManagerClass: removeFirstWord: Missing second parameter',E_USER_ERROR); //-TEH;
			
		}
			
		return $word;
		
	}
	
	function scrubTextFormatting($text){
	
		$scrubbedMarks = array('\super','\'ae','\nosupersub', '¬');
		
		foreach($scrubbedMarks as $scrubMark)
		   $text = str_replace($scrubMark, '', $text);
		 
		$text = str_replace('\\', '', $text);
		 
		$scrubbedMarksTwo = array('  ', '   ','    ');
		 
		foreach($scrubbedMarksTwo as $scrubMark)
		   $text = str_replace($scrubMark, '', $text);
		   

		return $text;
	
	}
	
	function fixFormatting($value){
	
		// $value = str_replace('(TM).', wsd_raw_rtf('\super TM\nosupersub') . '.', $value);
		// $value = str_replace('(TM))', wsd_raw_rtf('\super TM\nosupersub') . ')', $value);
		// $value = str_replace('(TM)', wsd_raw_rtf('\super TM \nosupersub'), $value);
		
		// if(substr($value , strlen($value) - 2, 2) == 'TM'){
			// $value = str_replace('TM.', wsd_raw_rtf('\super TM\nosupersub') . '.', $value);
			// $value = str_replace('TM)', wsd_raw_rtf('\super TM\nosupersub') . ')', $value);
			// $value = str_replace('TM', wsd_raw_rtf('\super TM \nosupersub'), $value);
		// }
		
		$value = str_replace('(TM)', '™', $value);
		
		$value = str_replace('(SM).', wsd_raw_rtf('\super SM\nosupersub') . '.', $value);
		$value = str_replace('(SM))', wsd_raw_rtf('\super SM\nosupersub') . ')', $value);
		$value = str_replace('(SM)', wsd_raw_rtf('\super SM \nosupersub'), $value);

		if(substr($value , strlen($value) - 2, 2) == 'SM'){	
			$value = str_replace('SM.', wsd_raw_rtf('\super SM\nosupersub') . '.', $value);
			$value = str_replace('SM)', wsd_raw_rtf('\super SM\nosupersub') . ')', $value);
			$value = str_replace('SM', wsd_raw_rtf('\super SM \nosupersub'), $value);
		}
		
		$value = str_replace('(R).', wsd_raw_rtf('\super '. chr(92) . chr(39) . 'ae\nosupersub') . '.', $value);
		$value = str_replace('(R))', wsd_raw_rtf('\super '. chr(92) . chr(39) . 'ae\nosupersub') . ')', $value);
		$value = str_replace('(R)', wsd_raw_rtf('\super '. chr(92) . chr(39) . 'ae \nosupersub'), $value);
		// $value = str_replace(chr(174), wsd_raw_rtf('\super '. chr(92) . chr(39) . 'ae \nosupersub'), $value);
		
		return $value;
		
	
	}
	
	function reorderArrayElements($arrayOrder = null, $arrayToSort = array()){
	
		// if($arrayOrder == null || count($arrayToSort) == 0)
			// trigger_error('reorderArrayElements: Invalid Input parameters Array: ' . print_r($arrayOrder) . ' ArrayToSort: '  . print_r($arrayToSort),E_USER_ERROR); //-TEH
		
		$tempArray = array();
		
		foreach($arrayToSort as $rowIndex => $rowContent)
			foreach($arrayOrder as $key => $value)
				$tempArray[$rowIndex][$value] = $rowContent[$value];
			
		return $tempArray;
		
	}
	
	
}

?>