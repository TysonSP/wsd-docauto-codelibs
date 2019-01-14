<?php

class docxMathClass {
	
	var $mathObject = '';
	var $endMathObjectFlag = true;
	
	var $ColorScheme;
	var $Formatting;
	
	function __construct(){
		
		//BMO COLOUR SCHEME
		$this->ColorScheme = array(
	
			'yellow' => 'e8ff1e',
			'grey' => '444436',
			'blue' => '2354c6',
			'light blue' => '80dfff',
			'red' => 'e00b0b',
			'green' => '0ee00b',
			'purple' => 'e00bd8',
			'pink' => 'ff7ce0',
			'white' => 'ffffff',
			'black' => '000000',
	
		);
		
		//Global Formatting - Default Values
		$this->Formatting['docxClass:globalFormattingOptions'] = array(
			'italic' => true, 
			'bold' => false,	
			'underline' => false,	
			'font size' => 10,	
			'color' => 'black',	
			'text align' => 'justified',	
			'font family' => 'Cambria Math',			
		);
		
		//Formatting Option to fix Pre and Post Table paragraph spacing
		$this->tableFormatting['docxClass:prepostSpacingFix'] = array(
			'text spacing' => array(
				'before' => 0, 
				'after' => 0, 
				'line' => 1
			)
		);
		
		$this->startMathObject();
		
	}
	
	function updateColorSchema($parameters = array()){
		
		foreach($parameters as $color => $hexColor)
			$this->ColorScheme[$color] = $hexColor;	
		
	}
	
