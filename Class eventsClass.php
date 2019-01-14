<?php

class eventsClass {

  var $baseDate = array();
  var $eventDates = array();
	var $sortOrderForEventDates = constantsClass::SORT_CALENDAR_YEAR;
	var $pushLeapYearDatesToMarch1 = true;
	var $eventDateIsLastBusinessDayOfMonth = false;
	var $superScriptOrdinals = false;
	var $language = constantsClass::ENGLISH;
	
  function __construct($issueDate, $maturityDate, $interval, $stub = constantsClass::NONE, $sortOrderForEventDates = -1, $eventDateCalendarDay = -1) {

		if($issueDate == NULL)
			trigger_error('Events class: no issue date specified ' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 500), E_USER_ERROR);
		else
			$issueDate = round($issueDate / 86400.0) * 86400;

		if($maturityDate == NULL)
			trigger_error('Events class: no maturity date specified', E_USER_ERROR);
		else
			$maturityDate = round($maturityDate / 86400.0) * 86400;
			
		if($interval == NULL)
			trigger_error('Events class: no interval specified', E_USER_ERROR);

		if($stub == NULL)
			trigger_error('Events class: no stub period specified', E_USER_ERROR);

    $this->issueDate = $issueDate;
    $this->maturityDate = $maturityDate;
		
    switch($interval) {
       case 'Bi-weekly':
        $this->interval = constantsClass::BIWEEKLY;
        $this->language = constantsClass::ENGLISH;
        break;
      
	  case 'Monthly':
        $this->interval = constantsClass::MONTHLY;
        $this->language = constantsClass::ENGLISH;
        break;
        
      case 'Monatlich':
        $this->interval = constantsClass::MONTHLY;
        $this->language = constantsClass::GERMAN;
        break;
      
      case 'Quarterly':
        $this->interval = constantsClass::QUARTERLY;
        $this->language = constantsClass::ENGLISH;
        break;
        
      case 'Vierteljaehrlich':
        $this->interval = constantsClass::QUARTERLY;
        $this->language = constantsClass::GERMAN;
        break;

      case 'Semi-annually':
      case 'Semi-annual':
        $this->interval = constantsClass::SEMIANNUALLY;
        $this->language = constantsClass::ENGLISH;
        break;
        
      case 'Halbjaehrlich':
        $this->interval = constantsClass::SEMIANNUALLY;
        $this->language = constantsClass::GERMAN;
        break;

      case 'Annually':
      case 'Annual':
        $this->interval = constantsClass::ANNUALLY;
        $this->language = constantsClass::ENGLISH;
        break;
        
      case 'Jaehrlich':
        $this->interval = constantsClass::ANNUALLY;
        $this->language = constantsClass::GERMAN;
        break;
        
      default:
        trigger_error('Invalid interval specified for event dates', E_USER_ERROR);
    }

    switch($stub) {
    
      case 'N/A':
      case 'None':
      case 'Keiner':
        $this->stub = constantsClass::NONE;
        break;
        
      case 'Short first coupon':
      case 'Kurzer erster Kupon':
        $this->stub = constantsClass::SHORT_FIRST_COUPON;
        break;
      
      case 'Long first coupon':
      case 'Langer erster Kupon':
        $this->stub = constantsClass::LONG_FIRST_COUPON;
        break;

      case 'Short last coupon':
      case 'Kurzer letzter Kupon':
        $this->stub = constantsClass::SHORT_LAST_COUPON;
        break;
        
      case 'Long last coupon':
      case 'Langer letzter Kupon':
		
        $this->stub = constantsClass::LONG_LAST_COUPON;
        break;
        
      default:
        trigger_error('Invalid special coupon for event dates', E_USER_ERROR);
    }
      
		// Make assumptions about base date based on data provided
			
    if($this->stub == constantsClass::NONE || $this->stub == constantsClass::SHORT_FIRST_COUPON || $this->stub == constantsClass::LONG_FIRST_COUPON) {
      $this->baseDate['month'] = $this->__getMonth($this->maturityDate);
      $this->baseDate['day'] = $this->__getDay($this->maturityDate);
    }

    if($this->stub == constantsClass::SHORT_LAST_COUPON || $this->stub == constantsClass::LONG_LAST_COUPON) {
      $this->baseDate['month'] = $this->__getMonth($this->issueDate);
      $this->baseDate['day'] = $this->__getDay($this->issueDate);
    }

	// Make assumptions about whether event date is last business day of month based on data provided
	
    if(($this->baseDate['day'] == 31) ||
       ($this->baseDate['day'] == 30 && ($this->baseDate['month'] == 4 || $this->baseDate['month'] == 6 || $this->baseDate['month'] == 9 || $this->baseDate['month'] == 11)) ||
       (($this->baseDate['day'] == 29 || $this->baseDate['day'] == 28) && $this->baseDate['month'] == 2))
			$this->eventDateIsLastBusinessDayOfMonth = true;

		if($eventDateCalendarDay != -1)
			$this->setEventDateCalendarDay($eventDateCalendarDay);
    
		if($sortOrderForEventDates != -1)
			$this->sortOrderForEventDates = $sortOrderForEventDates;

