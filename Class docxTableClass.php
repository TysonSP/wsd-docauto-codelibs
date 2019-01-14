<?php

/*
* Class:docxTableClass
* version: 1.1b
*
*/

class docxTableClass {

	var $table = '';
	
	var $numberOfColumns = 0;
	var $columnWidth = 3000;
	var $tableGridCols = array();
	var $rowCellIndex = 0;
	
	var $rows = array(); 
	var $headers = array(); 
	
	var $tableFormatting;
	var $tableColorScheme;
	
	var $units = array('centimeters' => 567, 'twips' => 1, 'percent' => 10.9);
	var $tableData;
	var $tableHeader;
	
	// var $unitCellWidth = 0;
	// var $totalTableWeighting = 0;
	// var $tableWidth = 0;
	
	function __construct(){
		
		//Preset Table Color Scheme
		$this->tableColorScheme = array(
	
			'yellow' => 'e8ff1e',
			'grey' => 'e6e6e6',
			'blue' => '2354c6',
			'light blue' => '80dfff',
			'red' => 'e00b0b',
			'green' => '0ee00b',
			'purple' => 'e00bd8',
			'pink' => 'ff7ce0',
			'white' => 'ffffff',
			'black' => '000000',
	
		);
		
		//Note: internal table styles which are managed by the class are prefixed with "docxClass:" ex: "docxClass:[tableComponent][FormattingOptions]"
		
		//INTERNAL TABLE STYLES
		
		//Default Table Header ROW Formatting
		$this->tableFormatting['docxClass:headerRowFormattingOptions'] = array();
		
		//Default TABLE Formatting
		$this->tableFormatting['docxClass:tableFormattingOptions'] = array(
			'table style' => 'TableGrid', 
			'table align' => 'center'
		);
		
		//Default Table ROW Formatting
		$this->tableFormatting['docxClass:rowFormattingOptions'] = array();
		
		//Default Table CELL Formatting
		$this->tableFormatting['docxClass:cellFormattingOptions'] = array();
		
		//Default Table cell->PARAGRAPH Formatting
		$this->tableFormatting['docxClass:cellParagraphFormattingOptions'] = array(
			'keep with next' => false, 
			'keep lines together' => false, 
			'text spacing' => array(
				'before' => 0, 
				'after' => 0
			)
		);
		
		//Default Table cell->Paragraph->RUNS Formatting
		$this->tableFormatting['docxClass:cellParagraphRunsFormattingOptions'] = array();
		
		
		//CUSTOM INTERNAL STYLES
		
		//Formatting Option to fix Pre and Post Table paragraph spacing
		$this->tableFormatting['docxClass:preTableSpacingFix'] = array(
			'text spacing' => array(
				'before' => 0, 
				'after' => 0, 
				'line' => 0.06
			)
		);
		
		//Formatting Option to fix Pre and Post Table paragraph spacing
		$this->tableFormatting['docxClass:postTableSpacingFix'] = array(
			'text spacing' => array(
				'before' => 0, 
				'after' => 0, 
				'line' => 0.06
			)
		);
		
	}
	
	// -- Table Formatting styles --
	
	//Table Styles - "table Styles" in the docx template for formatting
	
	function setTableStyle($tableStyle = null) {
		
		if($tableStyle != null || $tableStyle != '')
			$this->tableFormatting['docxClass:tableFormattingOptions']['table style'] = $tableStyle;
		else
			trigger_error('docxTableClass: setTableStyle: unsupported input: ' . $tableStyle,E_USER_ERROR);
		
	}
	
	//Table Styles - "table Styles" in the code for formatting
	
	//User set Table Styles
	
