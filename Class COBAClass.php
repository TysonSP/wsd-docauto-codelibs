<?php

class COBAClass {

	var $issueDate;
	var $maturityDate;
	
	var $monthsThatAreAutoCallObservationDates;
	var $numberOfAutoCallObservationDates;
	
	var $randomNumber;
	
	var $template;
	
	var $monthsThatAreCouponBarrierObservationDates;
	var $monthsThatAreCouponPaymentDates;
	var $couponPaymentDatesOffset = 0;
	
	var $monthsThatAreObservationDatesRaw;
	var $monthsThatArePaymentDatesRaw;
	
	var $monthsThatAreObservationDates;
	var $numberOfCouponBarrierObservationDates;
	var $numberOfCouponPaymentDates;
	
	
	var $interval;
	
	var $autoCallLevel;
	var $couponBarrierLevel;
	var $knockInBarrierLevel;
	
	var $autoCallLevelTerminology;
	var $couponBarrierLevelTerminology;
	var $knockInBarrierLevelTerminology;
	var $combinedCouponBarrierAndKnockInBarrierLevelTerminology;
	var $participationTerminology;
	var $initialLevel;
	var $initialLevelTerminology;
	var $autoCalledTerminology;
	var $finalLevelTerminology;
	
	var $yAxisString;
	var $xAxisString;
	
	var $yAxisRangeMaxValue = 150;
	
	var $showGraphOnlyUntilAutoCallOccurs = false;
	
	var $highlightAllValuationDates = true; // Change to false to dot only valuation dates below coupon barrier
	var $highlightPeriodsInWhichNoCouponIsPaid = false; // True highlights the periods when coupons are not paid
	var $highlightNonCallPeriods = true;
	var $highlightPeriodsAfterAutocall = true;
	
	var $PlotPointsFromZeroHasBeenSet = false;
	
	var $setPlotPointsFromZero = false;
	
	var $couponPaymentPercetage = null;
	var $couponPaymentPercetagePerFrequency = null;
	var $initialAbsoluteValue  = null;
	var $graphXAxisWidthMuliplier  = 1;
	var $graphYAxisWidthMuliplier  = 1;
	
	var $colorMap;
	var $docx = null;
	

	public $timeSeries;
	public $timeSeriesProperties;
	
	var $currentTimestamp;

	// Individual example parameters - these need to be set by the class user
	
	// Should the note be auto-called?
	
	var $whetherAutoCallLevelShouldBeHit;
	const AUTOCALL_MUST_OCCUR = 1;
	const AUTOCALL_MUST_NOT_OCCUR = 2;
	const AUTOCALL_IRRELEVANT = 3;

	var $whenAutoCallLevelShouldBeHit;
	const FIRST_OPPORTUNITY_AUTOCALL = 1; 
	const EARLY_AUTOCALL = 2; 
	const MID_AUTOCALL = 3; 
	const LATE_AUTOCALL = 4; 
	var $monthInWhichAutoCallLevelShouldBeHit;
	
	// Should coupon barrier be breached?
	
	var $whetherCouponBarrierShouldBeBreached;
	const COUPON_BARRIER_BREACH_MUST_OCCUR = 1;
	const COUPON_BARRIER_BREACH_MUST_NOT_OCCUR = 2;
	const COUPON_BARRIER_BREACH_IRRELEVANT = 3;
	var $numberOfTimesCouponBarrierShouldBeBreached;
	var $monthsInWhichCouponBarrierIsBreached;

	// What should the final level be?
	
	var $whereFinalLevelShouldBe;
	const FINAL_LEVEL_MUST_BE_ABOVE_KNOCKIN_BARRIER = 1;
	const FINAL_LEVEL_MUST_BE_BELOW_KNOCKIN_BARRIER = 2;
	const FINAL_LEVEL_MUST_BE_ABOVE_SPECIFIC_VALUE = 3;
	const FINAL_LEVEL_MUST_BE_BELOW_SPECIFIC_VALUE = 4;

	// What should the time series look like?
	
	var $timeSeriesShape;
	const TIME_SERIES_LOW_HIGH = 1; // Underlying first declines, then appreciates
	const TIME_SERIES_HIGH_LOW = 2; // Underlying first appreciates, then declines
	const TIME_SERIES_EVEN = 3; // // Underlying fluctuates around the same level
	
	function __construct($randomNumber, $template, $docx) {
	
		$this->issueDate = NULL;
		$this->monthsThatAreAutoCallObservationDates = array();
		$this->monthsThatAreCouponBarrierObservationDates = array();
		$this->randomNumber = $randomNumber;
		$this->template = $template;
		$this->docx = $docx;

		$this->autoCallLevel = 100;
		$this->initialLevel = 100;
		

		$this->colorMap = array(
			0 => array('r' => 0, 'g' => 36, 'b' => 250),
			1 => array('r' => 124, 'g' => 127, 'b' => 129),
			2 => array('r' => 214, 'g' => 62, 'b' => 251),
			3 => array('r' => 187, 'g' => 212, 'b' => 38)
		);
		
		if($this->docx != null){
			
			$this->graphXAxisWidthMuliplier = 10;
			$this->graphYAxisWidthMuliplier = 5;
		}
		
	}
	
	function setIssueDate($issueDate) {
	
		$this->issueDate = round($issueDate / 86400.0) * 86400;
	
	}

	function setMaturityDate($maturityDate) {
	
		$this->maturityDate = round($maturityDate / 86400.0) * 86400;
	
	}
	
	function setPlotPointsFromZero(){ //IMPORTANT READ THIS NOTE!!!!!  --  This function should only be used to affect the drawchart function (how the graph is drawn) --- however the calculateTimeSeries($example, $underlying) must be run for all examples before this is set as it would inadvertently affect the calculations of the plot points
		
		if($this->PlotPointsFromZeroHasBeenSet == false){
			
			global $manager;
			
			$this->initialLevel = 0;	
			$this->couponBarrierLevel -= 100;
			$this->knockInBarrierLevel -= 100;
			$this->autoCallLevel -= 100;
			
			$this->yAxisRangeMaxValue = 50;
			
			if($manager->getElement('Upside participation applies')){
			
				$this->participationLevel -= 100;
				
				
			}
			
			foreach($this->timeSeries as $example => $currentExample)
				foreach($currentExample as $underlying => $values)
					foreach($values as $month => $level)
						$this->timeSeries[$example][$underlying][$month] -= 100;
				

			
			$this->PlotPointsFromZeroHasBeenSet = true;
		
		}
		
	}
	
