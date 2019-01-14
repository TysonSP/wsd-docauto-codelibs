<?php
 
class docxClass {
 
    private $properties = array(
		'Italic' => array(
			'Applies' => false, 
			'Reference' => 'i'
		),
		'Bold' => array(
			'Applies' => false, 
			'Reference' => 'b'
		),
	);
	
    private $string = array();
     
    private $subString = array();
 
    private $buffer;
     
 
    function __construct() {
     
    }
    
	
	/* function checkForDocxMarkup($input, $formatting){
		
		if(strstr($input,'</w:t></w:r>') !== false){
			
			//remove </w:t>
			$input = substr($input, 12, strlen($input));
			
			//get previous formattingOptions applied
			$start = '<w:rPr>';
			$end = '</w:rPr>';
			$posStart = strpos($input,$start) + strlen($start);
			$posEnd = strpos($input,$end) - $posStart;
			$formattingOptions =  substr($input,$posStart,$posEnd);
			
			return array($formattingOptions, strpos($input,'<w:t xml:space="preserve">') !== false);
			
			//get previous text - Check if spacing has been previously preserverved
			if()
				$start = '<w:t xml:space="preserve">';
			else
				$start = '<w:t>';
			
			$end = '</w:t></w:r><w:r><w:t>';
			
			$posStart = strpos($input,$start) + strlen($start);
			$posEnd = strpos($input,$end) - $posStart;
			$previousText =  substr($input,$posStart,$posEnd);
			
			// if the previous text has a space at the beginning preserve the space
			if(substr($previousText,0,1) == ' ')
				$spacing = '<w:t xml:space="preserve"> </w:t>';
			else
				$spacing = '';
				
			
			// </w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:t>Underlyings</w:t></w:r><w:r><w:t>
			
			return '</w:t>'.$spacing.'</w:r><w:r><w:rPr>'.$formattingOptions.'</w:rPr><w:t>'.$previousText.'</w:t></w:r><w:r><w:t>');
			
		}else{
			
			// if(strpos($input,'<w:t xml:space="preserve">') !== false)
				$start = '<w:t xml:space="preserve">';
			// else
				// $start = '<w:t>';
			
			return '</w:t>'.$spacing.'</w:r><w:r><w:rPr>'.$formatting.'</w:rPr><w:t>'.$input.'</w:t></w:r><w:r><w:t>');
		
		}
		
	}
	 */
    function after ($this2, $inthat)
    {
     
        if (!is_bool(strpos($inthat, $this2)))
        return substr($inthat, strpos($inthat,$this2)+strlen($this2));
    }
 
    function after_last ($this2, $inthat)
    {
        if (!is_bool(strrevpos($inthat, $this2)))
        return substr($inthat, strrevpos($inthat,$this2)+strlen($this2));
    }
    function before ($this2, $inthat)
    {
        return substr($inthat, 0, strpos($inthat, $this2));
    }
 
    function before_last ($this2, $inthat)
    {
        return substr($inthat, 0, strrevpos($inthat, $this2));
    }
 
    function between ($this2, $that, $inthat)
    {
        return $this->before ($that, $this->after($this2, $inthat));
    }
 
    function between_last ($this2, $that, $inthat)
    {
     return $this->after_last($this2, before_last($that, $inthat));
    }
     
    function softreturn() {
        
        return wsd_raw_docx('</w:t></w:r><w:r><w:br/></w:r><w:r><w:t>');
        
    } 

    function startItalic(){
     
        $this->properties['Italic']['Applies'] = true;
         
    }
     
    function endItalic(){
     
        $this->properties['Italic']['Applies'] = false;
         
    }
     
    function startBold(){
     
        $this->properties['Bold']['Applies'] = true;
         
    }
     
    function endBold(){
     
        $this->properties['Bold']['Applies'] = false;
         
    }
    
    function clear() {
     
        $this->buffer = '';
     
    }
     
    function print($string) {
         
        $properties2 = array();
     
        foreach($this->properties as $key => $property){
            if($property['Applies'] == true)
                $properties2[] = $property['Reference'];
        }
     
        $this->string[] = array(
            'styles' => $properties2,
            'content' => $string
            );
     
    }
     