	/*
	*Sets the table formatting options
	*
	*@param string, array
	*/
	function addTableFormattingStyle($formattingName, $formatParameters = null){
		
		if(strlen(trim($formattingName)) == 0)
			trigger_Error('docxTableClass: addTableFormat: No name provided for table formatting style', E_USER_ERROR);
		
		if(array_key_exists($formattingName, $this->tableFormatting))
			trigger_error('docxTableClass: addTableFormat: the formatting style already exists: please use updateTableFormat',E_USER_ERROR); //-TEH
		
		//Resolves the edge case where the addTableFormat formattingOptions itself contains a preformatted table style (i.e the user assumes stye formatting is inherited from another existing style) - only supported when creating a new table format
		if(in_array('presetTableFormatting', array_keys($formatParameters)) !== false){
			if(in_array($formatParameters['presetTableFormatting'],array_keys($this->tableFormatting))){
				$formatParameters = array_merge($this->tableFormatting[$formatParameters['presetTableFormatting']],$formatParameters);
				unset($formatParameters['presetTableFormatting']);
			}else{
				trigger_error('docxTableClass: addTableFormat : Row Preset Table Formatting Not Found : Formatting Option Selected : ' . print_r($formatParameters['presetTableFormatting'],true) . ' Available/Set Table Formatting Options : ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
			}
		}
		
		if($formatParameters == null)
			$this->tableFormatting[$formattingName] = array();
		else
			foreach($formatParameters as $formatParameter => $value)
				$this->tableFormatting[$formattingName][$formatParameter]  = $value;
		
	}
	
	function updateTableFormattingStyle($formattingName, $formatParameters = null){
		
		if(array_key_exists($formattingName, $this->tableFormatting) == false)
			trigger_Error('docxTableClass: updateTableFormat: table formatting style, ' . $formattingName .' does not exist please use addTableFormat() to intialize the table style', E_USER_ERROR);
		
		if(strlen(trim($formattingName)) == 0)
			trigger_Error('docxTableClass: updateTableFormat: no name provided for table format', E_USER_ERROR);
		
		//Resolves the edge case: inhertance of a preexisting formatting style can not be applied to another formatting style once the format style has been created
		if(in_array('presetTableFormatting', array_keys($formatParameters)) !== false)
			trigger_error('docxTableClass: updateTableFormat: inhertance of preexisting formatting style is not supported once the format style has been created',E_USER_ERROR); //-TEH
		
		
		if($formatParameters == null)
			$this->tableFormatting[$formattingName] = array();
		else
			foreach($formatParameters as $formatParameter => $value)
				$this->tableFormatting[$formattingName][$formatParameter]  = $value;
				
		
	}
	
	function removeTableFormattingStyle($formattingName){
		
		if(substr($formattingName,0,10) == 'docxClass:'){
			
			switch($formattingName){
				
				case 'docxClass:tableHeaderFormattingOptions':
				case 'docxClass:internalTableRowFormattingStyle':
				break;
				
				default:
					//Resolves the edge case where the user tries to unset an internal docxStyle; to prevent breaking internal Class formatting unsetting an internal docxStyle is not allowed
					trigger_error('docxTableClass: removeTableFormattingStyle: to prevent breaking internal Class formatting unsetting an internal docxStyle is not allowed',E_USER_ERROR); //-TEH
				break;
			}
			
		}else{
			
			if(array_key_exists($formattingName, $this->tableFormatting))
				unset($this->tableFormatting[$formattingName]);
			
		}
		
	}
	
	function removeTableFormattingStyleOption($formattingName,$formatParameters){
		
		// if(substr($formattingName,0,10) == 'docxClass:'){
			
			////Resolves the edge case where the user tries to unset an internal docxStyle; to prevent breaking format unsetting an internal docxStyle is not allowed
			// trigger_error('docxTableClass: removeTableFormattingStyleOption: to prevent breaking format unsetting an internal docxStyle is not allowed',E_USER_ERROR); //-TEH
			
		// }else{
			
			foreach($formatParameters as $formatParameter => $value){
				if(array_key_exists($formatParameter, $this->tableFormatting[$formattingName])){
					unset($this->tableFormatting[$formattingName][$formatParameter]);
				}
			}
			
		// }
	}
	
	function updateColorSchema($parameters){
		
		foreach($parameters as $color => $hexColor)
			$this->tableColorScheme[$color] = $hexColor;	
			
	}
	
	//Internal Class managed Table Styles
	
	protected function updateGlobalTableFormattingOptions($formattingLevel, $formatParameters){
		
		//resolves an edge case where the user overrides internal styles with a custom style or assigns a style to a style
		if(in_array('presetTableFormatting', array_keys($formattingOptions)) !== false)
			trigger_error('docxTableClass: updateTableFormat: inhertance of preexisting formatting options is not supported by global docx styles',E_USER_ERROR); //-TEH
		
		if(is_array($formattingLevel) && $formattingLevel != null)
			trigger_Error('docxTableClass: updateGlobalTableFormatOptions: "formattingLevel" parameter is empty', E_USER_ERROR);
		
		if(is_array($formatParameters) == false && $formatParameters == null)
			trigger_Error('docxTableClass: updateGlobalTableFormatOptions: "formatting" parameter is empty', E_USER_ERROR);
		
		switch($formattingLevel){
			
			//Global Table Level formatting styles
			
			case 'table':
			case 'docxClass:tableFormattingOptions':
			
				$internalFormattingStyle = 'docxClass:tableFormattingOptions';
			break;
			
			case 'row':
			case 'docxClass:rowFormattingOptions':
			
				$internalFormattingStyle = 'docxClass:rowFormattingOptions';
			break;
			
			case 'cell':
			case 'docxClass:cellFormattingOptions':
			
				$internalFormattingStyle = 'docxClass:cellFormattingOptions';
			break;
			
			case 'paragraph':
			case 'docxClass:cellFormattingOptions':
			
				$internalFormattingStyle = 'docxClass:cellFormattingOptions';
			break;
			
			case 'text':
			case 'cellcontent':
			case 'cellContent':
			case 'cell content':
			case 'docxClass:cellParagraphRunsFormattingOptions':
			
				$internalFormattingStyle = 'docxClass:cellParagraphRunsFormattingOptions'; //Runs
			break;
			
			//Custom Internal Table formatting styles
			
			case 'header':
			case 'header row':
			case 'docxClass:headerRowFormattingOptions':
			
				$internalFormattingStyle = 'docxClass:headerRowFormattingOptions';
			break;
			
			case 'preTableSpacingFix':
			case 'docxClass:preTableSpacingFix':
			
				$internalFormattingStyle = 'docxClass:preTableSpacingFix';
			break;
			
			case 'postTableSpacingFix':
			case 'docxClass:postTableSpacingFix':
			
				$internalFormattingStyle = 'docxClass:postTableSpacingFix';
			break;
			
			default:
				trigger_Error('docxTableClass: updateGlobalTableFormatOptions: formattingLevel level not supported : formattingLevel selected: ' . $formattingLevel, E_USER_ERROR);
			break;
			
		}
		
		$this->updateTableFormattingStyle($internalFormattingStyle,$formatParameters);
		
	}
	
	
	// -- Table Core Procedural Code --
	
	function setNumberOfTableColumns($numberOfColumns, $weightings = array()){
		
		// Calculate column widths automatically based on weightings - assume equal weights if none are provided
		if(count($weightings) == 0){
			
			for($i = 0; $i < $numberOfColumns; $i++)
				$weightings[$i] = $this->columnWidth;
		
		}elseif(count($weightings) != $numberOfColumns){
			
			trigger_error('docxTableClass: setNumberOfTableColumns: weighting must be provided for each column',E_USER_ERROR);

		}
		
		// Save column widths in $this->tableGridCols
		$this->tableGridCols = $weightings;
		$this->numberOfColumns = $numberOfColumns;

	}
	
	function setNumberOfColumns($numberOfColumns, $weightings = array(),$format = 'twips'){
	// function setNumberOfColumns($numberOfColumns, $tableWidth, $weightings = array()){
		
		$this->table .= '<w:tblGrid>';
		
		//THE COMMENTED CODE BELOW CAUSES FORMATTING ISSUES (WITH VERTICAL AND HORIZONTAL MERGE ETC) WHEN USING DIFFERENT COLUMN RATIOS SWITCHED TO ABSOLUTE VALUES FOR WEIGHTINGS
		// $this->tableWidth = $tableWidth;
		// $this->numberOfColumns = $numberOfColumns;
		
		// if(count($weightings) == 0){
			
			// $this->totalTableWeighting = $numberOfColumns;
			// $this->unitCellWidth = $this->tableWidth/$this->totalTableWeighting;
			
			// for($i = 0; $i < $numberOfColumns; $i++)
				// $this->tableGridCols[] = $this->unitCellWidth;
		
		// }elseif(count($weightings) == $numberOfColumns){
			
			// foreach($weightings as $tableWeightng)
				// $this->totalTableWeighting += $tableWeightng;
			
			// $this->unitCellWidth = $this->tableWidth/$this->totalTableWeighting;
			
			// foreach($weightings as $tableWeightng)
				// $this->tableGridCols[] = $this->unitCellWidth * $tableWeightng;
			
		// }else{
			
			// trigger_error('docxTableClass: setNumberOfColumns: weighting must be provided for EACH column',E_USER_ERROR);
			
		// }
		
		// Calculate column widths automatically based on weightings - assume equal weights if none are provided
		if(count($weightings) == 0){

			for($i = 0; $i < $numberOfColumns; $i++)
				$weightings[$i] = $this->columnWidth;
		
		}elseif(in_array($format, array('cm','centimeters','%','percent'))){
			
			for($i = 0; $i < $numberOfColumns; $i++){
				
				switch($format){
			
					case 'twips':
						$weightings[$i] = $this->columnWidth  * $this->units['twips'];
					break;
					
					case 'cm':
					case 'centimeters':
						$weightings[$i] = $this->columnWidth  * $this->units['centimeters'];
					break;
					
					case '%':
					case 'percent':
						$weightings[$i] = $this->columnWidth  * $this->units['percent'];
					break;
					
				}
				
			}
			
			
		}elseif(count($weightings) != $numberOfColumns){
			
			trigger_error('docxTableClass: setNumberOfColumns: weighting must be provided for each column',E_USER_ERROR);

		}
		
		// Save column widths in $this->tableGridCols
		$this->tableGridCols = $weightings;
		$this->numberOfColumns = $numberOfColumns;
		
		for($i = 0; $i < $numberOfColumns; $i++)
			$this->table .= '<w:gridCol w:w="'. $weightings[$i] .'"/>';
		
		$this->table .= '</w:tblGrid>';

	}
	
	function startTable($parameters = array()){
		
		$this->table = '<w:tbl>';
		
		// if(is_array($parameters) && $parameters != null){
			// $this->table .= $this->parametersToXMLString(array_merge($this->tableFormatting['docxClass:tableFormattingOptions'],$parameters),'tblPr');
		// }else{
			// $this->table .= $this->parametersToXMLString($this->tableFormatting['docxClass:tableFormattingOptions'],'tblPr');
		// }
		
		if(count($parameters) > 0)
			$this->table .= $this->parametersToXMLString(array_merge($this->tableFormatting['docxClass:tableFormattingOptions'],$parameters),'tblPr');
		else
			$this->table .= $this->parametersToXMLString($this->tableFormatting['docxClass:tableFormattingOptions'],'tblPr');
			
	}
	
	function startTableRow($parameters = null) {
		
		if(count($parameters) > 0 && is_array($parameters) && $parameters != null){
			$this->table .= '<w:tr>' . $this->parametersToXMLString(array_merge($this->tableFormatting['docxClass:rowFormattingOptions'],$parameters), 'trPr');
		}else{
			$this->table .= '<w:tr>' . $this->parametersToXMLString($this->tableFormatting['docxClass:rowFormattingOptions'], 'trPr');
		}
	}
	
	function addTableRowCell($contents, $parameters = NULL){
		
		if(count($parameters) > 0 && is_array($parameters) && $parameters != null){
			$this->table .= '<w:tc>' . $this->parametersToXMLString(array_merge($this->tableFormatting['docxClass:cellFormattingOptions'],$parameters),'tcPr');
		}else{
			$this->table .= '<w:tc>' . $this->parametersToXMLString($this->tableFormatting['docxClass:cellFormattingOptions'],'tcPr');
		}
		
			if(count($parameters) > 0 && is_array($parameters) && $parameters != null){
				$this->table .= '<w:p>' . $this->parametersToXMLString(array_merge($this->tableFormatting['docxClass:cellParagraphFormattingOptions'],$parameters),'pPr');
				$this->table .= $this->__addTableRowCellContent($contents, array_merge($this->tableFormatting['docxClass:cellParagraphRunsFormattingOptions'],$parameters));
			}else{
				$this->table .= '<w:p>' . $this->parametersToXMLString($this->tableFormatting['docxClass:cellParagraphFormattingOptions'],'pPr');
				$this->table .= $this->__addTableRowCellContent($contents, $this->tableFormatting['docxClass:cellParagraphRunsFormattingOptions']);
			}
				
			$this->table .= '</w:p>';
		$this->table .= '</w:tc>';
		
		if($this->rowCellIndex >= $this->numberOfColumns)
			trigger_error('docxTableClass: addTableRowCell: Number of row cells has exceeded the number of columns, Column Content : ' . $contents . ', Row Index : ' . $this->rowCellIndex . ', Number Of Columns Set: ' . $this->numberOfColumns,E_USER_ERROR);
		
		$this->rowCellIndex++;
		
	}
	
	function __addTableRowCellContent($contents, $parameters = null) {
		
		$tempString = '';
		
		//Recursion no longer needed as formatting options for cell text can be set with the docx class formatting functions
		if(is_array($contents)){
			
			// return $this->addTableRowCellContent($contents[0], array_merge($parameters,$contents[1]));
				
			// if(array_key_exists(1, $contents))
				// $param .= $this->parametersToXMLString($contents[1], 'rPr');
			
			// for($c = 0; $c < count($contents); $c++){
				
				// if(is_array($contents[$c])){
					// $this->addTableRowCellContent($this->inlineParametersToXMLString($contents[$c]), null, $param);
				// }else{
					
					// $tempString .= '<w:r><w:rPr>' . $param . '</w:rPr><w:t xml:space="preserve">' . $this->inlineParametersToXMLString($contents[$c]) . '</w:t></w:r>';
					
				// }
				
			// }
			
			trigger_error('docxTableClass: Please use the docx class for in cell text formatting' . print_r($contents,true),E_USER_ERROR); //-TEH
			
		}else{
			
			
			$tempString .= '<w:r>' . $this->parametersToXMLString($parameters, 'rPr');
				$tempString .= '<w:t>' . $this->inlineParametersToXMLString($contents) . '</w:t>';
			$tempString .= '</w:r>';
			
			return $tempString;
			
		}
		
	}
	
	function endTableRow() {
		
		$this->rowCellIndex = 0;
		
		$this->table .= '</w:tr>';
		
	}

	function endTable() {
		
		$this->table .= '</w:tbl>';
		
	}
	
	function renderTableRaw(){
		
		$explodedElements = explode('{RAWTEXT}',$this->table);
		$string = '';
		for($i = 0; $i < count($explodedElements); $i++){
		   if(($i + 1) % 2 != 0){
				$string .=  wsd_raw_docx($explodedElements[$i]);
		   }else{
				$string .=  $explodedElements[$i];
		   }
		}
		
		return wsd_raw_docx('</w:t></w:r>' . $this->parametersToXMLString($this->tableFormatting['docxClass:preTableSpacingFix'], 'pPr') . '</w:p>') . $string . wsd_raw_docx('<w:p>' . $this->parametersToXMLString($this->tableFormatting['docxClass:postTableSpacingFix'], 'pPr') . '<w:r><w:t>');
		
	}
	
	
	// -- Table Object oriented Code (Uses internal state [Table data and Table Formatting] and procedural code above to generate table) --
	
	function setTableData($tableData, $formattingOptions = null) {
		
		if($formattingOptions != null){
			
			//Resolves the edge case where the setTableData formattingOptions itself contains a preformatted table style
			if(in_array('presetTableFormatting', array_keys($formattingOptions)) !== false){
				if(in_array($formattingOptions['presetTableFormatting'],array_keys($this->tableFormatting))){
					$formattingOptions = array_merge($this->tableFormatting[$formattingOptions['presetTableFormatting']],$formattingOptions);
					unset($formattingOptions['presetTableFormatting']);
				}else{
					trigger_error('docxTableClass: setTableData : Row Preset Table Formatting Not Found : Formatting Option Selected : ' . print_r($formatParameters['presetTableFormatting'],true) . ' Available/Set Table Formatting Options : ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
				}				
			}
			
			$this->removeTableFormattingStyle('docxClass:internalTableRowFormattingStyle');
			$this->addTableFormattingStyle('docxClass:internalTableRowFormattingStyle', $formattingOptions);
			
		}
		
		// $this->tableData = array();
		
		foreach($tableData as $tableRowIndex => $dataRows){
			
			foreach($dataRows as $tableCellIndex => $RowCell){
				
				if(is_array($RowCell)){
					
					if(count($RowCell) == 1){
						
						$this->tableData[$tableRowIndex][$tableCellIndex][0] = $RowCell[0];
						
						if($formattingOptions != null)
							$this->tableData[$tableRowIndex][$tableCellIndex][1] = array('presetTableFormatting' => 'docxClass:internalTableRowFormattingStyle');
					
					}else{
						
						if(in_array('presetTableFormatting', array_keys($RowCell[1])) !== false){
							
							if(in_array($RowCell[1]['presetTableFormatting'],array_keys($this->tableFormatting))){
								
								$this->tableData[$tableRowIndex][$tableCellIndex][0] = $RowCell[0];
								
								if($formattingOptions != null){
									
									//Global Cell Preset formatting => Global Row Preset formatting
									$temp = array_merge($formattingOptions,$this->tableFormatting[$RowCell[1]['presetTableFormatting']]);
									unset($temp['presetTableFormatting']);
									
									
									//Cell Specific formatting => [Global Cell Preset formatting => Global Row Preset formatting]
									$temp = array_merge($temp,$RowCell[1]);
									unset($temp['presetTableFormatting']);
									
								
								}else{
									
									//Cell Specific formatting => Global Cell Preset formatting
									$temp = array_merge($this->tableFormatting[$RowCell[1]['presetTableFormatting']],$RowCell[1]);
									unset($temp['presetTableFormatting']);
									
								}
								
								$this->tableData[$tableRowIndex][$tableCellIndex][1] = $temp;
								
							}else{
								trigger_error('docxTableClass: setTableData : Row Preset Table Formatting Not Found : Row Formatting Option Selected : ' . print_r($RowCell[1],true) . ' Available/Set Table Formatting Options : ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
							}
							
						}else{
							
							$this->tableData[$tableRowIndex][$tableCellIndex][0] = $RowCell[0];
							
							if($formattingOptions != null){
								$this->tableData[$tableRowIndex][$tableCellIndex][1] = array_merge($formattingOptions,$RowCell[1]);
							}else{
								$this->tableData[$tableRowIndex][$tableCellIndex][1] = $RowCell[1];
							}
							
						}
				
					}
				
				}else{
					
					$this->tableData[$tableRowIndex][$tableCellIndex][0] = $RowCell;
					
					if($formattingOptions != null)
						$this->tableData[$tableRowIndex][$tableCellIndex][1] = array('presetTableFormatting' => 'docxClass:internalTableRowFormattingStyle');
					
				}
				
			}
			
		}
		
	}
	
	function setTableHeader($tableHeader, $formattingOptions = null) {
		
		if($formattingOptions != null){
			
			//Resolves the edge case where the setTableData formattingOptions itself contains a preformatted table style
			if(in_array('presetTableFormatting', array_keys($formattingOptions)) !== false){
				if(in_array($formattingOptions['presetTableFormatting'],array_keys($this->tableFormatting))){
					$formattingOptions = array_merge($this->tableFormatting[$formattingOptions['presetTableFormatting']],$formattingOptions);
					unset($formattingOptions['presetTableFormatting']);
				}else{
					trigger_error('docxTableClass: setTableData : Row Preset Table Formatting Not Found : Formatting Option Selected : ' . print_r($formatParameters['presetTableFormatting'],true) . ' Available/Set Table Formatting Options : ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
				}				
			}
			
			$this->removeTableFormattingStyle('docxClass:tableHeaderFormattingOptions');
			$this->addTableFormattingStyle('docxClass:tableHeaderFormattingOptions', $formattingOptions);
			
		}
		
		$this->tableHeader = array();
		
		foreach($tableHeader as $tableRowIndex => $dataRows){
			foreach($dataRows as $tableCellIndex => $RowCell){
				
				if(is_array($RowCell)){
					
					if(count($RowCell) == 1){
						
						$this->tableHeader[$tableRowIndex][$tableCellIndex][0] = $RowCell[0];
						
						if($formattingOptions != null)
							$this->tableHeader[$tableRowIndex][$tableCellIndex][1] = array('presetTableFormatting' => 'docxClass:tableHeaderFormattingOptions');
					
					}else{
						
						if(in_array('presetTableFormatting', array_keys($RowCell[1])) !== false){
							
							if(in_array($RowCell[1]['presetTableFormatting'],array_keys($this->tableFormatting))){
								
								$this->tableHeader[$tableRowIndex][$tableCellIndex][0] = $RowCell[0];
								
								if($formattingOptions != null){
									
									//Global Cell Preset formatting => Global Row Preset formatting
									$temp = array_merge($formattingOptions,$this->tableFormatting[$RowCell[1]['presetTableFormatting']]);
									unset($temp['presetTableFormatting']);
									
									
									//Cell Specific formatting => [Global Cell Preset formatting => Global Row Preset formatting]
									$temp = array_merge($temp,$RowCell[1]);
									unset($temp['presetTableFormatting']);
									
								
								}else{
									
									//Cell Specific formatting => Global Cell Preset formatting
									$temp = array_merge($this->tableFormatting[$RowCell[1]['presetTableFormatting']],$RowCell[1]);
									unset($temp['presetTableFormatting']);
									
								}
								
								$this->tableHeader[$tableRowIndex][$tableCellIndex][1] = $temp;
								
							}else{
								trigger_error('docxTableClass: setTableData : Row Preset Table Formatting Not Found : Row Formatting Option Selected : ' . print_r($RowCell[1],true) . ' Available/Set Table Formatting Options : ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
							}
							
						}else{
							
							$this->tableHeader[$tableRowIndex][$tableCellIndex][0] = $RowCell[0];
							
							if($formattingOptions != null){
								$this->tableHeader[$tableRowIndex][$tableCellIndex][1] = array_merge($formattingOptions,$RowCell[1]);
							}else{
								$this->tableHeader[$tableRowIndex][$tableCellIndex][1] = $RowCell[1];
							}
							
						}
				
					}
				
				}else{
					
					$this->tableHeader[$tableRowIndex][$tableCellIndex][0] = $RowCell;
					
					if($formattingOptions != null)
						$this->tableHeader[$tableRowIndex][$tableCellIndex][1] = array('presetTableFormatting' => 'docxClass:tableHeaderFormattingOptions');
					
				}
				
			}
			
		}
		
	}
	
	function createTable($tableData,$tableHeader = null){
		
		if($tableHeader != null){
			
			foreach($tableHeader as $dataRows){
				
				$this->startTableRow($this->tableFormatting['docxClass:headerRowFormattingOptions']);
				
				if(count($this->headers) > 0){
					
					foreach($this->headers as $field){
						
						$RowCell = $dataRows[$field];
						
						if(is_array($RowCell)){
							
							if(count($RowCell) == 1){
								$this->addTableRowCell($RowCell[0]);
							}else{
									
								if(in_array('presetTableFormatting', array_keys($RowCell[1])) !== false){
									
									if(in_array($RowCell[1]['presetTableFormatting'],array_keys($this->tableFormatting))){
										$this->addTableRowCell($RowCell[0], $this->tableFormatting[$RowCell[1]['presetTableFormatting']]);
									}else{
										trigger_error('docxTableClass: createTable: Row Preset Table Formatting Not Found : Header Formatting Options : ' . print_r($RowCell[1],true) . ' Table Formatting Options available/set: ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
									}
								}else{
									$this->addTableRowCell($RowCell[0], $RowCell[1]);
								}
						
							}
						
						}else{
							$this->addTableRowCell($RowCell);
						}
						
					}
					
				}else{
					
					foreach($dataRows as $RowCell){
						
						if(is_array($RowCell)){
						
							if(count($RowCell) == 1){
								$this->addTableRowCell($RowCell[0]);
							}else{
									
								if(in_array('presetTableFormatting', array_keys($RowCell[1])) !== false){
									
									if(in_array($RowCell[1]['presetTableFormatting'],array_keys($this->tableFormatting))){
										$this->addTableRowCell($RowCell[0], $this->tableFormatting[$RowCell[1]['presetTableFormatting']]);
									}else{	
										trigger_error('docxTableClass: createTable: Preset Table Formatting Not Found : Header Formatting Options : ' . print_r($RowCell[1],true) . ' Table Formatting Options available/set: ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
									}
								}else{
									$this->addTableRowCell($RowCell[0], $RowCell[1]);
								}
						
							}
						
						}else{
							$this->addTableRowCell($RowCell);
						}
					}
				
				}
				
				$this->endTableRow();
				
			}
			
		}
		
		foreach($tableData as $dataRows){
			
			$this->startTableRow($this->tableFormatting['docxClass:rowFormattingOptions']);
			
			if(count($this->rows) > 0){
				
				foreach($this->rows as $field){
					
					$RowCell = $dataRows[$field];
					
					if(is_array($RowCell)){
						
						if(count($RowCell) == 1){
							$this->addTableRowCell($RowCell[0]);
						}else{
								
							if(in_array('presetTableFormatting', array_keys($RowCell[1])) !== false){
								
								if(in_array($RowCell[1]['presetTableFormatting'],array_keys($this->tableFormatting))){
									$this->addTableRowCell($RowCell[0], $this->tableFormatting[$RowCell[1]['presetTableFormatting']]);
								}else{
									trigger_error('docxTableClass: createTable: Row Preset Table Formatting Not Found : Header Formatting Options : ' . print_r($RowCell[1],true) . ' Table Formatting Options available/set: ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
								}
							}else{
								$this->addTableRowCell($RowCell[0], $RowCell[1]);
							}
					
						}
					
					}else{
						$this->addTableRowCell($RowCell);
					}
					
				}
				
			}else{
				
				foreach($dataRows as $RowCell){
					
					if(is_array($RowCell)){
						
						if(count($RowCell) == 1){
							$this->addTableRowCell($RowCell[0]);
						}else{
								
							if(in_array('presetTableFormatting', array_keys($RowCell[1])) !== false){
								
								if(in_array($RowCell[1]['presetTableFormatting'],array_keys($this->tableFormatting))){
									$this->addTableRowCell($RowCell[0], $this->tableFormatting[$RowCell[1]['presetTableFormatting']]);
								}else{
									trigger_error('docxTableClass: createTable: Row Preset Table Formatting Not Found : Header Formatting Options : ' . print_r($RowCell[1],true) . ' Table Formatting Options available/set: ' . print_r($this->tableFormatting,true),E_USER_ERROR); //-TEH
								}
							}else{
								$this->addTableRowCell($RowCell[0], $RowCell[1]);
							}
					
						}
					
					}else{
						$this->addTableRowCell($RowCell);
					}
				}
				
			}
			
			$this->endTableRow();
			
		}
		
		$this->endTable();
		
	}
	
	function renderTable(){
		
		$this->startTable();
		$this->setNumberOfColumns($this->numberOfColumns,$this->tableGridCols);
		if($this->tableHeader != null){
			$this->createTable($this->tableData,$this->tableHeader);
		}else{
			$this->createTable($this->tableData);
		}
		
		return $this->renderTableRaw();
		
	}
	
	
	// -- Converts Formatting parameters to Office Open XML standard (docx markup) -- 
	
	function inlineParametersToXMLString($formattedText, $parameters){
		
		// $inlineFormattingOptions = array('\tab' => '<w:tab/>');

		// $formattedText = array();	
		// foreach($inlineFormattingOptions as $inlineFormattingOption => $docxMarkup){
			// $formattedText = implode(wsd_raw_docx($docxMarkup), explode('{'. $inlineFormattingOption . '}', $string));
		// }
		
		return  '{RAWTEXT}' . $formattedText . '{RAWTEXT}';
		
	}
	
	function parametersToXMLString($parameters = array(), $propertyTag = null){
		
		$string = '';
		
		// if($parameters == null)
			// return '';
			
		if($propertyTag == null && in_array($propertyTag, array('tblPr','tcPr','rPr','pPr')))
			trigger_error("docxTableClass: parametersToXMLString: property tag is unsupported",E_USER_ERROR);
		
		$string .= '<w:'.$propertyTag.'>';
		
		switch($propertyTag){
			
			case 'tblPr':
				
				foreach($parameters as $key => $value){
				
					switch(strtolower($key)){
						
						case 'table align':
						case 'tablealign':
							
							if($value != '' && $value != null){
									
								switch(strtolower($value)){
									
									case 'justified':
									case 'both':
										
										trigger_error('docxTableClass: parametersToXMLString: Justified tables are not supported by MS Word please use "center"',E_USER_ERROR); //-TEH
										//$string .= '<w:jc w:val="both"/>';
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
						
						case 'table style':
						case 'tablestyle':
							
							if($value != null){
								
								$string .= '<w:tblStyle w:val="'.$value.'"/>';
								
							}else{
							
								$string .= '<w:tblStyle w:val="TableGrid"/>';
							}
							
						break;
						
						case 'all cell borders':
						case 'allcellborders':
						
							if($value == true){
								$string .= '<w:tblBorders>';
									$string .= '<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
								$string .= '</w:tblBorders>';
							}
							
						break;
						
					}
					
				}
				
				$string .= '<w:tblW w:w="0" w:type="auto"/>';
				
			break;
			
			case 'trPr':
				
				foreach($parameters as $key => $value){
				
					switch(strtolower($key)){
						
						case 'header row':
						case 'headerrow':
							
							if($value == true)
								$string .= '<w:tblHeader/>';
							
						break;
						
						case 'row height':
							
							$unitCMToTwips = 567;
							
							if($value == true){
								if(array_key_exists('height rule',$value)){
									switch($value['height rule']){
										case 'exact':
											$string .= '<w:trHeight w:hRule="exact" w:val="'.round($value['height'] * $unitCMToTwips).'"/>';
										break;
										case 'atLeast':
										default:
											$string .= '<w:trHeight w:val="'.round($value['height'] * $unitCMToTwips).'"/>';
										break;	
									}
								}else{
									$string .= '<w:trHeight w:val="'.round($value['height'] * $unitCMToTwips).'"/>'
								}
							}
							
						break;
						
					}
					
				}
				
			break;
			
			case 'tcPr':
				
				$string .= '<w:tcW w:w="'.$this->tableGridCols[$this->rowCellIndex].'" w:type="dxa"/>';
				
				foreach($parameters as $key => $value){
				
					switch(strtolower($key)){
						
						case 'cell align':
						case 'cellalign':
							
							if($value != '' && $value != null){
								
								switch(strtolower($value)){
									
									case 'top':
									case 'center':
									case 'bottom':
										$string .= '<w:vAlign w:val="'.strtolower($value).'"/>';
									
									break;
									
									default:
										trigger_error('docxTableClass: parametersToXMLString: Unsupported Cell Alignment',E_USER_ERROR);
									break;
								}
								
							}
							
						break;
						
						
						case 'cell margin':
						case 'cell margins':
						case 'cellmargins':
						
							$string .= '<w:tcMar>';
							
							if(array_key_exists('left', $value) || array_key_exists('Left', $value))
								$string .= '<w:left w:w="'.round($value['left'] * 566.67).'" w:type="dxa"/>';
							
							if(array_key_exists('right', $value) || array_key_exists('Right', $value))
								$string .= '<w:right w:w="'.round($value['right'] * 566.67).'" w:type="dxa"/>';
							
							if(array_key_exists('top', $value) || array_key_exists('Top', $value))
								$string .= '<w:top w:w="'.round($value['top'] * 566.67).'" />';
							
							if(array_key_exists('bottom', $value) || array_key_exists('Bottom', $value))
								$string .= '<w:bottom w:w="'.round($value['bottom'] * 566.67).'" w:type="dxa"/>';
							
							$string .= '</w:tcMar>';
								

							
						break;
						
						
						case 'shading':
						
							if(in_array(strtolower($value),array_keys($this->tableColorScheme)))
								$string .= '<w:shd w:val="clear" w:color="auto" w:fill="'.$this->tableColorScheme[strtolower($value)].'"/>';
							elseif(ctype_xdigit($value))
								$string .= '<w:shd w:val="clear" w:color="auto" w:fill="'.$value.'"/>';
							else
								trigger_error('docxTableClass: parametersToXMLString: unsupported table shading color: ' . $value . ', not found in docxtableclass color scheme OR unsupported Hex Color',E_USER_ERROR);
								
						break;
					
						
						case 'vmergestart':
						case 'vmerge start':
						
						
							if($value == true)
								$string .= '<w:vMerge w:val="restart"/>';
							
						break;
						
						case 'vmerge':
						case 'vmerge continue':
						case 'vmergecontinue':
						case 'vmerge end':
						case 'vmergeend':
							
							if($value == true)
								$string .= '<w:vMerge/>';
							
						break;
						
						case 'hmerge':
							
							if(is_int($value)){
								$string .= '<w:gridSpan w:val="'.$value.'"/>';
								$this->rowCellIndex += ($value - 1);
							}else
								trigger_error('docxTableClass: parametersToXMLString: hmerge : expected number',E_USER_ERROR); //-TEH
								
						break;
						
						case 'cell border':
						case 'cellborder':
						case 'cell borders':
						case 'cellborders':
						
							$string .= '<w:tcBorders>';
							
							if(array_key_exists('left', $value) || array_key_exists('Left', $value))
								if($value['left'] == false)
									$string .= '<w:left w:val="nil"/>';
								elseif($value['left'] == true)
									$string .= '<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
								
							if(array_key_exists('right', $value) || array_key_exists('Right', $value))
								if($value['right'] == false)
									$string .= '<w:right w:val="nil"/>';
								elseif($value['left'] == true)
									$string .= '<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									// $string .= '<w:shd w:val="clear" w:color="auto" w:fill="'.$this->tableColorScheme['red'].'"/>';
								
							if(array_key_exists('top', $value) || array_key_exists('Top', $value))
								if($value['top'] == false)
									$string .= '<w:top w:val="nil"/>';
								elseif($value['left'] == true)
									$string .= '<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									
							if(array_key_exists('bottom', $value) || array_key_exists('Bottom', $value))
								if($value['bottom'] == false)
									$string .= '<w:bottom w:val="nil"/>';
								elseif($value['left'] == true)
									$string .= '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									
							$string .= '</w:tcBorders>';
							
						break;
						
						
						case 'no cell border':
						case 'nocellborder':
						case 'no cell borders':
						case 'nocellborders':
							
								if($value == true){
									
									$string .= '<w:tcBorders>';
										$string .= '<w:left w:val="nil"/>';
										$string .= '<w:right w:val="nil"/>';
										$string .= '<w:top w:val="nil"/>';
										$string .= '<w:bottom w:val="nil"/>';
									$string .= '</w:tcBorders>';
								
								}else{
								
									$string .= '<w:tcBorders>';
										$string .= '<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
										$string .= '<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
										$string .= '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
										$string .= '<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '</w:tcBorders>';
								
								}
								
						break;
						
						case 'all cell borders':
						case 'allcellborders':
							
							if($value == true){
								
								$string .= '<w:tcBorders>';
									$string .= '<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
									$string .= '<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>';
								$string .= '</w:tcBorders>';
							
							}else{
							
								$string .= '<w:tcBorders>';
									$string .= '<w:left w:val="nil"/>';
									$string .= '<w:right w:val="nil"/>';
									$string .= '<w:top w:val="nil"/>';
									$string .= '<w:bottom w:val="nil"/>';
								$string .= '</w:tcBorders>';
							
							}
							
						break;
						
					}
					
				}
				
			break;
			
			case 'rPr':
				
				foreach($parameters as $key => $value){
				
					switch(strtolower($key)){
						
						case 'bold':
							
							if($value == true)
								$string .= '<w:b/>';
							else
								$string .= '<w:b w:val="0"/>';
							
						break;
						
						case 'italic':
							
						
							if($value == true)
								$string .= '<w:i/>';
							else
								$string .= '<w:i w:val="0"/>';
								
						break;
						
						case 'underline':
							
							
							if($value == true)
								$string .= '<w:u w:val="single"/>';
							else
								$string .= '<w:u w:val="none"/>';
								
						break;
						
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
						case 'doublestrikethrough':
						
						
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
						case 'allcaps':
							
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
						case 'fontfamily':
							
							$string .= '<w:rFonts w:ascii="'.$value.'" w:hAnsi="'.$value.'"/>';
							
						break;
						
						case 'font size':
						case 'fontsize':
						case 'text size':
						case 'textsize':
							
							if(is_int($value))
								$string .= '<w:sz w:val="'.($value*2).'"/>';
							else
								trigger_error('docxTableClass: parametersToXMLString: unsupported text size value: ' . $value,E_USER_ERROR);
						
						break;
						
						case 'font color':
						case 'fontcolor':
						case 'text color':
						case 'textcolor':
						
							if(in_array(strtolower($value),array_keys($this->tableColorScheme)))
								$string .= '<w:color w:val="'.$this->tableColorScheme[strtolower($value)].'"/>';
							elseif(ctype_xdigit($value))
								$string .= '<w:color w:val="'.$value.'"/>';
							else
								trigger_error('docxTableClass: parametersToXMLString: unsupported table text color: ' . $value . ', not found in docxtableclass color scheme OR unsupported Hex Color',E_USER_ERROR);
							
						break;

					}
					
				}
				
			break;
			
			case 'pPr':
				
				foreach($parameters as $key => $value){
				
					switch(strtolower($key)){

						case 'paragraph style':
						case 'paragraphstyle':
						
							$string .= '<w:pStyle w:val="'.$value.'"/>';
						
						break;
						
						case 'text align':
						case 'textalign':
						
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
						case 'indenttext':
						
							$string .= '<w:ind ';
							
							if(array_key_exists('left', $value) || array_key_exists('Left', $value))
								$string .= 'w:left="'. round($value['left'] * 566.67) .'" ';
								
							if(array_key_exists('right', $value) || array_key_exists('Right', $value))
								$string .= ' w:right="'. round($value['right']*566.67).'"';
							
							$string .= '/>';
							
							
						break;
						
						
						case 'text spacing':
						case 'textspacing':
						
							$string .= '<w:spacing ';
							
							if(array_key_exists('before', $value) || array_key_exists('Before', $value))
								$string .= 'w:before="'. $value['before']*20 .'" ';
								
							if(array_key_exists('after', $value) || array_key_exists('After', $value))
								$string .= ' w:after="'. $value['after']*20 .'"';
							
							if(array_key_exists('line', $value) || array_key_exists('Line', $value))
								$string .= ' w:line="'. round($value['line']*240) .'" w:lineRule="auto"';
							
							$string .= '/>';
							
						break;
						
						
						case 'keep with next':
						case 'keepwithnext':
							if($value == true)
								$string .= '<w:keepNext/>';
						break;
						
						case 'keep lines together':
						case 'keeplinestogether':
							if($value == true)
								$string .= '<w:keepLines/>';
						break;
					}
					
				}
				
			break;
				
		}
		
		$string .= '</w:'.$propertyTag.'>';
		
		return $string;
		
	}
	
	
	// -- Table Options --
	
	function repeatHeaderAcrossPages($state = true){
		
		if($state){
			$this->tableFormatting['docxClass:headerRowFormattingOptions']['header row'] = true;
		}else{
			$this->tableFormatting['docxClass:headerRowFormattingOptions']['header row'] = false;
		}
		
	}
	
	function keepWithNext($state = true){
		
		if($state){
			$this->tableFormatting['docxClass:cellParagraphFormattingOptions']['keep with next'] = true;
		}else{
			$this->tableFormatting['docxClass:cellParagraphFormattingOptions']['keep with next'] = false;
		}
		
	}
	
	function keepLinesTogether($state = true){
		
		if($state){
			$this->tableFormatting['docxClass:cellParagraphFormattingOptions']['keep lines together'] = true;
		}else{
			$this->tableFormatting['docxClass:cellParagraphFormattingOptions']['keep lines together'] = false;
		}
		
	}
	
	function applyAllCellBorders($state = true){
		
		if($state){
			$this->tableFormatting['docxClass:tableFormattingOptions']['all cell borders'] = true;
		}else{
			$this->tableFormatting['docxClass:tableFormattingOptions']['all cell borders'] = false;
		}
		
	}
	
	function preserveTableSpacing($state = true, $spacingBefore = 1, $spacingAfter = 1){
		
		if($state){
			
			if($spacingBefore >= 0){
			
				$this->tableFormatting['docxClass:preTableSpacingFix'] = array(
					'text spacing' => array(
						'before' => 0, 
						'after' => 0, 
						'line' => $spacingBefore
					)
				);
			
			}else{
				trigger_error('docxClass: preserveTableSpacing: spacing before must be equal to or greater than 0',E_USER_ERROR); //-TEH
			}	
			
			if($spacingAfter >= 0){
				
				$this->tableFormatting['docxClass:postTableSpacingFix'] = array(
					'text spacing' => array(
						'before' => 0, 
						'after' => 0, 
						'line' => $spacingAfter
					)
				);
			
			}else{
				trigger_error('docxClass: preserveTableSpacing: spacing after must be equal to or greater than 0',E_USER_ERROR); //-TEH
			}
				
		}else{
			
			$this->tableFormatting['docxClass:preTableSpacingFix'] = array(
				'text spacing' => array(
					'before' => 0, 
					'after' => 0, 
					'line' => 0.1
				)
			);
			
			
			$this->tableFormatting['docxClass:postTableSpacingFix'] = array(
				'text spacing' => array(
					'before' => 0, 
					'after' => 0, 
					'line' => 0.1
				)
			);
			
		}
		
	}
	
	function setDefaultColumnWidth($columnWidth = null, $format = 'twips'){
		
		switch($format){
			
			case 'twips':
				
				if($columnWidth != null && (is_int($columnWidth) || is_float($columnWidth) || is_double($columnWidth)))
					$this->columnWidth = $columnWidth * $this->units['twips'];
		
			break;
			
			case 'cm':
			case 'centimeters':
				if($columnWidth != null && (is_int($columnWidth) || is_float($columnWidth) || is_double($columnWidth)))
					$this->columnWidth = $columnWidth * $this->units['centimeters'];
				
			break;
			
			case '%':
			case 'percent':
				if($columnWidth != null && (is_int($columnWidth) || is_float($columnWidth) || is_double($columnWidth)))
					$this->columnWidth = $columnWidth / $this->units['percent']/100 * $this->units['centimeters'];
				
			break;
			
			
		}
		
		$this->columnWidth = round($this->columnWidth);
		
	}
	
	function setRowSpacing($spacing = null, $exactSpacing = false){
		
		$hRule = 'atLeast';
		if($exactSpacing){
			$hRule = 'exact';
		}
		
		$this->updateGlobalTableFormattingOptions('docxClass:rowFormattingOptions',array('row height' => array('height' => $spacing, 'height rule' => $hRule)));
		
	}
	
	function updateTableFormatting($parameters = null){
		
		if($parameters != null && count($parameters) != 0){
			$this->updateGlobalTableFormattingOptions('docxClass:tableFormattingOptions',$parameters);
		}
		
	}
	
}

?>