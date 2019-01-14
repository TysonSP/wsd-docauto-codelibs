<?php

//usage: $rtf = new rtfGenericClass();

class rtfGenericClass {

	private $bookmarks;
	private $paragraphStyles = array();
	
	function __construct() {

		$this->bookmarks = array();

	}
	
	function setfontsize($fontSize) { //only affects text echoed within the same tag - Doesn't work

		if(!is_numeric($fontSize))
			trigger_error('rtfClass: setfontsize2: expecting number as argument', E_USER_ERROR);

		return wsd_raw_rtf('\fs' . $fontSize * 2 . ' '); 

	}
	
	function equationBox($topText = '', $bottomText = ''){
		
		$string .= "{\mmath{{\* \moMath \froman \fs28 {\mf{\mnum{" . $topText . "}}";
		
		if($bottomText != ''){
			
			$string .= "{\mden " . $bottomText . "}";
			
		}
		
		$string .= "}}}}";
		
		return wsd_raw_rtf($string);
		
		
		
	}
	
	function space(){
		
		
		
	}
	
	function softreturn() {

		return wsd_raw_rtf("\line ");
	
	}
	
	function bold($text) {
	
		if(!is_string($text))
			$text = (string)$text;

		return wsd_bold($text);
	
	}
	
	function italic($text) {
	
		if(!is_string($text))
			$text = (string)$text;

		return wsd_italic($text);
	
	}

	function optionalsoftreturn() {

		return wsd_raw_rtf("\softline ");
	
	}

	function hardreturn() {

		return "\n";
	
	}
	
	function squarebullet() {
	
		return wsd_raw_rtf("\\u9642\'3f");
	
	}
	
	function bullet() {
	
		return wsd_raw_rtf('\bullet ');
	
	}

	function pagebreak() {

		return wsd_raw_rtf('\page ');
	
	}

	function tab($numberOfTabs = 1) {
		
		$string = '';
		
		for($i = 0; $i < $numberOfTabs; $i++){
			
			$string .= wsd_raw_rtf('\tab ');
			
		}
		
		
		return $string;
	
	}

	function leftquotationmark() {

		return wsd_raw_rtf("\lquote ");
	
	} 

	function rightquotationmark() {

		return wsd_raw_rtf("\rquote ");
	
	} 

	function leftdoublequotationmark() {

		return wsd_raw_rtf("\ldblquote ");
	
	} 

	function rightdoublequotationmark() {

		return wsd_raw_rtf("\rdblquote ");
	
	} 

	function superscript($string) {

		if(!is_string($string))
			$text = (string)$text;
		
		return wsd_raw_rtf('\super '). $string . wsd_raw_rtf('\nosupersub ');

	}

	function subscript($string) {

		if(!is_string($string))
			$text = (string)$text;
			
		return wsd_raw_rtf('\sub '). $string . wsd_raw_rtf('\nosupersub ');

	}

	function underline($string) {

		if(!is_string($string))
			$text = (string)$text;
		
		return wsd_underline($string);

	}

	function doubleunderline($string) {

		if(!is_string($string))
			$text = (string)$text;
		
		return wsd_raw_rtf('\\uldb '). $string . wsd_raw_rtf('\\ulnone');

	}

	function dotunderline($string) {

		if(!is_string($string))
			$text = (string)$text;
			
		return wsd_raw_rtf('\\uld '). $string . wsd_raw_rtf('\\ulnone');

	}

	function dotdashunderline($string) {

		if(!is_string($string))
			$text = (string)$text;
		
		return wsd_raw_rtf('\\uldashd '). $string . wsd_raw_rtf('\\ulnone');

	}

	function dotdotdashunderline($string) {

		if(!is_string($string))
			$text = (string)$text;
		
		return wsd_raw_rtf('\\uldashdd '). $string . wsd_raw_rtf('\\ulnone');

	}

	function thickunderline($string) {

		if(!is_string($string))
			$text = (string)$text;
		
		return wsd_raw_rtf('\\ulth '). $string . wsd_raw_rtf('\\ulnone');

	}

	function allcaps($string) {

		if(!is_string($string))
			$text = (string)$text;
		
		return wsd_raw_rtf('\caps '). $string . wsd_raw_rtf(' \caps0');

	}
	
	function enterlistsecondlevel() { 
	
		return wsd_raw_rtf("\ilvl1\lin720 ");
	
	}
	