    function flush() {
     
        $finalString = '';
     
        foreach ($this->string as $string){
            $finalString .= '<w:r>
              <w:rPr>';
         
            foreach ($string['styles'] as $style){          
                $finalString .= '<w:' . $style . '/>';
            }
             
            $finalString .= '</w:rPr>
              <w:t>' . $string['content'] . '</w:t>                     
            </w:r>';
        }
         
        return $finalString;
     
    }
	
	function pageReferenceSource($object1, $reference){		
		
		$object1 = trim($object1, chr(31));
		
		$object1 = str_replace('&', '&amp;', $object1);
		$object1 = str_replace('&&amp;', '&amp;', $object1);
		$object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
		$object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
		
		$finalString = '';
		if(strpos($object1, '<w:r>') !== false){
			
			while (strpos($object1, '</w:r>') !== false){
				if($this->before('</w:t></w:r><w:r>', $object1) != ''){
					$finalString .= (strpos($this->before('</w:t></w:r><w:r>', $object1), '<w:rPr>') === false ? '<w:r><w:rPr></w:rPr><w:t>' : '') . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
				}
				$object1 = $this->after('</w:t></w:r>', $object1);
				
				$finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
				$object1 = $this->after('</w:t></w:r>', $object1);
			}
			$object1 = $this->after('<w:r><w:t>', $object1);
		}
		
		if($object1 != ''){
			
			$finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
		}
		
	
		$finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
		$finalString = '</w:t></w:r><w:r><w:fldChar w:fldCharType="begin"/></w:r><w:r><w:instrText xml:space="preserve"> REF _Ref' . $reference . ' \h </w:instrText></w:r><w:r><w:fldChar w:fldCharType="separate"/></w:r><w:r><w:t>' . $object1 . '</w:t></w:r><w:r><w:fldChar w:fldCharType="end"/></w:r><w:r><w:t>';
		$finalString = str_replace('', '', $finalString);
		return wsd_raw_docx($finalString);
		
	}
	
	function pageReferenceTarget($object1, $reference){
		
		global $refId;
		
		$refId++;
		
		$object1 = trim($object1, chr(31));
		
		$object1 = str_replace('&', '&amp;', $object1);
		$object1 = str_replace('&&amp;', '&amp;', $object1);
		$object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
		$object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
		
		$finalString = '';
		if(strpos($object1, '<w:r>') !== false){
			
			while (strpos($object1, '</w:r>') !== false){
				if($this->before('</w:t></w:r><w:r>', $object1) != ''){
					$finalString .= (strpos($this->before('</w:t></w:r><w:r>', $object1), '<w:rPr>') === false ? '<w:r><w:rPr></w:rPr><w:t>' : '') . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
				}
				$object1 = $this->after('</w:t></w:r>', $object1);
				
				$finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
				$object1 = $this->after('</w:t></w:r>', $object1);
			}
			$object1 = $this->after('<w:r><w:t>', $object1);
		}
		
		if($object1 != ''){
			
			$finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
		}
		
	
		$finalString = str_replace('', '', $finalString);
		$finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
		return wsd_raw_docx('</w:t>></w:r><w:bookmarkStart w:id="' . $refId . '" w:name="_Ref' . $reference . '"/>' . $finalString . '<w:bookmarkEnd w:id="' . $refId . '"/><w:r><w:t>');
		
	}
	
    function highlight($object1, $colour) {
     
        $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:highlight w:val="' . $colour . '"/>', $finalString);
        $finalString = str_replace('', '', $finalString);
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
     
         
    }
     