	function updateGlobalFormatting($parameters = array()){
		
		if(is_array($parameters) && count($parameters) > 0)
			foreach($parameters as $attributeIndex => $attributeValue)
				$this->Formatting['docxClass:globalFormattingOptions'][$attributeIndex] = $attributeValue;	
		
	}
	
	
	function parametersToXMLString($parameters = array(), $propertyTag = null, $formattingMarker = null){
		
		// if(is_array($parameters) == false){
			
			// $errorMessage["Error"] = "parameters $parameters propertyTag $propertyTag formattingMarker $formattingMarker";
			// $errorMessage["Line"] = debug_backtrace()[0]['line'];
			// $errorMessage["backtrace"] = debug_backtrace();
			
			// trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		// }

		$parameters = array_merge($this->Formatting['docxClass:globalFormattingOptions'], $parameters);

		$string = '';
			
		if($propertyTag == null && in_array($propertyTag, array('fPr', 'pPr')))
			trigger_error("docxTableClass: parametersToXMLString: property tag is unsupported",E_USER_ERROR);
		
		// $formattingMarker = '';
		
		//Default Formatting Markers
		// if(in_array($propertyTag, array('fPr')) && $formattingMarker == null)
			// $formattingMarker = 'm';
		
		//Default Formatting Markers
		if(in_array($propertyTag, array('rPr', 'pPr')) && $formattingMarker == null)
			$formattingMarker = 'w';
		
		if($formattingMarker == '')
			trigger_error('formatting marker not set for property tag',E_USER_ERROR); //-TEH
		
		$string .= '<'.$formattingMarker.':'.$propertyTag.'>';
		
		switch($propertyTag){
			
			// case 'fPr':{
				
				// foreach($parameters as $key => $value){
				
					// switch(strtolower($key)){

						// case 'slash direction':
							
							// switch($value){
								
								// case 'vertical':
									// $string .= '<m:type m:val="sta"/>';
								// break;
								
								// case 'diagonal':
									// $string .= '<m:type m:val="skw"/>';
								// break;
								
								// case 'horizontal':
									// $string .= '<m:type m:val="lin"/>';
								// break;
								
							// }
							
						// break;
						
					// }
					
				// }
				
			// }break;
			
			case 'pPr':{
				
				foreach($parameters as $key => $value){
				
					switch(strtolower($key)){

						case 'paragraph style':
						
						
								$string .= '<w:pStyle w:val="'.$value.'"/>';
							
						break;
						
						case 'text align':
							
							
								if($value != '' && $value != null){
									
									switch(strtolower($value)){
										
										case 'justified':
										case 'both':
											$string .= '<w:jc w:val="both"/>';
										break;
										
										case 'left':
											$string .= '<w:jc w:val="left"/>';
										break;
										case 'center':
										case 'right':
										case 'end':
											$string .= '<w:jc w:val="'.strtolower($value).'"/>';
										break;
										
										default:
											trigger_error('docxTableClass: parametersToXMLString: Unsupported Text Alignment',E_USER_ERROR);  
										break;
										
									}
									
								}
							
						break;
						
						case 'indent text':
							
							
							
								$string .= '<w:ind ';
								
								if(array_key_exists('left', $value) || array_key_exists('Left', $value))
									$string .= 'w:left="'. round($value['left'] * 566.67) .'" ';
									
								if(array_key_exists('right', $value) || array_key_exists('Right', $value))
									$string .= ' w:right="'. round($value['right']*566.67).'"';
								
								$string .= '/>';
							
							
						break;
						
						
						case 'text spacing':
							
							
								
								$string .= '<w:spacing ';
								
								if(array_key_exists('before', $value) || array_key_exists('Before', $value))
									$string .= 'w:before="'. $value['before']*20 .'" ';
									
								if(array_key_exists('after', $value) || array_key_exists('After', $value))
									$string .= ' w:after="'. $value['after']*20 .'"';
								
								$string .= '/>';
								
							
							
							
						break;
						
					}
					
				}
				
			}break;
			
			case 'rPr':{
				
				$formattingApplied = array('boldItalic' => false);
				
				switch($formattingMarker){
					
					case 'm':
						
						foreach($parameters as $key => $value){
				
							switch(strtolower($key)){
								
								case 'bold':
									
									if($parameters['bold'] == true && $parameters['italic'] == true && !$formattingApplied['boldItalic']){
										
										$string .= '<m:sty m:val="bi"/>';
										$formattingApplied['boldItalic'] = true;
										
									}elseif(!$formattingApplied['boldItalic']){
										
										if($value == true)
											$string .= '<m:sty m:val="b"/>';
										// elseif($value == false)
											// $string .= '<m:sty m:val="bi"/>';
									}
									
								break;
								
								case 'italic':
									
									if($parameters['bold'] == true && $parameters['italic'] == true && !$formattingApplied['boldItalic']){
										
										$string .= '<m:sty m:val="bi"/>';
										$formattingApplied['boldItalic'] = true;
										
									}elseif(!$formattingApplied['boldItalic']){
										
										if($value == true)
											$string .= '<m:sty m:val="i"/>';
										elseif($value == false)
											$string .= '<m:sty m:val="p"/>';
									}
									
									
										
								break;
								
								case 'bolditalic':
								case 'italicbold':
								
									if($value == true)
										$string .= '<m:sty m:val="bi"/><m:sty m:val="i"/>';
									// elseif($value == false)
										// $string .= '<m:sty m:val="bi"/><m:sty m:val="b"/>';
										
								break;
								
								// case 'underline':
									
									// if($value == true)
										// $string .= '<w:u w:val="single"/>';
									// else
										// $string .= '<w:u w:val="none"/>';
										
								// break;
								
								case 'superscript':
								
									
									if($value == true)
										$string .= '<w:vertAlign w:val="superscript"/>';
									else
										$string .= '<w:vertAlign w:val="baseline"/>';
									
								break;
								
								
								case 'strikethrough':
								
								
									if($value == true)
										$string .= '<w:strike/>';
									else
										$string .= '<w:strike w:val="0"/>';
									
								break;	
								
								case 'double strikethrough':
								
									
									if($value == true)
										$string .= '<w:dstrike/>';
									else
										$string .= '<w:dstrike w:val="0"/>';
								break;
								
								case 'subscript':
								
								
									if($value == true)
										$string .= '<w:vertAlign w:val="subscript"/>';
									else
										$string .= '<w:vertAlign w:val="baseline"/>';
								break;
								
								case 'small caps':
								
							
									if($value == true)
										$string .= '<w:smallCaps/>';
									else
										$string .= '<w:smallCaps w:val="0"/>';
										
								break;
								
								case 'all caps':
									
								
									if($value == true)
										$string .= '<w:caps/>';
									else
										$string .= '<w:caps w:val="0"/>';
								
								break;
								
								case 'hide':
								case 'hidden':
									
								
									if($value == true)
										$string .= '<w:vanish/>';
									else
										$string .= '<w:vanish w:val="0"/>';
									
								break;
								
								
								case 'font family':
									
									
										$string .= '<w:rFonts w:ascii="'.$value.'" w:hAnsi="'.$value.'"/>';
									
								break;
								
								case 'font size':
									
									if(is_int($value))
										$string .= '<w:sz w:val="'.($value*2).'"/>';
									else
										trigger_error('docxTableClass: parametersToXMLString: unsupported text size value: ' . $value,E_USER_ERROR);
								break;
								
								case 'font color':
									
								
									if(in_array(strtolower($value),array_keys($this->ColorScheme)))
										$string .= '<w:color w:val="'.$this->ColorScheme[strtolower($value)].'"/>';
									elseif(ctype_xdigit($value))
										$string .= '<w:color w:val="'.$value.'"/>';
									else
										trigger_error('docxTableClass: parametersToXMLString: unsupported table text color: ' . $value . ', not found in docxtableclass color scheme OR unsupported Hex Color',E_USER_ERROR);
										
								break;

							}
							
						}
						
					break;
					
					case 'w':
					
						foreach($parameters as $key => $value){
				
							switch(strtolower($key)){

								case 'superscript':
								
								
									if($value == true)
										$string .= '<w:vertAlign w:val="superscript"/>';
									else
										$string .= '<w:vertAlign w:val="baseline"/>';
										
								break;
								
								
								case 'strikethrough':
								
									
									if($value == true)
										$string .= '<w:strike/>';
									else
										$string .= '<w:strike w:val="0"/>';
										
								break;	
								
								case 'double strikethrough':
								
								
									if($value == true)
										$string .= '<w:dstrike/>';
									else
										$string .= '<w:dstrike w:val="0"/>';
								break;
								
								case 'subscript':
								
									
									if($value == true)
										$string .= '<w:vertAlign w:val="subscript"/>';
									else
										$string .= '<w:vertAlign w:val="baseline"/>';
								break;
								
								case 'small caps':
								
							
									if($value == true)
										$string .= '<w:smallCaps/>';
									else
										$string .= '<w:smallCaps w:val="0"/>';
										
								break;
								
								case 'all caps':
									
								
									if($value == true)
										$string .= '<w:caps/>';
									else
										$string .= '<w:caps w:val="0"/>';
									
								break;
								
								case 'hide':
								case 'hidden':
									
							
									if($value == true)
										$string .= '<w:vanish/>';
									else
										$string .= '<w:vanish w:val="0"/>';
										
								break;
								
								
								case 'font family':
									
									
									$string .= '<w:rFonts w:ascii="'.$value.'" w:hAnsi="'.$value.'"/>';
									
								break;
								
								case 'font size':
									
									if(is_int($value))
										$string .= '<w:sz w:val="'.($value*2).'"/>';
									else
										trigger_error('docxTableClass: parametersToXMLString: unsupported text size value: ' . $value,E_USER_ERROR);
								break;
								
								case 'font color':
									
									
									if(in_array(strtolower($value),array_keys($this->ColorScheme)))
										$string .= '<w:color w:val="'.$this->ColorScheme[strtolower($value)].'"/>';
									elseif(ctype_xdigit($value))
										$string .= '<w:color w:val="'.$value.'"/>';
									else
										trigger_error('docxTableClass: parametersToXMLString: unsupported table text color: ' . $value . ', not found in docxtableclass color scheme OR unsupported Hex Color',E_USER_ERROR);
										
								break;

							}
							
						}
					
					break;
					
				}
					
				// $string .= '<w:rFonts w:ascii="Cambria Math" w:eastAsiaTheme="minorEastAsia" w:hAnsi="Cambria Math"/>';
				
			}break;
			
		}
		
		$string .= '</'.$formattingMarker.':'.$propertyTag.'>';
		return $string;
		
	}
	
