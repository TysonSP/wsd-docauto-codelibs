<?php

class holidayCheckerClass {

	var $issueDate;
	var $maturityDate;
	var $jurisdictions = array();
	var $holidays = array();
	
	function __construct($issueDate = NULL, $maturityDate = NULL, $jurisdictions = NULL) {
	
		$this->issueDate = $issueDate !== NULL ? $issueDate : round(time() / 86400.0) * 86400;
		$this->maturityDate = $maturityDate !== NULL ? $maturityDate : $this->issueDate + 86400 * 365 * 10;
		
		if($jurisdictions != NULL){
			
			if(!is_array($jurisdictions)){
				
				trigger_error('Jurisdictions must be provided as array', E_USER_ERROR);
			}
			
			$this->jurisdictions = $jurisdictions;
		
		}else{
			
			$this->jurisdictions = array('US');
		
		}
		
		for($i=date('Y', $this->issueDate); $i <= date('Y', $this->maturityDate); $i++) {
		
			if($i < 2009)
				trigger_error('Holiday checker: invalid year - issue date and maturity date must not fall before 2009', E_USER_ERROR);
		
			$this->holidays[$i] = array();
			
			foreach($this->jurisdictions as $jurisdiction)
		
				switch($jurisdiction) {
				
					case 'US':
						$dummy = new USFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;

					case 'EU':
						$dummy = new EUFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;

					case 'HK':
						$dummy = new HKFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;
						
					case 'CA':
						$dummy = new CAFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;
						
					case 'CH':
						$dummy = new SZFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;
						
					case 'DK':
						$dummy = new DKKFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;
						
					case 'GB':
						$dummy = new GBFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;
						
					case 'JP':
						$dummy = new JPNFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;
						
					case 'NO':
						$dummy = new NOKFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;
						
					case 'SE':
						$dummy = new SEKFederalHolidaysClass((int)$i);
						$this->holidays[$i] = array_merge($this->holidays[$i], $dummy->getHolidayList($i));
						break;
						
					default:
						trigger_error('Holiday checker: unsupported jurisdiction', E_USER_ERROR);
						
				}
			
		}
		
	}

	function isHoliday($date) {
	
		$date = round($date / 86400.0) * 86400;

		foreach($this->holidays as $holiday) {
			foreach($holiday as $item)
				if(round($item / 86400.0) * 86400 == round($date / 86400.0) * 86400)
					return true;
		}
		
		return false;
	
	}
	
	function isWeekendOrHoliday($date) {

		$date = round($date / 86400.0) * 86400;

		if(date('D', $date) == 'Sat' || date('D', $date) == 'Sun' || $this->isHoliday($date))
			return true;
	
		return false;
	
	}

	function adjustForWeekends($date, $backward = false) {
	
		$date = round($date / 86400.0) * 86400;
	
		while(date('D', $date) == 'Sat' || date('D', $date) == 'Sun')
			if(!$backward)
				$date += 86400;
			else
				$date -= 86400;
	
		return $date;
	
	}
	
	function adjustForWeekendsAndHolidays($date, $backward = false) {
	
		$date = round($date / 86400.0) * 86400;
	
		while(date('D', $date) == 'Sat' || date('D', $date) == 'Sun' || $this->isHoliday($date))
			if(!$backward)
				$date += 86400;
			else
				$date -= 86400;
	
		return $date;
	
	}
	
	function moveDate($date, $numberOfBusinessDays, $considerWeekendsOnly = false) {
	
		$date = round($date / 86400.0) * 86400;
	
		while(date('D', $date) == 'Sat' || date('D', $date) == 'Sun' || (!$considerWeekendsOnly && $this->isHoliday($date)))
			if($numberOfBusinessDays >= 0)
				$date += 86400;
			else
				$date -= 86400;
	
		for($i=0; $i<abs($numberOfBusinessDays); $i++) {

			if($numberOfBusinessDays > 0)
				$date += 86400;
			if($numberOfBusinessDays < 0)
				$date -= 86400;
		
			while(date('D', $date) == 'Sat' || date('D', $date) == 'Sun' || (!$considerWeekendsOnly && $this->isHoliday($date)))
				if($numberOfBusinessDays >= 0)
					$date += 86400;
				else
					$date -= 86400;
		
		}
	
		return $date;
	
	}
	