    function underline($object1) {
     
        $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:u w:val="single"/>', $finalString);
        $finalString = str_replace('', '', $finalString);
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('', '', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
     
         
    }
    
	function removeunderline($text) {
     
        $xmlString = $this->addProperty($text, '<w:u w:val="none"/>');    
        return wsd_raw_docx($xmlString);
     
    }
	
    function doubleunderline($object1) {
     
        $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:u w:val="double"/>', $finalString);
        $finalString = str_replace('', '', $finalString);
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
        return $finalString;
     
         
    }
	
	function color($text, $color) {

		$xmlString = $this->addProperty($text, '<w:color w:val="'. $color . '"/>');
		
		return wsd_raw_docx($xmlString);
	
		
	}
     
    function italic($object1) {
		
		
		$xmlString = $this->addProperty($object1, '<w:i/>');
		return wsd_raw_docx($xmlString);
		/*
		
		// return $this->applyFormattingMarkup('<b/>',$object1);
		
		// return $this->applyFormattingParameter($object1, 'b');
		// return wsd_raw_docx($this->applyFormattingParameter($object1, 'b'));
		
        $object1 = trim($object1, chr(31));
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&&amp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:t></w:r>', $object1);
            }
             
        }
         
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:i/>', $finalString);
        $finalString = str_replace('', '', $finalString);
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('', '', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
		
		// trigger_error(print_r('</w:t></w:r>' . $finalString . '<w:r><w:t>',true),E_USER_ERROR); //-TEH
		
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
     
         */
    }
    
	function italicsuperscript($object1) {
		
		return $this->italic($this->superscript($object1));
		
	}
	
	function boldsuperscript($object1) {
		
		return $this->bold($this->superscript($object1));
		
	}
	/*
    function color($object1, $color) {
 
        $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:color w:val="'. $color . '"/>', $finalString);
        $finalString = str_replace('', '', $finalString);
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
     
         
    }
     */
    function bold($object1) {
		
		  $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:b/>', $finalString);
        // if(strpos($finalString, '') !== false)
            // trigger_error($finalString, E_USER_ERROR);
        $finalString = str_replace('', '', $finalString);
         
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
         
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
		
		// $xmlString = $this->addProperty($object1, '<w:b/>');
		// return wsd_raw_docx($xmlString);
	
		// $xmlString = $this->addProperty($text, '<w:i/>');
		// return wsd_raw_docx($xmlString);
		
		/* // return wsd_raw_docx($this->applyFormattingParameter($object1, 'i'));
		
        $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:b/>', $finalString);
        // if(strpos($finalString, '') !== false)
            // trigger_error($finalString, E_USER_ERROR);
        $finalString = str_replace('', '', $finalString);
         
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
         
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>'); */
     
         
    }
    
	function removebold($text) {
 
        $xmlString = $this->addProperty($text, '<w:b w:val="0"/>');
        return wsd_raw_docx($xmlString);
		
    }
	
    function subscript($object1) {
		
        $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:vertAlign w:val="subscript"/>', $finalString);
        $finalString = str_replace('', '', $finalString);
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('', '', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
         
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
     
         
    }
     
    function superscript($object1) {
         
        $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
		
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
		
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
		
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:vertAlign w:val="superscript"/>', $finalString);
        $finalString = str_replace('', '', $finalString);
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('', '', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
         
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
     
         
    }
    
	function removeSuperscript($object1) {
		
		$object1 = trim($object1, chr(31));
		
		if(substr($object1, 0, 12) == '</w:t></w:r>')
			$object1 = substr($object1, 12, -10);

		$object1 = str_replace('&', '&amp;', $object1);
		$object1 = str_replace('&ampamp;', '&amp;', $object1);
		$object1 = str_replace('(R)','® ', $object1);
		$object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
		$object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
		
		$finalString = '';
		if(strpos($object1, '<w:r>') !== false){
			
			while (strpos($object1, '<w:r>') !== false){
				if($this->before('</w:t></w:r><w:r>', $object1) != ''){
					$finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
				}
				$finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
				$object1 = $this->after('</w:r><w:r><w:t>', $object1);
			}
		}
		if($object1 != ''){
			
			$finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
		}
		$finalString = str_replace('<w:rPr>',  '<w:rPr><w:vertAlign w:val=0/>', $finalString);
		$finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
		
		return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
	
		
	}
	
    function allCaps($object1) {
         
        $object1 = trim($object1, chr(31));
         
        if(substr($object1, 0, 12) == '</w:t></w:r>')
            $object1 = substr($object1, 12, -10);
 
        $object1 = str_replace('&', '&amp;', $object1);
        $object1 = str_replace('&ampamp;', '&amp;', $object1);
        $object1 = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $object1);
        $object1 = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $object1);
         
        $finalString = '';
        if(strpos($object1, '<w:r>') !== false){
             
            while (strpos($object1, '<w:r>') !== false){
                if($this->before('</w:t></w:r><w:r>', $object1) != ''){
                    $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $this->before('</w:t></w:r><w:r>', $object1) . '</w:t></w:r>';
                }
                $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $object1) . '</w:r>';
                $object1 = $this->after('</w:r><w:r><w:t>', $object1);
            }
        }
        if($object1 != ''){
             
            $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $object1 . '</w:t></w:r>';
        }
        $finalString = str_replace('<w:rPr>',  '<w:rPr><w:caps/>', $finalString);
        $finalString = str_replace('', '', $finalString);
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
         
         
        return wsd_raw_docx('</w:t></w:r>' . $finalString . '<w:r><w:t>');
     
         
    }
     