	function inlineParametersToXMLString($formattedText){

		return  '{RAWTEXT}' . $formattedText . '{RAWTEXT}';
		
	}
	
	function startMathObject($parameters = array()){
		
		$this->mathObject .='<w:p>';
		// $this->mathObject .= $this->parametersToXMLString($parameters,'pPr');
		$this->mathObject .='<m:oMathPara><m:oMath>';
		
	}
	
	function endMathObject(){
		
		if($this->endMathObjectFlag){
			$this->mathObject .= '</m:oMath></m:oMathPara></w:p>';
			$this->endMathObjectFlag = false;
		}
	}
	
	function addText($content, $parameters = array()){
		
		$this->mathObject .= '<m:r>';
			$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
			$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
				$this->mathObject .= '<m:t>' . $this->inlineParametersToXMLString($content) . '</m:t>';
		$this->mathObject .= '</m:r>';
		
	}
	
	function startFraction($style = 'horizontal', $parameters = array()){
		
		$this->mathObject .= '<m:f>';
			$this->mathObject .= '<m:fPr>';
			
				// if(in_array('slash direction',array_keys($parameters)))
					// $style = $parameters['slash direction'];
				// else
					// $style = $this->Formatting['docxClass:globalFormattingOptions']['slash direction'];
				
				
				switch($style){
							
					case 'vertical':
					case '|':
						$this->mathObject .= '<m:type m:val="lin"/>';
					break;
					
					case 'diagonal':
					case '/':
						$this->mathObject .= '<m:type m:val="skw"/>';
					break;
					
					case 'horizontal':
					case '-':
						$this->mathObject .= '<m:type m:val="sta"/>';
					break;
					
				}
			
				$this->mathObject .= '<m:ctrlPr>';
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr');
				$this->mathObject .= '</m:ctrlPr>';
			
			$this->mathObject .= '</m:fPr>';
	
	}
	