    $this->__calculateEventDates();
	$this->__sortEventDates();		
    $this->__calculateFirstEventDate();
    $this->__calculateLastEventDate();

  }
  
	function setLanguage($language) {
	
		switch($language) {
		
			case constantsClass::ENGLISH:
			case constantsClass::GERMAN:
			case constantsClass::FRENCH:
				$this->language = $language;
			break;
			
			default:
				trigger_error('Events class: invalid language specified ' . "\n\nStack trace (truncated): \n" . substr(print_r(debug_backtrace(), true), 0, 500), E_USER_ERROR);
		
		}
	
	}
	
	function setEventDateCalendarDay($day, $index = 1) {
	
		// Let user decide whether event date is last business day of month - override only if the day is specified to be 31st of month
		if($index == 1){
			if($day == 'Last calendar day of the month' || $day == 'Last business day of the month') {
				$this->eventDateIsLastBusinessDayOfMonth = true;
				switch($this->baseDate['month']) {
				
					case 1:
					case 3:
					case 5:
					case 7:
					case 8:
					case 10:
					case 12:
						$this->baseDate['day'] = 31;
						break;
						
					case 2:
						$this->baseDate['day'] = 28;
						break;
						
					case 4:
					case 6:
					case 9:
					case 11:
						$this->baseDate['day'] = 30;
						break;
				
				}
			} else {
			
				if(!is_numeric($day))
					trigger_error('eventsClass: Invalid base date passed to setEventDateCalendarDay()', E_USER_ERROR);
			
				$this->baseDate['day'] = $day;
				if($this->baseDate['day'] == 31)
					$this->eventDateIsLastBusinessDayOfMonth = true;
				else
					$this->eventDateIsLastBusinessDayOfMonth = false;
			}
		}
		
		if($index == 2){

			if($day == 'Last calendar day of the month' || $day == 'Last business day of the month') {
				$this->secondEventDateIsLastBusinessDayOfMonth = true;
				switch($this->baseDate['month']) {
				
					case 1:
					case 3:
					case 5:
					case 7:
					case 8:
					case 10:
					case 12:
						$this->baseDate['day 2'] = 31;
						break;
						
					case 2:
						$this->baseDate['day 2'] = 28;
						break;
						
					case 4:
					case 6:
					case 9:
					case 11:
						$this->baseDate['day 2'] = 30;
						break;
				
				}
			} else {
			
				if(!is_numeric($day))
					trigger_error('eventsClass: Invalid base date passed to setEventDateCalendarDay()', E_USER_ERROR);
			
				$this->baseDate['day 2'] = $day;
				if($this->baseDate['day 2'] == 31)
					$this->secondEventDateIsLastBusinessDayOfMonth = true;
				else
					$this->secondEventDateIsLastBusinessDayOfMonth = false;
			}
		}
	}
	
	function calculateEventDates() {
	
		$this->eventDates = array();

		$this->__calculateEventDates();
		$this->__sortEventDates();
		$this->__calculateFirstEventDate();
		$this->__calculateLastEventDate();
	}

	function recalculateEventDates() {
	
		$this->calculateEventDates();

	}
	
	function __sortEventDates() {
	
		switch($this->sortOrderForEventDates) {
		
			case constantsClass::SORT_CALENDAR_YEAR:
			default:
		
				$earliestEventDate = -1;
				$earliestEventDateIndex = -1;
				$i=0;
				foreach($this->eventDates as $eventDate) {
					if($earliestEventDate == -1 || $eventDate['month'] < $earliestEventDate) {
						$earliestEventDate = $eventDate['month'];
						$earliestEventDateIndex = $i;
					}
					$i++;
				}

				for($j=0; $j<$i; $j++)
					$newEventDates[$j] = $this->eventDates[($earliestEventDateIndex + $j) % $i];
				$this->eventDates = $newEventDates;
				break;
		
			case constantsClass::SORT_ISSUE_DATE:

				$differenceToIssueDate = -1;
				$earliestEventDateIndex = -1;
				$i=0;
				foreach($this->eventDates as $eventDate) {
					if(($differenceToIssueDate == -1 || $eventDate['month'] - wsd_date_format($this->issueDate, 'M') < $differenceToIssueDate) && $eventDate['month'] - wsd_date_format($this->issueDate, 'M') >= 0) {
						$differenceToIssueDate = $eventDate['month'] - wsd_date_format($this->issueDate, 'M');
						$earliestEventDateIndex = $i;
					}
					$i++;
				}
				for($j=0; $j<$i; $j++)
					$newEventDates[$j] = $this->eventDates[($earliestEventDateIndex + $j) % $i];
					
				$this->eventDates = $newEventDates;
				break;

			default:
				trigger_error('Events class: no sort order set for event dates', E_USER_ERROR);
		
		}

	}
	
	function __getDay($date) {
    return wsd_date_format($date, 'd');
  }
  
  function __getMonth($date) {
    return wsd_date_format($date, 'M');
  }
  
  function __incrementDate($date, $incrementInMonths) {

    $currentMonth = $date['month'];
    $currentMonth += $incrementInMonths;
    $currentMonth = ($currentMonth - 1) % 12 + 1;
    
    if($this->eventDateIsLastBusinessDayOfMonth) {
      switch($currentMonth) {
        case '1':
        case '3':
        case '5':
        case '7':
        case '8':
        case '10':
        case '12':
          $currentDay = 31;
          break;
          
        case '4':
        case '6':
        case '9':
        case '11':
          $currentDay = 30;
          break;
          
        case '2':
					if($this->pushLeapYearDatesToMarch1) {
						$currentMonth ++;
						$currentDay = 1;
					} else
						$currentDay = 28;
          break;
      }
    } else
      $currentDay = $this->baseDate['day'];
      
    if($this->secondEventDateIsLastBusinessDayOfMonth) {
      switch($currentMonth) {
        case '1':
        case '3':
        case '5':
        case '7':
        case '8':
        case '10':
        case '12':
          $secondDay = 31;
          break;
          
        case '4':
        case '6':
        case '9':
        case '11':
          $secondDay = 30;
          break;
          
        case '2':
					if($this->pushLeapYearDatesToMarch1) {
						$currentMonth ++;
						$secondDay = 1;
					} else
						$secondDay = 28;
          break;
      }
    } else
      $secondDay = $this->baseDate['day 2'];
      
    $currentMonth = ($currentMonth - 1) % 12 + 1;

    $dateArray = array('month' => $currentMonth, 'day' => $currentDay, 'day 2' => $secondDay);
				
		if($dateArray['month'] == 2 && $dateArray['day'] >= 29) {
			if($this->pushLeapYearDatesToMarch1) {
				$dateArray['month'] = 3;
				$dateArray['day'] = 1;
			} else
				$dateArray['day'] = 28;					
		}
		
		if($dateArray['month'] == 2 && $dateArray['day 2'] >= 29) {
			
				$dateArray['day 2'] = 28;					
		}

    return $dateArray;
  }
  
  function __calculateEventDates() {
  
		$this->eventDates = array();
	
    switch($this->interval) {
    
	  case constantsClass::BIWEEKLY:
      case constantsClass::MONTHLY:
        for($i=0; $i<12; $i++) {
          $this->eventDates[$i] = $this->__incrementDate($this->baseDate, $i);
        }
        break;
        
      case constantsClass::QUARTERLY:
        for($i=0; $i<4; $i++) {
          $this->eventDates[$i] = $this->__incrementDate($this->baseDate, 3 * $i);  
        }
        break;

      case constantsClass::SEMIANNUALLY:
			
        for($i=0; $i<2; $i++) {
          $this->eventDates[$i] = $this->__incrementDate($this->baseDate, 6 * $i);
        }
        break;

      case constantsClass::ANNUALLY:
        $this->eventDates[0] = $this->baseDate;
        break;

    }

  }

	function sortEventDates() {
	
		uasort($this->eventDates, array($this, '__compareEventDates'));
	
	}
	
	function __compareEventDates($date1, $date2) {
	
		if($date1['month'] == $date2['month'] && $date1['day'] == $date2['day'])
			return 0;
			
		if($date1['month'] < $date2['month'])
			return -1;
		else
			if($date1['month'] > $date2['month'])
				return 1;
			else
				if($date1['day'] < $date2['day'])
					return -1;
				else
					if($date1['day'] > $date2['day'])
						return 1;
		
	}
	
	
  function __isEventDate($date) {
  
    foreach($this->eventDates as $key => $value) {
    
      if($this->eventDates[$key]['month'] == $this->__getMonth($date) && $this->eventDates[$key]['day'] == $this->__getDay($date))
        return true;
    }
  
    return false;

  }
  
  function isEventDate($date) {
  
    foreach($this->eventDates as $key => $value) {
    
		if($this->eventDates[$key]['month'] == date('n', $date) && $this->eventDates[$key]['day'] == date('j', $date) || $this->eventDates[$key]['month'] == date('n', $date) && $this->eventDates[$key]['day 2'] == date('j', $date))
			return true;
    
    }
  
    return false;

  }
		
  function __calculateFirstEventDate() {
    
    $currentDate = $this->issueDate + 86400 * 3;
    $flag = 0;
    $safety = 1;
    while($safety++) {

      if($safety == 1000)
        trigger_error('Internal error calculating first event date (issue date: ' . date('F j, Y', $this->issueDate) . ', maturity date: ' . date('F j, Y', $this->maturityDate) . ' / ' . print_r($this->eventDates, true) . ')', E_USER_ERROR);
        
      $currentDate += 86400;
      
      if($this->__isEventDate($currentDate) == true)
        if($flag == 0 && $this->stub == constantsClass::LONG_FIRST_COUPON)
          $flag = 1;
        else
          break;
    }
    $this->firstEventDate = $currentDate;
    
  }
  
  function __calculateLastEventDate() {
    
    $currentDate = $this->maturityDate;
    $flag = 0;
    $safety = 1;
    while($safety++) {
    
      if($safety == 1000)
        trigger_error('Internal error calculating last event date (issue date: ' . date('F j, Y', $this->issueDate) . ', maturity date: ' . date('F j, Y', $this->maturityDate . ')'), E_USER_ERROR);
          
      if($this->__isEventDate($currentDate) == true)
        if($flag == 0 && $this->stub == constantsClass::LONG_LAST_COUPON)
          $flag = 1;
        else
          break;
					
      $currentDate -= 86400;
    }
    $this->lastEventDate = $currentDate;
    
  }

  function createListOfEventDates($cutOffDate = null) {
    
		if(is_null($cutOffDate))
			$cutOffDate = $this->maturityDate;
		
		$eventDates[0] = $this->firstEventDate;

		$currentDate = $this->firstEventDate;
		$safety = 1;
		while($safety++) {
		
			if($safety == 10000)
				trigger_error('Internal error calculating event dates (issue date: ' . date('F j, Y', $this->issueDate) . ', maturity date: ' . date('F j, Y', $this->maturityDate) . ')', E_USER_ERROR);
				
			if($currentDate >= $cutOffDate || $currentDate > $this->lastEventDate)
				break;
			
			$currentDate += 86400;
			
			if($this->isEventDate($currentDate) == true)
				$eventDates[count($eventDates)] = $currentDate;
		
		}

		return $eventDates;
    
  }

  function createListOfEventDatesStartingWithX($startDate, $cutOffDate = null) {
    
		if(is_null($cutOffDate))
			$cutOffDate = $this->maturityDate;
		
		$eventDates[0] = $startDate;

		$currentDate = $eventDates[0];
		$safety = 1;
		while($safety++) {
		
			if($safety == 10000)
				trigger_error('Internal error calculating event dates (issue date: ' . date('F j, Y', $this->issueDate) . ', maturity date: ' . date('F j, Y', $this->maturityDate) . ')', E_USER_ERROR);
				
			if($currentDate >= $cutOffDate || $currentDate > $this->lastEventDate)
				break;
			
			$currentDate += 86400;
			
			if($this->isEventDate($currentDate) == true)
				$eventDates[count($eventDates)] = $currentDate;
		
		}

		return $eventDates;
    
  }
	
	function countEventDates() {
	
		return count($this->createListOfEventDates());
		
	}
  
	function getNextEventDate($startDate) {
	
		$currentDate = round($startDate / 86400.0) * 86400;
		$safety = 1;
		while($safety++) {
		
			if($safety == 10000)
				trigger_error('Internal error determining next event date (issue date: ' . date('F j, Y', $this->issueDate) . ', maturity date: ' . date('F j, Y', $this->maturityDate) . ')', E_USER_ERROR);
				
			if($currentDate >= $this->maturityDate)
				$this->maturityDate;
			
			$currentDate += 86400;
			
			if($this->isEventDate($currentDate) == true)
				return $currentDate;
		
		}
		
		return false;
	
	}
	
	function adjustBusinessDays($date, $numberOfBusinessDays, $roll = 'forward') {
	
		if($numberOfBusinessDays == 0) {
			switch($roll) {
			
				case 'forward':
					while(date('D', $date) == 'Sat' || date('D', $date) == 'Sun')					
						$date += 86400;
					break;
					
				case 'backward':
					while(date('D', $date) == 'Sat' || date('D', $date) == 'Sun')					
						$date -= 86400;
					break;
					
				default:
					trigger_error('eventsClass: Invalid roll option');
			}
		} else {

			for($i=0; $i<abs($numberOfBusinessDays); $i++) {
			
				if($numberOfBusinessDays < 0) {
					$date -= 86400;
					while(date('D', $date) == 'Sat' || date('D', $date) == 'Sun')					
						$date -= 86400;
				}
				if($numberOfBusinessDays > 0) {
					$date += 86400;
					while(date('D', $date) == 'Sat' || date('D', $date) == 'Sun')					
						$date += 86400;
				}
			
			}

		}
		
		return $date;
	
	}
		
  function __getDate($date) {
  
		$string = '';
		
    switch($this->language) {
    
      case constantsClass::GERMAN:
        $string .= wsd_decimal_format($date['day'], '00') . '.' . wsd_decimal_format($date['month'], '00') . '.';
        break;
        
      case constantsClass::ENGLISH:
        $string .= $this->__getMonthEnglish($date['month']) . ' ' . wsd_decimal_format($date['day'], '0');
        break;
				
      case constantsClass::FRENCH:
        $string .= wsd_decimal_format($date['day'], '0') . ' ' . $this->__getMonthFrench($date['month']);
        break;

    }
				
		return $string;
  }
  
  function __getOrdinalDate($date) {
  
		$string = '';
		
    switch($this->language) {
    
      case constantsClass::GERMAN:
        $string .= wsd_decimal_format($date['day'], '00') . '.' . wsd_decimal_format($date['month'], '00') . '.';
        break;
        
      case constantsClass::ENGLISH:
        $string .= $this->__getMonthEnglish($date['month']) . ' ' . $this->__getOrdinal(wsd_decimal_format($date['day'], '0'));
        break;
				
      case constantsClass::FRENCH:
        $string .= wsd_decimal_format($date['day'], '0') . ' ' . $this->__getMonthFrench($date['month']);
        break;

    }
				
		return $string;
  }

	function __getOrdinal($number) {
		
		$string = wsd_decimal_format($number, '0');
		
    switch($this->language) {
    
      case constantsClass::ENGLISH:
				switch($string % 10) {
					case 1:
						if($string == 11) {
							if($this->superScriptOrdinals)
								$string .= wsd_raw_rtf('\super '). 'th' . wsd_raw_rtf(' \nosupersub');
							else
								$string .= 'th';				
						} else {
							if($this->superScriptOrdinals)
								$string .= wsd_raw_rtf('\super '). 'st' . wsd_raw_rtf(' \nosupersub');
							else
								$string .= 'st';
						}
						break;
						
					case 2:
						if($string == 12) {
							if($this->superScriptOrdinals)
								$string .= wsd_raw_rtf('\super '). 'th' . wsd_raw_rtf(' \nosupersub');
							else
								$string .= 'th';				
						} else {
							if($this->superScriptOrdinals)
								$string .= wsd_raw_rtf('\super '). 'nd' . wsd_raw_rtf(' \nosupersub');
							else
								$string .= 'nd';
						}
						break;
						
					case 3:
						if($string == 13) {
							if($this->superScriptOrdinals)
								$string .= wsd_raw_rtf('\super '). 'th' . wsd_raw_rtf(' \nosupersub');
							else
								$string .= 'th';				
						} else {
							if($this->superScriptOrdinals)
								$string .= wsd_raw_rtf('\super '). 'rd' . wsd_raw_rtf(' \nosupersub');
							else
								$string .= 'rd';
						}
						break;
						
					default:
						if($this->superScriptOrdinals)
							$string .= wsd_raw_rtf('\super '). 'th' . wsd_raw_rtf(' \nosupersub');
						else
							$string .= 'th';
				}
			break;
			
      case constantsClass::FRENCH:
				if($string == '1')
					$string .= wsd_raw_rtf('\super ') . 'er' . wsd_raw_rtf(' \nosupersub');
				else
					$string .= wsd_raw_rtf('\super ') . 'e' . wsd_raw_rtf(' \nosupersub');
			break;
			
		}
		
		return $string;

	}

	function __isBaseDateLastDayOfMonth() {
		if($this->eventDateIsLastBusinessDayOfMonth)
			return true;
		else
			return false;
	}
	
	function __getMonthEnglish($month) {
		switch($month) {
			case 1:
				$string = 'January';
				break;
				
			case 2:
				$string = 'February';
				break;
				
			case 3:
				$string = 'March';
				break;
				
			case 4:
				$string = 'April';
				break;
				
			case 5:
				$string = 'May';
				break;
				
			case 6:
				$string = 'June';
				break;
				
			case 7:
				$string = 'July';
				break;
				
			case 8:
				$string = 'August';
				break;
				
			case 9:
				$string = 'September';
				break;
				
			case 10:
				$string = 'October';
				break;
				
			case 11:
				$string = 'November';
				break;
				
			case 12:
				$string = 'December';
				break;
		}
		
		return $string;
	}
	
	function __getMonthFrench($month) {
		switch($month) {
			case 1:
				$string = 'de janvier';
				break;
				
			case 2:
				$string = 'de février';
				break;
				
			case 3:
				$string = 'de mars';
				break;
				
			case 4:
				$string = 'd\'avril';
				break;
				
			case 5:
				$string = 'de mai';
				break;
				
			case 6:
				$string = 'de juin';
				break;
				
			case 7:
				$string = 'de juillet';
				break;
				
			case 8:
				$string = 'd\'août';
				break;
				
			case 9:
				$string = 'de septembre';
				break;
				
			case 10:
				$string = 'd\'octobre';
				break;
				
			case 11:
				$string = 'de novembre';
				break;
				
			case 12:
				$string = 'de décembre';
				break;
		}
		
		return $string;
	}
	
  function getEventDates($forceOrdinals = false, $ifOrdinalsThenNameLastDayOfMonthSpecifically = false, $dateListAsOrdinals = false, $barclaysSpecificLanguage = false) {
  
		$string = '';
		
    switch($this->language) {
    
      case constantsClass::GERMAN:
        switch($this->interval) {
          case constantsClass::ANNUALLY:
            $string .= $this->__getDate($this->eventDates[0], $this->language);
            break;

          case constantsClass::SEMIANNUALLY:
            $string .= $this->__getDate($this->eventDates[0], $this->language) . ' und ' . $this->__getDate($this->eventDates[1], $this->language);
            break;

          case constantsClass::QUARTERLY:
						$string .= $this->__getDate($this->eventDates[0], $this->language) . ', ' . $this->__getDate($this->eventDates[1], $this->language) . ', ' . $this->__getDate($this->eventDates[2], $this->language) . ' und ' . $this->__getDate($this->eventDates[3], $this->language);
            break;
            
          case constantsClass::MONTHLY:
						$isLastDayOfMonth = false;
						foreach($this->eventDates as $eventDate)
							if(wsd_decimal_format($eventDate['day'], '0') == 31)
								$isLastDayOfMonth = true;
						if($isLastDayOfMonth)
							$string .= 'der letzte Tag jedes Monats';
						else
							$string .= 'der ' . wsd_decimal_format($this->eventDates[0]['day'], '0') . 'te Tag jedes Monats';
            break;
        }

        if($this->interval != constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 1 || $this->interval == constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 2)
           $string .= " eines jeden Jahres";
        break;
        
      case constantsClass::ENGLISH:
        switch($this->interval) {
          case constantsClass::ANNUALLY:
						if(!$forceOrdinals)
							if($dateListAsOrdinals)
								$string .= $this->__getOrdinalDate($this->eventDates[0], $this->language);
							else
								$string .= $this->__getDate($this->eventDates[0], $this->language);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									$string .= 'the last day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
								else
									if($barclaysSpecificLanguage)
										$string .= 'the ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
									else
										$string .= 'the ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
							else
								if($barclaysSpecificLanguage)
									$string .= 'the ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
								else
									$string .= 'the ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
            break;

          case constantsClass::SEMIANNUALLY:
						if(!$forceOrdinals)
							if($dateListAsOrdinals)
								$string .= $this->__getOrdinalDate($this->eventDates[0], $this->language) . ' and ' . $this->__getOrdinalDate($this->eventDates[1], $this->language);
							else
								$string .= $this->__getDate($this->eventDates[0], $this->language) . ' and ' . $this->__getDate($this->eventDates[1], $this->language);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									$string .= 'the last day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
								else
									$string .= 'the ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and the ' . $this->__getOrdinal($this->eventDates[1]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
							else
								$string .= 'the ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
            break;

          case constantsClass::QUARTERLY:
						if(!$forceOrdinals)
							if($dateListAsOrdinals)
								$string .= $this->__getOrdinalDate($this->eventDates[0], $this->language) . ', ' . $this->__getOrdinalDate($this->eventDates[1], $this->language) . ', ' . $this->__getOrdinalDate($this->eventDates[2], $this->language) . ' and ' . $this->__getOrdinalDate($this->eventDates[3], $this->language);
							else
								$string .= $this->__getDate($this->eventDates[0], $this->language) . ', ' . $this->__getDate($this->eventDates[1], $this->language) . ', ' . $this->__getDate($this->eventDates[2], $this->language) . ' and ' . $this->__getDate($this->eventDates[3], $this->language);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									$string .= 'the last day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[3]['month']);
								else
									$string .= 'the ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', the ' . $this->__getOrdinal($this->eventDates[1]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', the ' . $this->__getOrdinal($this->eventDates[2]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and the ' . $this->__getOrdinal($this->eventDates[3]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[3]['month']);
							else
								$string .= 'the ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[3]['month']);

            break;
            
          case constantsClass::MONTHLY:
						if($this->__isBaseDateLastDayOfMonth())
							$string .= 'the last';
						else
							$string .= 'the ' . $this->__getOrdinal($this->baseDate['day']);
						$string .= ' day of each month';
            break;
        }

        if($this->interval != constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 1 || $this->interval == constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 2)
					;
//           $string .= ' of each year';
        break;
				
      case constantsClass::FRENCH:
        switch($this->interval) {
          case constantsClass::ANNUALLY:
						if(!$forceOrdinals)
							if($dateListAsOrdinals)
								$string .= $this->__getOrdinalDate($this->eventDates[0], $this->language);
							else
								$string .= $this->__getDate($this->eventDates[0], $this->language);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									$string .= 'le dernier jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
								else
									$string .= 'le ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
							else
								$string .= 'le ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
            break;

          case constantsClass::SEMIANNUALLY:
						if(!$forceOrdinals)
							if($dateListAsOrdinals)
								$string .= $this->__getOrdinalDate($this->eventDates[0], $this->language) . ' et ' . $this->__getOrdinalDate($this->eventDates[1], $this->language);
							else
								$string .= $this->__getDate($this->eventDates[0], $this->language) . ' et ' . $this->__getDate($this->eventDates[1], $this->language);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									$string .= 'le dernier jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[1]['month']);
								else
									$string .= 'le ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et le ' . $this->__getOrdinal($this->eventDates[1]['day'], '0') . ' jour du mois ' . $this->__getMonthFrench($this->eventDates[1]['month']);
							else
								$string .= 'le ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et '. $this->__getMonthFrench($this->eventDates[1]['month']);
            break;

          case constantsClass::QUARTERLY:
						if(!$forceOrdinals)
							if($dateListAsOrdinals)
								$string .= $this->__getOrdinalDate($this->eventDates[0], $this->language) . ', ' . $this->__getOrdinalDate($this->eventDates[1], $this->language) . ', ' . $this->__getOrdinalDate($this->eventDates[2], $this->language) . ' et ' . $this->__getOrdinalDate($this->eventDates[3], $this->language);
							else
								$string .= $this->__getDate($this->eventDates[0], $this->language) . ', ' . $this->__getDate($this->eventDates[1], $this->language) . ', ' . $this->__getDate($this->eventDates[2], $this->language) . ' et ' . $this->__getDate($this->eventDates[3], $this->language);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									$string .= 'le dernier jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[1]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[2]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[3]['month']);
								else
									$string .= 'le ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', le ' . $this->__getOrdinal($this->eventDates[1]['day'], '0') . ' jour du mois ' . $this->__getMonthFrench($this->eventDates[1]['month']) . ', le ' . $this->__getOrdinal($this->eventDates[2]['day'], '0') . ' jour du mois ' . $this->__getMonthFrench($this->eventDates[2]['month']) . ' et le ' . $this->__getOrdinal($this->eventDates[3]['day'], '0') . ' jour du mois ' . $this->__getMonthFrench($this->eventDates[3]['month']);								
							else
								$string .= 'le ' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . ' jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', '. $this->__getMonthFrench($this->eventDates[1]['month']) . ', '. $this->__getMonthFrench($this->eventDates[2]['month']) . ' et '. $this->__getMonthFrench($this->eventDates[3]['month']);

            break;
            
          case constantsClass::MONTHLY:
						if($this->__isBaseDateLastDayOfMonth())
							$string .= 'le dernier';
						else
							$string .= 'le ' . $this->__getOrdinal($this->baseDate['day']);
						$string .= ' jour de chacque mois';
            break;
        }

        if($this->interval != constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 1 || $this->interval == constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 2)
					;
//           $string .= ' of each year';
        break;

    }
  
				return $string;
		
  }

  function __getIndicativeDate($date, $useBracketsInsteadOfSpaces) {
  
		$string = '';
		
    switch($this->language) {
    
      case constantsClass::GERMAN:
        $string .= ($useBracketsInsteadOfSpaces ? '[' . $date['day'] . ']' : '   ') . '.' . wsd_decimal_format($date['month'], '00') . '.';
        break;
        
      case constantsClass::ENGLISH:
        $string .= $this->__getMonthEnglish($date['month']) . ' ' . ($useBracketsInsteadOfSpaces ? '[' . $date['day'] . ']' : '   ');
        break;

     case constantsClass::FRENCH:
        $string .= ($useBracketsInsteadOfSpaces ? '[' . $date['day'] . ']' : '   ') . ' ' . $this->__getMonthFrench($date['month']);
        break;

		}
				
		return $string;

  }

  function getIndicativeEventDates($forceOrdinals = false, $useBracketsInsteadOfSpaces = false, $ifOrdinalsThenNameLastDayOfMonthSpecifically = false) {
  
		$string = '';
		
    switch($this->language) {
    
      case constantsClass::GERMAN:
        switch($this->interval) {
          case constantsClass::ANNUALLY:
						$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces);
            break;

          case constantsClass::SEMIANNUALLY:
						$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces) . ' und ' . $this->__getIndicativeDate($this->eventDates[1], $useBracketsInsteadOfSpaces);
            break;

          case constantsClass::QUARTERLY:
						$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces) . ', ' . $this->__getIndicativeDate($this->eventDates[1], $useBracketsInsteadOfSpaces) . ', ' . $this->__getIndicativeDate($this->eventDates[2], $useBracketsInsteadOfSpaces) . ' und ' . $this->__getIndicativeDate($this->eventDates[3], $useBracketsInsteadOfSpaces);
            break;
            
          case constantsClass::MONTHLY:
						$string .= 'der   Tag jedes Monats';
            break;
        }

        if($this->interval != constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 1 || $this->interval == constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 2)
           $string .= " eines jeden Jahres";
        break;
        
      case constantsClass::ENGLISH:
        switch($this->interval) {
          case constantsClass::ANNUALLY:
						if(!$forceOrdinals)
							$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									if(!$useBracketsInsteadOfSpaces)
										$string .= 'the       day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
									else
										$string .= 'the [last] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
								else
									if($useBracketsInsteadOfSpaces)
										$string .= 'the [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
									else
										$string .= 'the      day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
							else
								if($useBracketsInsteadOfSpaces)
									$string .= 'the [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
								else
									$string .= 'the      day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']);
            break;

          case constantsClass::SEMIANNUALLY:
						if(!$forceOrdinals)
							$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces) . ' and ' . $this->__getIndicativeDate($this->eventDates[1], $useBracketsInsteadOfSpaces);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									if($useBracketsInsteadOfSpaces)
										$string .= 'the [last] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
									else
										$string .= 'the      day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
								else
									if($useBracketsInsteadOfSpaces)
										$string .= 'the [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and the [' . $this->__getOrdinal($this->eventDates[1]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
									else
										$string .= 'the      day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and the day of ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
							else
								if($useBracketsInsteadOfSpaces)
									$string .= 'the [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
								else
									$string .= 'the      day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[1]['month']);
            break;

          case constantsClass::QUARTERLY:
						if(!$forceOrdinals)
							$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces) . ', ' . $this->__getIndicativeDate($this->eventDates[1], $useBracketsInsteadOfSpaces) . ', ' . $this->__getIndicativeDate($this->eventDates[2], $useBracketsInsteadOfSpaces) . ' and ' . $this->__getIndicativeDate($this->eventDates[3], $useBracketsInsteadOfSpaces);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									if($useBracketsInsteadOfSpaces)
										$string .= 'the [last] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[3]['month']);
									else
										$string .= 'the      day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[3]['month']);
								else
									if($useBracketsInsteadOfSpaces)
										$string .= 'the [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', the [' . $this->__getOrdinal($this->eventDates[1]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', the [' . $this->__getOrdinal($this->eventDates[2]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and the [' . $this->__getOrdinal($this->eventDates[3]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[3]['month']);								
									else
										$string .= 'the      day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', the      day of ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', the      day of ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and the      day of ' . $this->__getMonthEnglish($this->eventDates[3]['month']);								
							else
								if($useBracketsInsteadOfSpaces)
									$string .= 'the [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[3]['month']);

								else
									$string .= 'the      day of ' . $this->__getMonthEnglish($this->eventDates[0]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[1]['month']) . ', ' . $this->__getMonthEnglish($this->eventDates[2]['month']) . ' and ' . $this->__getMonthEnglish($this->eventDates[3]['month']);
            break;
            
          case constantsClass::MONTHLY:
						if($useBracketsInsteadOfSpaces) {
							$isLastDayOfMonth = false;
							foreach($this->eventDates as $eventDate)
								if(wsd_decimal_format($eventDate['day'], '0') == 31)
									$isLastDayOfMonth = true;
							if($isLastDayOfMonth)
								$string .= 'the last';
							else
								$string .= 'the [' . $this->__getOrdinal($this->eventDates[0]['day']) . ']';
							$string .= ' day of each month';
						} else
							$string .= 'the      day of each month';
            break;
        }

        if($this->interval != constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 1 || $this->interval == constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 2)
					;
//           $string .= ' of each year';
        break;
				
      case constantsClass::FRENCH:
        switch($this->interval) {
          case constantsClass::ANNUALLY:
						if(!$forceOrdinals)
							$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									if(!$useBracketsInsteadOfSpaces)
										$string .= 'le       jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
									else
										$string .= 'le [dernier] jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
								else
									if($useBracketsInsteadOfSpaces)
										$string .= 'le [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
									else
										$string .= 'le      jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
							else
								if($useBracketsInsteadOfSpaces)
									$string .= 'le [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
								else
									$string .= 'le      jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']);
            break;

          case constantsClass::SEMIANNUALLY:
						if(!$forceOrdinals)
							$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces) . ' et ' . $this->__getIndicativeDate($this->eventDates[1], $useBracketsInsteadOfSpaces);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									if($useBracketsInsteadOfSpaces)
										$string .= 'le [dernier] jour de mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[1]['month']);
									else
										$string .= 'le      jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[1]['month']);
								else
									if($useBracketsInsteadOfSpaces)
										$string .= 'le [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et le [' . $this->__getOrdinal($this->eventDates[1]['day'], '0') . '] jour du mois ' . $this->__getMonthFrench($this->eventDates[1]['month']);
									else
										$string .= 'le      jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et le      jour du mois ' . $this->__getMonthFrench($this->eventDates[1]['month']);
							else
								if($useBracketsInsteadOfSpaces)
									$string .= 'le [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[1]['month']);
								else
									$string .= 'le      jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[1]['month']);
            break;

          case constantsClass::QUARTERLY:
						if(!$forceOrdinals)
							$string .= $this->__getIndicativeDate($this->eventDates[0], $useBracketsInsteadOfSpaces) . ', ' . $this->__getIndicativeDate($this->eventDates[1], $useBracketsInsteadOfSpaces) . ', ' . $this->__getIndicativeDate($this->eventDates[2], $useBracketsInsteadOfSpaces) . ' et ' . $this->__getIndicativeDate($this->eventDates[3], $useBracketsInsteadOfSpaces);
						else
							if($this->__isBaseDateLastDayOfMonth())
								if($ifOrdinalsThenNameLastDayOfMonthSpecifically == false)
									if($useBracketsInsteadOfSpaces)
										$string .= 'le [dernier] jour de mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[1]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[2]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[3]['month']);
									else
										$string .= 'le      jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[1]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[2]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[3]['month']);
								else
									if($useBracketsInsteadOfSpaces)
										$string .= 'le [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', le [' . $this->__getOrdinal($this->eventDates[1]['day'], '0') . '] jour du mois ' . $this->__getMonthFrench($this->eventDates[1]['month']) . ', le [' . $this->__getOrdinal($this->eventDates[2]['day'], '0') . '] jour du mois ' . $this->__getMonthFrench($this->eventDates[2]['month']) . ' et le [' . $this->__getOrdinal($this->eventDates[3]['day'], '0') . '] jour du mois ' . $this->__getMonthFrench($this->eventDates[3]['month']);								
									else
										$string .= 'le      jour du mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', le      jour du mois ' . $this->__getMonthFrench($this->eventDates[1]['month']) . ', le      jour du mois ' . $this->__getMonthFrench($this->eventDates[2]['month']) . ' et le      jour du mois ' . $this->__getMonthFrench($this->eventDates[3]['month']);								
							else
								if($useBracketsInsteadOfSpaces)
									$string .= 'le [' . $this->__getOrdinal($this->eventDates[0]['day'], '0') . '] jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[1]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[2]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[3]['month']);

								else
									$string .= 'le      jour des mois ' . $this->__getMonthFrench($this->eventDates[0]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[1]['month']) . ', ' . $this->__getMonthFrench($this->eventDates[2]['month']) . ' et ' . $this->__getMonthFrench($this->eventDates[3]['month']);
            break;
            
          case constantsClass::MONTHLY:
						if($useBracketsInsteadOfSpaces) {
							$isLastDayOfMonth = false;
							foreach($this->eventDates as $eventDate)
								if(wsd_decimal_format($eventDate['day'], '0') == 31)
									$isLastDayOfMonth = true;
							if($isLastDayOfMonth)
								$string .= 'le dernier';
							else
								$string .= 'le [' . $this->__getOrdinal($this->eventDates[0]['day']) . ']';
							$string .= ' jour de chacun mois';
						} else
							$string .= 'le      jour de chacun mois';
            break;
        }

        if($this->interval != constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 1 || $this->interval == constantsClass::ANNUALLY && wsd_date_format($this->lastEventDate, 'yyyy') - wsd_date_format($this->issueDate, 'yyyy') >= 2)
					;
//           $string .= ' of each year';
        break;

    }
  
				return $string;
		
  }
  
	function isEventDateLastDayOfMonth() {
	
    if($this->baseDate['day'] == 31 ||
       $this->baseDate['day'] == 30 && ($this->baseDate['month'] == 4 || $this->baseDate['month'] == 6 || $this->baseDate['month'] == 9 || $this->baseDate['month'] == 11) ||
       ($this->baseDate['day'] == 29 || $this->baseDate['day'] == 28) && $this->baseDate['month'] == 2)
			return true;
		else
			return false;
	
	}
	
}

?>