	function boldReferenceLink($string, $referenceName, $referenceType = null){
    
        $refId = $this->refId;
        
        global $n;
        global $previousStyle;
        
            
        switch($referenceType){
			
            case 'page reference':
                $finalString = '</w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve"></w:t></w:r><w:r w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr><w:fldChar w:fldCharType="begin" w:dirty="true" /></w:r><w:r w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr><w:instrText xml:space="preserve"> PAGEREF ' . $referenceName . ' \h </w:instrText></w:r><w:r w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr></w:r><w:r w:rsidR="006125E7" w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr><w:fldChar w:fldCharType="separate" w:dirty="true" /></w:r><w:r><w:rPr><w:b/><w:noProof/></w:rPr><w:t>' . $string . '</w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:fldChar w:fldCharType="end"/></w:r><w:r><w:rPr><w:b/></w:rPr><w:fldChar w:fldCharType="end" w:dirty="true" /></w:r><w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">';
				
				
				//<w:rPr><w:b/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr><w:fldChar w:fldCharType="begin"/></w:r><w:r w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr><w:instrText xml:space="preserve"> PAGEREF riskFactors \h </w:instrText></w:r><w:r w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr></w:r><w:r w:rsidR="006125E7" w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr><w:fldChar w:fldCharType="separate"/></w:r><w:r w:rsidR="006125E7" w:rsidRPr="006125E7"><w:rPr><w:b/><w:noProof/></w:rPr><w:t>9</w:t></w:r><w:r w:rsidRPr="006125E7"><w:rPr><w:b/></w:rPr><w:fldChar w:fldCharType="end"/></w:r><w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">
				
            break;
            default:                
                // $finalString = '</w:t></w:r><w:hyperlink w:anchor="' . $referenceName . '"><w:r><w:rPr><w:rStyle w:val="' . $previousStyle . '"/></w:rPr><w:t>' . $string . '</w:t></w:r></w:hyperlink><w:r><w:t>';
                
				$finalString = '</w:t></w:r><w:hyperlink w:anchor="' . $referenceName . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="https://www.google.co.uk" TargetMode="External"><w:r><w:rPr><w:rStyle w:val="' . $previousStyle . '"/></w:rPr><w:t>' . $string . '</w:t></w:r></w:hyperlink><w:r><w:t>';
            break;
        }
        
        $this->refId++;
        
        $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
        $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
        
		
        return wsd_raw_docx($finalString);
    
    } 
	
    function space() {
         
        return wsd_raw_docx('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r><w:r><w:t>');
         
    }
     
    function tab($numberOfTabs = 1) {
         
		 $string = '';
		 for($i = 0; $i < $numberOfTabs; $i++){
			 $string .= '<w:tab/>';
		 }
		 
        return wsd_raw_docx('</w:t></w:r><w:r>'. $string.'</w:r><w:r><w:t>');
         
    }
     
    // function newParagraph($style = '', $parameters) {
    function newParagraph($style, $parameters) {
		
		// if($style == ''){
			
			// return wsd_raw_docx('</w:t></w:r></w:p><w:p><w:r><w:t>');			
		
		// }else{
			
			$string = '';
     
			if(in_array('centered', $parameters))
				$string .= '<w:jc w:val="center"/>';
		
			return wsd_raw_docx('</w:t></w:r></w:p><w:p><w:pPr><w:pStyle w:val="' . $style . '"/>' . $string . '</w:pPr><w:r><w:t>');			
		// }
		
         
    }
	
	function newStyle($style,$parameters) {
		
		return str_replace('</w:p<w:p>','',$this->newParagraph($style,$parameters));
		
	}
	