	function startFractionDenominator(){
		
		//Denominator
			$this->mathObject .= '<m:den>';
				// $this->mathObject .= '<m:r>' . $this->parametersToXMLString($parameters,'rPr', 'm') . $this->parametersToXMLString($parameters,'rPr', 'w');
					// $this->mathObject .= '<m:t>'.$this->inlineParametersToXMLString($denominator).'</m:t>';
				// $this->mathObject .= '</m:r>';
	}
	
	function endFractionDenominator(){
		$this->mathObject .= '</m:den>';
	}
	
	function startFractionNumerator(){
		
		//Numerator
		$this->mathObject .= '<m:num>';
			// $this->mathObject .= '<m:r>' . $this->parametersToXMLString($parameters,'rPr', 'm') . $this->parametersToXMLString($parameters,'rPr', 'w');
				// $this->mathObject .= '<m:t>'.$this->inlineParametersToXMLString($numerator).'</m:t>';
			// $this->mathObject .= '</m:r>';	
	}
	
	function endFractionNumerator(){
		$this->mathObject .= '</m:num>';	
	}
	
	function endFraction(){
		$this->mathObject .= '</m:f>';
	}
	
	function startSuperScript($parameters = array()){
	
		$this->mathObject .= '<m:sSup>';
		
			$this->mathObject .= '<m:sSupPr>';
			
				$this->mathObject .= '<m:ctrlPr>';
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
				$this->mathObject .= '</m:ctrlPr>';
			
			$this->mathObject .= '</m:sSupPr>';
			
			$this->mathObject .= '<m:e>';
			
				// $this->mathObject .= '<m:r>';
					// $this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
					// $this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
					// $this->mathObject .= '<m:t>'.$this->inlineParametersToXMLString($above).'</m:t>';
				// $this->mathObject .= '</m:r>';
			// $this->mathObject .= '</m:e>';
			
		// $this->mathObject .= '</m:nary>';
		
	}
	
	function endSuperScript($above = null,$parameters = array()){
		
		$this->mathObject .= '</m:e>';
		
		if($above == null)
			trigger_error('missing parameter: super script value',E_USER_ERROR); //-TEH
		else
			$this->mathObject .= '<m:sup>';
				$this->mathObject .= '<m:r>';
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
					$this->mathObject .= '<m:t>'.$this->inlineParametersToXMLString($above).'</m:t>';
				$this->mathObject .= '</m:r>';
			$this->mathObject .= '</m:sup>';
		
		$this->mathObject .= '</m:sSup>';
		
	}
	