	function enterlistfirstlevel() {
	
		return wsd_raw_rtf("\ilvl0 ");
	
	}

	function insertsymbol($symbol, $superScriptcript = TRUE) {

		if($symbol != 'R' && $symbol != 'SM' && $symbol != 'TM')
			trigger_error('rtfClass: insertsymbol: not a valid argument', E_USER_ERROR);
		
		if($superScriptcript) {
			$praefix = '\super ';
			$suffix = ' \nosupersub';
		} else {
			$praefix = '';
			$suffix = '';
		}

		switch($symbol) {
		
			case 'R':
				return wsd_raw_rtf($praefix . chr(92) . chr(39) . 'ae' . $suffix);
			case 'TM':
				return wsd_raw_rtf($praefix . 'TM' . $suffix);
			case 'SM':
			return wsd_raw_rtf($praefix . 'SM' . $suffix);

		}
	}

	function definebookmark($identifier, $text = '') {

		if(!is_string($text))
			trigger_error('rtfClass: setbookmark: expecting string as text argument', E_USER_ERROR);

		if(!is_string($identifier))
			trigger_error('rtfClass: setbookmark: expecting string as identifier argument', E_USER_ERROR);
			
		if(in_array($identifier, $this->bookmarks))
			trigger_error('rtfClass: setbookmark: can\'t define bookmark "' . $identifier, '", bookmark already exists', E_USER_ERROR);
		else
			$this->bookmarks[] = $identifier;

		return wsd_raw_rtf("{ \bkmkstart " . $identifier . "}{ \bkmkend " . $identifier . "}");

	}

	function insertreferencepagenumber($identifier) {

		if(!is_string($identifier))
			trigger_error('rtfClass: insertbookmarkreference: expecting string as identifier argument', E_USER_ERROR);

		$rtf_raw = "{\field{\*\fldinst {PAGEREF " . $identifier . "}}{\fldrslt {!updatePageReferences!}}}";

		return wsd_raw_rtf($rtf_raw);

	}
	
	function insertreferenceabovebelow($identifier) {

		if(!is_string($identifier))
			trigger_error('rtfClass: insertbookmarkabovebelow: expecting string as identifier argument', E_USER_ERROR);

		$rtf_raw = "{\field{\*\fldinst {REF " . $identifier . " \\\\p \\\\h}}{\fldrslt {!updatePageReferences!}}}";

		return wsd_raw_rtf($rtf_raw);

	}
	
	function highlight($string, $color = 'yellow') {
	
		if(!is_string($string))
			trigger_error('rtfClass: highlight: expecting string as first argument', E_USER_ERROR);
			
		if(!is_string($color))
			trigger_error('rtfClass: highlight: expecting string (color name) as second argument', E_USER_ERROR);
		
		switch($color) {
			
			case noColor:
				$colorNumber = 0;
			break;
			
			case black:
				$colorNumber = 1;
			break;
			
			case blue:
				$colorNumber = 2;
			break;
			
			case cyan:
				$colorNumber = 3;
			break;
			
			case green:
				$colorNumber = 4;
			break;
		
			case magenta:
				$colorNumber = 5;
			break;
			
			case red:
				$colorNumber = 6;
			break;
			
			case yellow:
				$colorNumber = 7;
			break;
			
			case darkblue:
				$colorNumber = 9;
			break;
			
			case darkcyan:
				$colorNumber = 10;
			break;
			
			case darkgreen:
				$colorNumber = 11;
			break;
			
			case darkmagenta:
				$colorNumber = 12;
			break;
			
			case darkred:
				$colorNumber = 13;
			break;
			
			case darkyellow:
				$colorNumber = 14;
			break;
			
			case darkgray:
				$colorNumber = 15;
			break;
			
			case lightgray:
				$colorNumber = 16;
			break;
		
			default:
				trigger_error('rtfClass: highlight: not a valid color', E_USER_ERROR);
			break;
		
	
		}
	
		return wsd_raw_rtf("\highlight" . $colorNumber . " ") . $string . wsd_raw_rtf("\highlight8 ");

	}
	