	function addProperty($text, $property){
	
		$text = trim($text, chr(31));
		
		$text = str_replace('&', '&amp;', $text);
		$text = str_replace('&&amp;', '&amp;', $text);
		$text = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $text);
		$text = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $text);
		
		$finalString = '';
		if(strpos($text, '<w:r>') !== false){
			
			while (strpos($text, '</w:r>') !== false){
				if($this->before('</w:t></w:r><w:r>', $text) != ''){
					if(strpos($this->before('</w:t></w:r><w:r>', $text), '<w:r><w:t>') === 0)
						$text = $this->after('<w:r><w:t>', $text);
					$finalString .= (strpos($this->before('</w:t></w:r><w:r>', $text), '<w:rPr>') === false ? '<w:r><w:rPr></w:rPr><w:t>' : '') . $this->before('</w:t></w:r><w:r>', $text) . '</w:t></w:r>';
				}
				$text = $this->after('</w:t></w:r>', $text);
				
				$finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $text) . '</w:r>';
				$text = $this->after('</w:t></w:r>', $text);
			}
			$text = $this->after('<w:r><w:t>', $text);
		}
		
		if($text != ''){
			
			$finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $text . '</w:t></w:r>';
		}
		$finalString = str_replace('<w:rPr>',  '<w:rPr>' . $property, $finalString);
		$finalString = str_replace('', '', $finalString);
		$finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
		
		return '</w:t></w:r>' . $finalString . '<w:r><w:t>';
		
	}
	
	/*
	
	function addProperty($text, $property){

	   $text = trim($text, chr(31));
	   
	   $text = str_replace('&', '&amp;', $text);
	   $text = str_replace('&&amp;', '&amp;', $text);
	   $text = str_replace('</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', ' </w:t></w:r>', $text);
	   $text = str_replace('<w:t xml:space="preserve"> ', '<w:t> ', $text);
	   
	   $finalString = '';
	   if(strpos($text, '<w:r>') !== false && strpos($text, '</w:instrText>') === false){
		   
		   while (strpos($text, '</w:r>') !== false){
			   if($this->before('</w:t></w:r><w:r>', $text) != ''){
				   if(strpos($this->before('</w:t></w:r><w:r>', $text), '<w:r><w:t>') === 0)
					   $text = $this->after('<w:r><w:t>', $text);
				   $finalString .= (strpos($this->before('</w:t></w:r><w:r>', $text), '<w:rPr>') === false ? '<w:r><w:rPr></w:rPr><w:t>' : '') . $this->before('</w:t></w:r><w:r>', $text) . '</w:t></w:r>';
			   }
			   $text = $this->after('</w:t></w:r>', $text);
			   
			   $finalString .= '<w:r>' . $this->between('<w:r>', '</w:r>', $text) . '</w:r>';
			   $text = $this->after('</w:t></w:r>', $text);
		   }
		   $text = $this->after('<w:r><w:t>', $text);
	   }
	   
	   if($text != ''){
		   
		   $finalString .= '<w:r><w:rPr></w:rPr><w:t>' . $text . '</w:t></w:r>';
	   }
	   
	   if(strpos($text, '</w:instrText>') !== false)
		   $finalString = $text;
		   
	   $finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:rPr></w:rPr><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
	   $finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
	   $finalString = str_replace('', '', $finalString);
	   $finalString = str_replace('', '', $finalString);
	   $finalString = str_replace('<w:rPr>',  '<w:rPr>' . $property, $finalString);
	   
	   if(strpos($text, '</w:instrText>') === false)
		   return '</w:t></w:r>' . $finalString . '<w:r><w:t>';
	   else
		   return $finalString;
	   
	} */
	
	function pageBreak() {
		
		return wsd_raw_docx('<w:r><w:lastRenderedPageBreak/></w:r>');
	}
	
	function columnBreak() {

		return wsd_raw_docx('</w:t></w:r><w:r><w:br w:type="column"/></w:r><w:r><w:t xml:space="preserve">');
	}
	
	function referenceLink($string, $referenceName, $referenceType = null){
	
		$refId = $this->refId;
		
		global $n;
		global $previousStyle;
		
			
		switch($referenceType){
			case 'page reference':
				$finalString = '</w:t></w:r><w:r><w:fldChar w:fldCharType="begin" w:dirty="true" /></w:r><w:r><w:instrText xml:space="preserve"> PAGEREF ' . $referenceName . ' \h </w:instrText></w:r><w:r><w:fldChar w:fldCharType="separate" w:dirty="true" /></w:r><w:r><w:rPr><w:noProof/></w:rPr><w:t>' . $string . '</w:t></w:r><w:r><w:fldChar w:fldCharType="end" w:dirty="true" /></w:r><w:r><w:t>';
			break;
			default:				
				$finalString = '</w:t></w:r><w:hyperlink w:anchor="' . $referenceName . '"><w:r><w:rPr><w:rStyle w:val="' . $previousStyle . '"/></w:rPr><w:t>' . $string . '</w:t></w:r></w:hyperlink><w:r><w:t>';
			break;
		}
		
		$this->refId++;
		
		$finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
		
		return wsd_raw_docx($finalString);
	
	}
	
	function referenceTarget($string, $referenceName){
	
		$refId = $this->refId;
	
		$finalString = '</w:t></w:r><w:bookmarkStart w:id="' . $refId . '" w:name="' . $referenceName . '"/><w:r><w:t>' . $string . '</w:t></w:r><w:bookmarkEnd w:id="' . $refId . '"/><w:r><w:t>';
	
		$finalString = str_replace(' </w:t></w:r>',  '</w:t></w:r><w:r><w:t xml:space="preserve"> </w:t></w:r>', $finalString);
		$finalString = str_replace('<w:t> ',  '<w:t xml:space="preserve"> ', $finalString);
		
		$this->refId++;
		
		return wsd_raw_docx($finalString);
	
	}
	
	function setFontSize(){
	}

	function shading($text, $colour) {

		$xmlString = $this->addProperty($text, '<w:shd w:val="clear" w:fill="' . $colour . '"/>');
		return wsd_raw_docx($xmlString);
		
	}
	
	function applyFormattingParameter($xmlstr, $formattingOption, $attributes = array()){
		
		
		$wordmarkup = '_word_';

		$docxmarkup = new SimpleXMLElement('<temp></temp>');

		//$docxmarkup->addAttribute('type', 'documentary');
		$docxmarkup->addChild('_word_r');
		$docxmarkup->_word_r->addChild('_word_rPr');
		$docxmarkup->_word_r->_word_rPr->addChild('b');
		$docxmarkup->_word_r->addChild('_word_t', 'This is all about the people who make it work.');

		$xmlstr = str_replace('<?xml version="1.0"?>','',$docxmarkup->asXML());
		$xmlstr = str_replace($wordmarkup,'w:',$xmlstr);
		$xmlstr = str_replace('<temp>','</w:t></w:r>',$xmlstr);
		$xmlstr = str_replace('</temp>','<w:r><w:t>',$xmlstr);


		return $xmlstr;

		
		/* $wordmarkup = '_word_';

		$docxmarkup = new SimpleXMLElement('<temp></temp>');
		$docxmarkup->temp->addChild('_word_r');
		$docxmarkup->temp->_word_r->addChild('_word_rPr');
		// $docxmarkup->temp->_word_r->_word_rPr->addChild('_word_b');
		$docxmarkup->temp->_word_r->addChild('_word_t','TEST OUTPUT');

		//$docxmarkup->addChild('rPr','documentary');
		$xmlstr = (string) $docxmarkup->asXML();

		$xmlstr = str_replace('<?xml version="1.0"?>','',$xmlstr);
		$xmlstr = str_replace($wordmarkup,'w:',$xmlstr);
		$xmlstr = str_replace('<temp>','</w:t></w:r>',$xmlstr);
		$xmlstr = str_replace('</temp>','<w:r><w:t>',$xmlstr);

		return $xmlstr; */
		
		/*
		
		$xmlstr = preg_replace("/>\\s*</","><",$xmlstr);
		$first = trim(substr(trim($xmlstr) , 0 ,strlen('</w:t></w:r>')));
		$last = trim(substr(trim($xmlstr) , -strlen('<w:r><w:t>')));
		
		// $debugArray["first"] = $first;
		// $debugArray["last"] = $last;
		
		// echo print_r($debugArray);
		
		// return $xmlstr;
		// return strstr($xmlstr,"</w:t></w:r>"s);
		
		// $xmlstr = '</w:t></w:r>';
		
		if(strstr($xmlstr, '</w:t></w:r>') !== false) {
		
			switch(true){
				
				case $first != '</w:t></w:r>' && $last != '<w:r><w:t>':
					
					$xmlstr = '</w:t></w:r><w:r><w:t>'.$xmlstr.'</w:t></w:r><w:r><w:t>';
					$xmlstr = str_replace('','',$xmlstr);
					
					// $before = substr($xmlstr,0,strpos($xmlstr,'</w:t></w:r>'));
					// $middle = substr($xmlstr,strpos($xmlstr,'</w:t></w:r>'),(strrpos($xmlstr,'<w:r><w:t>')) - strpos($xmlstr,'</w:t></w:r>'));
					// $after = substr($xmlstr,strrpos($xmlstr,'<w:r><w:t>') + strlen('<w:r><w:t>'));
					// $before = str_replace('','',$before);
					// $after = str_replace('','',$after);
					
					
					// $xmlstr = '</w:t></w:r><w:r><w:rPr></w:rPr><w:t>' . $before;
					// $xmlstr .= $middle;
					// $xmlstr .= '<w:r><w:rPr></w:rPr><w:t>' . $after . '</w:t></w:r><w:r><w:t>';
					
					// $debugArrayq["xmlstr"] = $xmlstr;
					// $debugArray["before"] = $before;
					// $debugArray["middle"] = $middle;
					// $debugArray["after"] = $after;
					
					// print_r($debugArray);
					
					// return $xmlstr;
					
				break;
				
				case $first != '</w:t></w:r>':
					
					$xmlstr = '</w:t></w:r><w:r><w:t>'.$xmlstr.'';
					$xmlstr = str_replace('','',$xmlstr);
					
					// $before = substr($xmlstr,0,strpos($xmlstr,'</w:t></w:r>'));
					// $middle = substr($xmlstr,strpos($xmlstr,'</w:t></w:r>'));
					// $before = str_replace('','',$before);
					
					// $xmlstr = '</w:t></w:r><w:r><w:rPr></w:rPr><w:t>' . $before;
					// $xmlstr .= $middle;
					
					// return 'FIRST';
				break;
				
				case $last != '<w:r><w:t>':
					
					$xmlstr = ''.$xmlstr.'</w:t></w:r><w:r><w:t>';
					$xmlstr = str_replace('','',$xmlstr);
					
					// $middle = substr($xmlstr,0,strrpos($xmlstr,'<w:r><w:t>'));
					// $after = substr($xmlstr,strrpos($xmlstr,'<w:r><w:t>') + strlen('<w:r><w:t>'));
					// $after = str_replace('','',$after);
					
					// $xmlstr = $middle;
					// $xmlstr .= '<w:r><w:rPr></w:rPr><w:t>' . $after . '</w:t></w:r><w:r><w:t>';
					
					// $debugArray["xmlstr"] = $xmlstr;
					// $debugArray["before"] = $before;
					// $debugArray["middle"] = $middle;
					// $debugArray["after"] = $after;
					
					// print_r($debugArray);
					
					// return $xmlstr;
					
					// return 'LAST';
				break;
				
			}
			
			$xmlstr = substr($xmlstr , strpos($xmlstr,'</w:t></w:r>') + strlen('</w:t></w:r>') , -(strlen('<w:r><w:t>')));
			
		}else{
			$xmlstr = '</w:t></w:r><w:r><w:rPr></w:rPr><w:t>'.$xmlstr.'</w:t></w:r><w:r><w:t>';
			$xmlstr = substr($xmlstr , strpos($xmlstr,'</w:t></w:r>') + strlen('</w:t></w:r>') , -(strlen('<w:r><w:t>')));
		}
		
		$wordmarkup = '_word_';
		$xmlstr = '<w:test>'.$xmlstr.'</w:test>';
		$xmlstr = str_replace('w:',$wordmarkup,$xmlstr);
		$docxmarkup = new SimpleXMLElement($xmlstr);
		
		
		for($i=0;$i<count($docxmarkup->_word_r);$i++){
			
			if(!isset($docxmarkup->_word_r[$i]->_word_rPr)){
				$docxmarkup->_word_r[$i]->addChild($wordmarkup.'rPr');
			}
			
			if(!isset($docxmarkup->_word_r[$i]->_word_rPr->{$wordmarkup.$formattingOption})){
				$docxmarkup->_word_r[$i]->_word_rPr->addChild($wordmarkup.$formattingOption);
			}
			
			foreach($attributes as $attribute => $value){
				$docxmarkup->_word_r[$i]->_word_rPr->{$formattingOption}->addChild($attribute,$value);
			}
		}
		
		$xmlstr = (string) $docxmarkup->asXML();
		
		$xmlstr = str_replace($wordmarkup,'w:',$xmlstr);
		$xmlstr = str_replace('<w:test>','</w:t></w:r>',$xmlstr);
		$xmlstr = str_replace('</w:test>','<w:r><w:t>',$xmlstr);
		$xmlstr = str_replace('<?xml version="1.0"?>','',$xmlstr);
		
		
		return $xmlstr;
		
		*/
	}
	
	
	function afterN($textBreakPointOne, $inputText){
		return substr($inputText,strpos($inputText,$textBreakPointOne) + strlen($textBreakPointOne));
	}


	function betweenN($textBreakPointAfter,$textBreakPointOneBefore, $inputText){
		return $this->beforeN($textBreakPointOneBefore,$this->afterN($textBreakPointAfter,$inputText));
	}

	function beforeN($textBreakPointOne, $inputText){
		return substr($inputText,0,strpos($inputText,$textBreakPointOne));
	}


	function updateMarkup($inputText){
		
		if(strstr($inputText,'</w:t></w:r>') === false){
			  $inputText = '</w:t></w:r><w:r><w:rPr></w:rPr><w:t>'.$inputText.'</w:t></w:r><w:r><w:t>';
		}else{
		
			if(strlen($this->beforeN('</w:t></w:r>',$inputText)) !== 0){
				  $inputText = '</w:t></w:r><w:r><w:rPr></w:rPr><w:t>' . $inputText;
				$debug['one']['pos'] = 1;
			}
		
	   
			if(strlen(substr($inputText,strrpos($inputText,'<w:r><w:t>') + strlen('<w:r><w:t>'))) != 0){
			
				$inputText = substr($inputText,0,strrpos($inputText,'</w:t></w:r>') + strlen('</w:t></w:r>')) . '<w:r><w:rPr></w:rPr><w:t>' . substr($inputText,strrpos($inputText,'<w:r><w:t>') + strlen('<w:r><w:t>')) . '</w:t></w:r><w:r><w:t>';
				 
			}
		}
		
		return $inputText;
		
	}

	function applyFormattingMarkup($formatting, $inputText){
		
		$inputText = $this->updateMarkup($inputText);
		
		$beforeText = '';
		$afterText = $inputText;
		
		while(strstr($afterText,'<w:rPr>')){
			
			$beforeText .= $this->beforeN('<w:rPr>',$afterText) . '<w:rPr>';
			$afterText = $this->afterN('<w:rPr>',$afterText);
			$between = $this->beforeN('</w:rPr>',$afterText);
			
			if(strstr($between,substr($formatting,0,strlen(explode(' ', $formatting)[0]))) === false){
				$beforeText .= $between . $formatting;
			}else{
				$beforeText .= $between;
			}
			$beforeText .= '</w:rPr>';
			$afterText = $this->afterN('</w:rPr>',$afterText);
			
		}
		
		$beforeText .= $afterText;
		
		return wsd_raw_docx($beforeText);
		
	}

	
}
 
 
//echo wsd_raw_docx($docx->italic('Italic ' . $docx->bold('Bold and Italic' . $docx->underline('Bold and Italic and Underlined')) . ' Italic again'  . $docx->bold('Bold and Italic') . ' Italic again'));
 
//$docx->print('plain'); $docx->startItalic(); $docx->print('Italic'); $docx->startBold(); $docx->print('Bold and Italic'); $docx->endBold(); $docx->print('Bold'); $docx->endItalic();
 
//echo $docx->flush();




?>