	function startSubScript($parameters = array()){
	
		$this->mathObject .= '<m:sSub>';
		
			$this->mathObject .= '<m:sSubPr>';
			
				$this->mathObject .= '<m:ctrlPr>';
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
				$this->mathObject .= '</m:ctrlPr>';
			
			$this->mathObject .= '</m:sSubPr>';
			
			$this->mathObject .= '<m:e>';
			
				// $this->mathObject .= '<m:r>';
					// $this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
					// $this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
					// $this->mathObject .= '<m:t>'.$this->inlineParametersToXMLString($above).'</m:t>';
				// $this->mathObject .= '</m:r>';
			// $this->mathObject .= '</m:e>';
			
		// $this->mathObject .= '</m:nary>';
		
	}
	
	function endSubScript($above = null,$parameters = array()){
		
		$this->mathObject .= '</m:e>';
		
		if($above == null)
			trigger_error('missing parameter: super script value',E_USER_ERROR); //-TEH
		else
			$this->mathObject .= '<m:sub>';
				$this->mathObject .= '<m:r>';
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
					$this->mathObject .= '<m:t>'.$this->inlineParametersToXMLString($above).'</m:t>';
				$this->mathObject .= '</m:r>';
			$this->mathObject .= '</m:sub>';
		
		$this->mathObject .= '</m:sSub>';
		
	}
	
	function startSurd($deg = null,$parameters = array()){
	
		$this->mathObject .= '<m:rad>';
		
			$this->mathObject .= '<m:radPr>';
			
				if($deg == null)
					$this->mathObject .= '<m:degHide m:val="1"/>';
				
				$this->mathObject .= '<m:ctrlPr>';
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
				$this->mathObject .= '</m:ctrlPr>';
		
			$this->mathObject .= '</m:radPr>';
			
			if($deg == null)
				$this->mathObject .= '<m:deg/>';
			else{
				$this->mathObject .= '<m:deg>';
					$this->mathObject .= '<m:r>';
						$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
						$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
						$this->mathObject .= '<m:t>'.$this->inlineParametersToXMLString($deg).'</m:t>';
					$this->mathObject .= '</m:r>';
				$this->mathObject .= '</m:deg>';
			}

			$this->mathObject .= '<m:e>';
			
				// $this->mathObject .= '<m:r>';
					// $this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
					// $this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
					// $this->mathObject .= '<m:t>'.$this->inlineParametersToXMLString($above).'</m:t>';
				// $this->mathObject .= '</m:r>';
			// $this->mathObject .= '</m:e>';
			
		// $this->mathObject .= '</m:nary>';
		
	}
	
	function endSurd(){
		
			$this->mathObject .= '</m:e>';
		$this->mathObject .= '</m:rad>';
		
	}
	
	function startSum($above = null, $below = null, $parameters = array()){
		
		$this->startMathOperator('∑', $above, $below, $parameters);
		
		if($above != null){
			$this->startMathOperatorSuper();
				$this->addText($above);
			$this->endMathOperatorSuper();
		}
		
		if($below != null){
			$this->startMathOperatorSub();
				$this->addText($below);
			$this->endMathOperatorSub();
		}
		
		$this->startMathOperatorBase();
		
	}
	
	function endSum(){
		$this->endMathOperatorBase();
		$this->endMathOperator();
	}
	
	function startMathOperator($symbol = '∑', $above = null, $below = null, $parameters = array()){
	
		$this->mathObject .= '<m:nary>';
		
			$this->mathObject .= '<m:naryPr>';
				$this->mathObject .= '<m:chr m:val="'.$symbol.'"/>';
				
					$this->mathObject .= '<m:limLoc m:val="undOvr"/>';
					
					if($above == null)
						$this->mathObject .= '<m:subHide m:val="1"/>';
					if($below == null)
						$this->mathObject .= '<m:supHide m:val="1"/>';
					
					$this->mathObject .= '<m:limLoc m:val="undOvr"/>';
					
					$this->mathObject .= '<m:ctrlPr>';
						$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'm');
						$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
					$this->mathObject .= '</m:ctrlPr>';
			
			$this->mathObject .= '</m:naryPr>';
			
			if($below == null)
				$this->mathObject .= '<m:sub/>';
			
