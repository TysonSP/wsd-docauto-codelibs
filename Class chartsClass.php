<?php

// RBC ChartsClass

class payoffChartClass{
	
	var $docx = null;
	var $debugArray;
	var $displayFloorLevelAnnotation = true;
	
	var $issuerSchema = NULL;

	var $denomination;

	var $productType;
	
	var $underlying_x;
	var $underlying_y;
	
	var $derivative_x;
	var $derivative_y;
	
	var $derivative_x_1;
	var $derivative_y_1;
	
	var	$derivativeLineSplitApplies;
	
	var $derivativeLineColor;
	var $derivativeLineWeight;
	var $derivativeLineStyle;
	
	var $underlyingLineColor;
	var $underlyingLineWeight;
	var $underlyingLineStyle;
	
	var $polygonOutlineColor;
	var $polygonFillColor;
	
	var $highlightAreasWhereUnderlyingOutperformsDerivative = true;
	
	var $chartWidth = 1200;
	var $chartHeight = 600;
	
	var $showAnnotations = true;
	
	var $fontFamily;
	
	var $statedPrincipalAmountTerminology = 'Stated principal amount';
	var $bufferZoneTerminology = 'Buffer zone';	
	var $barrierZoneTerminology = 'Barrier zone';
	var $participationZoneTerminology = 'Participation zone';
	var $digitalZoneTerminology = 'Digital zone';
	var $fixedReturnAmountTerminology = 'Fixed return amount';
	var $minimumReturnAtMaturityTerminology = "Minimum payment\nat maturity";
	var $maximumReturnAmountTerminology = 'Maximum payment\nat maturity';

	
	var $polygons = array();
	
	var $fixedReturnAmountPercentage = NULL;
	var $maximumReturnAmountPercentage = NULL;
	var $maximumReturnAmountAbsolute = NULL;
	var $bufferPercentage = NULL;
	var $barrierPercentage = NULL;
	var $floorPercentage = NULL;
	var $leverageFactor = 100;
	var $boosterLevel = NULL;
	var $floorPercentageGraph = 5;
		
	var $xAxisTitle;
	var $yAxisTitle;

	function __construct($docx = null) {
		
		if($docx == null)
			trigger_error('DocX Formatting Class not initialized',E_USER_ERROR); //-TEH
		else
			$this->docx = $docx;
		
		$this->denomination = 1000;
		
		$this->derivativeLineSplitApplies = false;
		
		$this->derivativeLineColor = [100, 100, 100];
		$this->derivativeLineWeight = 5;
		$this->derivativeLineStyle = 'solid';
		$this->underlyingLineColor = [150, 150, 150];
		$this->underlyingLineWeight = 5;
		$this->underlyingLineStyle = 'dashed';
		$this->polygonOutlineColor = [100, 100, 100];
		$this->polygonOutlineAlpha = 255;
		$this->polygonOutlineWeight = 1;
		$this->polygonOutlineStyle = 'solid';
		$this->polygonFillColor = [200, 200, 200];
		$this->polygonFillAlpha = 255;

		$this->xAxisTitle = 'Underlying performance';
		$this->yAxisTitle = 'Payment at maturity';

		$this->fontFamily = 'Arial';
		
	}
	
	function setProductTypeProperty($property, $value){
	
		switch($property){
		
			case 'Fixed return amount percentage':
			
				$this->fixedReturnAmountPercentage = $value;
			
			break;
		
			case 'Maximum return amount percentage':
			
				$this->maximumReturnAmountPercentage = $value;
			
			break;

			case 'Leverage factor':
			
				$this->leverageFactor = $value;
			break;
			
			case 'Buffer percentage':
			
				$this->bufferPercentage = $value;
			
			break;

			case 'Barrier percentage':
			
				$this->barrierPercentage = $value;
				
			break;
			
			case 'Floor percentage':
			
				$this->floorPercentage = $value;
				
				if($value < 2)
					$this->floorPercentageGraph += $value;
				else
					$this->floorPercentageGraph = $value;
				
			break;
			
			case 'Booster level':
			
				$this->boosterLevel = $value;
				
			break;

		}
	
	}
	
	function setProductType($productType) {
	
		$this->productType = $productType;
	
	}

	function setIssuerSchema($issuerSchema) {
	
		$this->issuerSchema = $issuerSchema;
		
		$this->__setPlotPropertiesForIssuerSchema();
	
	}
	
	function setXYAxisRange() {
		
		//CAP LEVEL SET
		if($this->maximumReturnAmountPercentage != 0){
		
			if($this->maximumReturnAmountPercentage / $this->denomination > 1.6){

				// $this->__yAxisRange = Array(0, 300);
				$this->__yAxisRange = Array(0, 200);
			
			} else {
			
				if($this->maximumReturnAmountPercentage / $this->denomination > 1.4){

					// $this->__yAxisRange = Array(0, 275);
					$this->__yAxisRange = Array(0, 200);
				
				} else {
				
					if($this->maximumReturnAmountPercentage / $this->denomination > 1.2){
						
						// $this->__yAxisRange = Array(0, 250);
						$this->__yAxisRange = Array(0, 200);
						
					} else {
						
						$this->__yAxisRange = Array(0, 200);	
						
					}
				}
			}
		
		//NO CAP LEVEL SET
		}else{
		
			if($this->fixedReturnAmountPercentage / $this->denomination > 1.4){
				
				$this->__yAxisRange = Array(0, 200);
			
			}else{
				
				// if ($this->fixedReturnAmountPercentage / $this->denomination > 1.4) {
				
					// $this->__yAxisRange = Array(0, 275);
				
				// } else {
				
					if($this->fixedReturnAmountPercentage / $this->denomination > 1.2){
						
						$this->__yAxisRange = Array(0, 200);
						
					}else{
					
						$this->__yAxisRange = Array(0, 200);
						
					}
					
				// }
			}
		}
		
		$this->__xAxisRange = Array(0, 200);
		
	}
		
	function __setPlotPropertiesForIssuerSchema(){

		if($this->issuerSchema == NULL){
			
			trigger_error('payoffChartClass: no issuer schema specified', E_USER_ERROR);
	
		}
		
		switch($this->issuerSchema){
		
			case 'Citi blue':
			case 'Citi green':
			case 'Citi orange':
			
				$this->underlyingLineColor = array(0, 0, 0);
				$this->underlyingLineWeight = 5;
				$this->underlyingLineStyle = 'solid';
				$this->derivativeLineWeight = 5;
				$this->derivativeLineStyle = 'solid';
				$this->polygonOutlineAlpha = 255;
				$this->polygonOutlineWeight = 1;
				$this->polygonOutlineStyle = 'solid';
				$this->polygonFillAlpha = 255;
				$this->highlightAreasWhereUnderlyingOutperformsDerivative = false;
				
			break;
		
			case 'RBC Canada':

				$this->chartWidth = 1100;
				$this->chartHeight = 500;
				$this->fontFamily = 'TimesRoman';
				$this->underlyingLineColor = array(0, 0, 0);
				$this->underlyingLineWeight = 5;
				$this->underlyingLineStyle = 'dashed';
				$this->derivativeLineWeight = 5;
				$this->derivativeLineStyle = 'solid';
				$this->polygonOutlineAlpha = 255;
				$this->polygonOutlineWeight = 1;
				$this->polygonOutlineStyle = 'solid';
				$this->polygonFillAlpha = 255;
				$this->highlightAreasWhereUnderlyingOutperformsDerivative = false;
				
			break;
			
			case 'BMO':
				
				$this->denomination = 100;
				
				$this->chartWidth = 1100;
				$this->chartHeight = $this->chartWidth * (10 / 16) ;
				
				// $this->chartWidth = 1000;
				// $this->chartHeight = 1000;
				
				$this->fontFamily = 'Arial';
				
				$this->underlyingLineColor = array(255, 0, 0);
				$this->underlyingLineWeight = 2;
				$this->underlyingLineStyle = 'solid';
				
				$this->derivativeLineWeight = 5;
				$this->derivativeLineStyle = 'solid';
				
				$this->polygonOutlineAlpha = 255;
				$this->polygonOutlineWeight = 1;
				$this->polygonOutlineStyle = 'solid';
				$this->polygonFillAlpha = 255;
				
				$this->highlightAreasWhereUnderlyingOutperformsDerivative = false;
				
			break;
			
			default:
			
				trigger_error('payoffChartClass: issuer invalid schema', E_USER_ERROR);
				
			break;
		}
		
		switch($this->issuerSchema){
		
			case 'Citi blue':
			
				$this->derivativeLineColor = array(41, 109, 193);
				$this->polygonOutlineColor = array(41, 109, 193);
				$this->polygonFillColor = array(220, 235, 244);
				
			break;
			
			case 'Citi green':
			
				$this->derivativeLineColor = array(89, 174, 64);
				$this->polygonOutlineColor = array(89, 174, 64);
				$this->polygonFillColor = array(234, 243, 229);
				
			break;
			
			case 'Citi orange':
			
				$this->derivativeLineColor = array(225, 148, 49);
				$this->polygonOutlineColor = array(225, 148, 49);
				$this->polygonFillColor = array(251, 240, 232);
				
			break;

			case 'RBC Canada':
			
				$this->underlyingLineColor = array(0, 15, 126);
				$this->derivativeLineColor = $this->polygonOutlineColor = $this->polygonFillColor = array(255, 0, 0);
				
			break;
			
			case 'BMO':
			
				$this->underlyingLineColor = array(255, 0, 0);
				$this->polygonOutlineColor = $this->polygonFillColor = array(180, 180, 180);
				$this->derivativeLineColor = array(0, 0, 0);
				
			break;

		}
	
		$this->initializeTerminology();
	}