	function fontcolor($text, $color = 'black') {
	
		if(!is_string($text))
			trigger_error('rtfClass: fontcolor: expecting string as text argument', E_USER_ERROR);
			
		if(!is_string($color))
			trigger_error('rtfClass: fontcolor: expecting string (color name) as second argument', E_USER_ERROR);
	
		switch($color) {
		
			case black:
				$colorNumber = 1;
			break;
			
			case blue:
				$colorNumber = 2;
			break;
			
			case cyan:
				$colorNumber = 3;
			break;
			
			case green:
				$colorNumber = 4;
			break;
		
			case magenta:
				$colorNumber = 5;
			break;
			
			case red:
				$colorNumber = 6;
			break;
			
			case yellow:
				$colorNumber = 7;
			break;
			
			case darkblue:
				$colorNumber = 9;
			break;
			
			case darkcyan:
				$colorNumber = 10;
			break;
			
			case darkgreen:
				$colorNumber = 11;
			break;
			
			case darkmagenta:
				$colorNumber = 12;
			break;
			
			case darkred:
				$colorNumber = 13;
			break;
			
			case darkyellow:
				$colorNumber = 14;
			break;
			
			case darkgray:
				$colorNumber = 15;
			break;
			
			case lightgray:
				$colorNumber = 16;
			break;
			
			case white:
				$colorNumber = 8;
			break;
		
			default:
				trigger_error('rtfClass: setfontcolor: not a valid color', E_USER_ERROR);
			break;
			
		}
		
		return wsd_raw_rtf("\cf" . $colorNumber . ' ') . $text .  wsd_raw_rtf("\cf1 ");
		
	}
	
	function newParagraph($styleName) {
		
		return wsd_raw_rtf('/pard/plain /par' . $this->paragraphStyles[$styleName]); 
		
	}
	
	function parametersToRTFString($parameters){
		
		$string = '';
		
		foreach($paramters as $attribute => $value){
			
			$string .= '\\';
			
			switch($parameters){
				
				case 'font family':
					$string .= 'f44';
				break;
				
				case 'font size':
					$string .= 'fs' . $value * 2;
				break;
				
				case 'font color':
					$string .= 'PLACEHOLDER';
				break;
				
				case 'text direction':
					
					switch($value){
						
						case 'rightToLeft':
							$string .= 'ltrch';
						break;
						
						case 'leftToRight':
							$string .= 'rtlch';
						break;
						
					}
					
				break;
				
				case 'text align':
					$string .= 'PLACEHOLDER';
				break;
				
				case 'bold':
					$string .= 'b';
				break;
				
				case 'underline':
					$string .= 'ul';
				break;
				
				case 'italic':
					$string .= 'i';
				break;
				
				
				case 'highlight':
					
					switch($value) {
			
						case 'noColor':
							$colorNumber = 0;
						break;
						
						case 'black':
							$colorNumber = 1;
						break;
						
						case 'blue':
							$colorNumber = 2;
						break;
						
						case 'cyan':
							$colorNumber = 3;
						break;
						
						case 'green':
							$colorNumber = 4;
						break;
					
						case 'magenta':
							$colorNumber = 5;
						break;
						
						case 'red':
							$colorNumber = 6;
						break;
						
						case 'yellow':
							$colorNumber = 7;
						break;
						
						case 'darkblue':
							$colorNumber = 9;
						break;
						
						case 'darkcyan':
							$colorNumber = 10;
						break;
						
						case 'darkgreen':
							$colorNumber = 11;
						break;
						
						case 'darkmagenta':
							$colorNumber = 12;
						break;
						
						case 'darkred':
							$colorNumber = 13;
						break;
						
						case 'darkyellow':
							$colorNumber = 14;
						break;
						
						case 'darkgray':
							$colorNumber = 15;
						break;
						
						case 'lightgray':
							$colorNumber = 16;
						break;
					
						default:
							trigger_error('rtfClass: highlight: not a valid color', E_USER_ERROR);
						break;
					
					}
					
					$string .= 'highlight' . $colorNumber;
					
				break;
				
			}
			
			$string .= ' ';
			
		}
			
		return $string;
		
	}
	
	function addNewParagraphStyle($styleName, $parameters){
		
		$tempArray = array(
			
			'font family' => 'Times new Roman',
			'font size' => 12,
			'font color' => 'black',
			'text direction' => 'left',
			'text align' => 'left',
			'list style' => 'none',
			'bold' => false,
			'underline' => false,
			'italic' => false,
			'highlight' => 'none',
			
		);
		
		$this->paragraphStyles[$styleName] = $this->parametersToRTFString(array_merge($tempArray, $parameters));
		
	}
	
}

?>