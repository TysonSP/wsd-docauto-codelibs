<?php

$errors = array();
$missingFields = array();

function addError($type, $message, $fields) {

	global $errors;
	
	$enumeration = new enumerationClass();
	foreach($fields as $path) {
	
		if(strpos($path, '/') == false)
			$name = $path;
		else
			$name = substr($path, strrpos($path, '/') + 1);
	
		$enumeration->add('<item><path>' . $path . '</path><name>' . $name . '</name></item>');
		
	}
	
	$errors[] = '<error><type>' . $type . '</type><message>' . $message . '</message>' . ($enumeration->count() > 0 ? '<fields>' . $enumeration->getEnumeration('', '') . '</fields>' : '') . '</error>';

}

function addMissingFieldsToErrors() {

	global $manager, $errors, $missingFields;
	
	if($manager->getElement('Document type') == 'Prelim')
		return;
	
	$enumeration = new enumerationClass();
	foreach($missingFields as $missingField)
		$enumeration->add($missingField);
		
	if($enumeration->count() > 0){
		//trigger_error('here',E_USER_ERROR);
		//$errors[] = '<error><type>Missing fields</type><message>One or more fields required for a "final" document are missing</message><fields>' . $enumeration->getEnumeration('','') . '</fields></error>';
	}
}

function validateField($path, $wsdElement = NULL) {

	global $v, $manager, $missingFields;

	if(strpos($path, '/') == false)
		$name = $path;
	else
		$name = substr($path, strrpos($path, '/') + 1);
		
	if($wsdElement == NULL)
		$wsdElement = $name;
					
	if($manager->doesElementExist($wsdElement)) {
		if($manager->getElementRawValue($wsdElement) === NULL || strlen($manager->getElementRawValue($wsdElement)) == 0) {

			$missingFields[] = '<item><path>' . $path . '</path><name>' . $name . '</name></item>';
		}	
	}
			
}

function validateUserInput() {

	global $v, $manager, $missingFields, $errors;

	$missingFields = new enumerationClass();

	/*
	if($v['__WSD_COMMON_TAGS']['TEMPLATE_NAME'] == 'Pricing supplement' && $manager->getElement('Document type') != 'Final') {
	
		trigger_error('<error><message>Pricing supplements can only be generated for "final" document type<message></error>', E_USER_ERROR);
		
	}
	*/
	/* 
	if($manager->getElement('Document type') == 'Final') {
		validateField('Document date');
		validateField('Issue size');
		validateField('Series size');
		validateField('Minimum trading size');
		validateField('Issue price');	
		validateField('Trade date');
		validateField('Strike date');
		validateField('Issue date');
		validateField('Settlement date');
		validateField('Valuation date');
		validateField('Autocall level');
		validateField('Series no');
		validateField('ISIN');
		//validateField('Common code');
		
		if($missingFields->count() > 0)
			trigger_error('<error><message>One or more fields required for a "final" document are missing</message><missingFields>' . $missingFields->getEnumeration('', '') . '</missingFields></error>', E_USER_ERROR);
		
	} */
	
	addMissingFieldsToErrors();
	
	if(count($errors) > 0) {
	
		$enumeration = new enumerationClass();
		foreach($errors as $error)
			$enumeration->add($error);
			
		trigger_error(html_entity_decode('<errors>' . $enumeration->getEnumeration('', '') . '</errors>'), E_USER_ERROR);
	
	}
	
}

?>