			if($above == null)
				$this->mathObject .= '<m:sup/>';
			
	}
	
	function startMathOperatorSuper(){
		$this->mathObject .= '<m:sup>';
	}
	
	function endMathOperatorSuper(){
		$this->mathObject .= '</m:sup>';
	}
	
	function startMathOperatorSub(){
		$this->mathObject .= '<m:sub>';
	}
	
	function endMathOperatorSub(){
		$this->mathObject .= '</m:sub>';
	}
	
	function startMathOperatorBase(){
		$this->mathObject .= '<m:e>';
	}
	
	function endMathOperatorBase(){
		$this->mathObject .= '</m:e>';
	}
	
	function endMathOperator(){
		$this->mathObject .= '</m:nary>';
	}
	
	function startBrackets($style = null, $parameters = array()){
		
		$this->mathObject .= '<m:d>';
			$this->mathObject .= '<m:dPr>';
				
				
				if($style == null){
					$this->mathObject .= '<m:begChr m:val="("/>';
					$this->mathObject .= '<m:endChr m:val=")"/>';
				}else{
					
					if(strlen($style) > 2)
						trigger_error('to many brackets in style parameter',E_USER_ERROR); //-TEH
					
					$style = str_split($style);
					$this->mathObject .= '<m:begChr m:val="'.$style[0].'"/>';
					$this->mathObject .= '<m:endChr m:val="'.$style[1].'"/>';
					
				}
				
				$this->mathObject .= '<m:ctrlPr>';
					$this->mathObject .= $this->parametersToXMLString($parameters,'rPr', 'w');
				$this->mathObject .= '</m:ctrlPr>';
			$this->mathObject .= '</m:dPr><m:e>';
			
	}
	
	function endBrackets(){
		
			$this->mathObject .= '</m:e>';
		$this->mathObject .= '</m:d>';
		
	}
	
	function getSymbol($symbol){
		
		switch($symbol){
			
			case 'plusminus':
				return '±';
			break;
			case 'infinty':
				return '∞';
			break;
			case 'equal':
			case 'equals':
				return '=';
			break;
			case 'notEqual':
				return '≠';
			break;
			case 'approximatly':
				return '~';
			break;
			case 'multiply':
			case 'times':
				return '×';
			break;
			case 'divide':
				return '÷';
			break;
			case 'plus':
			case 'add':
				return '+';
			break;
			case 'minus':
			case 'subtract':
				return '-';
			break;
			case 'factorial':
				return '!';
			break;
			case 'modulus':
			case 'percent':
				return '%';
			break;
			case 'lessthan':
				return '&lt;';
			break;
			case 'greaterthan':
				return '&gt;';
			break;
			case 'lessthanequalto':
				return '≤';
			break;
			case 'greaterthanequalto':
				return '≥';
			break;
			case 'sum':
				return '∑';
			break;
			case 'integral':
				return '∫';
			break;
			default:
				return '[symbolNotFound:'.$symbol.']';
			break;
		}
	}

	function flushMathObject(){
		
		$this->mathObject = '';
		$this->startMathObject();
		$this->endMathObjectFlag = true;
		
	}
	
	function renderMathObject($preserveSpacing = false){
		
		$this->endMathObject();
		
		$explodedElements = explode('{RAWTEXT}',$this->mathObject);
		$string = '';
		for($i = 0; $i < count($explodedElements); $i++){
		   if(($i + 1) % 2 != 0)
			   $string .=  wsd_raw_docx($explodedElements[$i]);
		   else
				$string .=  $explodedElements[$i];
		}
		
		if($preserveSpacing){
			
			return wsd_raw_docx('</w:t></w:r></w:p>') . $string . wsd_raw_docx('<w:p><w:r><w:t>');
				
		}else{
			
			return wsd_raw_docx('</w:t></w:r>' . $this->parametersToXMLString($this->tableFormatting['docxClass:prepostSpacingFix'], 'pPr', 'w') . '</w:p>') . $string . wsd_raw_docx('<w:p>' . $this->parametersToXMLString($this->tableFormatting['docxClass:prepostSpacingFix'], 'pPr', 'w') . '<w:r><w:t>');
			
		}
		
		// return wsd_raw_docx('</w:t></w:r></w:p>') . $string . wsd_raw_docx('<w:p><w:r><w:t>');
		// return $string;
		
		// return wsd_raw_docx('</w:t></w:r></w:p>' . $this->mathObject . '</w:p><w:p><w:r><w:t>');
		
	} 
	
}

?>