	function getNumberOfBusinessDaysBetweenTwoDates($date1, $date2) {
	
		$date1 = round($date1 / 86400.0) * 86400;
		$date2 = round($date2 / 86400.0) * 86400;
		
		$businessDaysCount = 0;
		
		if($date1 < $date2) {
			$firstDate = $date1;
			$secondDate = $date2;
		} else {
			$firstDate = $date2;
			$secondDate = $date1;
		}
		
		while($firstDate < $secondDate) {
		
			$firstDate += 86400;
			
			if(date('D', $firstDate) != 'Sat' && date('D', $firstDate) != 'Sun' && !$this->isHoliday($firstDate))
				$businessDaysCount++;
	
		}
	
		return $businessDaysCount;
	
	}
	
	function getListHolidaysAndWeekendsBetweenTwoDates($date1, $date2, $dateFormatted = false){
	
		$date1 = round($date1 / 86400.0) * 86400;
		$date2 = round($date2 / 86400.0) * 86400;
		
		$HolidaysFoundArray;
		
		if($date1 < $date2){
			
			$firstDate = $date1;
			$secondDate = $date2;
		
		}else{
			
			$firstDate = $date2;
			$secondDate = $date1;
		
		}
		
		$safetyBreak = 0;
		
		while($firstDate <= $secondDate){
			
			
			if($safetyBreak == 1000 ){
			
				trigger_error('Holiday Checker Class: getListHolidaysAndWeekendsBetweenTwoDates : Saftey break : invalid dates',E_USER_ERROR); //-TEH
			
			}
			
			if(date('D', $firstDate) == 'Sat'){
				
				if($dateFormatted){

					$HolidaysFoundArray[] = date("F j Y", $firstDate) . ' is a Saturday';
			
				}else{
					
					$HolidaysFoundArray[] = $firstDate;
					
				}
				
			}
			
			if(date('D', $firstDate) == 'Sun'){
				
				if($dateFormatted){

					$HolidaysFoundArray[] = date("F j Y", $firstDate) . ' is a Sunday';
			
				}else{
					
					$HolidaysFoundArray[] = $firstDate;
					
				}
				
			}
			
			if($this->isHoliday($firstDate)){
				
				if($dateFormatted){
					
					$temp_string = date("F j Y", $firstDate) . ' is a ';
					
					if(count($this->jurisdictions) == 1){
						
						foreach($this->jurisdictions as $jurisdiction){
						
							$temp_string .= $jurisdiction;
						
						}
						
					}
					
					$temp_string .= ' Holiday';
					
					$HolidaysFoundArray[] = $temp_string;
					
					
			
				}else{
					
					$HolidaysFoundArray[] = $firstDate;
					
				}
				
			}
			
			$firstDate += 86400;
			$safetyBreak++;
			
		}
		
		if(count($HolidaysFoundArray) == 0){
			
			return 'No Holidays or Weekends Found';
			
		}else{
			
			return $HolidaysFoundArray;
			
		}
		
	}
	
	
	
	
}

class USFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);
			
    }
  
		private function getHolidays($year) {
		
			switch($year) {
			
				case 2009:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 19, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 16, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 8, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 3, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 7, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 1, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 26, 2009) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2009) / 86400.0) * 86400
					);

				case 2010:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 18, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 15, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 14, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 31, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 5, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 6, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 11, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 7, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 25, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2010) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2010) / 86400.0) * 86400
					);

				case 2011:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 17, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 13, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 30, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 5, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 10, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 6, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 24, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2011) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2011) / 86400.0) * 86400
					);

				case 2012:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 16, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 20, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 11, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 28, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 3, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 8, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 4, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 12, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 22, 2012) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2012) / 86400.0) * 86400
					);
					
				case 2013:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 21, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 18, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 27, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 2, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 28, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2013) / 86400.0) * 86400
					);
					
				case 2014:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 20, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 17, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 26, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 13, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 27, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2014) / 86400.0) * 86400
					);
				
				case 2015:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 19, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 16, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 7, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 26, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400
					);

				case 2016:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 18, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 15, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 30, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 5, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 10, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 24, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400
					);

				case 2017:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 16, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 20, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 14, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 29, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 4, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 9, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 10, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400
					);

				case 2018:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 15, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 19, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 28, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 3, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 8, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 12, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 22, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400
					);
					
				case 2019:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 21, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 18, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 27, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 2, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 28, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400
					);

				case 2020:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 20, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 17, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 3, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 7, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 25, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400
					);
					
				case 2021:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 18, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 15, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 31, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 5, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 6, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 11, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 25, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2021) / 86400.0) * 86400
					);

				case 2022:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 17, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 21, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 15, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 30, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 5, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 10, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 24, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2022) / 86400.0) * 86400
					);

				case 2023:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 2, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 16, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 20, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 7, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 29, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 4, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 9, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 10, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2023) / 86400.0) * 86400
					);

				case 2024:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 15, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 19, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 27, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 2, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 28, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2024) / 86400.0) * 86400
					);

				case 2025:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 20, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 17, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 26, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 1, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 13, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 27, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2025) / 86400.0) * 86400
					);
					
				case 2026:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 19, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 16, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 3, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 7, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 26, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2026) / 86400.0) * 86400
					);

				case 2027:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 18, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 15, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 26, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 31, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 5, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 6, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 11, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 25, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2027) / 86400.0) * 86400
					);
				case 2028:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 17, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 21, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 29, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 4, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 9, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2028) / 86400.0) * 86400,
					);
				case 2029:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 15, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 19, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 28, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 3, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 8, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 12, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 22, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2029) / 86400.0) * 86400,
					);
				case 2030:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 21, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 18, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 27, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 4, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 2, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 28, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2030) / 86400.0) * 86400,
					);
			}
		
		}
  
    public function getHolidayList() {
      return $this->dateList;
    }
		
    public function get_list() {
        return $this->dateList;
    }

}
	
class EUFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
			
				case 2013:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2013) / 86400.0) * 86400
					);
					
				case 2014:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 21, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2014) / 86400.0) * 86400
					);
				
				case 2015:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2015) / 86400.0) * 86400
					);

				case 2016:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 28, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400
					);

				case 2017:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 14, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 17, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2017) / 86400.0) * 86400
					);

				case 2018:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2018) / 86400.0) * 86400
					);
					
				case 2019:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2019) / 86400.0) * 86400
					);

				case 2020:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 13, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2020) / 86400.0) * 86400
					);
					
				case 2021:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 5, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2021) / 86400.0) * 86400
					);

				case 2022:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 15, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2022) / 86400.0) * 86400
					);

				case 2023:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 7, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2023) / 86400.0) * 86400
					);

				case 2024:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 1, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2024) / 86400.0) * 86400
					);

				case 2025:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 21, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2025) / 86400.0) * 86400
					);
					
				case 2026:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2028) / 86400.0) * 86400,
					);

				case 2027:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2027) / 86400.0) * 86400,
					);
				case 2028:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 17, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2028) / 86400.0) * 86400,
					);
				case 2029:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2029) / 86400.0) * 86400,
					);
				case 2030:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2030) / 86400.0) * 86400,
					);
						
						
			}
		
		}
  
    public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }

}

class HKFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
			
				case 2013:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 10, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 11, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 12, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 13, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 4, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 12, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 20, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 1, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2013) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2013) / 86400.0) * 86400
					);
					
				case 2014:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 31, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 3, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 21, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 6, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 2, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 9, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 1, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 2, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2014) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2014) / 86400.0) * 86400
					);
				
				case 2015:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 19, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 20, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 21, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 7, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 28, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2015) / 86400.0) * 86400
					);

				case 2016:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 8, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 9, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 10, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 28, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 4, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 2, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 9, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 16, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 9, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400
					);
				case 2017:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 2, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 14, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 17, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2017) / 86400.0) * 86400,
					);
				case 2018:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2018) / 86400.0) * 86400,
					);
				case 2019:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2019) / 86400.0) * 86400,
					);
				case 2020:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 27, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 28, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 13, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400,
					);
				case 2021:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 5, 2021) / 86400.0) * 86400,
					);
				case 2022:
					return array (
					round(wsd_mktime(0, 0, 0, 4, 15, 2022) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 4, 18, 2022) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 12, 26, 2022) / 86400.0) * 86400,
					);
				case 2023:
					return array (
						round(wsd_mktime(0, 0, 0, 4, 7, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2023) / 86400.0) * 86400,
					);
				case 2024:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 1, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2024) / 86400.0) * 86400,
					);
				case 2025:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 21, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2025) / 86400.0) * 86400,
					);
				case 2026:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2026) / 86400.0) * 86400,
					);
				case 2027:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 26, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2027) / 86400.0) * 86400,
					);
				case 2028:
					return array (
						round(wsd_mktime(0, 0, 0, 4, 14, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 17, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2028) / 86400.0) * 86400,
					);
				case 2029:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2029) / 86400.0) * 86400,
					);
				case 2030:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2030) / 86400.0) * 86400,
					);
			}
		
		}
  
    public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }

}
class SZFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
				
				case 2015:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 14, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400,
					);
				case 2016:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 28, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 16, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400,
					);
				case 2017:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 2, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 17, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 5, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 1, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2017) / 86400.0) * 86400,
					)
				case 2018:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 10, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2018) / 86400.0) * 86400,
					);
				case 2019:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 30, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 10, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2019) / 86400.0) * 86400,
					);
				case 2020:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 13, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400,
					);
			}
		}
	public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }
}
class DKKFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
				
				case 2015:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 5, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 14, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 5, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2015) / 86400.0) * 86400,
					);
				case 2016:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 24, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 27, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 28, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 16, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 5, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2016) / 86400.0) * 86400,
					);
				case 2017:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 13, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 14, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 16, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 17, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 12, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 5, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2017) / 86400.0) * 86400,
					);
				case 2018:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 27, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 10, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 5, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2018) / 86400.0) * 86400,
					);
				case 2019:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 21, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 17, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 30, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 5, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 10, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2019) / 86400.0) * 86400,
					);
				case 2020:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 9, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 12, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 13, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 8, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 5, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2020) / 86400.0) * 86400,
					);
			}
		}
	public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }
}
class CAFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
				
				case 2014:
					return array (
						round(wsd_mktime(0, 0, 0, 9, 1, 2014) / 86400.0) * 86400
					);
				break;
				
				case 2015:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 16, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 18, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 7, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 28, 2015) / 86400.0) * 86400,
					);
				case 2016:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 15, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 23, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 5, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 10, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 27, 2016) / 86400.0) * 86400,
					);
				case 2017:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 2, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 20, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 14, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 22, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 3, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 7, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 4, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 9, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 13, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2017) / 86400.0) * 86400,
					);
				case 2018:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 19, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 6, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 3, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 8, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 12, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2018) / 86400.0) * 86400,
					);
				case 2019:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 18, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 20, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 5, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 2, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2019) / 86400.0) * 86400,
					);
				case 2020:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 17, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 18, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 3, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 7, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 28, 2020) / 86400.0) * 86400,
					);
				case 2021:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 15, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 24, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 2, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 6, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 11, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2021) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 27, 2021) / 86400.0) * 86400,
					);
				case 2022:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 3, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 21, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 15, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 23, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 1, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 5, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 10, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 23, 2022) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2022) / 86400.0) * 86400,
					);
				case 2023:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 2, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 20, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 7, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 22, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 3, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 7, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 4, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 9, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 10, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2023) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2023) / 86400.0) * 86400,
					);
				case 2024:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 19, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 20, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 5, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 2, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2024) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2024) / 86400.0) * 86400,
					);
				case 2025:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 17, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 19, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 4, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 1, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 13, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2025) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2025) / 86400.0) * 86400,
					);
				case 2026:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 16, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 18, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 3, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 7, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2026) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2026) / 86400.0) * 86400,
					);
				case 2027:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 15, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 26, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 24, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 2, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 6, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 11, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2027) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 27, 2027) / 86400.0) * 86400,
					);
				case 2028:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 3, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 21, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 14, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 22, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 3, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 7, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 4, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 9, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 10, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2028) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2028) / 86400.0) * 86400,
					);
				case 2029:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 19, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 2, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 6, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 3, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 8, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 12, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2029) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2029) / 86400.0) * 86400,
					);
				case 2030:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 18, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 20, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 1, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 5, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 2, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 11, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2030) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2030) / 86400.0) * 86400,
					);	
				break;
				
				default:
					trigger_error('CAFederalHolidaysClass: unsupported year ' . $year, E_USER_ERROR);
			
			}
			
		}
		
	public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }
}
class GBFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
				
				case 2015:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 4, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 31, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 28, 2015) / 86400.0) * 86400,
					);
				case 2016:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 28, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 2, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 30, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 29, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 27, 2016) / 86400.0) * 86400,
					);
				case 2017:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 2, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 14, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 17, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 29, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 28, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2017) / 86400.0) * 86400,
					);
				case 2018:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 7, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 28, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 27, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2018) / 86400.0) * 86400,
					);
				case 2019:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 6, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 27, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 26, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2019) / 86400.0) * 86400,
					);
				case 2020:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 13, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 4, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 31, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 28, 2020) / 86400.0) * 86400,
					);
			}
		}
	public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }
}

class JPNFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
				
				case 2015:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 12, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 11, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 21, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 29, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 4, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 20, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 21, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 22, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 23, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 23, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2015) / 86400.0) * 86400,
					);
				case 2016:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 3, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 11, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 11, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 21, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 29, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 3, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 4, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 18, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 19, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 22, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 10, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 3, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 23, 2016) / 86400.0) * 86400,
					);
				case 2017:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 3, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 9, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 11, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 20, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 29, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 3, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 4, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 17, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 11, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 18, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 23, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 9, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 3, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 23, 2017) / 86400.0) * 86400,
					);
				case 2018:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 3, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 8, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 12, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 21, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 3, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 4, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 16, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 11, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 17, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 24, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 8, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 3, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2018) / 86400.0) * 86400,
					);
				case 2019:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 3, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 14, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 11, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 21, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 29, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 3, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 4, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 6, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 15, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 12, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 16, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 23, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 14, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 4, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 23, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2019) / 86400.0) * 86400,
					);
				case 2020:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 2, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 3, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 13, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 2, 11, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 20, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 29, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 3, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 4, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 7, 20, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 11, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 9, 21, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 8, 22, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 10, 12, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 3, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 11, 23, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 23, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2020) / 86400.0) * 86400,
					);
						
			}
		}
	public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }
}
class NOKFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
				
				case 2015:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 14, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400,
					);
				case 2016:
					return array(
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 24, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 28, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 16, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 17, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400,
					);
				case 2017:
					return array (
					round(wsd_mktime(0, 0, 0, 4, 13, 2017) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 4, 14, 2017) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 4, 17, 2017) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 5, 1, 2017) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 5, 17, 2017) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 5, 25, 2017) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 6, 5, 2017) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400,
					round(wsd_mktime(0, 0, 0, 12, 26, 2017) / 86400.0) * 86400,
					);
				case 2018:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 29, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 10, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 17, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2018) / 86400.0) * 86400,
					);
				case 2019:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 18, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 17, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 30, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 10, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2019) / 86400.0) * 86400,
					);
				case 2020:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 9, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 13, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400,
					);
			}
		}
	public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }
}

class SEKFederalHolidaysClass {

    var $dateList;
		
    function __construct($year) {

			$this->dateList = $this->getHolidays($year);

	  }
  
		private function getHolidays($year) {
		
			switch($year) {
				
				case 2015:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 3, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 6, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 14, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 19, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2015) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2015) / 86400.0) * 86400,
					);
				case 2016:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 6, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 25, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 28, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 5, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 6, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 24, 2016) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2016) / 86400.0) * 86400,
					);
				case 2017:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 6, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 14, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 17, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 6, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 23, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2017) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2017) / 86400.0) * 86400,
					);
				case 2018:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 3, 30, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 2, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 10, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 6, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 22, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2018) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2018) / 86400.0) * 86400,
					);
				case 2019:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 19, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 22, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 30, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 6, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 21, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 26, 2019) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2019) / 86400.0) * 86400,
					);
				case 2020:
					return array (
						round(wsd_mktime(0, 0, 0, 1, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 1, 6, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 10, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 4, 13, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 1, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 5, 21, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 6, 26, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 24, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 25, 2020) / 86400.0) * 86400,
						round(wsd_mktime(0, 0, 0, 12, 31, 2020) / 86400.0) * 86400,
					);
			}
		}
	public function getHolidayList() {
      return $this->dateList;
    }

    public function get_list() {
			return $this->dateList;
    }
}


function isLeapYear($year) {

	if($year % 4 == 0)
		if($year % 100 == 0)
			if($year % 400 == 0)
				$isLeapYear = true;
			else
				$isLeapYear = false;
		else
			$isLeapYear = true;
	else
		$isLeapYear = false;

	return $isLeapYear;	
	
}

?>