	function setAutoCallObservationDates($observationDates) {
	
		if($this->issueDate == NULL){
			
			$errorMessage["Error"] = 'COBAClass: setAutoCallObservationDates() - need to set issue date before setting autocall observation dates';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
			
		if(!is_array($observationDates)){
			
			$errorMessage["Error"] = 'COBAClass: setAutoCallObservationDates() - invalid observation dates';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
		}
		
		$observationCounter = 0;
		
		foreach($observationDates as $obervationCounter => $observationDate) {
			$this->monthsThatAreAutoCallObservationDates[round(($observationDate - $this->issueDate) / 86400 / 30.5)] = 1;
			$this->monthsThatAreObservationDates[round(($observationDate - $this->issueDate) / 86400 / 30.5)] = 1;
			$this->monthsThatAreObservationDatesRaw[$observationCounter++] = $observationDate;
		}
			
		$this->numberOfAutoCallObservationDates = count($observationDates);
	
	}

	function setCouponBarrierObservationDates($observationDates) {
	
		if($this->issueDate == NULL){
			
			$errorMessage["Error"] = 'COBAClass: setCouponBarrierObservationDates() - need to set issue date before setting coupon barrier observation dates';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
			
			
		if(!is_array($observationDates)){
			
			$errorMessage["Error"] = 'COBAClass: setCouponBarrierObservationDates() - invalid observation dates';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
	
		//trigger_error($observationDates[0] . ' ' . $this->issueDate, E_USER_ERROR);
	
		foreach($observationDates as $observationDate) {
			$this->monthsThatAreCouponBarrierObservationDates[round(($observationDate - $this->issueDate) / 86400 / 30.5)] = 1;
			$this->monthsThatAreObservationDates[round(($observationDate - $this->issueDate) / 86400 / 30.5)] = 1;
		}
		$this->numberOfCouponBarrierObservationDates = count($observationDates);
		
		$dummy = array_keys($this->monthsThatAreCouponBarrierObservationDates);
		$date2 = end($dummy);
		$date1 = prev($dummy);
		$this->couponBarrierObservationDatesInterval = $date2 - $date1;
		
	}
	
	function setCouponPaymentDates($paymentDates) {
	
		if(!is_array($this->monthsThatAreCouponBarrierObservationDates)){
			
			$errorMessage["Error"] = 'COBAClass: setCouponPaymentDates() - need to set coupon barrier observation dates before setting coupon payment dates';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
		
		
		if($this->issueDate == NULL){
			
			$errorMessage["Error"] = 'COBAClass: setCouponPaymentDates() - need to set issue date before setting coupon payment dates';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
			
		if(!is_array($paymentDates)){
			
			$errorMessage["Error"] = 'COBAClass: setCouponPaymentDates() - invalid payment dates';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
		
		$paymentCounter = 0;
	
		foreach($paymentDates as $paymentDate) {
			$this->monthsThatAreCouponPaymentDates[round(($paymentDate - $this->issueDate) / 86400 / 30.5)] = 1;
			$this->monthsThatArePaymentDates[round(($paymentDate - $this->issueDate) / 86400 / 30.5)] = 1;
			$this->monthsThatArePaymentDatesRaw[$paymentCounter++] = $paymentDate;
		}
	
		$this->numberOfCouponPaymentDates = count($paymentDates);
		
		$dummy = array_keys($this->monthsThatAreCouponPaymentDates);
		$date2 = end($dummy);
		$date1 = prev($dummy);
		$this->couponPaymentDatesInterval = $date2 - $date1;
		
		$this->couponPaymentDatesOffset = reset(array_keys($this->monthsThatAreCouponPaymentDates)) - reset(array_keys($this->monthsThatAreCouponBarrierObservationDates));

		$dummy = array_keys($this->monthsThatAreCouponPaymentDates);
		$date2 = end($dummy);
		$date1 = prev($dummy);
		$this->couponPaymentDatesInterval = $date2 - $date1;
		
	}

	function setInterval($interval) {
	
		switch($interval) {
			case 'monthly':
				$this->timestampAdjustmentFactor = 1;
			break;
			
			case 'quarterly':
				$this->timestampAdjustmentFactor = 3;
			break;
			
			case 'semi-annually':
				$this->timestampAdjustmentFactor = 6;
			break;
			
			case 'annually':
				$this->timestampAdjustmentFactor = 12;
			break;
			
			default:
				
				$errorMessage["Error"] = 'COBAClass: setInterval() - invalid interval';
				$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
				trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
			break;
		
		}
	
		$this->interval = $interval;
	
	}

	function setAutoCallLevel($autoCallLevel){
		
		global $manager;
		
		switch($manager->getElement('Product family')){
			
			case 'Growth family':
				
				switch($manager->getElement('Product subtype')){
					
					case 'Accelerator':
					
						if(!is_numeric($autoCallLevel) || $autoCallLevel < 100 || $autoCallLevel > 200){
							
							$errorMessage["Error"] = 'COBAClass: Downside Protection - out of bounds: ' . $autoCallLevel;
							$errorMessage["Line"] = debug_backtrace()[0]['line'];
							
							trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
							
						}
							
					break;
					
					case 'Booster':
					
						if(!is_numeric($autoCallLevel) || $autoCallLevel < 100 || $autoCallLevel > 200){
							
							$errorMessage["Error"] = 'COBAClass: Boosted Return - boosted return out of bounds: ' . $autoCallLevel;
							$errorMessage["Line"] = debug_backtrace()[0]['line'];
							
							trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
							
						}
						
					break;
					
				}
		
			break;
			
			case 'Autocallable family':
				
				if(!is_numeric($autoCallLevel) || $autoCallLevel < 50 || $autoCallLevel > 200){
					
					$errorMessage["Error"] = 'COBAClass: Boosted Return - boosted return out of bounds: ' . $autoCallLevel;
					$errorMessage["Line"] = debug_backtrace()[0]['line'];
					
					trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
					
					
				}
				
			break;
			
			
			default:
				
				if(!is_numeric($autoCallLevel) || $autoCallLevel < 50 || $autoCallLevel > 150){
					
					$errorMessage["Error"] = 'COBAClass: setAutoCallLevel() - invalid autocall level: ' . $autoCallLevel;
					$errorMessage["Line"] = debug_backtrace()[0]['line'];
					
					trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
					
				}
				
			
			break;
			
		}
		
		$this->autoCallLevel = $autoCallLevel;
	
	}
	
	function setParticipationLevel($participationLevel) {
	
		if(!is_numeric($participationLevel) || $participationLevel < 100){
			
			$errorMessage["Error"] = 'COBAClass: setParticipationLevel() - invalid Participation level: ' . $participationLevel;
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
			
		$this->participationLevel = $participationLevel;
	
	}
	
	function setAutoCallLevelTerminology($autoCallLevelTerminology) {
	
		$this->autoCallLevelTerminology = $autoCallLevelTerminology;
	
	}

	function setCouponBarrierLevel($couponBarrierLevel) {
	
		if(!is_numeric($couponBarrierLevel) || $couponBarrierLevel < 30 || $couponBarrierLevel > 150){
			
			$errorMessage["Error"] = 'COBAClass: setCouponBarrierLevel() - invalid coupon barrier level';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
		
		$this->couponBarrierLevel = $couponBarrierLevel;
	
	}

	function setCouponBarrierLevelTerminology($couponBarrierLevelTerminology) {
	
		$this->couponBarrierLevelTerminology = $couponBarrierLevelTerminology;
		
	}
		
	function setKnockInBarrierLevel($knockInBarrierLevel) {
	
		if(!is_numeric($knockInBarrierLevel) || $knockInBarrierLevel < 30 || $knockInBarrierLevel > 150){
			
			$errorMessage["Error"] = 'COBAClass: setKnockInBarrierLevel() - invalid knock-in barrier level';
			$errorMessage["Line"] = debug_backtrace()[0]['line'];
			
			trigger_error(print_r($errorMessage,true),E_USER_ERROR); //-TEH
			
		}
		
		$this->knockInBarrierLevel = $knockInBarrierLevel;
	
	}
	
	function setKnockInBarrierLevelTerminology($knockInBarrierLevelTerminology) {

		$this->knockInBarrierLevelTerminology = $knockInBarrierLevelTerminology;
		
	}
	
	function setCombinedCouponBarrierAndKnockInBarrierLevelTerminology($combinedCouponBarrierAndKnockInBarrierLevelTerminology) {
	
		$this->combinedCouponBarrierAndKnockInBarrierLevelTerminology = $combinedCouponBarrierAndKnockInBarrierLevelTerminology;
		
	}
	
	function setParticipationTerminology($participationTerminology) {
	
		$this->participationTerminology = $participationTerminology;
		
	}
	
	function setInitialLevelTerminology($initialLevelTerminology) {
	
		$this->initialLevelTerminology = $initialLevelTerminology;
		
	}
	
	function setAutoCalledTerminology($autoCalledTerminology) {
	
		$this->autoCalledTerminology = $autoCalledTerminology;
		
	}
	
	function setFinalLevelTerminology($finalLevelTerminology) {
	
		$this->finalLevelTerminology = $finalLevelTerminology;
		
	}
	function setYAxisString($yAxisString) {
	
		$this->yAxisString = $yAxisString;
		
	}
	function setXAxisString($xAxisString) {
	
		$this->xAxisString = $xAxisString;
		
	}
	
	function setWhetherAutoCallLevelShouldBeHit($whetherAutoCallLevelShouldBeHit, $whenAutoCallLevelShouldBeHit = COBAClass::FIRST_OPPORTUNITY_AUTOCALL) {
	
		global $manager;
		
		$this->whetherAutoCallLevelShouldBeHit = $whetherAutoCallLevelShouldBeHit;
	
		switch($this->whetherAutoCallLevelShouldBeHit) {
		
			case COBAClass::AUTOCALL_MUST_NOT_OCCUR:
				$this->autoCallTimeStamp = -1;
			break;
		
			case COBAClass::AUTOCALL_MUST_OCCUR:
				$this->whenAutoCallLevelShouldBeHit = $whenAutoCallLevelShouldBeHit;
			
				switch($this->whenAutoCallLevelShouldBeHit) {
				
					case COBAClass::FIRST_OPPORTUNITY_AUTOCALL:
						$this->monthInWhichAutoCallLevelShouldBeHit = array_keys($this->monthsThatAreAutoCallObservationDates)[0 + $manager->getElement('Number of non call periods')];
					break;
					
					case COBAClass::EARLY_AUTOCALL:
						$this->monthInWhichAutoCallLevelShouldBeHit = array_keys($this->monthsThatAreAutoCallObservationDates)[round(($this->numberOfAutoCallObservationDates + $manager->getElement('Number of non call periods')) / 3)];
					break;
					
					case COBAClass::MID_AUTOCALL:
						$this->monthInWhichAutoCallLevelShouldBeHit = array_keys($this->monthsThatAreAutoCallObservationDates)[round(($this->numberOfAutoCallObservationDates + $manager->getElement('Number of non call periods')) / 2)];
					break;
					
					case COBAClass::LATE_AUTOCALL:
						$this->monthInWhichAutoCallLevelShouldBeHit = array_keys($this->monthsThatAreAutoCallObservationDates)[round(($this->numberOfAutoCallObservationDates + $manager->getElement('Number of non call periods')) / 3 * 2)];
					break;
		
					default:
						trigger_error('COBAClass: setWhetherAutoCallLevelShouldBeHit() - invalid specification of when autocall should occur', E_USER_ERROR);
				}
			break;
			
			case COBAClass::AUTOCALL_IRRELEVANT:
			break;
			
			default:
				trigger_error('COBAClass: setWhetherAutoCallLevelShouldBeHit() - invalid specification of whether autocall should occur', E_USER_ERROR);
		}
	
	}
	
	// function getAutoCallObservationDateOnWhichAutoCallLevelIsHit($inPeriods = false, $getEpochTimeStamp = false) {
	function getAutoCallObservationDateOnWhichAutoCallLevelIsHit($inPeriods = false) {
	
		// if($getEpochTimeStamp){
			
			// return $this->monthsThatAreObservationDatesRaw[$this->monthInWhichAutoCallLevelShouldBeHit/$this->timestampAdjustmentFactor - 1];
			
		// }else{
		
			if($inPeriods = true){
		
				return floor($this->monthInWhichAutoCallLevelShouldBeHit / $this->timestampAdjustmentFactor);
			
			} else
				return $this->monthInWhichAutoCallLevelShouldBeHit;
		
		// }
		
		
	}
	

	function getNumberOfAutoCallObservationDatesBeforeNoteIsAutoCalled() {
	
		$count=0;
		for($i=0; $i<=$this->monthInWhichAutoCallLevelShouldBeHit; $i++)
			if($this->monthsThatAreAutoCallObservationDates[$i])
				$count++;
				
		return $count;
		
	}
	
	function getNumberOfCouponBarrierObservationDates() {
	
		return $this->numberOfCouponBarrierObservationDates;
	
	}
	
	function getNumberOfCouponBarrierObservationDatesBeforeNoteIsAutoCalled() {
	
		$count=0;
		for($i=0; $i<=$this->monthInWhichAutoCallLevelShouldBeHit; $i++)
			if($this->monthsThatAreCouponBarrierObservationDates[$i])
				$count++;
				
		return $count;
		
	}
	
	function getCouponBarrierObservationDatesOnWhichTheNoteIsAutoCalled() {
	
		return $this->monthsThatArePaymentDatesRaw[$this->monthInWhichAutoCallLevelShouldBeHit/$this->timestampAdjustmentFactor - 1]
		
	}
	

	function getNumberOfCouponBarrierObservationDatesOnWhichCouponBarrierIsNotBreached() {
	
		return $this->numberOfCouponBarrierObservationDates - $this->numberOfTimesCouponBarrierShouldBeBreached; 
	
	}

	function getNumberOfCouponBarrierObservationDatesOnWhichCouponBarrierIsBreached() {
	
		return $this->numberOfTimesCouponBarrierShouldBeBreached; 
	
	}
	
	function getFinalCouponBarrierObservationDate() {
	
		return end(array_keys($this->monthsThatAreCouponBarrierObservationDates));
	
	}
	
	function setWhetherCouponBarrierShouldBeBreached($whetherCouponBarrierShouldBeBreached, $numberOfTimesCouponBarrierShouldBeBreached = 1) {
	
		$this->whetherCouponBarrierShouldBeBreached = $whetherCouponBarrierShouldBeBreached;
		
		switch($this->whetherCouponBarrierShouldBeBreached) {
		
			case COBAClass::COUPON_BARRIER_BREACH_MUST_OCCUR:

				$this->numberOfTimesCouponBarrierShouldBeBreached = $numberOfTimesCouponBarrierShouldBeBreached;
			
				$this->monthsInWhichCouponBarrierIsBreached = array();

				if($this->whereFinalLevelShouldBe == COBAClass::FINAL_LEVEL_MUST_BE_BELOW_KNOCKIN_BARRIER && $this->knockInBarrierLevel <= $this->couponBarrierLevel)
					$numberOfTimesCouponBarrierShouldBeBreached--; // This takes into account if the coupon barrier level is going to be breached on the final valuation date, anyway / this won't work if the coupon barrier level is greater than the knock in barrier level, but I haven't seen this in practice
				
				for($i=0; $i<$numberOfTimesCouponBarrierShouldBeBreached; $i++) {
				
					$safety = 1;
					while($safety++ < 1000) {
						
						// Ignore the final valuation date because that's going to be set by setWhereFinalLevelShouldBe()
						$randomObservationDate = $this->randomNumber->rand(1, $this->numberOfCouponBarrierObservationDates - 1) - 1; 
						
						if($this->monthsInWhichCouponBarrierIsBreached[array_keys($this->monthsThatAreCouponBarrierObservationDates)[$randomObservationDate]] == NULL) {
							$this->monthsInWhichCouponBarrierIsBreached[array_keys($this->monthsThatAreCouponBarrierObservationDates)[$randomObservationDate]] = 1;
							break;
						}
						if($safety == 999)
							trigger_error('setWhetherCouponBarrierShouldBeBreached(): unable to distribute coupon barrier breaches across coupon barrier xobservation dates', E_USER_ERROR);
					}
				}		
			break;

			case COBAClass::COUPON_BARRIER_BREACH_MUST_NOT_OCCUR:
			break;

			case COBAClass::COUPON_BARRIER_BREACH_IRRELEVANT:
			break;
			
			default:
				trigger_error('COBAClass: setWhetherCouponBarrierShouldBeBreached() - invalid specification of whether coupon barrier breach should occur', E_USER_ERROR);
			
		}
		

	}
	
	function setWhereFinalLevelShouldBe($whereFinalLevelShouldBe, $targetFinalLevel = 0) {
	
		$this->whereFinalLevelShouldBe = $whereFinalLevelShouldBe;
		
		switch($this->whereFinalLevelShouldBe) {
		
			case COBAClass::FINAL_LEVEL_MUST_BE_ABOVE_KNOCKIN_BARRIER:
			case COBAClass::FINAL_LEVEL_MUST_BE_BELOW_KNOCKIN_BARRIER:
			break;
			
			case COBAClass::FINAL_LEVEL_MUST_BE_ABOVE_SPECIFIC_VALUE:
			case COBAClass::FINAL_LEVEL_MUST_BE_BELOW_SPECIFIC_VALUE:
				$this->whereFinalLevelShouldBeTarget = $targetFinalLevel;
			break;
			
			default:
				trigger_error('COBAClass: setWhereFinalLevelShouldBe() - invalid specification of whether final level should be above or below knock-in barrier', E_USER_ERROR);
			
		}
	}
	
	function setColorMap($colorMap) {

		$this->colorMap = $colorMap;
	
	}
	
	function setTimeSeriesShape($timeSeriesShape, $baseLevel = 100) {
	
		$this->timeSeriesShape = $timeSeriesShape;
		
		switch($this->timeSeriesShape) {
		
			case COBAClass::TIME_SERIES_LOW_HIGH:
			case COBAClass::TIME_SERIES_HIGH_LOW:
			case COBAClass::TIME_SERIES_EVEN:
			break;
			
			default:
				trigger_error('COBAClass: setTimeSeriesShape() - invalid specification of time series shape', E_USER_ERROR);
		
		}
		
		$this->timeSeriesBaseLevel = $baseLevel;
		if(!is_numeric($this->timeSeriesBaseLevel))
			trigger_error('COBAClass: setTimeSeriesShape() - invalid specification of time series baase level', E_USER_ERROR);
	
	}
	
	function calculateTimeSeries($example, $underlying){
		
		if($this->PlotPointsFromZeroHasBeenSet){
			
			//NOTE: setPlotPointsFromZero function might have been called before all of the calculateTimeSeries were excuted the setPlotPointsFromZero function affects the calculations for calculateTimeSeries function and should only be called after all of the calculateTimeSeries has been run for all examples
			
			trigger_error('COBA: calculateTimeSeries initial validation failed',E_USER_ERROR); 
			
		}
			
			
		$this->timeSeriesProperties[$example]['Autocall'] = $this->whetherAutoCallLevelShouldBeHit;
	
		$this->timeSeries[$example][$underlying] = array();
	
		// Calculate duration of time series
		
		switch($this->whetherAutoCallLevelShouldBeHit) {
		
			case COBAClass::AUTOCALL_MUST_NOT_OCCUR:
			case COBAClass::AUTOCALL_IRRELEVANT:
				$numberOfTimestamps = round(($this->maturityDate - $this->issueDate) / 86400.0 / 30.5);
			break;

			case COBAClass::AUTOCALL_MUST_OCCUR:
				$numberOfTimestamps = $this->monthInWhichAutoCallLevelShouldBeHit;
			break;
		
		}
		
		mt_srand();
	
		$this->timeSeries[$example][$underlying][0] = 100;

		$previousCouponBarrierLevel = -1;
		for($this->timestamp=1; $this->timestamp<=$numberOfTimestamps; $this->timestamp++) {
		
			$previousLevel = $this->timeSeries[$example][$underlying][$this->timestamp-1];
		
			// Calculate random price movement
		
			switch($this->timeSeriesShape) {
			
				case COBAClass::TIME_SERIES_LOW_HIGH:
					if($this->timestamp < $numberOfTimestamps / 3)
						$level = $previousLevel + $this->randomNumber->rand(1, 16) - 11;
					else
						if($this->timestamp < $numberOfTimestamps / 3 * 2)
							$level = $previousLevel + $this->randomNumber->rand(1, 11) - 6;
						else
							$level = $previousLevel + $this->randomNumber->rand(1, 12) - 6;
									
				break;
				
				case COBAClass::TIME_SERIES_HIGH_LOW:
					if($this->timestamp < $numberOfTimestamps / 3)
						$level = $previousLevel + $this->randomNumber->rand(1, 16) - 6;
					else
						if($this->timestamp < $numberOfTimestamps / 3 * 2)
							$level = $previousLevel + $this->randomNumber->rand(1, 11) - 6;
						else
							$level = $previousLevel + $this->randomNumber->rand(1, 16) - 11;
				break;
				
				case COBAClass::TIME_SERIES_EVEN:
				default:
					$level = $previousLevel + $this->randomNumber->rand(1, 10) - 6;
				break;
			}
			
			// Adjust for autocall settings
			
			if($this->timestamp < $this->monthInWhichAutoCallLevelShouldBeHit - 2) {
			
				switch($this->whetherAutoCallLevelShouldBeHit) {
				
					case COBAClass::AUTOCALL_MUST_NOT_OCCUR:
					case COBAClass::AUTOCALL_MUST_OCCUR:
					
						if($this->monthsThatAreAutoCallObservationDates[$this->timestamp]) {
							$level = min($this->autoCallLevel - 10, $level) - $this->randomNumber->rand(1, 10) - 3;							
						}
							
					break;
					
				}
					
			}
			
			if($this->timestamp >= $this->monthInWhichAutoCallLevelShouldBeHit - 2)
				switch($this->whetherAutoCallLevelShouldBeHit) {
				
					case COBAClass::AUTOCALL_MUST_NOT_OCCUR:
					
						// If autocall date comes closer, gradually approach autocall level
						
						if($this->timestamp == $this->monthInWhichAutoCallLevelShouldBeHit - 2)
							$level = min($this->autoCallLevel + 20, $level) - $this->randomNumber->rand(1, 7) - 3;
						
						if($this->timestamp == $this->monthInWhichAutoCallLevelShouldBeHit - 1) {
							$level = min($this->autoCallLevel + 10, $level) - $this->randomNumber->rand(1, 7) - 3;
							
						}	
							
						// On autocall date, make sure you don't hit the autocall level
					
						if($this->monthsThatAreCouponBarrierObservationDates[$this->timestamp])
							$level = min($level, $this->autoCallLevel - 1);
							
					break;
					
					case COBAClass::AUTOCALL_MUST_OCCUR:

						// If autocall date comes closer, gradually approach autocall level
						
						if($this->timestamp == $this->monthInWhichAutoCallLevelShouldBeHit - 2)
							$level = max($this->autoCallLevel - 20, $level) + $this->randomNumber->rand(1, 12) - 2;
						
						if($this->timestamp == $this->monthInWhichAutoCallLevelShouldBeHit - 1)
							$level = max($this->autoCallLevel - 10, $level) + $this->randomNumber->rand(1, 7) - 3;
						
						// On autocall date, hit autocall level
						
						if($this->timestamp == $this->monthInWhichAutoCallLevelShouldBeHit) {
							$level = max($this->autoCallLevel + 1, $level) + $this->randomNumber->rand(1, 15);
						}
					break;
					
					case COBAClass::AUTOCALL_IRRELEVANT:
					break;

				}

			// Adjust for coupon barrier settings
			
			switch($this->whetherCouponBarrierShouldBeBreached) {

				case COBAClass::COUPON_BARRIER_BREACH_MUST_OCCUR:
				
					// If coupon barrier breach date comes closer, gradually approach the coupon barrier level - if no coupon barrier brech is supposed to occur on the upcoming observation date, do so from the opposite side
					
					if($this->monthsThatAreCouponBarrierObservationDates[$this->timestamp + 2]) 
						if($this->monthsInWhichCouponBarrierIsBreached[$this->timestamp + 2])
							$level = min($this->couponBarrierLevel + 10, $level);
						else
							$level = max($this->couponBarrierLevel - 10, $level);
				
					if($this->monthsThatAreCouponBarrierObservationDates[$this->timestamp + 1]) 
						if($this->monthsInWhichCouponBarrierIsBreached[$this->timestamp + 1])
							$level = min($this->couponBarrierLevel + 5, $level);
						else
							$level = max($this->couponBarrierLevel - 5, $level);
						
					// On coupon barrier breach date, hit coupon barrier level - if no coupon barrier brech is supposed to occur on the upcoming observation date, do the opposite
						
					if($this->monthsThatAreCouponBarrierObservationDates[$this->timestamp])
						if($this->monthsInWhichCouponBarrierIsBreached[$this->timestamp])
							$level = min($this->couponBarrierLevel - $this->randomNumber->rand(1, 7), $level);
						else
							$level = max($this->couponBarrierLevel + $this->randomNumber->rand(1, 7), $level);
						
					if($previousCouponBarrierLevel != -1 && $previousCouponBarrierLevel == $level)
						$level ++;
						
					$previousCouponBarrierLevel = $level;
				break;

				case COBAClass::COUPON_BARRIER_BREACH_MUST_NOT_OCCUR:
					if($this->monthsThatAreCouponBarrierObservationDates[$this->timestamp + 2]) 
						$level = max($this->couponBarrierLevel - 10, $level);

					if($this->monthsThatAreCouponBarrierObservationDates[$this->timestamp + 1]) 
						$level = max($this->couponBarrierLevel - 5, $level);
						
					if($this->monthsThatAreCouponBarrierObservationDates[$this->timestamp])
						$level = max($this->couponBarrierLevel + 3, $level);
					
					// NOTE:
					if($this->monthsThatAreCouponBarrierObservationDates[$this->timestamp])
							$level = max($this->couponBarrierLevel + $this->randomNumber->rand(1, 10), $level) +2;
					
					if($previousCouponBarrierLevel != -1 && $previousCouponBarrierLevel == $level)
						$level ++;
					

					$previousCouponBarrierLevel = $level;
				break;

				case COBAClass::COUPON_BARRIER_BREACH_IRRELEVANT:
				break;
			
			}

			// Adjust for final level settings
			
			if($this->timestamp >= $this->getFinalCouponBarrierObservationDate() - 2)
				if($this->whetherAutoCallLevelShouldBeHit == COBAClass::AUTOCALL_MUST_NOT_OCCUR || $this->whetherAutoCallLevelShouldBeHit == COBAClass::AUTOCALL_IRRELEVANT) { // Obviously, if the note is autocalled, all of this is irrelevant
				
				switch($this->whereFinalLevelShouldBe) {
				
					case COBAClass::FINAL_LEVEL_MUST_BE_ABOVE_SPECIFIC_VALUE:

						if($this->timestamp == $this->getFinalCouponBarrierObservationDate() - 2)
							$level = max($this->whereFinalLevelShouldBeTarget - 10, $level);
							
						if($this->timestamp == $this->getFinalCouponBarrierObservationDate() - 1)
							$level = max($this->whereFinalLevelShouldBeTarget - 5, $level);

						if($this->timestamp == $this->getFinalCouponBarrierObservationDate()) {
							
							// NOTE: Non callable Income Final level here
							//if($manager->getElement('Autocall applies'))
							if($this->whetherAutoCallLevelShouldBeHit == COBAClass::AUTOCALL_IRRELEVANT)
								$level = $this->whereFinalLevelShouldBeTarget;
							else 
								$level = max($this->whereFinalLevelShouldBeTarget + 3, $level);

						}
					
						//trigger_error(print_r($level, true), E_USER_ERROR);
					
					break;
					
					case COBAClass::FINAL_LEVEL_MUST_BE_BELOW_SPECIFIC_VALUE:

						if($this->timestamp == $this->getFinalCouponBarrierObservationDate() - 2)
							$level = min($this->whereFinalLevelShouldBeTarget - 15, $level);
							//$level = max($this->whereFinalLevelShouldBeTarget - 15, $level);
							
						if($this->timestamp == $this->getFinalCouponBarrierObservationDate() - 1)
							$level = min($this->whereFinalLevelShouldBeTarget - 10, $level);
							//$level = max($this->whereFinalLevelShouldBeTarget - 10, $level);

						if($this->timestamp == $this->getFinalCouponBarrierObservationDate())
							$level = min($this->whereFinalLevelShouldBeTarget - 7, $level);
							//$level = max($this->whereFinalLevelShouldBeTarget - 7, $level);
					break;
					
					case COBAClass::FINAL_LEVEL_MUST_BE_ABOVE_KNOCKIN_BARRIER:
					
						if($this->timestamp == $this->getFinalCouponBarrierObservationDate() - 2)
							$level = max($this->knockInBarrierLevel - 10, $level);
							
						if($this->timestamp == $this->getFinalCouponBarrierObservationDate() - 1) {
							$level = max($this->knockInBarrierLevel - 5, $level);
						
						}

						if($this->timestamp == $this->getFinalCouponBarrierObservationDate())
							$level = max($this->knockInBarrierLevel + 3, $level);
							
					break;
					
					case COBAClass::FINAL_LEVEL_MUST_BE_BELOW_KNOCKIN_BARRIER:

						if($this->timestamp == $this->getFinalCouponBarrierObservationDate() - 2)
							$level = min($this->knockInBarrierLevel - 12, $level);
							//$level = min($this->knockInBarrierLevel + 10, $level);
							
						if($this->timestamp == $this->getFinalCouponBarrierObservationDate() - 1)
							$level = min($this->knockInBarrierLevel - 17, $level);
							//$level = min($this->knockInBarrierLevel + 5, $level);

						if($this->timestamp == $this->getFinalCouponBarrierObservationDate()) {
							$level = $this->knockInBarrierLevel - 20;
							//$level = $this->knockInBarrierLevel - 3;
						}

					break;
				
				}
				
			}
			
			// Keep underlyings within a sensible range
			
			$level = min(200, max(30, $level)); // specific for ROC
			//$level = min(130, max(30, $level));
			
			// Override for first value
			
			if($this->timestamp == 1 && $this->timeSeriesBaseLevel != 100)
				$level = $this->timeSeriesBaseLevel;
				
			// need to fix
			if($example == 2){		
				if($this->timestamp == 66)
					$level = $level = min($this->autoCallLevel + 10, $level) - $this->randomNumber->rand(1, 7) - 3;
			}	
				
			$this->timeSeries[$example][$underlying][$this->timestamp] = $level;
	
		}
		
	}
	
	function getPeriodsInWhichACouponIsPaid($example, $underlying){
	
		$i = 0;
		$periods = array();
		
		foreach($this->timeSeries[$example][$underlying] as $level){
			
			
			if($i != 0 && $i % $this->timestampAdjustmentFactor == 0 && $level >= $this->couponBarrierLevel)
				$periods[] = $i / $this->timestampAdjustmentFactor;
				
			$i++;
		}
		
		if(count($periods > 0))
			return $periods;
		else
			return false;
	}
	
	function getPeriodsInWhichACouponIsNotPaid($example, $underlying){
	
		$i = 0;
		$periods = array();
		
		foreach($this->timeSeries[$example][$underlying] as $level){
			
			if($i != 0 && $i % $this->timestampAdjustmentFactor == 0 && $level < $this->couponBarrierLevel)
				$periods[] = $i / $this->timestampAdjustmentFactor;
				
			$i++;
		}
		
		if(count($periods > 0))
			return $periods;
		else
			return false;
	}
	
	
	function isThisAnAutoCallTimeSeries($example) {
	
		foreach($this->monthsThatAreObservationDates as $month => $dummy1) {
			$allUnderlyingsAboveAutoCallLevel = true;
			foreach($this->timeSeries[$example] as $underlying => $dummy2)
				if($this->timeSeries[$example][$underlying][$month] < $this->autoCallLevel) {
					$allUnderlyingsAboveAutoCallLevel = false;
					break;
				}
			if($allUnderlyingsAboveAutoCallLevel == true)
				return $month;
		}

		return false;
		
	}

	function getLowestFinalLevel($example) {

		$lowestFinalLevel = 100000;
		foreach($this->timeSeries[$example] as $underlying => $values)
			if(end($values) < $lowestFinalLevel)
				$lowestFinalLevel = end($values);
				
		return $lowestFinalLevel;
	}
	
	function getCouponPaymentMonthCorrespondingToCouponBarrierObservationMonth($month) {
	
		for($i=0; $i<count($this->monthsThatAreCouponBarrierObservationDates); $i++)
			if(array_keys($this->monthsThatAreCouponBarrierObservationDates)[$i] == $month)
				return (int)array_keys($this->monthsThatAreCouponPaymentDates)[$i];

		return false;
	
	}
	
	function getFinalObservationValue($example, $underlying) {
	
		return end($this->timeSeries[$example][$underlying]);
	
	}
	

	
	function drawchart($example){

		global $manager;
		
		$chart = wsd_chart_xy_line("", $this->xAxisString, $this->yAxisString, false);
		
		if($this->removeGraphOutline)
			$chart->setOutlineVisible(false);
			
		
		// In the 'Automatically Called Scenario', the Dates/ months on the X axis shouldnt show after the Call Date

		$chart->overrideTicks(true);
		
		if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_OCCUR) {
			
			$monthsThatAreCouponPaymentDatesBeforeAutocalled = array_keys($this->monthsThatAreCouponPaymentDates);
			
			$monthsThatAreCouponPaymentDatesBeforeAutocalled = array_slice($monthsThatAreCouponPaymentDatesBeforeAutocalled, 0, floor($this->monthInWhichAutoCallLevelShouldBeHit / $this->timestampAdjustmentFactor));
			
			//Ouput Clean Up - Remove observation dates markers if Monthly Observation dates with term greater than 3 years
			if(($manager->getTermInYears() > 3 && $this->timestampAdjustmentFactor == 1)){
				
				$chart->setOverrideTicks(range(0,floor($this->monthInWhichAutoCallLevelShouldBeHit / $this->timestampAdjustmentFactor),12));
				
			}else{
			
				$chart->setOverrideTicks($monthsThatAreCouponPaymentDatesBeforeAutocalled);
			
			}
			
			
			// $chart->setOverrideTicks($monthsThatAreCouponPaymentDatesBeforeAutocalled);
			
		}else{
			
			//Ouput Clean Up - Remove observation dates markers if Monthly Observation dates with term greater than 3 years
			if(($manager->getTermInYears() > 3 && $this->timestampAdjustmentFactor == 1)){
				
				$chart->setOverrideTicks(range(0,$manager->getTermInMonths(),12));
				
			}else{
			
				$chart->setOverrideTicks(array_keys($this->monthsThatAreCouponPaymentDates));
			
			}
			
		}
		
		$chart->setSplineRenderer(false); // this is what you call to make the graph smoooooooth
		$chart->setTitleAndFont("", "TimesRoman", 0, 20);

		switch(true) { // TEMPLATE TYPE

			case ($manager->getElement('Product family') == 'ROC family' || $manager->getElement('Product family') == 'Fixed ROC family')
				&& (($manager->getElement('Template') == 'Client brochure') || $manager->getElement('Template') == 'Green sheet'):
			
					$chart->setXAxisLabelFont("TimesRoman", 0, 17);
					$chart->setYAxisLabelFont("TimesRoman", 0, 20);
					$chart->setXAxisLegendFont("TimesRoman", 0, 25);
					$chart->setYAxisLegendFont("TimesRoman", 0, 25);
					
			break;
			
			default:
			
				$chart->setXAxisLabelFont("TimesRoman", 0, 15);
				$chart->setYAxisLabelFont("TimesRoman", 0, 15);
				$chart->setXAxisLegendFont("TimesRoman", 0, 20);
				$chart->setYAxisLegendFont("TimesRoman", 0, 20);
			
			break;
			
		}
		
		
		$min = min($this->couponBarrierLevel, $this->knockInBarrierLevel) - 25;
		$max = $this->autoCallLevel + 25;
		
		if($manager->getElement('Upside participation applies')){
			$max = $this->participationLevel + 25;
		}

		$count = 0;
		
		
		foreach($this->timeSeries[$example] as $underlying => $values) {

			foreach($values as $month => $level) {
				
				$chart->plot(ucfirst($underlying), (int)$month, (float)$level, $this->colorMap[$count]['r'], $this->colorMap[$count]['g'], $this->colorMap[$count]['b'], 2.5, 'solid');
				
				if($level > $max)
					$max = $level;
				if($level < $min)
					$min = $level;
			}
			
			$count++;
			
		}
		
	
		$min = floor(($min - 10) / 10) * 10;
		$max = max($this->yAxisRangeMaxValue, ceil(($max + 10) / 10) * 10);
		
		
		// Highlight periods in which no coupon is paid
		
		if($this->highlightPeriodsInWhichNoCouponIsPaid) {
		
			foreach($this->timeSeries[$example] as $underlying => $values) {
				foreach($values as $month => $level) {
					if($this->monthsThatAreCouponBarrierObservationDates[$month] && $level < $this->couponBarrierLevel) {
						$chart->addXYPolygonAnnotation(array($month, $max, $month, $min, $month - $this->couponBarrierObservationDatesInterval, $min, $month - $this->couponBarrierObservationDatesInterval, $max), 200, 250, 250, 247, 200, 200, 250, 247, 1, 'solid', true);

					}
				}
			}
		}
		
		
		// Highlight not-callable periods
		
		if($manager->getElement('Autocall applies')) {
		
			$autocalledObservationMonth = $month;
		
			if ($manager->getElement('Number of non call periods') > 0 && $this->highlightNonCallPeriods) {
		
				$i = 1;
				foreach($this->timeSeries[$example] as $underlying => $values) {
					foreach($values as $month => $level) {
						if($this->monthsThatAreCouponBarrierObservationDates[$month]) {
							
							if ($manager->getElement('Number of non call periods') < $i)
								break;

							$chart->addXYPolygonAnnotation(array($month, $max, $month, $min, $month - $this->couponBarrierObservationDatesInterval, $min, $month - $this->couponBarrierObservationDatesInterval, $max), 200, 250, 250, 247, 200, 250, 250, 247, 1, 'solid', true);
							
							$i++;
						}
					}
				}
			}
		}
		
		// Highlight periods after the underlying is Autocalled

		if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_OCCUR && $autocalledObservationMonth == $this->isThisAnAutoCallTimeSeries($example) && $this->highlightPeriodsAfterAutocall) {

			foreach($this->monthsThatAreObservationDates as $month => $uselessValue) {
			
				if ($month <= $autocalledObservationMonth)
					continue;

				$chart->addXYPolygonAnnotation(array($month, $max, $month, $min, $month - $this->couponBarrierObservationDatesInterval, $min, $month - $this->couponBarrierObservationDatesInterval, $max), 200, 250, 250, 247, 225, 225, 225, 247, 1, 'solid', true);
				
			}
		}
		

		
		$chart->setBackgroundRGB(255, 255, 255);
		$chart->setXAxisGridlineOff();
		//$chart->setXAxisGridline(1, "solid", 100, 100, 100);

		switch($this->showGraphOnlyUntilAutoCallOccurs) {
		
			case false:
				$numberOfTimeStamps = round(($this->maturityDate - $this->issueDate) / 86400.0 / 30.5);
			break;
			
			case true:
				$numberOfTimeStamps = $month;
			break;
			
		}
	
		// Set grey area for legends
		
		if($numberOfTimeStamps <= 24){
			
			$this->interval = 3;
			
			switch($manager->getElement('Product family')){
				
				case 'ROC family':
				case 'Fixed ROC family':
	
					$chart->setXAxisRange(0, $numberOfTimeStamps);
					//$chart->addXYPolygonAnnotation(array($numberOfTimeStamps, $max, $numberOfTimeStamps + 12, $max, $numberOfTimeStamps + 12, $min, $numberOfTimeStamps, $min), 225, 225, 225, 255, 255, 255, 255, 255, 1, 'solid', true);
				break;
				
				default:
				
					$chart->setXAxisRange(0, $numberOfTimeStamps + 12);
					$chart->addXYPolygonAnnotation(array($numberOfTimeStamps, $max, $numberOfTimeStamps + 12, $max, $numberOfTimeStamps + 12, $min, $numberOfTimeStamps, $min), 225, 225, 225, 255, 200, 200, 200, 255, 1, 'solid', true);
				break;
				
			}
			
			$pointerLength = 75;
			
		}else{
			
			$this->interval = 6;
			
			switch($manager->getElement('Product family')){
				
				case 'ROC family':
				case 'Fixed ROC family':
	
					$chart->setXAxisRange(0, $numberOfTimeStamps);
					//$chart->addXYPolygonAnnotation(array($numberOfTimeStamps, $max, $numberOfTimeStamps + 12, $max, $numberOfTimeStamps + 12, $min, $numberOfTimeStamps, $min), 225, 225, 225, 255, 255, 255, 255, 255, 1, 'solid', true);
				
				break;
				
				default:
				
					$chart->setXAxisRange(0, $numberOfTimeStamps + 24);
					$chart->addXYPolygonAnnotation(array($numberOfTimeStamps, $max, $numberOfTimeStamps + 24, $max, $numberOfTimeStamps + 24, $min, $numberOfTimeStamps, $min), 225, 225, 225, 255, 200, 200, 200, 255, 1, 'solid', true);
					
				break;
				
			}
			
			$pointerLength = 50;
			
		}

		$chart->setYAxisRange($min, $max);

		$chart->setXAxisInterval(1000);

		switch($manager->doesElementExist('Product sub-type') && !($manager->getElement('Product sub-type') == 'ROC' && $manager->getElement('Autocall applies'))) {
		
			case false:
				
				//Initial and/or Autocall level
				
				$chart->plot("Initial level", 0, $this->initialLevel, 255, 48, 48, 2.5, 'dashed');
				//$chart->plot("Initial level", $numberOfTimeStamps, 100, 255, 48, 48, 2.5, 'dashed');
				
				//HERE TO STOP THE RED LINE BEFORE ENTERING THE GREY BOX FOR THE AUTOCALLED SCENARIO FOR ROCs
				if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_NOT_OCCUR || $manager->getElement('Product family') != 'ROC family') {
					
					$chart->plot("Initial level", $numberOfTimeStamps, $this->initialLevel, 255, 48, 48, 2.5, 'dashed');
				
					//$chart->addPointerAnnotation($this->initialLevelTerminology . "\n" . ' (' . $this->initialLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, $this->initialLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength, 5.683, 'CENTER_LEFT', 5);
					
					if ($manager->getElement('Autocall applies') && $manager->getElement('Product family') != 'Fixed ROC family') {
						
						switch($manager->getElement('Product family')){
						
							// Solid black line for Autocall level 
							case 'ROC family':
							
								$chart->plot("Autocall level", 0, $this->autoCallLevel, 0, 0, 0, 2.5, 'solid');
								$chart->plot("Autocall level", $numberOfTimeStamps, $this->autoCallLevel, 0, 0, 0, 2.5, 'solid');
							
							break;
							
							default:
							
								$chart->plot("Autocall level", 0, $this->autoCallLevel, 255, 48, 48, 2.5, 'dashed');
								$chart->plot("Autocall level", $numberOfTimeStamps, $this->autoCallLevel, 255, 48, 48, 2.5, 'dashed');
								
							
									
									$pointerLength = 30;
									
									$chart->addPointerAnnotation($this->autoCallLevelTerminology . "\n" . ' (' . $this->autoCallLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 21, $numberOfTimeStamps + 0.5, $this->autoCallLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength, 5.683, 'CENTER_LEFT', 5);
									
									$pointerLength = 50;
									
							
							break;

						}
						
						//$chart->addPointerAnnotation($this->autoCallLevelTerminology . "\n" . ' (' . $this->autoCallLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, $this->autoCallLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength, 6.283, 'CENTER_LEFT', 5);
						
					}

				}	
				
			break;
			
			case true:
		
				// Barrier conditional ROC notes

				$chart->plot("Autocall level", 0, 100, 255, 48, 48, 2.5, 'dashed');
				$chart->plot("Autocall level", $numberOfTimeStamps, 100, 255, 48, 48, 2.5, 'dashed');
				
				$chart->addPointerAnnotation($manager->getElement('Initial underlying price terminology') . "\n" . '(100' . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, 100, 0, 0, 0, 0, 0, 0, 0, $pointerLength, 6.283, 'CENTER_LEFT', 5);
				
			break;
			
		}
		
		// Coupon barrier level
	
		switch($this->couponBarrierLevel == $this->knockInBarrierLevel) {
		
			case false:
						
						
				if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_NOT_OCCUR || $manager->getElement('Product family') != 'ROC family'){			
				// trigger_error('Try in 3 seconds', E_USER_ERROR);
					
					switch($manager->getElement('Product family')) {
					
						case 'Fixed ROC family':	
					
							$chart->plot("Knock-in barrier level", 0, $this->knockInBarrierLevel, 255, 48, 48, 2.5, 'solid');
							$chart->plot("Knock-in barrier level", $numberOfTimeStamps, $this->knockInBarrierLevel, 255, 48, 48, 2.5, 'solid');
						
						break;

						default:
					
							$chart->plot("Coupon barrier level", 0, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'dashed');
							$chart->plot("Coupon barrier level", $numberOfTimeStamps, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'dashed');
						
							$chart->plot("Knock-in barrier level", 0, $this->knockInBarrierLevel, 255, 48, 48, 2.5, 'dashed');
							$chart->plot("Knock-in barrier level", $numberOfTimeStamps, $this->knockInBarrierLevel, 255, 48, 48, 2.5, 'dashed');
							
						break;
					}
					
				}
				
				// trigger_error($pointerLength,E_USER_ERROR); //-TEH
				//NOTE: pointerLength is 30 for Main and 50 for Debug
				
				$pointerLength_knockInGradient = $pointerLength;
				$pointerLength_couponGradient = $pointerLength;
				
				if($this->couponBarrierLevel > $this->knockInBarrierLevel) {
					
					if($manager->getElement('Autocall applies'))
						$couponGradient = 1.9; //2;
					else
						$couponGradient = 1.9;
					
					$pointerLength_couponGradient -= 25;
					$pointerLength_knockInGradient += 25;
					$knockInGradient = 2.3;
					
				}else{

					$couponGradient = 2.4;
					
					if($manager->getElement('Autocall applies'))
						$knockInGradient = 1.9;
					else
						$knockInGradient = 1.9;
					
					$pointerLength_couponGradient += 22;
					$pointerLength_knockInGradient -= 25;
					
				}
				
				// CAN DELETE THIS SWITCH BUT PUT DONT FORGET THE DEFAULT CASE
				switch($manager->getElement('Product family')) {
					case 'ROC family':
					case 'Fixed ROC family':
				
						// if barrier level is greater than Payment threshold the $gradient value should be greater than Payment threshold too
				
						if($this->couponBarrierLevel > $this->knockInBarrierLevel) {
				
							$knockInBarrierGradient = 0.65;
							$couponBarrierGradient = 1.15;
						
						} else {
						
							$knockInBarrierGradient = 1.15;
							$couponBarrierGradient = 0.65;
						
						}
				
						// CAN DELETE
						// $numberOfTimeStamps + -0.1 play with the numbers to make the pointers point right at the end of red line
						
						/* if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_NOT_OCCUR || $manager->getElement('Product family') != 'ROC family'){
						
							/* // Payment Threshold
							$chart->addPointerAnnotation($this->couponBarrierLevelTerminology . ' (' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + -0.1, $this->couponBarrierLevel, 0, 0, 0, 0,  0, 0, 0, 70, $couponBarrierGradient * pi(), 'CENTER_RIGHT', 5);
							
							
							// Barrier Level
							$chart->addPointerAnnotation($this->knockInBarrierLevelTerminology . '(' . $this->knockInBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + -0.1, $this->knockInBarrierLevel, 0, 0, 0, 0, 0, 0, 0, 70, $knockInBarrierGradient * pi(), 'CENTER_RIGHT', 5);
							 */
							// CAN DELETE
							/* if ($manager->getElement('Autocall applies')){
							
								$chart->addPointerAnnotation($this->autoCallLevelTerminology . ' (' . $this->autoCallLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + -0.1, $this->autoCallLevel, 0, 0, 0, 0,  0, 0, 0, 80, 1.45 * pi(), 'CENTER_RIGHT', 5);
								
							} 
							
						} */
						
					break;
					
					
					default:
						
						// $chart->addPointerAnnotation($this->couponBarrierLevelTerminology . '(' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 22, $numberOfTimeStamps + 0.5, $this->couponBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength_couponGradient, $couponGradient * pi(), 'CENTER_LEFT', 5);
						
						// $chart->addPointerAnnotation($this->knockInBarrierLevelTerminology . '(' . $this->knockInBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, $this->knockInBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength_knockInGradient, $knockInGradient * pi(), 'CENTER_LEFT', 5);
						
						$pointerLength_couponGradient = 20;
						$pointerLength_knockInGradient = 40;
						
						$chart->addPointerAnnotation($this->couponBarrierLevelTerminology . '(' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 22, $numberOfTimeStamps + 0.5, $this->couponBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength_couponGradient, $couponGradient * pi(), 'CENTER_LEFT', 5);
						
						$chart->addPointerAnnotation($this->knockInBarrierLevelTerminology . '(' . $this->knockInBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, $this->knockInBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength_knockInGradient, $knockInGradient * pi(), 'CENTER_LEFT', 5);
					
					break;
					
				}
				
				/*
				if($this->couponBarrierLevel > $this->knockInBarrierLevel) {
			
					$chart->addPointerAnnotation($this->couponBarrierLevelTerminology . '(' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, $this->couponBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength, 1.8 * pi(), 'CENTER_LEFT', 5);

					$chart->addPointerAnnotation($this->knockInBarrierLevelTerminology . '(' . $this->knockInBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, $this->knockInBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength, 2.2 * pi(), 'CENTER_LEFT', 5);
					
				} else {

					$chart->addPointerAnnotation($this->couponBarrierLevelTerminology . '(' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, $this->couponBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength, 2.2 * pi(), 'CENTER_LEFT', 5);
					
					$chart->addPointerAnnotation($this->knockInBarrierLevelTerminology . '(' . $this->knockInBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + 0.5, $this->knockInBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength, 1.8 * pi(), 'CENTER_LEFT', 5);
				
				}
				*/
				
			break;
			
			case true:
			
				if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_NOT_OCCUR || $manager->getElement('Product family') != 'ROC family'){
			
					switch($manager->getElement('Product family')) {	
					
						case 'Fixed ROC family':
						case 'ROC family':
					
							$chart->plot("Barrier level", 0, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'solid');
							$chart->plot("Barrier level", $numberOfTimeStamps, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'solid');
							
						break;
						
						default:
						
							$chart->plot("Barrier level", 0, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'dashed');
							$chart->plot("Barrier level", $numberOfTimeStamps, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'dashed');
							
						
						break;
						
					}
					
				}
			
				// CAN DELETE THIS SWITCH BUT PUT DONT FORGET THE DEFAULT CASE
				switch($manager->getElement('Product family')) {
					case 'ROC family':
					case 'Fixed ROC family':
					
						$gradient = 0.65;
						
						// CAN DELETE
						/* if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_NOT_OCCUR){
						
							// For non Autocalled Scenario
						
							// Autocall Level Annotation 
							$chart->addPointerAnnotation($this->autoCallLevelTerminology . ' (' . $this->autoCallLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps + -0.1, $this->autoCallLevel, 0, 0, 0, 0,  0, 0, 0, 80, 1.45 * pi(), 'CENTER_RIGHT', 5);
					
							// Payment Threshold/ Barrier Level Annotation
							$chart->addPointerAnnotation($this->combinedCouponBarrierAndKnockInBarrierLevelTerminology . ' (' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 20, $numberOfTimeStamps - 0.1, $this->couponBarrierLevel, 0, 0, 0, 0, 0, 0, 0, 70, $gradient * pi(), 'CENTER_RIGHT', 5);
						
						} */ 

						/* if(!$manager->getElement('Autocall applies')){
				
							// Annotation for Autocalled ROC Scenario is done below
					
							$chart->addPointerAnnotation($this->combinedCouponBarrierAndKnockInBarrierLevelTerminology . ' (' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 20, $numberOfTimeStamps - 0.1, $this->couponBarrierLevel, 0, 0, 0, 0, 0, 0, 0, 70, $gradient * pi(), 'CENTER_RIGHT', 5);
							
						
						} */
											
					break;
					
					default:
				
						if ($manager->getElement('Autocall applies'))
							$gradient = 2.2;
						else
							$gradient = 2;
						
						
						
						
							$pointerLength = 30;
						
							$chart->addPointerAnnotation($this->combinedCouponBarrierAndKnockInBarrierLevelTerminology . ' (' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 21, $numberOfTimeStamps + 0.5, $this->couponBarrierLevel, 0, 0, 0, 0, 0, 0, 0, $pointerLength, $gradient * pi(), 'CENTER_LEFT', 5);
						
							$pointerLength = 50;
						
						
						//$chart->addPointerAnnotation(TEXT -> $this->combinedCouponBarrierAndKnockInBarrierLevelTerminology . ' (' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, FONT SIZE, X COORDINATE, Y COORDINATE, 0, 0, 0, 0, 0, 0, 0, $pointerLength, $gradient * pi(), 'CENTER_LEFT', 5);
					break;
				}
				
			break;
						
		}
		
		// FOR FIXED ROC FAMILY
		
		switch($manager->getElement('Upside participation applies')){
		
			case true:
			
				$chart->plot("Participation level", 0, $this->participationLevel, 0,0,0, 2.5, 'solid');
				$chart->plot("Participation level", $numberOfTimeStamps, $this->participationLevel, 0,0,0, 2.5, 'solid');
			
				$gradient = 2;
				
				// CAN DELETE
				/* $chart->addPointerAnnotation('Upside' . "\n" . 'Participation' . "\n" . 'level (' . $this->participationLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $numberOfTimeStamps - 0.1, $this->participationLevel, 0, 0, 0, 0, 0, 0, 0, 70, 1.30 * pi(), 'CENTER_RIGHT', 5); */
			
			break;
		
		}
			
		// Highlight autocall dates
		
		switch($this->highlightAllValuationDates){

			case false:
			
				// Highlight only those valuation dates where one underlying is below the coupon barrier level or all underlyings are above the autocall level

				foreach($this->monthsThatAreObservationDates as $month => $dummy1) {
				
					$allUnderlyingsAboveAutoCallLevel = true;
					
					foreach($this->timeSeries[$example] as $underlying => $dummy2)
						if($this->timeSeries[$example][$underlying][$month] < $this->autoCallLevel) {
							$allUnderlyingsAboveAutoCallLevel = false;
							break;
						}

					foreach($this->timeSeries[$example] as $underlying => $dummy2) {
					
						$scalingFactor = $numberOfTimeStamps / 40;

						$chart->addXMarker($month, 2.5, 'dashed', 150, 150, 150);

						if($this->timeSeries[$example][$underlying][$month] < $this->couponBarrierLevel || $this->timeSeries[$example][$underlying][$month] < $this->knockInBarrierLevel || $allUnderlyingsAboveAutoCallLevel) {
			
							$circle = $chart->createCircle($month - 0.375 * $scalingFactor, $this->timeSeries[$example][$underlying][$month] - 1.5, 0.75 * $scalingFactor, 3);

							if($allUnderlyingsAboveAutoCallLevel) {

								$chart->addXYShapeAnnotation($circle, 150, 200, 255, 255, 150, 200, 255, 255, 1, 0, 'solid');
							
							} else {
							
								$chart->addXYShapeAnnotation($circle, 255, 48, 48, 255, 255, 48, 48, 255, 1, 0, 'solid');
							
							}
						}
					}
				}

			break;
		
			case true:
				
				// Highlight all valuation dates

				foreach($this->monthsThatAreObservationDates as $month => $dummy1){

					foreach($this->timeSeries[$example] as $underlying => $dummy2){
					
						$scalingFactor = $numberOfTimeStamps / 40;
					
						// !!!
						
						switch($manager->getElement('Product family')){

							// stop the vertical line when the note is automatically called for ROC family
							
							case 'ROC family':
							
								if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_OCCUR){
									
									if($month < $this->monthInWhichAutoCallLevelShouldBeHit){
										
										$chart->addXMarker($month, 2.5, 'dashed', 150, 150, 150);
									
									}else{
										
										$chart->addXMarker($month, 2.5, 'dashed', 226, 226, 226);
											
									}
									
								}else{
									
									$chart->addXMarker($month, 2.5, 'dashed', 150, 150, 150);
								
								}
								
							break;
							
							case 'Income family':
								
								
								//Ouput Clean Up - Remove observation dates markers if Monthly Observation dates with term greater than 3 years
								if(($manager->getTermInYears() > 3 && $this->timestampAdjustmentFactor == 1)){
									
									
									$tempArray = range(0,$manager->getTermInMonths(),12);
									
									if(in_array($month,$tempArray)){
										
										$chart->addXMarker($month, 2.5, 'dashed', 150, 150, 150);
									
									}else{
										
										
										$chart->addXMarker($month, 0.5, 'dashed', 150, 150, 150);
										// Plot Red line for Initial level only until the closing level is not above the Autocall level
										// $chart->plot("Month Marker" . $month, 6, 50, 255, 48, 48, 2.5, 'dashed');
										// $chart->plot("Month Marker" . $month, 2, 100, 255, 48, 48, 2.5, 'dashed');
										
									}
										
									
								}else{
									
									$chart->addXMarker($month, 2.5, 'dashed', 150, 150, 150);
							
								}
								
							break;
							
							default:
								
								$chart->addXMarker($month, 2.5, 'dashed', 150, 150, 150);
							
							break;
							
						}
							
						switch($manager->getElement('Product family')){
							
							case 'Income family':
								
								//Ouput Clean Up - Remove observation dates markers if Monthly Observation dates with term greater than 3 years
								if(($manager->getTermInYears() > 3 && $this->timestampAdjustmentFactor == 1) == false){
								
									if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_OCCUR){
							
										if($month <= $this->monthInWhichAutoCallLevelShouldBeHit){
										
											$circle = $chart->createCircle($month - 0.375 * $scalingFactor, $this->timeSeries[$example][$underlying][$month] - 1.5, 0.75 * $scalingFactor, 3);
											$chart->addXYShapeAnnotation($circle, 255, 48, 48, 255, 255, 48, 48, 255, 1, 0, 'solid');
								
										}
										
									}else{
									
										$circle = $chart->createCircle($month - 0.375 * $scalingFactor, $this->timeSeries[$example][$underlying][$month] - 1.5, 0.75 * $scalingFactor, 3);
										$chart->addXYShapeAnnotation($circle, 255, 48, 48, 255, 255, 48, 48, 255, 1, 0, 'solid');
									
									}
									
								}
								
							
							break;
							
							
							default:
						
								$circle = $chart->createCircle($month - 0.375 * $scalingFactor, $this->timeSeries[$example][$underlying][$month] - 1.5, 0.75 * $scalingFactor, 3);
								$chart->addXYShapeAnnotation($circle, 255, 48, 48, 255, 255, 48, 48, 255, 1, 0, 'solid');
							
							break;
							
						}
						
					}
					
				}
				
			break;
		
		}

		reset($this->timeSeries[$example]);
		$dummy = current($this->timeSeries[$example]);
		end($dummy);
		$month = key($dummy);

		//trigger_error($example . ' / ' . $this->timeSeriesProperties[$example]['Autocall'] . ' / ' . $month . ' / ' . $this->isThisAnAutoCallTimeSeries($example), E_USER_ERROR);
		
		if($this->timeSeriesProperties[$example]['Autocall'] == COBAClass::AUTOCALL_MUST_OCCUR && $month == $this->isThisAnAutoCallTimeSeries($example)) {

			$lowestAutoCallLevel = 100000;
			foreach($this->timeSeries[$example] as $underlying => $values)
				if($values[$month] < $lowestAutoCallLevel)
					$lowestAutoCallLevel = $values[$month];

			switch($manager->getElement('Product family')) {
					
				case 'ROC family':
				
					// Plot Red line for Initial level only until the closing level is not above the Autocall level
					$chart->plot("Initial level", $month, $this->initialLevel, 255, 48, 48, 2.5, 'dashed');
					$chart->plot("Initial level", $month, $this->initialLevel, 255, 48, 48, 2.5, 'dashed');
					
					// Autocal call
					$chart->plot("Autocall level", 0, $this->autoCallLevel, 0,0,0, 2.5, 'solid');
					$chart->plot("Autocall level", $month, $this->autoCallLevel, 255, 48, 48, 2.5, 'solid');
					
					if($this->couponBarrierLevel == $this->knockInBarrierLevel) {
					
						// Combined Payment and Barrier
						
						$chart->plot("Barrier level", 0, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'solid');
						$chart->plot("Barrier level", $month, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'solid');
					
					} else {
					
						// Payment Threshold  
						$chart->plot("Coupon barrier level", 0, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'dashed');
						$chart->plot("Coupon barrier level", $month, $this->couponBarrierLevel, 255, 48, 48, 2.5, 'dashed');
					
						// Barrier level
						$chart->plot("Knock-in barrier level", 0, $this->knockInBarrierLevel, 255, 48, 48, 2.5, 'dashed');
						$chart->plot("Knock-in barrier level", $month, $this->knockInBarrierLevel, 255, 48, 48, 2.5, 'dashed');
						
						// CAN DELETE
						// Payment Threshold
						/* $chart->addPointerAnnotation($this->couponBarrierLevelTerminology . ' (' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $month, $this->couponBarrierLevel, 0, 0, 0, 0,  0, 0, 0, 70, $couponBarrierGradient * pi(), 'CENTER_RIGHT', 5);
						
						// Barrier Level
						$chart->addPointerAnnotation($this->knockInBarrierLevelTerminology . '(' . $this->knockInBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $month, $this->knockInBarrierLevel, 0, 0, 0, 0, 0, 0, 0, 150, $knockInBarrierGradient * pi(), 'CENTER_RIGHT', 5); */
					
					}
					
					if(stristr($this->template, 'French'))
						$automaticallyCalledTerminology = 'REMBOURSEMENT' . "\n" . 'PAR ANTICIPATION';
					else
						$automaticallyCalledTerminology = 'AUTOMATICALLY' . "\n" . 'CALLED';
					
					$chart->addPointerAnnotation($automaticallyCalledTerminology, 'TimesRoman', 1, 20, $numberOfTimeStamps - 0.4, $this->initialLevel - 10, 0, 0, 0, 226, 226, 226, 0, 80, 1.10 * pi(), 'CENTER_RIGHT', 5);
					
					// CAN DELETE
					/* 
					$chart->addPointerAnnotation($this->autoCalledTerminology, 'TimesRoman', 1, 23, $month + 0.5, $lowestAutoCallLevel, 0, 0, 0, 0, 0, 0, 0, 50, 1.40 * pi(), 'CENTER_RIGHT', 5);
				
					// Autocall Level Annotation 
					$chart->addPointerAnnotation($this->autoCallLevelTerminology . ' (' . $this->autoCallLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $month + 0.5, $this->autoCallLevel, 0, 0, 0, 0,  0, 0, 0, 80, 1.15 * pi(), 'CENTER_RIGHT', 5);
					 */
				
				break;
			
				default:
				
					if ($this->whenAutoCallLevelShouldBeHit == COBAClass::LATE_AUTOCALL)
						$chart->addPointerAnnotation($this->autoCalledTerminology, 'TimesRoman', 1, 23, $month + 0.5, $lowestAutoCallLevel, 0, 0, 0, 0, 0, 0, 0, 50, 3.4 * pi(), 'CENTER_RIGHT', 5);
					else
						$chart->addPointerAnnotation($this->autoCalledTerminology, 'TimesRoman', 1, 23, $month + 0.5, $lowestAutoCallLevel, 0, 0, 0, 0, 0, 0, 0, 50, 1.87 * pi(), 'CENTER_LEFT', 5);
						//$chart->addPointerAnnotation('Autocall' . "\n" . 'Redemption' . "\n" . 'at par', 'TimesRoman', 1, 23, $month + 0.5, $lowestAutoCallLevel, 0, 0, 0, 0, 0, 0, 0, 50, 1.87 * pi(), 'CENTER_LEFT', 5);
						
				break;
			}
			
		} else {
		
			$month = round(($this->maturityDate - $this->issueDate) / 86400.0 / 30.5);
			
			if($lowestFinalLevel = $this->getLowestFinalLevel($example)) {
								
				$gradient = 3.4;
			
				$adjust = 80;
				
				while ($lowestFinalLevel > $adjust){
				
					$gradient -= 0.1;
					$adjust +=15;
					
				}
				
				// CAN DELETE THIS SWITCH BUT PUT DONT FORGET THE DEFAULT CASE
				switch($manager->getElement('Product family')){
					
					case 'ROC family':
					case 'Fixed ROC family':
						
						// CAN DELETE
						/* $gradient -= 0.15;	
						
						$chart->addPointerAnnotation($this->finalLevelTerminology . "\n" . '(' . $lowestFinalLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $month - 0.5, $lowestFinalLevel, 0, 0, 0, 0, 0, 0, 0, 300 - ($lowestFinalLevel - 50), $gradient * pi(), 'CENTER_RIGHT', 5);
						
						//$chart->addPointerAnnotation(TEXT -> $this->combinedCouponBarrierAndKnockInBarrierLevelTerminology . ' (' . $this->couponBarrierLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, FONT SIZE, X COORDINATE, Y COORDINATE, 0, 0, 0, 0, 0, 0, 0, $pointerLength, $gradient * pi(), 'CENTER_LEFT', 5); */
					break;
					
					case 'Income family':
						
						if($this->PlotPointsFromZeroHasBeenSet){
							
							if($example == 0){
							
								$chart->addPointerAnnotation($this->finalLevelTerminology . "\n" . '(' . $lowestFinalLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $month - 0.5, $lowestFinalLevel, 0, 0, 0, 0, 0, 0, 0, 140, $gradient * pi(), 'CENTER_RIGHT', 5);
							
							}else{
							
								$chart->addPointerAnnotation($this->finalLevelTerminology . "\n" . '(' . $lowestFinalLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $month - 0.5, $lowestFinalLevel, 0, 0, 0, 0, 0, 0, 0, 60, $gradient * pi(), 'CENTER_RIGHT', 5);
							
							}
							
						}else{
						
							$chart->addPointerAnnotation($this->finalLevelTerminology . "\n" . '(' . $lowestFinalLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $month - 0.5, $lowestFinalLevel, 0, 0, 0, 0, 0, 0, 0, 160 - ($lowestFinalLevel - 50), $gradient * pi(), 'CENTER_RIGHT', 5);
							
						
						}
					
					break;
					
					default:
						
						$chart->addPointerAnnotation($this->finalLevelTerminology . "\n" . '(' . $lowestFinalLevel . (stristr($this->template, 'French') ? ' ' : '') . '%)', 'TimesRoman', 1, 23, $month - 0.5, $lowestFinalLevel, 0, 0, 0, 0, 0, 0, 0, 160 - ($lowestFinalLevel - 50), $gradient * pi(), 'CENTER_RIGHT', 5);
					break;
				
				}
				
			}
			
			/* // Differ between Final Level ending below or above the initial level
			if(($lowestFinalLevel = $this->getLowestFinalLevel($example)) < $this->knockInBarrierLevel) {
				
				$chart->addPointerAnnotation('Payment at Maturity' . "\n" . 'at Reduced' . "\n" . 'Principal', 'TimesRoman', 1, 23, $month - 0.5, $lowestFinalLevel, 0, 0, 0, 0, 0, 0, 0, 250 - ($lowestFinalLevel - 50), 3.4 * pi(), 'CENTER_RIGHT', 5);
			
			} else {
			
				$chart->addPointerAnnotation('Payment at Maturity' . "\n" . 'at Par', 'TimesRoman', 1, 23, $month - 0.5, $lowestFinalLevel, 0, 0, 0, 0, 0, 0, 0, 250 - ($lowestFinalLevel - 50), 3.4 * pi(), 'CENTER_RIGHT', 5);
				
			}
			*/
		}
		
		$chart->setYAxisInterval(20);
		$chart->setXAxisFormat("#");
		$chart->setYAxisFormat("#");
		$chart->setAxisOffsets(0.0, 0.0, 0.0, 0.0);
		
		
		switch($manager->getElement('Template')) {

			case 'Green sheet':
			
				case 'Fixed ROC family':
				case 'ROC family':
					echo wsd_chart_draw($chart, 28 * $this->graphXAxisWidthMuliplier, 28 * $this->graphYAxisWidthMuliplier, 1100, 500);
				break;
				
				case 'Income family':
					echo wsd_chart_draw($chart, 30 * $this->graphXAxisWidthMuliplier, 30 * $this->graphYAxisWidthMuliplier, 1100, 500);
				break;
				
				default:
					echo wsd_chart_draw($chart, 30 * $this->graphXAxisWidthMuliplier, 30 * $this->graphYAxisWidthMuliplier, 1100, 500);
				break;
				
			break;
			
			case 'Client brochure':
				
				switch($manager->getElement('Product family')) {
				
					case 'Fixed ROC family':
					case 'ROC family':
						echo wsd_chart_draw($chart, 28 * $this->graphXAxisWidthMuliplier, 28 * $this->graphYAxisWidthMuliplier, 1100, 500);
					break;                          
					
					case 'Income family':           
						echo wsd_chart_draw($chart, 35 * $this->graphXAxisWidthMuliplier, 35 * $this->graphYAxisWidthMuliplier, 1000, 500);
					break;                           
					
					default:                         
						echo wsd_chart_draw($chart, 35 * $this->graphXAxisWidthMuliplier, 35 * $this->graphYAxisWidthMuliplier, 1100, 500);
					break;
				
				}
			break;
			
			case 'Pricing supplement':
			
				switch($manager->getElement('Product family')) {
				
					case 'ROC family':
					case 'Fixed ROC family':
						echo wsd_chart_draw($chart, 40 * $this->graphXAxisWidthMuliplier, 40 * $this->graphYAxisWidthMuliplier, 1100, 500);
					break;                           
					
					case 'Income family':            
						echo wsd_chart_draw($chart, 50 * $this->graphXAxisWidthMuliplier, 50 * $this->graphYAxisWidthMuliplier, 1100, 500);
					break;                            
					
					default:                          
						echo wsd_chart_draw($chart, 50 * $this->graphXAxisWidthMuliplier, 50 * $this->graphYAxisWidthMuliplier, 1100, 500);
					break;
				}
			break;
			
		}
		
	}
	
}

?>