	function initializeTerminology(){
	
		if($this->issuerSchema == NULL){
			
			trigger_error('payoffChartClass: no issuer schema specified', E_USER_ERROR);
	
		}
		
		switch($this->issuerSchema){
		
			case 'Citi blue':
			case 'Citi green':
			case 'Citi orange':
			
				$this->showAnnotations = true;
				$this->statedPrincipalAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . $docx->softReturn() . 'Stated Principal Amount';
				$this->bufferZoneTerminology = 'Buffer Zone';	
				$this->barrierZoneTerminology = 'Barrier Zone';
				$this->digitalZoneTerminology = 'Digital Zone';
				$this->participationZoneTerminology = 'Participation' . $docx->softReturn() . 'Zone';
				
				 // What Citi calls the maximum return return at maturity in a participation structure, we call the fixed return amount
				if(stristr($this->productType, 'Participation')){
					
					$this->fixedReturnAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), ',##0.00') . $docx->softReturn() . '($' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . ' plus' . $docx->softReturn() . 'Maximum Payment at Maturity)';
				
				}else{
					
					$this->fixedReturnAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), ',##0.00') . $docx->softReturn() . '($' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . ' plus' . $docx->softReturn() . 'Fixed Return Amount)';
					
				}
				
				$this->maximumReturnAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema($this->maximumReturnAmountPercentage), ',##0.00') . ' Maximum' .$docx->softReturn() . 'Payment at Maturity';
				$this->minimumReturnAtMaturityTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100 - $this->bufferPercentage), ',##0.00') . $docx->softReturn() . 'Minimum payment' . $docx->softReturn() . 'at Maturity';
				
			break;
		
			case 'RBC Canada':
			
				$this->showAnnotations = true;
				$this->statedPrincipalAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . $docx->softReturn() . 'Principal Amount';
				$this->bufferZoneTerminology = 'Buffer zone';	
				$this->barrierZoneTerminology = 'Barrier zone';
				$this->digitalZoneTerminology = 'Booster zone';
				$this->participationZoneTerminology = 'Participation' . $docx->softReturn() . 'Zone';
				
				// What Citi calls the maximum return return at maturity in a participation structure, we call the fixed return amount
				if(stristr($this->productType, 'Participation')){
					
					$this->fixedReturnAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), ',##0.00') . $docx->softReturn() . '($' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . ' plus' . $docx->softReturn() . 'Cap)';
				
				}else{
					
					$this->fixedReturnAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), ',##0.00') . $docx->softReturn() . '($' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . ' plus' . $docx->softReturn() . 'Booster Amount)';
					
				}
				
				$this->maximumReturnAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema($this->maximumReturnAmountPercentage), ',##0.00') . ' Cap';
				$this->minimumReturnAtMaturityTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100 - $this->bufferPercentage), ',##0.00') . $docx->softReturn() . 'Minimum payment' . $docx->softReturn() . 'at Maturity'	;
				
			break;
			
			case 'BMO':
				
				
				$this->showAnnotations = true;
				
				$this->statedPrincipalAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . $docx->softReturn() . 'Principal Amount';
				
				$this->bufferZoneTerminology =  $this->bufferPercentage . '% Buffer Level';	
				
				$this->barrierZoneTerminology = 'Barrier Level = ' . $this->barrierPercentage . '% of '. $docx->softReturn() . ' the Initial Index Level';
				
				$this->digitalZoneTerminology = 'Boosted Return = ' . ($this->fixedReturnAmountPercentage - 100) . '%';
				
				$this->participationZoneTerminology = $this->leverageFactor .'% Upside Participation';
				
				// What Citi calls the maximum return return at maturity in a participation structure, we call the fixed return amount
				if(stristr($this->productType, 'Participation')){
					
					$this->fixedReturnAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), ',##0.00') . $docx->softReturn() . '($' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . ' plus' . $docx->softReturn() . 'Cap)';
				
				}else{
					
					$this->fixedReturnAmountTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), ',##0.00') . $docx->softReturn() . '($' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100), ',##0.00') . ' plus' . $docx->softReturn() . 'Booster Amount)';
				
				}
				
				// $this->maximumReturnAmountTerminology = ' Cap Level = ' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(((($this->maximumReturnAmountPercentage / 100.0 - 1)   * $this->leverageFactor/ 100.0) ) * $this->denomination), ',##0.00') . '%';
				
				$this->maximumReturnAmountTerminology = ' Cap Level = ' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(((($this->maximumReturnAmountPercentage / 100.0 - 1)) ) * $this->denomination), ',##0.00') . '%';
				
				
				$this->minimumReturnAtMaturityTerminology = '$' . wsd_decimal_format($this->__adjustYCoordinateForIssuerSchema(100 - $this->bufferPercentage), ',##0.00') . $docx->softReturn() . 'Minimum payment' . $docx->softReturn() . 'at Maturity'	;
				
			break;
			
			default:
			
				trigger_error('payoffChartClass: issuer invalid schema', E_USER_ERROR);
			
			break;
			
		}
		
	}

	function __adjustXCoordinateForIssuerSchema($xCoordinate) {

		if($this->issuerSchema == NULL){
			
			trigger_error('payoffChartClass: no issuer schema specified', E_USER_ERROR);
	
		}
		
		switch($this->issuerSchema){
			
			case 'Citi blue':
			case 'Citi green':
			case 'Citi orange':
			case 'RBC Canada':
			case 'BMO':
			
				return ($xCoordinate - 100) / 100.0;
			
			break;
			
			default:
			
				trigger_error('payoffChartClass: issuer invalid schema', E_USER_ERROR);
			
			break;
			
		}
		
	}

	function __adjustXEdgesForIssuerSchema($edges){

		$adjustedEdges = array();
		
		foreach($edges as $edge){
			
			array_push($adjustedEdges, $this->__adjustXCoordinateForIssuerSchema($edge));
			
		}
		
		return $adjustedEdges;
	
	}
	
	function __adjustYCoordinateForIssuerSchema($yCoordinate){

		if($this->issuerSchema == NULL){
			
			trigger_error('payoffChartClass: no issuer schema specified', E_USER_ERROR);
	
		}
		
		switch($this->issuerSchema){
			
			case 'Citi blue':
			case 'Citi green':
			case 'Citi orange':
			case 'RBC Canada':
			case 'BMO':
			
				return $yCoordinate / 100.0 * $this->denomination;
			
			break;
			
			default:
			
				trigger_error('payoffChartClass: issuer invalid schema', E_USER_ERROR);
				
			break;
			
		}
		
	}

	function __adjustYEdgesForIssuerSchema($edges) {

		$adjustedEdges = array();		
		
		foreach($edges as $edge){
			
			array_push($adjustedEdges, $this->__adjustYCoordinateForIssuerSchema($edge));
			
		}
		
		return $adjustedEdges;
	
	}

	function setDenomination($denomination) {

		$this->denomination = $denomination;
		
	}
	
	function setXAxisTitle($xAxisTitle) {
	
		$this->xAxisTitle = $xAxisTitle;
	
	}
	
	function setYAxisTitle($yAxisTitle) {
	
		$this->yAxisTitle = $yAxisTitle;
	
	}
	
	function __addPolygon($xEdges, $yEdges) {
	
		$polygon = array();
		
		$polygon['xEdges'] = $xEdges;
		$polygon['yEdges'] = $yEdges;

		array_push($this->polygons, $polygon);

	}
	
	function __createPolygonArray($polygon) {
	
		$adjustedXEdges = $this->__adjustXEdgesForIssuerSchema($polygon['xEdges']);
		$adjustedYEdges = $this->__adjustYEdgesForIssuerSchema($polygon['yEdges']);
		
		$polygonArray = array();

		foreach($adjustedXEdges as $key => $value){
			
			array_push($polygonArray, $adjustedXEdges[$key]);
			array_push($polygonArray, $adjustedYEdges[$key]);
		
		}
		
		return $polygonArray;
	
	}
	
	function __calculateEdges(){ //Product Family Specific information

		$this->underlying_x = [0, 200];
		$this->underlying_y = [0, 200];
		
		$this->setXYAxisRange();
		
		switch($this->productType){ //ALT 5

			case 'Digital':{
				
				$this->derivative_x = [0, 100, 100, 250];
				$this->derivative_y = [0, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage];
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				$this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				$this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
				
				if($this->highlightAreasWhereUnderlyingOutperformsDerivative){
					
					$this->__addPolygon([$this->fixedReturnAmountPercentage, 250, 250], [$this->fixedReturnAmountPercentage, 250, $this->fixedReturnAmountPercentage]);
				
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->statedPrincipalAmountTerminology,
						'x' => 50,
						'y' => 100 + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]/*, [
					
						'terminology' => $this->digitalZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 100) / 2.0,
						'y' => (3 * $this->fixedReturnAmountPercentage + 100) / 4.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					
					]*/, [
					
						'terminology' => $this->fixedReturnAmountTerminology,
						'x' => (100 + $this->fixedReturnAmountPercentage) / 2.0,
						'y' => $this->fixedReturnAmountPercentage + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]
					
				];
				
			}break;
			
			case 'Enhanced digital':{
				
				$this->derivative_x = [0, 100, 100, 250];
				$this->derivative_y = [0, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage];
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				$this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				$this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
				
				if($this->highlightAreasWhereUnderlyingOutperformsDerivative){
					
					$this->__addPolygon([$this->fixedReturnAmountPercentage, 250, 250], [$this->fixedReturnAmountPercentage, 250, $this->fixedReturnAmountPercentage]);
				
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->statedPrincipalAmountTerminology,
						'x' => 50,
						'y' => 100 + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]/*, [
					
						'terminology' => $this->digitalZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 100) / 2.0,
						'y' => (3 * $this->fixedReturnAmountPercentage + 100) / 4.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					
					]*/, [
					
						'terminology' => $this->fixedReturnAmountTerminology,
						'x' => (100 + $this->fixedReturnAmountPercentage) / 2.0,
						'y' => $this->fixedReturnAmountPercentage + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]
					
				];
				
			}break;
			
			case 'Digital plus':{
				
				$this->derivative_x = [0, 1, 100, 100, $this->fixedReturnAmountPercentage, 200];
				$this->derivative_y = [1, 1,  100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, (100 + (100 * $this->leverageFactor/100))];
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],
				
				];
				
			}break;
			
			case 'Enhanced digital plus':{
				
				$this->derivativeLineSplitApplies = true;
				
				$this->derivative_x_1 = [0,1, $this->boosterLevel];
				$this->derivative_y_1 = [1,1, $this->boosterLevel];
				
				$this->derivative_x = [$this->boosterLevel , $this->fixedReturnAmountPercentage, 200];
				$this->derivative_y = [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, (100 + (100 * $this->leverageFactor/100))];
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([$this->boosterLevel, $this->boosterLevel, $this->fixedReturnAmountPercentage], [$this->boosterLevel, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]
				
				];
				
			}break;
			
			case 'Capped digital plus':{
			
				$this->maximumReturnAmountAbsolute =  100 + ($this->leverageFactor/100 * ($this->maximumReturnAmountPercentage - 100));
				
				$this->derivative_x = [0, 1, 100, 100, $this->fixedReturnAmountPercentage,$this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [1, 1, 100, $this->fixedReturnAmountPercentage,  $this->fixedReturnAmountPercentage, $this->maximumReturnAmountAbsolute, $this->maximumReturnAmountAbsolute];
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100)),
						'pointerLength' => 35,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->maximumReturnAmountTerminology,
						'x' => $this->maximumReturnAmountPercentage,
						'y' => $this->maximumReturnAmountAbsolute,
						'pointerLength' => 25,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]
				
				];
				
			}break;
			
			case 'Capped enhanced digital plus':{
			
				$this->derivativeLineSplitApplies = true;
				
				$this->maximumReturnAmountAbsolute =  100 + ($this->leverageFactor/100 * ($this->maximumReturnAmountPercentage - 100));
				
				$this->derivative_x_1 = [0,1, $this->boosterLevel];
				$this->derivative_y_1 = [1,1, $this->boosterLevel];
				
				$this->derivative_x = [$this->boosterLevel , $this->fixedReturnAmountPercentage,$this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage,  $this->maximumReturnAmountAbsolute,  $this->maximumReturnAmountAbsolute];
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([$this->boosterLevel, $this->boosterLevel, $this->fixedReturnAmountPercentage], [$this->boosterLevel, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->participationZoneTerminology,
						'x' =>  ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 25,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->maximumReturnAmountTerminology,
						'x' => $this->maximumReturnAmountPercentage,
						'y' => $this->maximumReturnAmountAbsolute,
						'pointerLength' => 25,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]
				
				];
				
			}break;
			
			case 'Buffered digital':{
			
				$this->derivative_x = [0, 0,$this->bufferPercentage, 100, 100, 250];
				$this->derivative_y = [100 - $this->bufferPercentage,100 - $this->bufferPercentage, 100, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage];
				$this->horizontalReferenceLines = [[$this->bufferPercentage, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[$this->bufferPercentage, 100], [100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				$this->xTicks = [0, 50, $this->bufferPercentage, 100, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountPercentage, 150, 250];
				$this->yTicks = [0, 50, 100 - $this->bufferPercentage, 100, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountPercentage, 150, 250];
				$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
				$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
				
				if($this->highlightAreasWhereUnderlyingOutperformsDerivative){
					
					$this->__addPolygon([$this->fixedReturnAmountPercentage, 250, 250], [$this->fixedReturnAmountPercentage, 250, $this->fixedReturnAmountPercentage]);
				
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->statedPrincipalAmountTerminology,
						'x' => 50 + ($this->bufferPercentage < 70 ? 25 : 0),
						'y' => 100 + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]/*, [
					
						'terminology' => $this->bufferZoneTerminology,
						'x' => ($this->bufferPercentage * 2 + 100) / 3.0,
						'y' => ($this->bufferPercentage * 2 + 100) / 3.0 + (100 - $this->bufferPercentage) / 3.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					
					]*/, [
					
						'terminology' => $this->fixedReturnAmountTerminology,
						'x' => (100 + $this->fixedReturnAmountPercentage) / 2.0,
						'y' => $this->fixedReturnAmountPercentage + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]/*, [
					
						'terminology' => $this->digitalZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 100) / 2.0,
						'y' => (3 * $this->fixedReturnAmountPercentage + 100) / 4.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					
					]*/, [
						
						'terminology' => $this->minimumReturnAtMaturityTerminology,
						'x' => 2.5,
						'y' => 100 - $this->bufferPercentage + 5,
						'pointerLength' => 137,
						'pointerAngle' => 5.5,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]
					
				];
				
			}break;

			case 'Buffered digital plus':{
				
				$this->derivative_x = [0,0, $this->bufferPercentage, 100, 100, $this->fixedReturnAmountPercentage, 200];
				
				$this->derivative_y = [100 - $this->bufferPercentage,100 - $this->bufferPercentage ,100, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, (100 + (100 * $this->leverageFactor/100))];
				
				$this->horizontalReferenceLines = [[$this->bufferPercentage, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[$this->bufferPercentage, 100], [100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, $this->bufferPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100 - $this->bufferPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
					
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->bufferZoneTerminology,
						'x' => $this->bufferPercentage,
						'y' => 100,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]/*[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]*/
					
				];
				
			}break;

			case 'Capped buffered digital plus':{
				
				
				// $this->derivative_x = [0, 1, 100, 100, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountPercentage, 200];
				// $this->derivative_y = [1, 1, 100, $this->fixedReturnAmountPercentage,  $this->fixedReturnAmountPercentage, $this->maximumReturnAmountAbsolute, $this->maximumReturnAmountAbsolute];
				
				//----
				
				$this->maximumReturnAmountAbsolute =  100 + ($this->leverageFactor/100 * ($this->maximumReturnAmountPercentage - 100));
				
				$this->derivative_x = [0, $this->bufferPercentage, 100, 100, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [100 - $this->bufferPercentage, 100, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountAbsolute, $this->maximumReturnAmountAbsolute];
				
				$this->horizontalReferenceLines = [[$this->bufferPercentage, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[$this->bufferPercentage, 100], [100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, $this->bufferPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100 - $this->bufferPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
					
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 25,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->bufferZoneTerminology,
						'x' => $this->bufferPercentage,
						'y' => 100,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->maximumReturnAmountTerminology,
						'x' => $this->maximumReturnAmountPercentage,
						'y' => $this->maximumReturnAmountAbsolute,
						'pointerLength' => 25,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]/*[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]*/
					
				];
			
			}break;

			case 'Barrier digital':{
				
				$this->derivative_x = [0, $this->barrierPercentage, $this->barrierPercentage, 100, 100, 250];
				$this->derivative_y = [0, $this->barrierPercentage, 100, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage];
				
				$this->horizontalReferenceLines = [[$this->barrierPercentage, $this->barrierPercentage], [$this->barrierPercentage, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[$this->barrierPercentage, $this->barrierPercentage], [100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				$this->xTicks = [0, 50, $this->barrierPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				$this->yTicks = [0, 50, $this->barrierPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->__addPolygon([$this->barrierPercentage, $this->barrierPercentage, 100], [$this->barrierPercentage, 100, 100]);
				$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
				
				if($this->highlightAreasWhereUnderlyingOutperformsDerivative){
					
					$this->__addPolygon([$this->fixedReturnAmountPercentage, 250, 250], [$this->fixedReturnAmountPercentage, 250, $this->fixedReturnAmountPercentage]);
					
				}
				
				$this->annotations = [
					
					[
					
						'terminology' => $this->statedPrincipalAmountTerminology,
						'x' => 50,
						'y' => 100 + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]/*, [
					
						'terminology' => $this->barrierZoneTerminology,
						'x' => ($this->barrierPercentage * 2 + 100) / 3.0,
						'y' => ($this->barrierPercentage * 2 + 100) / 3.0 + (100 - $this->barrierPercentage) / 3.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					
					]*/, [
					
						'terminology' => $this->fixedReturnAmountTerminology,
						'x' => (100 + $this->fixedReturnAmountPercentage) / 2.0,
						'y' => $this->fixedReturnAmountPercentage + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					
					]/*, [
						
						'terminology' => $this->digitalZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 100) / 2.0,
						'y' => (3 * $this->fixedReturnAmountPercentage + 100) / 4.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
						
					]*/
					
				];	
				
			}break;

			case 'Barrier digital plus':{
				
				$this->derivativeLineSplitApplies = true;
				
				$this->derivative_x_1 = [0,1, $this->barrierPercentage];
				$this->derivative_y_1 = [1,1, $this->barrierPercentage];
				
				$this->derivative_x = [$this->barrierPercentage, 100, 100, $this->fixedReturnAmountPercentage, 200];
				$this->derivative_y = [100, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, (100 + (100 * $this->leverageFactor/100))];
				
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						
						$this->polygonFillColor = array(120,159,253);
						
						
						//Barrier Area - covers the entire Barier Level Zone
						$this->__addPolygon(
						
							[$this->barrierPercentage, $this->barrierPercentage, 100, 100], 
							[0, 100, 100, 0],
							
						);
						
					break;
					
					default:
					
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage),
						'y' => ($this->fixedReturnAmountPercentage),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->barrierZoneTerminology,
						'x' => $this->barrierPercentage,
						'y' => 100,
						'pointerLength' => 85,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_CENTER'
					],[
						'terminology' => 'PROTECTION BARRIER',
						'x' => (100 - (100 - $this->barrierPercentage)) + ((100 - $this->barrierPercentage) / 2),
						'y' => 15,
						/* 'x' => 100,
						'y' => 100, */
						'type' => 'RotateAnnotationWithoutPointer',
							'rotationAngle' => pi() /180 * 270,
						//'rotationAngle' => -atan(45),
						'textColor' => array(255,255,255),
						'size' => 21,
						'textStyle' => 0
					]/*[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],*/
				
				];
			
			}break;

			case 'Capped barrier digital plus':{
				
			
				$this->derivativeLineSplitApplies = true;
				
				$this->maximumReturnAmountAbsolute =  100 + ($this->leverageFactor/100 * ($this->maximumReturnAmountPercentage - 100));
				
				$this->derivative_x_1 = [0,1, $this->barrierPercentage];
				$this->derivative_y_1 = [1,1, $this->barrierPercentage];
				
				$this->derivative_x = [$this->barrierPercentage, 100, 100, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [100, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountAbsolute, $this->maximumReturnAmountAbsolute];
				
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						$this->polygonFillColor = array(120,159,253);
						
						
						//Barrier Area - covers the entire Barier Level Zone
						$this->__addPolygon(
						
							[$this->barrierPercentage, $this->barrierPercentage, 100, 100], 
							[0, 100, 100, 0],
							
						);
						
					break;
					
					default:
					
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->barrierZoneTerminology,
						'x' => $this->barrierPercentage,
						'y' => 100,
						'pointerLength' => 85,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_CENTER'
					],[
						'terminology' => 'PROTECTION BARRIER',
						'x' => (100 - (100 - $this->barrierPercentage)) + ((100 - $this->barrierPercentage) / 2),
						'y' => 15,
						/* 'x' => 100,
						'y' => 100, */
						'type' => 'RotateAnnotationWithoutPointer',
							'rotationAngle' => pi() /180 * 270,
						//'rotationAngle' => -atan(45),
						'textColor' => array(255,255,255),
						'size' => 20,
						'textStyle' => 0
					]/*[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],*/
				
				];
				
			}break;
				
			case 'Enhanced buffered digital':{
			
				$this->derivativeLineSplitApplies = true;
				
				$this->derivative_x_1 = [0,0,1, $this->bufferPercentage];
				$this->derivative_y_1 = [1,1,1, 100];
				
				$this->derivative_x = [$this->bufferPercentage , $this->fixedReturnAmountPercentage, 200];
				$this->derivative_y = [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, (100 + (100 * $this->leverageFactor/100))];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
					
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->bufferZoneTerminology,
						'x' => $this->bufferPercentage,
						'y' => 100,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]/*[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]*/
					
				];
			
			}break;

			case 'Enhanced buffered digital plus':{
			
				$this->derivative_x = [0,0 ,$this->bufferPercentage, $this->bufferPercentage , $this->fixedReturnAmountPercentage, 200];
				$this->derivative_y = [(100 - $this->bufferPercentage),(100 - $this->bufferPercentage), 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, (100 + (100 * $this->leverageFactor/100))];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						
						//Blue Digital Zone
						$this->__addPolygon(
							
							[$this->bufferPercentage, $this->bufferPercentage, $this->fixedReturnAmountPercentage], 
							[$this->bufferPercentage, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]
							
						);
						
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
					
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' =>  (100 - $this->bufferPercentage) . '% Protection Buffer',
						'x' => 50,
						'y' => 50 + 1,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]/*,[
						'terminology' => $this->bufferZoneTerminology,
						'x' => $this->bufferPercentage,
						'y' => 100,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]*/
					
				];
			
			}break;

			case 'Capped enhanced buffered digital plus':{
				
				$this->maximumReturnAmountAbsolute =  100 + ($this->leverageFactor/100 * ($this->maximumReturnAmountPercentage - 100));
				
				$this->derivative_x = [0,0 ,$this->bufferPercentage, $this->bufferPercentage , $this->fixedReturnAmountPercentage, $this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [(100 - $this->bufferPercentage),(100 - $this->bufferPercentage), 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountAbsolute, $this->maximumReturnAmountAbsolute];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						
						//Blue Digital Zone
						$this->__addPolygon(
							
							[$this->bufferPercentage, $this->bufferPercentage, $this->fixedReturnAmountPercentage], 
							[$this->bufferPercentage, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]
							
						);
						
						$this->polygonFillColor = array(120,159,253);
						
					break;
					
					default:
					
						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
					
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 25,
						'pointerAngle' => (pi()/180) * (180 + 45 + 10),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' =>  (100 - $this->bufferPercentage) . '% Protection Buffer',
						'x' => 50,
						'y' => 50 + 1,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->maximumReturnAmountTerminology,
						'x' => $this->maximumReturnAmountPercentage,
						'y' => $this->maximumReturnAmountAbsolute,
						'pointerLength' => 25,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]/*,[
						'terminology' => $this->bufferZoneTerminology,
						'x' => $this->bufferPercentage,
						'y' => 100,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]*/
					
				];
			
			}break;

			case 'Enhanced barrier digital':{
				
				$this->derivative_x = [0, $this->barrierPercentage, $this->barrierPercentage, 250];
				$this->derivative_y = [0, $this->barrierPercentage, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage + (250 - $this->fixedReturnAmountPercentage) * $this->leverageFactor / 100.0];
				$this->horizontalReferenceLines = [[100, 100], [$this->barrierPercentage, $this->barrierPercentage], [$this->barrierPercentage, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->barrierPercentage, $this->barrierPercentage], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				$this->xTicks = [0, 50, $this->barrierPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				$this->yTicks = [0, 50, $this->barrierPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				$this->__addPolygon([$this->barrierPercentage, $this->barrierPercentage, $this->fixedReturnAmountPercentage], [$this->barrierPercentage, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
				if($this->highlightAreasWhereUnderlyingOutperformsDerivative)
					$this->__addPolygon([$this->fixedReturnAmountPercentage, 250, 250], [$this->fixedReturnAmountPercentage, 250, $this->fixedReturnAmountPercentage]);
				$this->annotations = [
					[
						'terminology' => $this->statedPrincipalAmountTerminology,
						'x' => 50,
						'y' => 100 + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					]/*, [
						'terminology' => $this->barrierZoneTerminology,
						'x' => ($this->barrierPercentage + $this->fixedReturnAmountPercentage) / 2.0,
						'y' => (100 + $this->fixedReturnAmountPercentage) / 2.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					]*/, [
						'terminology' => $this->fixedReturnAmountTerminology,
						'x' => ($this->barrierPercentage + $this->fixedReturnAmountPercentage) / 2.0,
						'y' => $this->fixedReturnAmountPercentage + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					]
				];	
			
			}break;

			case 'Enhanced barrier digital plus':{
				
				//Note: An enhanced booster Level and barrier level are assumed to be the the same so barrier level is not plotted in the derivative line as booster level
				//is used in its place
				
				$this->derivativeLineSplitApplies = true;
				
				$this->derivative_x_1 = [0,1, $this->boosterLevel];
				$this->derivative_y_1 = [1,1, $this->boosterLevel];
				
				$this->derivative_x = [$this->boosterLevel , $this->fixedReturnAmountPercentage, 200];
				$this->derivative_y = [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, (100 + (100 * $this->leverageFactor/100))];
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Blue Buffer Zone
						$this->__addPolygon([$this->boosterLevel, $this->boosterLevel, $this->fixedReturnAmountPercentage], [$this->boosterLevel, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						$this->polygonFillColor = array(120,159,253);
						
						//Barrier Area - covers the entire Barier Level Zone
						// $this->__addPolygon(
						
							// [$this->barrierPercentage, $this->barrierPercentage, 100, 100], 
							// [0, 100, 100, 0],
							
						// );
						
					break;
					
					default:
					
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 10),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->barrierZoneTerminology,
						'x' => $this->barrierPercentage,
						'y' => 0,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],
				
				];
			
			}break;

			case 'Capped enhanced barrier digital plus':{
				
				//Note: An enhanced booster Level and barrier level are assumed to be the the same so barrier level is not plotted in the derivative line as booster level is used in its place
				
				$this->derivativeLineSplitApplies = true;
				
				$this->maximumReturnAmountAbsolute =  100 + ($this->leverageFactor/100 * ($this->maximumReturnAmountPercentage - 100));
				
				$this->derivative_x_1 = [0,1, $this->boosterLevel];
				$this->derivative_y_1 = [1,1, $this->boosterLevel];
				
				$this->derivative_x = [$this->boosterLevel , $this->fixedReturnAmountPercentage, $this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, $this->maximumReturnAmountAbsolute, $this->maximumReturnAmountAbsolute];
				
				$this->horizontalReferenceLines = [[100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						$this->polygonFillColor = array(120,159,253);
						
						//Blue Buffer Zone
						$this->__addPolygon(
						
							[$this->boosterLevel, $this->boosterLevel, $this->fixedReturnAmountPercentage], 
							[$this->boosterLevel, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]
							
						);
						
						//Barrier Area - covers the entire Barrier Level Zone
						// $this->__addPolygon(
						
							// [$this->barrierPercentage, $this->barrierPercentage, 100, 100], 
							// [0, 100, 100, 0],
							
						// );
						
					break;
					
					default:
					
						$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
					
					break;
					
				}
				
				$this->annotations = [
				
					[
						'terminology' => $this->participationZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 10),
						'y' => ( 100 + ((($this->fixedReturnAmountPercentage + 10) - 100) * $this->leverageFactor/100) ),
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45 + 20),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->digitalZoneTerminology,
						'x' => $this->boosterLevel,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => 'Booster Level = ' . $this->boosterLevel . '%',
						'x' => $this->boosterLevel,
						'y' => $this->boosterLevel,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 10),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					],[
						'terminology' => $this->barrierZoneTerminology,
						'x' => $this->barrierPercentage,
						'y' => 0,
						'pointerLength' => 50,
						'pointerAngle' => (pi()/180) * (180 + 45),
						'pointerOrientation' => 'BOTTOM_RIGHT'
					]
				
				];
			
			}break;

			case 'Participation':{
				
				if($this->leverageFactor == NULL || $this->leverageFactor == 0){
					
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);
				
				}
				
				$this->derivative_x = [0, 0, 100, 200];
				$this->derivative_y = [0, 0, 100, (100 + (100 * $this->leverageFactor/100))];
				
				// $this->horizontalReferenceLines = [[100, 100]];
				// $this->verticalReferenceLines = [[100, 100]];
				
				// $this->xTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];	
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema){
				
					case 'BMO':
					break;
					
					default:
					
						$this->__addPolygon([100, 100 + 150 * 100 / $this->leverageFactor, 250, 100], [100, 250, 250, 100]);
						
					break;
					
				}
				
				//Annotation
				
				// if($this->leverageFactor != 100) {
					
					//Annotation position Adjusted for leverage factor
					if($this->leverageFactor > 200){
						
						$this->annotations = [
							[
								'terminology' => $this->participationZoneTerminology,
								'x' => 110,
								'y' => ( 100 + ($this->leverageFactor/100 * 10) ),
								'pointerLength' => 70,
								'pointerAngle' => (pi()/180) * (180 + 00 + 20),
								'pointerOrientation' => 'BOTTOM_RIGHT'
							]
							
						];
						
					}else{
						
						$this->annotations = [
							[
								'terminology' => $this->participationZoneTerminology,
								'x' => 130,
								'y' => ( 100 + ($this->leverageFactor/100 * 30) ),
								'pointerLength' => 60,
								'pointerAngle' => (pi()/180) * (180 + 00 + 20),
								'pointerOrientation' => 'BOTTOM_RIGHT'
							],/*,[
								'terminology' => $this->statedPrincipalAmountTerminology,
								'x' => 50,
								'y' => 100 + 5,
								'pointerLength' => 75,
								'pointerAngle' => 3 * pi() / 2.0,
								'pointerOrientation' => 'BOTTOM_CENTER'
							]*/
						];
					
					}
					
					
					
				
				// }else{
					
					// $this->annotations = [
					
					// ];

				// }
				
				
				
			}break;

			case 'Limited participation':{
				
				if($this->leverageFactor == NULL || $this->leverageFactor == 0){
					
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);
				
				}
				
				$this->maximumReturnAmountAbsolute = (100 + ($this->maximumReturnAmountPercentage - 100) * $this->leverageFactor/100);
				
				$this->derivative_x = [0, 100, $this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [0, 100, $this->maximumReturnAmountAbsolute, $this->maximumReturnAmountAbsolute];
				
				$this->horizontalReferenceLines = [[100, 100], [($this->fixedReturnAmountPercentage - 100) / ($this->leverageFactor / 100.0) + 100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[100, 100], [($this->fixedReturnAmountPercentage - 100) / ($this->leverageFactor / 100.0) + 100, $this->fixedReturnAmountPercentage], [$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]];
				
				// $this->xTicks = [0, 50, 100, $this->maximumReturnAmountPercentage, 150, 200];
				// $this->yTicks = [0, 50, 100, $this->maximumReturnAmountPercentage, 150, 200];
				
				$this->xTicks = [0, 200];
				$this->yTicks = [0, 200];
				
				switch($this->issuerSchema){
				
					case 'BMO':
					break;
					
					default:
					
						$this->__addPolygon([100, 100 + ($this->fixedReturnAmountPercentage - 100) *  100 / $this->leverageFactor, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
						
					break;
					
				}
				
				if($this->highlightAreasWhereUnderlyingOutperformsDerivative){
					
					$this->__addPolygon([$this->fixedReturnAmountPercentage, 250, 250], [$this->fixedReturnAmountPercentage, 250, $this->fixedReturnAmountPercentage]);
					
				}
				
				// if($this->leverageFactor != 100){
					
					$this->annotations = [
						
						[
							'terminology' => $this->participationZoneTerminology,
							'x' => 130,
							'y' => ( 100 + ($this->leverageFactor/100 * 30) ),
							'pointerLength' => 35,
							'pointerAngle' => (pi()/180) * (180 + 45 + 20),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						],[
							'terminology' => $this->maximumReturnAmountTerminology,
							'x' => $this->maximumReturnAmountPercentage,
							'y' => $this->maximumReturnAmountAbsolute,
							'pointerLength' => 25,
							'pointerAngle' => (pi()/180) * (180 + 45 + 20),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						]/*,
						[
							'terminology' => $this->statedPrincipalAmountTerminology,
							'x' => 50,
							'y' => 100 + 5,
							'pointerLength' => 75,
							'pointerAngle' => 3 * pi() / 2.0,
							'pointerOrientation' => 'BOTTOM_CENTER'
						]*/
					];	
					
				// }else{
				
					// $this->annotations = [
						
					// ];	
				
				// }
				
			}break;

			case 'Buffered participation':{
			
				if($this->leverageFactor == NULL || $this->leverageFactor == 0){
					
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);
				
				}
				
				$this->derivative_x = [0,0, $this->bufferPercentage, 100, 200];
				$this->derivative_y = [100 - $this->bufferPercentage,100 - $this->bufferPercentage, 100, 100,  100 + (100 * $this->leverageFactor/100)];
				
				$this->horizontalReferenceLines = [[$this->bufferPercentage, 100]];
				$this->verticalReferenceLines = [[$this->bufferPercentage, 100], [100, 100]];
				
				// $this->xTicks = [0, $this->bufferPercentage, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 100 - $this->bufferPercentage, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema){
				
					case 'BMO':
					
						$this->__addPolygon(
							
							[$this->bufferPercentage, $this->bufferPercentage, 100, 100], 
							[0, 100, 100, 0]
							
						);
						
						$this->polygonFillColor = array(121, 154, 254);
					
					break;
					
					default:

						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100 + 100 * 150 / $this->leverageFactor, 250, 100], [100, 250, 250, 100]);
						
					break;
					
				}
				
				// if($this->leverageFactor != 100) {

					$this->annotations = [
						[
							'terminology' => $this->participationZoneTerminology,
							'x' => 130,
							'y' => ( 100 + ($this->leverageFactor/100 * 30) ),
							'pointerLength' => 50,
							'pointerAngle' => (pi()/180) * (180 + 45 + 20),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						],[
							'terminology' => $this->bufferZoneTerminology,
							'x' => $this->bufferPercentage,
							'y' => 100,
							'pointerLength' => 50,
							'pointerAngle' => (pi()/180) * (180 + 45 + 20),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						// ],[
							// 'terminology' => 'Protection Buffer',
							// 'x' => (100 - (100 - $this->bufferPercentage)) + ((100 - $this->bufferPercentage) / 2),
							// 'y' => 50,
							// 'pointerLength' => 120,
							// 'pointerAngle' => pi()/180 * (180 + 90 + 45 + 10),
							// 'pointerOrientation' => 'BOTTOM_RIGHT',
							// 'type' => 'AnnotationWithPointer'
						],[
							'terminology' => 'Protection Buffer',
							'x' => (100 - (100 - $this->bufferPercentage)) + ((100 - $this->bufferPercentage) / 2),
							'y' => 15,
							/* 'x' => 100,
							'y' => 100, */
							'type' => 'RotateAnnotationWithoutPointer',
								'rotationAngle' => pi() /180 * 270,
							//'rotationAngle' => -atan(45),
							'textColor' => array(255,255,255),
							'size' => 24,
							'textStyle' => 0
						]/*[
							'terminology' => $this->minimumReturnAtMaturityTerminology,
							'x' => 2.5,
							'y' => 100 - $this->bufferPercentage + 5,
							'pointerLength' => 137,
							'pointerAngle' => 5.5,
							'pointerOrientation' => 'BOTTOM_CENTER'
						]*/
					];
					
				// }else{
				
					// $this->annotations = [
					
					// ];

				// }
			
			}break;

			case 'Buffered limited participation':{
			
				if($this->leverageFactor == NULL || $this->leverageFactor == 0){
					
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);
				
				}
				
				
				// $this->derivative_x = [$this->barrierPercentage,$this->barrierPercentage 100, $this->maximumReturnAmountPercentage, 200];
				// $this->derivative_y = [100, 100,$this->maximumReturnAmountAbsolute,$this->maximumReturnAmountAbsolute];
				
				$this->maximumReturnAmountAbsolute =  100 + ($this->leverageFactor/100 * ($this->maximumReturnAmountPercentage - 100));
				
				$this->derivative_x = [0, 0,$this->bufferPercentage, 100, $this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [(100 - $this->bufferPercentage),(100 - $this->bufferPercentage), 100, 100, $this->maximumReturnAmountAbsolute,  $this->maximumReturnAmountAbsolute];
				
				$this->horizontalReferenceLines = [[$this->bufferPercentage, 100]];
				$this->verticalReferenceLines = [[$this->bufferPercentage, 100], [100, 100]];
				
				// $this->xTicks = [0, $this->bufferPercentage, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				// $this->yTicks = [0, 100 - $this->bufferPercentage, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->xTicks = [0, 250];
				$this->yTicks = [0, 250];
				
				switch($this->issuerSchema){
				
					case 'BMO':
					
						$this->__addPolygon(
							
							[$this->bufferPercentage, $this->bufferPercentage, 100, 100], 
							[0, 100, 100, 0]
							
						);
						
						$this->polygonFillColor = array(121, 154, 254);
					
					break;
					
					default:

						$this->__addPolygon([0, 0, $this->bufferPercentage, 100], [0, 100 - $this->bufferPercentage, 100, 100]);
						$this->__addPolygon([100, 100 + 100 * 150 / $this->leverageFactor, 250, 100], [100, 250, 250, 100]);
						
					break;
					
				}
				
				// if($this->leverageFactor != 100) {

					$this->annotations = [
						[
							'terminology' => $this->participationZoneTerminology,
							'x' => 130,
							'y' => ( 100 + ($this->leverageFactor/100 * 30) ),
							'pointerLength' => 35,
							'pointerAngle' => (pi()/180) * (180 + 45 + 20),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						],[
							'terminology' => 'Protection Buffer',
							'x' => (100 - (100 - $this->bufferPercentage)) + ((100 - $this->bufferPercentage) / 2),
							'y' => 15,
							/* 'x' => 100,
							'y' => 100, */
							'type' => 'RotateAnnotationWithoutPointer',
								'rotationAngle' => pi() /180 * 270,
							//'rotationAngle' => -atan(45),
							'textColor' => array(255,255,255),
							'size' => 24,
							'textStyle' => 0
						],[
							'terminology' => $this->bufferZoneTerminology,
							'x' => $this->bufferPercentage,
							'y' => 100,
							'pointerLength' => 50,
							'pointerAngle' => (pi()/180) * (180 + 45 + 20),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						// ],[
							// 'terminology' => 'Protection Buffer',
							// 'x' => (100 - (100 - $this->bufferPercentage)) + ((100 - $this->bufferPercentage) / 2),
							// 'y' => 50,
							// 'pointerLength' => 120,
							// 'pointerAngle' => pi()/180 * (180 + 90 + 45 + 10),
							// 'pointerOrientation' => 'BOTTOM_CENTER',
							// 'type' => 'AnnotationWithPointer'
						],[
							'terminology' => $this->maximumReturnAmountTerminology,
							'x' => $this->maximumReturnAmountPercentage,
							'y' => $this->maximumReturnAmountAbsolute,
							'pointerLength' => 25,
							'pointerAngle' => (pi()/180) * (180 + 45 + 10),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						]/*[
							'terminology' => $this->minimumReturnAtMaturityTerminology,
							'x' => 2.5,
							'y' => 100 - $this->bufferPercentage + 5,
							'pointerLength' => 137,
							'pointerAngle' => 5.5,
							'pointerOrientation' => 'BOTTOM_CENTER'
						]*/
					];
					
				// }else{
				
					// $this->annotations = [
					
					// ];

				// }
			
			}break;

			case 'Barrier participation':{
				
				
				if($this->leverageFactor == NULL || $this->leverageFactor == 0){
					
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);
					
				}
				
				$this->derivativeLineSplitApplies = true;
				
				$this->derivative_x_1 = [0,1, $this->barrierPercentage];
				$this->derivative_y_1 = [1,1, $this->barrierPercentage];
				
				$this->derivative_x = [$this->barrierPercentage, 100, 200];
				$this->derivative_y = [100, 100, 100 + ($this->leverageFactor/100 * 100)];
				
				// $this->derivative_x = [0, 100, 100 + ($this->fixedReturnAmountPercentage - 100) * 100 / $this->leverageFactor, 250];
				// $this->derivative_y = [0, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage];
				
				//trigger_error('Fixed Return : ' . $this->fixedReturnAmountPercentage,E_USER_ERROR); //-TEH
				
				// $this->derivative_x = [$this->barrierPercentage, 100, 200];
				// $this->derivative_y = [100, 100, 100 + $this->leverageFactor/100 * 100];
				
				
				$this->horizontalReferenceLines = [[$this->barrierPercentage, $this->barrierPercentage], [$this->barrierPercentage, 100]];
				
				$this->verticalReferenceLines = [[$this->barrierPercentage, 100], [100, 100]];
				$this->verticalReferenceLines = [[$this->barrierPercentage, 100]];
				
				$this->xTicks = [0, 200];
				$this->yTicks = [0, 200];
				
				//Polygons now Issuer Specific
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Barrier Area - covers the only the Barier Level Zone
						// $this->__addPolygon(
						
							// [$this->barrierPercentage, $this->barrierPercentage, 100], 
							// [$this->barrierPercentage, 100, 100],
							
						// );
						
						
						//Barrier Area - covers the entire Barier Level Zone
						$this->__addPolygon(
						
							[$this->barrierPercentage, $this->barrierPercentage, 100, 100], 
							[0, 100, 100, 0],
							
						);
						
						//Comment this out to change the colour to grey
						$this->polygonFillColor = array(121, 154, 254);
						
					break;
					
					default:
					
						// $this->__addPolygon(
				
						// [$this->barrierPercentage, $this->barrierPercentage, 100], 
						// [$this->barrierPercentage, 100, 100],
						
						// );
					
					break;
					
				}
				
				
				if($this->leverageFactor < 200){
					
					$xMarkerPos = 40;
					
				}else{
					
					$xMarkerPos = 25;
					
				}
				
				// if($this->leverageFactor != 100){
				
				
					$this->annotations = [
						
						/*[
							'terminology' => ,
							'x' => 130,
							'y' => ( 100 + ($this->leverageFactor/100 * 30) ),
							'pointerLength' => 35,
							'pointerAngle' => (pi()/180) * (180 + 45 + 20),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						],*/[
							
							'type' => 'AnnotationWithPointer',
							'terminology' => $this->participationZoneTerminology,
							'x' => 100 + $xMarkerPos,
							'y' => ( 100 + ($this->leverageFactor/100 * $xMarkerPos)),
							'pointerAngle' => (pi() / 180 * (180 + 45 + 10 + 5)),
							'pointerLength' => 40,
							'pointerOrientation' => 'BOTTOM_RIGHT'
		
						],[
							'terminology' => $this->barrierZoneTerminology,
							'x' => $this->barrierPercentage,
							'y' => 100,
							'pointerLength' => 85,
							'pointerAngle' => (pi()/180) * (180 + 45),
							'pointerOrientation' => 'BOTTOM_CENTER'
						],[
							'terminology' => 'PROTECTION BARRIER',
							'x' => (100 - (100 - $this->barrierPercentage)) + ((100 - $this->barrierPercentage) / 2),
							'y' => 15,
							/* 'x' => 100,
							'y' => 100, */
							'type' => 'RotateAnnotationWithoutPointer',
								'rotationAngle' => pi() /180 * 270,
							//'rotationAngle' => -atan(45),
							'textColor' => array(255,255,255),
							'size' => 20,
							'textStyle' => 0
						]/*[
							'terminology' => 'BARRIER',
							'x' => 100 - ((100 - $this->barrierPercentage)/2) - 2,
							'y' => 25 + 7,
							'type' => 'RotateAnnotationWithoutPointer',
							'rotationAngle' => 270 * ( pi() / 180),
							'textColor' => array(0,0,0),
							'size' => 25,
							'textStyle' => 1 //bold 
						],[
							'terminology' => 'PROTECTION',
							'x' => 100 - ((100 - $this->barrierPercentage)/2) + 5 - 2,
							'y' => 25,
							'type' => 'RotateAnnotationWithoutPointer',
							'rotationAngle' => 270 * ( pi() / 180),
							'textColor' => array(0,0,0),
							'size' => 25,
							'textStyle' => 1 //bold
						]*//*,[
							'terminology' => $this->statedPrincipalAmountTerminology,
							'x' => 50,
							'y' => 100 + 5,
							'pointerLength' => 75,
							'pointerAngle' => 3 * pi() / 2.0,
							'pointerOrientation' => 'BOTTOM_CENTER'
						
						]*/
						
					];	
				
				// }else{
				
					// $this->annotations = [
					
					// ];	
				
				// }
			
			}break;

			case 'Barrier limited participation':{
				
				if($this->leverageFactor == NULL || $this->leverageFactor == 0){
					
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);
					
				}
				
				
				// $this->derivative_x = [0, 100, 100 + ($this->fixedReturnAmountPercentage - 100) * 100 / $this->leverageFactor, 250];
				// $this->derivative_y = [0, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage];
				
				//trigger_error('Fixed Return : ' . $this->fixedReturnAmountPercentage,E_USER_ERROR); //-TEH
				
				// $this->derivative_x = [$this->barrierPercentage, 100, 200];
				// $this->derivative_y = [100, 100, 100 + $this->leverageFactor/100 * 100];
				
				$this->derivativeLineSplitApplies = true;
				
				$this->derivative_x_1 = [0,1, $this->barrierPercentage];
				$this->derivative_y_1 = [1,1, $this->barrierPercentage];
				
				$this->maximumReturnAmountAbsolute =  100 + ($this->leverageFactor/100 * ($this->maximumReturnAmountPercentage - 100));
				
				$this->derivative_x = [$this->barrierPercentage, 100, $this->maximumReturnAmountPercentage, 200];
				$this->derivative_y = [100, 100,$this->maximumReturnAmountAbsolute,$this->maximumReturnAmountAbsolute];
				
				$this->horizontalReferenceLines = [[$this->barrierPercentage, $this->barrierPercentage], [$this->barrierPercentage, 100]];
				
				$this->verticalReferenceLines = [[$this->barrierPercentage, 100], [100, 100]];
				$this->verticalReferenceLines = [[$this->barrierPercentage, 100]];
				
				$this->xTicks = [0, 200];
				$this->yTicks = [0, 200];
				
				//Polygons now Issuer Specific
				
				switch($this->issuerSchema == 'BMO'){
					
					case 'BMO':
						
						//Barrier Area - covers the only the Barier Level Zone
						// $this->__addPolygon(
						
							// [$this->barrierPercentage, $this->barrierPercentage, 100], 
							// [$this->barrierPercentage, 100, 100],
							
						// );
						
						
						//Barrier Area - covers the entire Barier Level Zone
						$this->__addPolygon(
						
							[$this->barrierPercentage, $this->barrierPercentage, 100, 100], 
							[0, 100, 100, 0],
							
						);
						
						$this->polygonFillColor = array(121, 154, 254);
						
					break;
					
					default:
					
						// $this->__addPolygon(
				
						// [$this->barrierPercentage, $this->barrierPercentage, 100], 
						// [$this->barrierPercentage, 100, 100],
						
						// );
					
					break;
					
				}
				
				
				// if($this->leverageFactor != 100){

					$this->annotations = [
						
						[
							'terminology' => $this->participationZoneTerminology,
							'x' => 130,
							'y' => ( 100 + ($this->leverageFactor/100 * 30) ),
							'pointerLength' => 35,
							'pointerAngle' => (pi()/180) * (180 + 45 + 20),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						],[
							'terminology' => $this->barrierZoneTerminology,
							'x' => $this->barrierPercentage,
							'y' => 100,
							'pointerLength' => 85,
							'pointerAngle' => (pi()/180) * (180 + 45),
							'pointerOrientation' => 'BOTTOM_CENTER'
						],[
							'terminology' => $this->maximumReturnAmountTerminology,
							'x' => $this->maximumReturnAmountPercentage,
							'y' => $this->maximumReturnAmountAbsolute,
							'pointerLength' => 25,
							'pointerAngle' => (pi()/180) * (180 + 45 + 10),
							'pointerOrientation' => 'BOTTOM_RIGHT'
						],[
							'terminology' => 'PROTECTION BARRIER',
							'x' => (100 - (100 - $this->barrierPercentage)) + ((100 - $this->barrierPercentage) / 2),
							'y' => 15,
							/* 'x' => 100,
							'y' => 100, */
							'type' => 'RotateAnnotationWithoutPointer',
								'rotationAngle' => pi() /180 * 270,
							//'rotationAngle' => -atan(45),
							'textColor' => array(255,255,255),
							'size' => 20,
							'textStyle' => 0
						]/*,[
							'terminology' => $this->statedPrincipalAmountTerminology,
							'x' => 50,
							'y' => 100 + 5,
							'pointerLength' => 75,
							'pointerAngle' => 3 * pi() / 2.0,
							'pointerOrientation' => 'BOTTOM_CENTER'
						
						]*/
						
					];	
				
				// }else{
				
					// $this->annotations = [
					
					// ];	
				
				// }
			
			}break;

			case 'Twin win participation':{
			
				if($this->leverageFactor == NULL || $this->leverageFactor == 0){
					
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);
				
				}
				
				$this->derivative_x = [0, $this->barrierPercentage, $this->barrierPercentage, 100, 100 + 200 * 100 / $this->leverageFactor];
				$this->derivative_y = [0, $this->barrierPercentage, 100 + 100 - $this->barrierPercentage, 100, 300];
				
				$this->horizontalReferenceLines = [[$this->barrierPercentage, $this->barrierPercentage], [$this->barrierPercentage, 100 + 100 - $this->barrierPercentage], [100, 100]];
				$this->verticalReferenceLines = [[$this->barrierPercentage, 100 + 100 - $this->barrierPercentage], [100, 100]];
				
				$this->xTicks = [0, $this->barrierPercentage, 50, 100, $this->fixedReturnAmountPercentage, 150, 250];
				$this->yTicks = [0, $this->barrierPercentage, 50, 100, 100 + 100 - $this->barrierPercentage, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->__addPolygon([$this->barrierPercentage, $this->barrierPercentage, 100], [$this->barrierPercentage, 100 + 100 - $this->barrierPercentage, 100]);
				$this->__addPolygon([100, 100 + 100 * 150 / $this->leverageFactor, 250,], [100, 250, 250,]);
				
				$this->annotations = [
					[
						'terminology' => $this->statedPrincipalAmountTerminology,
						'x' => 50,
						'y' => 100 + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					]/*, [
						'terminology' => $this->barrierZoneTerminology,
						'x' => ($this->barrierPercentage + 100.0) / 2,
						'y' => 100,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					]*//*, [
						'terminology' => $this->participationZoneTerminology,
						'x' => 130,
						'y' => (130 + 100 + min(50, 30 * $this->leverageFactor / 100.0)) / 2.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					]*/
				];	
			
			}break;
				
			case 'Twin win limited participation':{
            
            
				if($this->leverageFactor == NULL || $this->leverageFactor == 0)
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);					
				
				$this->derivative_x = [0, $this->barrierPercentage, $this->barrierPercentage, 100, 
					$this->fixedReturnAmountPercentage / ($this->leverageFactor/100.0), 250];
				$this->derivative_y = [0, $this->barrierPercentage, 100 + 100 - $this->barrierPercentage, 100, 
					$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage];
				
				$this->horizontalReferenceLines = [
					[$this->barrierPercentage, $this->barrierPercentage], 
					[$this->barrierPercentage, 100 + 100 - $this->barrierPercentage],  
					[100, 100], 
					[$this->fixedReturnAmountPercentage / ($this->leverageFactor / 100.0), $this->fixedReturnAmountPercentage]
				];
				$this->verticalReferenceLines = [
					[$this->barrierPercentage, 100 + 100 - $this->barrierPercentage], 
					[100, 100], 
					[$this->fixedReturnAmountPercentage / ($this->leverageFactor / 100.0), $this->fixedReturnAmountPercentage], 
					[$this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]
				];
				$this->xTicks = [0, 50, $this->barrierPercentage, 100, $this->fixedReturnAmountPercentage, 150, 250];
				$this->yTicks = [0, 50, $this->barrierPercentage, 100, 100 + 100 - $this->barrierPercentage, $this->fixedReturnAmountPercentage, 150, 250];
				
				$this->__addPolygon([$this->barrierPercentage, $this->barrierPercentage, 100], [$this->barrierPercentage, 100 + 100 - $this->barrierPercentage, 100]);
				$this->__addPolygon(
					[100, $this->fixedReturnAmountPercentage / ($this->leverageFactor/100.0), $this->fixedReturnAmountPercentage], 
					[100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]
				);
				
				if($this->highlightAreasWhereUnderlyingOutperformsDerivative)
					$this->__addPolygon([$this->fixedReturnAmountPercentage, 250, 250], [$this->fixedReturnAmountPercentage, 250, $this->fixedReturnAmountPercentage]);
				$this->annotations = [
					[
						'terminology' => $this->statedPrincipalAmountTerminology,
						'x' => 50,
						'y' => 100 + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					]/*, [
						'terminology' => $this->barrierZoneTerminology,
						'x' => ($this->barrierPercentage + 100.0) / 2,
						'y' => 100,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					]*/, [
						'terminology' => $this->fixedReturnAmountTerminology,
						'x' => $this->fixedReturnAmountPercentage * 0.7,
						'y' => $this->fixedReturnAmountPercentage,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					]/*, [
						'terminology' => $this->participationZoneTerminology,
						'x' => $this->fixedReturnAmountPercentage / ($this->leverageFactor / 100.0),
						'y' => ($this->fixedReturnAmountPercentage + ($this->fixedReturnAmountPercentage) / ($this->leverageFactor / 100.0)) * 0.5,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'TOP_LEFT'
					]*/
				];
				
			}break;

			case 'Twin win digital':{
				
			
				if($this->leverageFactor == NULL || $this->leverageFactor == 0)
					trigger_error('Missing or invalid leverage factor', E_USER_ERROR);
				$this->derivative_x = [0, $this->barrierPercentage, $this->barrierPercentage, 100, 100, 250];
				$this->derivative_y = [0, $this->barrierPercentage, 100 + 100 - $this->barrierPercentage, 100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage];
				$this->horizontalReferenceLines = [[$this->barrierPercentage, $this->barrierPercentage], [$this->barrierPercentage, 100 + 100 - $this->barrierPercentage], [100, 100], [100, $this->fixedReturnAmountPercentage]];
				$this->verticalReferenceLines = [[$this->barrierPercentage, 100 + 100 - $this->barrierPercentage], [100, 100]];
				$this->xTicks = [0, $this->barrierPercentage, 50, 100, 150, 250];
				$this->yTicks = [0, $this->barrierPercentage, 50, 100, 100 + 100 - $this->barrierPercentage, $this->fixedReturnAmountPercentage, 150, 250];
				$this->__addPolygon([$this->barrierPercentage, $this->barrierPercentage, 100], [$this->barrierPercentage, 100 + 100 - $this->barrierPercentage, 100]);
				$this->__addPolygon([100, 100, $this->fixedReturnAmountPercentage], [100, $this->fixedReturnAmountPercentage, $this->fixedReturnAmountPercentage]);
				$this->annotations = [
					[
						'terminology' => $this->statedPrincipalAmountTerminology,
						'x' => 50,
						'y' => 100 + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					]/*, [
						'terminology' => $this->barrierZoneTerminology,
						'x' => $this->barrierPercentage * 80 / 100.0,
						'y' => $this->barrierPercentage * (80 + (100 - $this->barrierPercentage) * 0.5) / 100.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					]*//*, [
						'terminology' => $this->digitalZoneTerminology,
						'x' => ($this->fixedReturnAmountPercentage + 100) / 2.0,
						'y' => (3 * $this->fixedReturnAmountPercentage + 100) / 4.0,
						'pointerLength' => 100,
						'pointerAngle' => 0.4,
						'pointerOrientation' => 'CENTER_LEFT'
					]*/, [
						'terminology' => $this->fixedReturnAmountTerminology,
						'x' => (100 + $this->fixedReturnAmountPercentage) / 2.0,
						'y' => $this->fixedReturnAmountPercentage + 5,
						'pointerLength' => 75,
						'pointerAngle' => 3 * pi() / 2.0,
						'pointerOrientation' => 'BOTTOM_CENTER'
					]
				];	
			
			}break;

		}
		
		
		// Set the minimum for the graphs
		// if($this->bufferPercentage == NULL)
	
		if($this->bufferPercentage == NULL){
			
			$adjustFloorForBuffer = 0;
			
			if($this->derivativeLineSplitApplies){
			
				$this->derivative_x_1[0] = 0;
				$this->derivative_y_1[0] = $this->floorPercentageGraph;
				
				$this->derivative_x_1[1] = $this->floorPercentageGraph - $adjustFloorForBuffer;
				$this->derivative_y_1[1] = $this->floorPercentageGraph;
				
			}else{
				
				$this->derivative_x[0] = 0;
				$this->derivative_y[0] = $this->floorPercentageGraph;
				
				$this->derivative_x[1] = $this->floorPercentageGraph - $adjustFloorForBuffer;
				$this->derivative_y[1] = $this->floorPercentageGraph;
				
			}
			
		}else{
			
			if($this->floorPercentageGraph < $this->bufferPercentage){
				
				$this->floorPercentageGraph = (100 - $this->bufferPercentage);
				$adjustFloorForBuffer = 0;
				
				if($this->derivativeLineSplitApplies){
			
					$this->derivative_x_1[0] = 0;
					$this->derivative_y_1[0] = (100 - $this->bufferPercentage);
					
					$this->derivative_x_1[1] = $this->bufferPercentage;
					$this->derivative_y_1[1] = 100;
					
				}else{
					
					$this->derivative_x[0] = 0;
					$this->derivative_y[0] = (100 - $this->bufferPercentage);
					
					$this->derivative_x[1] = $this->bufferPercentage;
					$this->derivative_y[1] = 100;
					
				}
				
			}else{
			
				$adjustFloorForBuffer = 10;
				
				if($this->derivativeLineSplitApplies){
			
					$this->derivative_x_1[0] = 0;
					$this->derivative_y_1[0] = $this->floorPercentageGraph;
					
					$this->derivative_x_1[1] = $this->floorPercentageGraph - $adjustFloorForBuffer;
					$this->derivative_y_1[1] = $this->floorPercentageGraph;
					
				}else{
					
					$this->derivative_x[0] = 0;
					$this->derivative_y[0] = $this->floorPercentageGraph;
					
					$this->derivative_x[1] = $this->floorPercentageGraph - $adjustFloorForBuffer;
					$this->derivative_y[1] = $this->floorPercentageGraph;
					
				}
			
			}
		
		}
		
		if($this->floorPercentageGraph <= (100 - $this->bufferPercentage) && ($this->bufferPercentage != null)){
			
			$this->displayFloorLevelAnnotation = false;
		
		}else{
			
			$this->displayFloorLevelAnnotation = true;
		
		}
		
		if($this->displayFloorLevelAnnotation){
			
			if($floorPercentageGraph == 5)
				$minimumReturn = ' $1';
			else
				$minimumReturn = '';
			
			$this->annotations[] = array( 
			
				'terminology' => 'Floor Level' . $minimumReturn,
				'x' => $this->floorPercentageGraph - $adjustFloorForBuffer,
				'y' => $this->floorPercentageGraph,
				'pointerLength' => 90,
				'pointerAngle' => (pi()/180) * (180 + 90 + 20),
				'pointerOrientation' => 'BOTTOM_CENTER'
				
			);
			
			if($this->floorPercentageGraph > 45){
				
				$this->annotations[] = array(
								
					'terminology' => 'FLOOR PROTECTION',
					'x' => 4,
					'y' => $this->floorPercentageGraph - 7,
					'type' => 'RotateAnnotationWithoutPointer',
					'rotationAngle' => 0,
					'textColor' => array(255,255,255),
					'size' => $this->floorPercentageGraph * 0.33,
					'textStyle' => 0
				
				);
			}
			
			// }elseif($this->floorPercentageGraph > 50){
				
				// $this->annotations[] = array(
								
					// 'terminology' => 'FLOOR PROTECTION',
					// 'x' => 4,
					// 'y' => $this->floorPercentageGraph - 7,
					// 'type' => 'RotateAnnotationWithoutPointer',
					// 'rotationAngle' => 0,
					// 'textColor' => array(255,255,255),
					// 'size' => 16,
					// 'textStyle' => 1
				
				// );
			
			// }
			
			// $this->verticalReferenceLines = [[$this->floorPercentageGraph,$this->floorPercentageGraph], [0,$this->floorPercentageGraph]];
			
			// $this->verticalReferenceLines[] = [$this->floorPercentageGraph,$this->floorPercentageGraph];

			
			
			//Floor Protection Area - covers the entire Floor Protection Area
			$this->__addPolygon(
			
				[0,0, $this->floorPercentageGraph], 
				[0,$this->floorPercentageGraph, $this->floorPercentageGraph],
				
			);
			
			//Comment this out to change the colour to blue
			$this->polygonFillColor = array(120,159,253);
			
		}
		
		//Shortens the arrow length for the orange derivative line to show in the upside return
		$lastDerivativePlotPointX = end($this->underlying_x) - 10;
		$lastDerivativePlotPointY = end($this->underlying_x) - 10 - 100;
		
		if($this->leverageFactor > 100)
			$lastDerivativePlotPointY *= $this->leverageFactor/100;
		
		$lastDerivativePlotPointY += 100;
		
		// if($this->derivativeLineSplitApplies){
			
			// $this->derivative_x_1[count($this->derivative_x_1) - 1] = $lastDerivativePlotPointX;
			// $this->derivative_y_1[count($this->derivative_y_1) - 1] = $lastDerivativePlotPointY;
			
		// }else{
			
			$this->derivative_x[count($this->derivative_x) - 1] = $lastDerivativePlotPointX;
			$this->derivative_y[count($this->derivative_y) - 1] = $lastDerivativePlotPointY;
			
		// }
		
	}
	
	function calculateEdges() {

		$this->__calculateEdges();
	
	}
	
	function drawArrowHead($x, $y, $color){
	
		// Last line in user coordinates
		
		$pointerXUser = $x;
		$pointerYUser = $y;

		// Adjust end point of line for last visible point
		
		$k = ($pointerYUser[1] - $pointerYUser[0]) / ($pointerXUser[1] - $pointerXUser[0]); # slope of the given line

		if($pointerXUser[1] > $this->__xAxisRange[1]){
			
			$pointerXUser[1] = $this->__xAxisRange[1];
			$pointerYUser[1] = $pointerYUser[0] + $k * ($pointerXUser[1] - $pointerXUser[0]);
		
		}
		
		if($pointerYUser[1] > $this->__yAxisRange[1]){
			
			$pointerYUser[1] = $this->__yAxisRange[1];
			$pointerXUser[1] = $pointerXUser[0] + ($pointerYUser[1] - $pointerYUser[0]) / $k;
		
		}

		// trigger_error(print_r($pointerXUser, true) . ' ' . print_r($pointerYUser, true), E_USER_ERROR);
		
		// the end point of the line should carry a triangle.
		// the triangle should be symmetric in window geometry !! (not in user geometry)
		// properties of the triangle:

		$alpha = 20; # angle of the triangle legs in degrees 
		$alpha_arc = $alpha * pi() / 180;
		$len = 100; # length of triangle legs

		// Last line in virtual world coordinates
		
		$pointerXWorld[0] = $pointerXUser[0] / 100 * $this->chartWidth;
		$pointerXWorld[1] = $pointerXUser[1] / 100 * $this->chartWidth;
		$pointerYWorld[0] = $pointerYUser[0] / 100 * $this->chartHeight;
		$pointerYWorld[1] = $pointerYUser[1] / 100 * $this->chartHeight;
	
		// trigger_error(print_r($pointerXWorld, true) . ' ' . print_r($pointerYWorld, true), E_USER_ERROR);
	
		// now we calculate the triangle in window coordinates

		$k = ($pointerYWorld[1] - $pointerYWorld[0]) / ($pointerXWorld[1] - $pointerXWorld[0]); # slope of the given line
		$k1 =($k - tan($alpha_arc)) / (1 + $k * tan($alpha_arc)); # slope of the upper leg
		$k2 =($k + tan($alpha_arc)) / (1 - $k * tan($alpha_arc)); # slope of the lower leg

		// we have to distinguish between positive and negative slopes

		$sgn1 = $k1 / abs($k1); # sign of k1
		$sgn2 = $k2 / abs($k2); # sign of k2
		
		// trigger_error($k . ' : ' . $k1 . ' : ' . $k2,E_USER_ERROR); //-TEH
		// trigger_error($sgn1 . ' : ' . $sgn2, E_USER_ERROR); //-TEH
		
		// These are the endpoints of the legs in window coordinates
		
		$arrow1XWorld = $pointerXWorld[1] - 1 * $len / sqrt(1 + pow($k1, 2));
		$arrow1YWorld = $pointerYWorld[1] - $k1 * $len / sqrt(1 + pow($k1, 2));
		$arrow2XWorld = $pointerXWorld[1] - 1 * $len / sqrt(1 + pow($k2, 2)) * $sgn2;
		$arrow2YWorld = $pointerYWorld[1] - $k2 * $len / sqrt(1 + pow($k2, 2)) * $sgn2;

		// trigger_error($arrow1XWorld . ' ' . $arrow1YWorld . ' ' . $arrow2XWorld . ' ' . $arrow2YWorld, E_USER_ERROR).
		
		$arrow1XUser = $arrow1XWorld / $this->chartWidth * 100;
		$arrow1YUser = $arrow1YWorld / $this->chartHeight * 100;
		$arrow2XUser = $arrow2XWorld / $this->chartWidth * 100;
		$arrow2YUser = $arrow2YWorld / $this->chartHeight * 100;

		/* Debug:
		
		$arrow1XUser == 205.5
		$arrow1YUser == 203.95
		$arrow2XUser == 199.1
		$arrow2YUser == 191.5
		
		*/

		// trigger_error($arrow1XUser . ' ' . $arrow1YUser . ' ' . $arrow2XUser . ' ' . $arrow2YUser, E_USER_ERROR).

		// Plot arrow

		$this->chart->addXYPolygonAnnotation($this->__createPolygonArray(array('xEdges' => [$arrow1XUser, $pointerXUser[1], $arrow2XUser], 'yEdges' => [$arrow1YUser, $pointerYUser[1], $arrow2YUser])), $color[0], $color[1], $color[2], $this->polygonOutlineAlpha, $color[0], $color[1], $color[2], $this->polygonFillAlpha,$this->polygonOutlineLineWeight, $this->polygonOutlineLineStyle, true);	
	
	}
	
	function drawChart(){
		
		//Error Checking
		
		if($this->barrierPercentage != null)
			if($this->barrierPercentage < $this->floorPercentageGraph)
				trigger_error('Barrier level must be greater than or equal to Floor level',E_USER_ERROR); //-TEH
		
		if($this->bufferPercentage != null)
			if($this->bufferPercentage < $this->floorPercentageGraph)
				trigger_error('Barrier level must be greater than or equal to Floor level',E_USER_ERROR); //-TEH
		
		//Chart Title
		
		$this->chart = wsd_chart_xy_line("", $this->xAxisTitle, $this->yAxisTitle, false);
		
		
		//Chart Tickers X Axis
		
		$this->chart->overrideTicks(true);
		
		switch($this->issuerSchema){
			
			case 'BMO':
				
				//$tickMarks = array(-1, -0.5, 0, 0.5, 1);
				$tickMarks = array(0);
			
			break;
			
			
			default:
			
				$tickMarks = array(-1, -0.5, 0, 0.5, 1);
			
			break;
			
		}
		
		if($this->barrierPercentage != NULL){
			
			$tickMarks[] = $this->barrierPercentage / 100.0 - 1;
		}
		
		if($this->boosterLevel != NULL){
			
			$tickMarks[] = $this->boosterLevel/100.0 - 1;
		}
		
		if($this->bufferPercentage != NULL){
			
			$tickMarks[] = $this->bufferPercentage / 100.0 - 1;
		
		}
		
		if($this->fixedReturnAmountPercentage != NULL){
			
			$tickMarks[] = $this->fixedReturnAmountPercentage / 100.0 - 1;
		}
		
		if($this->maximumReturnAmountPercentage != NULL){
			
			
			$tickMarks[] = $this->maximumReturnAmountPercentage/100.0 - 1;
		
		}
		
		if($this->floorPercentageGraph > 5){
			
			$tickMarks[] =  $this->floorPercentageGraph/100.0 - 1;
			
		}
		
		
		sort($tickMarks);
		
		$this->chart->setOverrideTicks($tickMarks);
		$this->chart->setXAxisInterval(300);
		
		//Chart Tickers Y Axis
		
		$this->chart->overrideTicksYAxis(true);
		
		//$tickMarks = array(0, 1, 100, 144);
		
		$tickMarks = array(100);
		
		if($this->barrierPercentage != NULL){
			
			$tickMarks[] = $this->barrierPercentage / 100.0 * $this->denomination;
		
		}
		
		if($this->bufferPercentage != NULL){
			
			$tickMarks[] = (100 - $this->bufferPercentage) / 100.0 * $this->denomination;
		
		}
		
		if($this->fixedReturnAmountPercentage != NULL){
			
			$tickMarks[] = $this->fixedReturnAmountPercentage / 100.0 * $this->denomination;
		
		}
		
		if($this->maximumReturnAmountPercentage != NULL){
			
			$tickMarks[] =  ( 1 + ( ($this->maximumReturnAmountPercentage / 100.0 - 1)   * $this->leverageFactor/ 100.0) ) * $this->denomination;
		
		}
		
		if($this->floorPercentageGraph > 5){
			
			$tickMarks[] =  $this->floorPercentageGraph;
			
		}
		
		// if($this->floorPercentage != NULL && $this->bufferPercentage == NULL){
			
			// $tickMarks[] = $this->floorPercentage / 100 * $this->denomination;
			
		// }{
			
			//$tickMarks[] = 0;
			
		// }

		$sortedTicks = $tickMarks;
		$tickMarks = array();
		
		foreach($sortedTicks as $t){
			
			$tToBeAdded = true;
			foreach($sortedTicks as $t2){
				
				if($t2 >= ($t-0.04) and $t2 <= ($t+0.04) and $t2 != $t and floor($t2) != $t)
					$tToBeAdded = false;
				
			}
			
			if($tToBeAdded)
				$tickMarks[] = $t;
			
		}
		
		
		sort($tickMarks);
		array_unique($tickMarks);
		// trigger_error(print_r($tickMarks,true),E_USER_ERROR); //-TEH
			
		$this->chart->setOverrideTicksYAxis($tickMarks);
		$this->chart->setYAxisInterval(300);

	
		// This is necessary because no vertical lines should be drawn in the case of jumps
		$previousXCoordinate = -1; 
		$counter = 0;
		
		switch($this->derivativeLineSplitApplies){
			
			case true:
			
				//Performance Derivative Line Part 1
		
				foreach($this->derivative_x_1 as $key => $value){
					
					/*if($previousXCoordinate == $this->derivative_x[$key])
						$counter++;*/
					$this->chart->plot(
					
						'Derivative 1' . $counter, 
						$this->__adjustXCoordinateForIssuerSchema($this->derivative_x_1[$key]), 
						$this->__adjustYCoordinateForIssuerSchema($this->derivative_y_1[$key]), 
						$this->derivativeLineColor[0], 
						$this->derivativeLineColor[1], 
						$this->derivativeLineColor[2], 
						$this->derivativeLineWeight, 
						$this->derivativeLineStyle
						
					);
					
					$previousXCoordinate = $this->derivative_x_1[$key];
				
				}
				
				//Performance Derivative Line Part 2
		
				foreach($this->derivative_x as $key => $value){
					
					/*if($previousXCoordinate == $this->derivative_x[$key])
						$counter++;*/
					$this->chart->plot(
					
						'Derivative ' . $counter, 
						$this->__adjustXCoordinateForIssuerSchema($this->derivative_x[$key]), 
						$this->__adjustYCoordinateForIssuerSchema($this->derivative_y[$key]), 
						$this->derivativeLineColor[0], 
						$this->derivativeLineColor[1], 
						$this->derivativeLineColor[2], 
						$this->derivativeLineWeight, 
						$this->derivativeLineStyle
						
					);
					
					$previousXCoordinate = $this->derivative_x[$key];
				
				}
			
			break;
			
			
			case false:
			
				//Performance Derivative
				
				foreach($this->derivative_x as $key => $value){
					
					/*if($previousXCoordinate == $this->derivative_x[$key])
						$counter++;*/
					$this->chart->plot(
					
						'Derivative ' . $counter, 
						$this->__adjustXCoordinateForIssuerSchema($this->derivative_x[$key]), 
						$this->__adjustYCoordinateForIssuerSchema($this->derivative_y[$key]), 
						$this->derivativeLineColor[0], 
						$this->derivativeLineColor[1], 
						$this->derivativeLineColor[2], 
						$this->derivativeLineWeight, 
						$this->derivativeLineStyle
						
					);
					
					$previousXCoordinate = $this->derivative_x[$key];
				
				}
			
			break;
			
		}
		
		//Performance Underlying
		
		foreach($this->underlying_x as $key => $value){
			
			$this->chart->plot(
			
				'Underlying', 
				$this->__adjustXCoordinateForIssuerSchema($this->underlying_x[$key]), 
				$this->__adjustYCoordinateForIssuerSchema($this->underlying_y[$key]), 
				$this->underlyingLineColor[0], 
				$this->underlyingLineColor[1], 
				$this->underlyingLineColor[2], 
				$this->underlyingLineWeight, 
				$this->underlyingLineStyle
				
			);
			
		}
		
		
		
		// Polygon code - deactivated for RBC (see Citi for a client that uses this code)
		// Re-Activated for BMO
		
		foreach($this->polygons as $polygon){
			
			$this->chart->addXYPolygonAnnotation(
			
				$this->__createPolygonArray($polygon), 
				$this->polygonOutlineColor[0], 
				$this->polygonOutlineColor[1], 
				$this->polygonOutlineColor[2], 
				$this->polygonOutlineAlpha, 
				$this->polygonFillColor[0], 
				$this->polygonFillColor[1], 
				$this->polygonFillColor[2], 
				$this->polygonFillAlpha, 
				$this->polygonOutlineLineWeight, 
				$this->polygonOutlineLineStyle, 
				true
				
			);
		
		}
		
		
		//Chart Barrier - Reference line
		
		switch($this->productType){
			
			case 'Enhanced barrier digital plus':
			case 'Capped enhanced barrier digital plus':
				//Barrier line is replaced with booster line below as booster/digital level == barrier level for "Enhanced" booster/digital products
			break;
			
			default:
			
				if($this->barrierPercentage != NULL){
			
					$this->chart->plot(
					
						'Derivative barrier dashed line',
						$this->__adjustXCoordinateForIssuerSchema($this->barrierPercentage), 
						$this->__adjustYCoordinateForIssuerSchema(0), 
						255, 0, 0, 
						$this->derivativeLineWeight, 'dashed'
						
					);
					
					$this->chart->plot(
					
						'Derivative barrier dashed line', 
						$this->__adjustXCoordinateForIssuerSchema($this->barrierPercentage), 
						$this->__adjustYCoordinateForIssuerSchema(100), 
						255, 0, 0, 
						$this->derivativeLineWeight, 'dashed'
						
					);
			
				}
			
			break;
		}
		
		//Booster/digital Level - Reference line
		
		if($this->boosterLevel < 100 && $this->boosterLevel != NULL && $this->derivativeLineSplitApplies){
			
			$this->chart->plot(
			
				'Booster Level dashed line',
				$this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				$this->__adjustYCoordinateForIssuerSchema($this->boosterLevel), 
				255, 0, 0, 
				$this->derivativeLineWeight, 'dashed'
				
			);
			
			$this->chart->plot(
			
				'Booster Level dashed line', 
				$this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				$this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), 
				255, 0, 0, 
				$this->derivativeLineWeight, 'dashed'
				
			);
			
			$this->chart->plot(
			
				'Booster Level dashed line grey',
				$this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				$this->__adjustYCoordinateForIssuerSchema(0), 
				128, 128, 128, 
				2, 'dashed'
				
			);
			
			$this->chart->plot(
			
				'Booster Level dashed line grey',
				$this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				$this->__adjustYCoordinateForIssuerSchema($this->boosterLevel), 
				128, 128, 128, 
				2, 'dashed'
				
			);
			
		}elseif($this->boosterLevel < 100 && $this->boosterLevel != NULL && $this->derivativeLineSplitApplies == false){
			
			// $this->chart->plot(
			
				// 'Booster Level dashed line',
				// $this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				// $this->__adjustYCoordinateForIssuerSchema($this->boosterLevel), 
				// 255, 0, 0, 
				// $this->derivativeLineWeight, 'dashed'
				
			// );
			
			// $this->chart->plot(
			
				// 'Booster Level dashed line', 
				// $this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				// $this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), 
				// 255, 0, 0, 
				// $this->derivativeLineWeight, 'dashed'
				
			// );
			
			$this->chart->plot(
			
				'Booster Level dashed line grey',
				$this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				$this->__adjustYCoordinateForIssuerSchema(0), 
				128, 128, 128, 
				2, 'dashed'
				
			);
			
			$this->chart->plot(
			
				'Booster Level dashed line grey',
				$this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				$this->__adjustYCoordinateForIssuerSchema(100), 
				128, 128, 128, 
				2, 'dashed'
				
			);
			
		}
		
		if($this->fixedReturnAmountPercentage != NULL){
			
			$this->chart->plot(
			
				'Booster Return Horizontal dashed line grey',
				$this->__adjustXCoordinateForIssuerSchema(0), 
				$this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), 
				128, 128, 128, 
				2, 'dashed'
				
			);
			
			$this->chart->plot(
			
				'Booster Return Horizontal dashed line grey',
				$this->__adjustXCoordinateForIssuerSchema($this->boosterLevel), 
				$this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), 
				128, 128, 128, 
				2, 'dashed'
				
			);
		
		}
		
		//Chart Cap - Reference line
		
		if($this->maximumReturnAmountAbsolute != NULL){
			
			$this->chart->plot(
			
				'Cap dashed line',
				$this->__adjustXCoordinateForIssuerSchema($this->maximumReturnAmountPercentage), 
				$this->__adjustYCoordinateForIssuerSchema(0), 
				128, 128, 128, 
				2, 'dashed'
				
			);
			
			$this->chart->plot(
			
				'Cap dashed line', 
				$this->__adjustXCoordinateForIssuerSchema($this->maximumReturnAmountPercentage), 
				$this->__adjustYCoordinateForIssuerSchema($this->maximumReturnAmountAbsolute), 
				128, 128, 128, 
				2, 'dashed'
				
			);
			
		}
		
		//Chart Floor - Reference line
		
		if($this->displayFloorLevelAnnotation){
			
			if($this->floorPercentageGraph > 5){
			
				$this->chart->plot('Floor protection line vertical', $this->__adjustXCoordinateForIssuerSchema($this->floorPercentageGraph), $this->__adjustYCoordinateForIssuerSchema($this->floorPercentageGraph), 0, 0, 0, 1, 'dashed');
						
				$this->chart->plot('Floor protection line vertical', $this->__adjustXCoordinateForIssuerSchema($this->floorPercentageGraph), $this->__adjustYCoordinateForIssuerSchema(0), 0, 0, 0, 1, 'dashed');
				
				if($this->bufferPercentage != null){
				
					$this->chart->plot('Floor protection line horizontal', $this->__adjustXCoordinateForIssuerSchema(0), $this->__adjustYCoordinateForIssuerSchema($this->floorPercentageGraph), 0, 0, 0, 1, 'dashed');
							
					$this->chart->plot('Floor protection line horizontal', $this->__adjustXCoordinateForIssuerSchema($this->floorPercentageGraph), $this->__adjustYCoordinateForIssuerSchema($this->floorPercentageGraph), 0, 0, 0, 1, 'dashed');
				
				}
		
			}
		
		}
		
		//Chart Digital/Booster - Reference line
		
		switch($this->productType){

			case 'Digital':
			case 'Digital plus':
			case 'Enhanced digital plus':
			case 'Capped digital plus':
			case 'Capped enhanced digital plus':
			case 'Barrier digital':
			case 'Barrier digital plus':
			case 'Enhanced barrier digital plus':
			case 'Capped barrier digital plus':
			case 'Capped enhanced barrier digital plus':
			case 'Buffered digital':
			case 'Buffered digital plus':
			case 'Enhanced buffered digital plus':
			case 'Capped buffered digital plus':
			case 'Capped enhanced buffered digital plus':
				
				$this->chart->plot(
				
					'Derivative digital dashed line', 
					$this->__adjustXCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), 
					$this->__adjustYCoordinateForIssuerSchema(0), 
					128, 128, 128, 
					2, 'dashed'
					
				);
				
				$this->chart->plot(
				
					'Derivative digital dashed line', 
					$this->__adjustXCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), 
					$this->__adjustYCoordinateForIssuerSchema($this->fixedReturnAmountPercentage), 
					128, 128, 128, 
					2, 'dashed'
				
				);

			break;
			
		}
		
		
		// The following code attaches a pointer at the end of the right-most line for the DERIVATIVE underlying performance line

		// Last line of derivative
		
		$lastDerivativeLineX = array_slice($this->derivative_x, -2, 2);
		$lastDerivativeLineY = array_slice($this->derivative_y, -2, 2);

		// $lastDerivativeLineSlope = ($lastDerivativeLineY[1] - $lastDerivativeLineY[0]) / ($lastDerivativeLineX[1] - $lastDerivativeLineX[0]); # slope of the given line
		
		$this->drawArrowHead($lastDerivativeLineX, $lastDerivativeLineY, $this->derivativeLineColor);
		
		// The following code attaches a pointer at the end of the right-most line for the REFERENCE underlying performance line
		
		// Now let's check if the underlying line is different
		
		// $lastUnderlyingLineX = array_slice($this->underlying_x, -2, 2);
		// $lastUnderlyingLineY = array_slice($this->underlying_y, -2, 2);
		
		// $lastUnderlyingLineSlope = ($lastUnderlyingLineY[1] - $lastUnderlyingLineY[0]) / ($lastUnderlyingLineX[1] - $lastUnderlyingLineX[0]); # slope of the given line
		
		//Deactivated Refernce Underlying's line arrow head for BMO
		
		// if($lastDerivativeLineSlope != $lastUnderlyingLineSlope) {
		
			// $this->drawArrowHead($lastUnderlyingLineX, $lastUnderlyingLineY, $this->underlyingLineColor);

		// }
		
		//Horizontal and Vertical Reference Lines  - No Longer needed as the feature specific Reference lines adapt to the trade features via checks for specific variable feature values being set for example ($this->barrierPercentage != NULL)
		
		switch($this->issuerSchema){
			
			case 'BMO':
			
				//Deactivated for BMO
				
			break;	
			
			default:
			
				foreach($this->horizontalReferenceLines as $key => $value){	
			
					$this->chart->plot('Horizontal reference lines ' . $key, $this->__adjustXCoordinateForIssuerSchema(0), $this->__adjustYCoordinateForIssuerSchema($value[1]), 0, 0, 0, 1, 'solid');
					
					$this->chart->plot('Horizontal reference lines ' . $key, $this->__adjustXCoordinateForIssuerSchema($value[0]), $this->__adjustYCoordinateForIssuerSchema($value[1]), 0, 0, 0, 1, 'solid');
				
				}

				foreach($this->verticalReferenceLines as $key => $value){
					
					$this->chart->plot('Vertical reference lines ' . $key, $this->__adjustXCoordinateForIssuerSchema($value[0]), $this->__adjustYCoordinateForIssuerSchema($value[1]), 0, 0, 0, 1, 'dashed');
					
					$this->chart->plot('Vertical reference lines ' . $key, $this->__adjustXCoordinateForIssuerSchema($value[0]), $this->__adjustYCoordinateForIssuerSchema(0), 0, 0, 0, 1, 'dashed');
				
				}
			
			break;

		}
		
		if($this->floorPercentage != NULL && $this->bufferPercentage == NULL){
		
			$this->annotations[] = [
				'terminology' => '$' . ($this->floorPercentage/100 * $this->denomination) . 'Minimum Return',
				'x' => 0.5,
				'y' => $this->floorPercentageGraph,
				'pointerLength' => 120,
				'pointerAngle' => (pi()/180) * (180 + 90 + 7),
				'pointerOrientation' => 'BOTTOM_LEFT'
			];
		
		}
		
		
		//Chart Annotations
		
		if($this->showAnnotations){
		
			foreach($this->annotations as $annotation){
				
				switch($annotation['type']){
					
					case 'AnnotationWithPointer':{
						
						$this->chart->addPointerAnnotation(
					
							$annotation['terminology'], 
							$this->fontFamily, 0, 20, 
							$this->__adjustXCoordinateForIssuerSchema($annotation['x']), 
							$this->__adjustYCoordinateForIssuerSchema($annotation['y']),
							0, 0, 0, 
							0, 0, 0, 
							5, 
							$annotation['pointerLength'], 
							$annotation['pointerAngle'], 
							$annotation['pointerOrientation'], 
							5,
							
						);
						
						// addPointerAnnotation(
						
							// label,key,value,angle,
							// labelFont,labelFontStyle, 
							// labelFontSize,
							// r,g,b,
							// arrowR,arrowG,arrowB,
							// arrowtipOffSetFromYAxis
							// tipRadius,  baseRadius,
							// textAnchor,
							// labelOffset,
							// weight,style,arrowWidth
						
						// )
						
					}break;
					
					case 'AnnotationWithoutPointer':{
					
						$this->chart->addNonPointerAnnotation(
						
							$annotation['terminology'], 
							$this->fontFamily, 0, 20, 
							$this->__adjustXCoordinateForIssuerSchema($annotation['x']), 
							$this->__adjustYCoordinateForIssuerSchema($annotation['y']),
							0, 0, 0
							
						);
						
						// addNonPointerAnnotation(
						
							// String label, 
							// String labelFont, int labelFontStyle, int labelFontSize, 
							// double value,
							// String category, 
							// int r, int g, int b,
							// double textAngle
							
						//)
						
				}break;
					
					//Functionality needs to be added first
					case 'RotateAnnotationWithoutPointer':{
						
						$this->chart->addNonPointerAnnotation(
							
							$annotation['terminology'], 
							$this->fontFamily, $annotation['textStyle'], $annotation['size'], 
							$this->__adjustXCoordinateForIssuerSchema($annotation['x']), 
							$this->__adjustYCoordinateForIssuerSchema($annotation['y']),
							$annotation['textColor'][0],$annotation['textColor'][1],$annotation['textColor'][2],
							$annotation['rotationAngle'], 'CENTER_LEFT', 'CENTER_LEFT'
						);
						
						
						// String label, 
							// String labelFont, 
							// int labelFontStyle, 
							// int labelFontSize, 
							// double value, 
							// String category,
							// int r, int g, int b,
							
							// Double rotationAngle, String rotationAnchor, String textAnchor)
						
					}break;
					
					default:{
					
						$this->chart->addPointerAnnotation(
					
							$annotation['terminology'], 
							$this->fontFamily, 0, 20, 
							$this->__adjustXCoordinateForIssuerSchema($annotation['x']), 
							$this->__adjustYCoordinateForIssuerSchema($annotation['y']),
							0, 0, 0, 
							0, 0, 0, 
							5, 
							$annotation['pointerLength'], 
							$annotation['pointerAngle'], 
							$annotation['pointerOrientation'], 
							0,
							
						);
						
						// addPointerAnnotation(
							
							// label,key,value,angle,
							// labelFont,labelFontStyle, 
							// labelFontSize,
							// r,g,b,
							// arrowR,arrowG,arrowB,
							// arrowtipOffSetFromYAxis
							// tipRadius,  baseRadius,
							// textAnchor,
							// labelOffset,
							// weight,style,arrowWidth
							
						// )
					
					}break;
					
				}
				
			}
		
		}
		
		//Chart Title and X/Y Legends
		
		$this->chart->setTitleAndFont("", $this->fontFamily, 0, 30);
		
		$this->chart->setXAxisLabelFont($this->fontFamily, 0, 18);
		// $this->chart->setXAxisLegendFont($this->fontFamily, 0, 23);
		$this->chart->setXAxisLegendFont($this->fontFamily, 0, 28);
		
		$this->chart->setYAxisLabelFont($this->fontFamily, 0, 18);
		// $this->chart->setYAxisLegendFont($this->fontFamily, 0, 23);
		$this->chart->setYAxisLegendFont($this->fontFamily, 0, 28);
		
		//Chart Colour and Grid Markers
		
		$this->chart->setBackgroundRGB(255, 255, 255);
		
		switch($this->issuerSchema){
			
			case 'BMO':
				
				$this->chart->addXMarker(0, 2.5, 'solid', 128, 128, 128);
				$this->chart->addYMarker(100, 2.5, 'solid', 128, 128, 128);
				
				// $this->chart->addXMarker(-100, 2.5, 'solid', 128, 0, 0);
				// $this->chart->addYMarker(0, 2.5, 'solid', 128, 0, 0);
				
				// $this->chart->setYAxisLineColor(0, 0, 0, 100);
				// $this->chart->setXAxisLineColor(0, 0, 0, 100);
				
				
				$this->chart->setXAxisGridlineOff();
				$this->chart->setYAxisGridlineOff();
			
			break;
			
			default:
			
				$this->chart->setXAxisGridlineOff();
				
				$this->chart->setYAxisGridlineOff();
				
				$this->chart->addYMarker(0, 1, 'solid', 0, 0, 0);
			
			break;
			
		}
		
		//Set X/Y Axis Range
		
		$this->chart->setXAxisRange(
		
			$this->__adjustXCoordinateForIssuerSchema($this->__xAxisRange[0]), 
			$this->__adjustXCoordinateForIssuerSchema($this->__xAxisRange[1])
		
		);
		
		$this->chart->setYAxisRange(
			
			$this->__adjustYCoordinateForIssuerSchema($this->__yAxisRange[0]), 
			$this->__adjustYCoordinateForIssuerSchema($this->__yAxisRange[1])
			
		);
		
		
		$this->chart->setYAxisRange(0,200.0);
		
		//$this->chart->setXAxisInterval(0.5);
		//$this->chart->setYAxisInterval($this->denomination * 0.5);
		$this->chart->setXAxisFormat("#%");
		$this->chart->setYAxisFormat("$#");
		$this->chart->setAxisOffsets(0.0, 0.0, 0.0, 0.0);
		// $this->chart->yAxis()->isVisible(false);
		// trigger_error(print_r(get_class_methods($this->chart->yAxis()),true),E_USER_ERROR); //-TEH;
		// $this->chart->setAxisOffsets(2.0, 2.0, 2.0, 2.0);
		// $this->chart->yAxis()->setAutoRangeIncludesZero(false);
		
		//Draw Chart
		echo wsd_chart_draw($this->chart, 50, 50, $this->chartWidth, $this->chartHeight);
		
	}

}

/*
// Usage

$payoffChart = new payoffChartClass();
$payoffChart->setIssuerSchema('Citi green');
$payoffChart->setDenomination(1000);
$payoffChart->setProductTypeProperty('Fixed return amount percentage', 120);
$payoffChart->setProductTypeProperty('Maximum return amount percentage', 150);
$payoffChart->setProductTypeProperty('Leverage factor', 200);
$payoffChart->setProductTypeProperty('Buffer percentage', 90);
$payoffChart->setProductTypeProperty('Barrier percentage', 80);
$payoffChart->setProductType('Capped enhanced buffered digital plus');
$payoffChart->initializeTerminology();
$payoffChart->calculateEdges();
$payoffChart->drawChart();

*/

?>