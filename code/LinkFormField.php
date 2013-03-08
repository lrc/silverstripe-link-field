<?php
/**
 * Description of LinkFormField
 *
 * @author Simon
 */
class LinkFormField extends FormField {
	
	static $module_dir = ''; // This is initially set in _config.php
	
	public static $url_handlers = array(
		'$Action!/$ID' => '$Action'
	);
	
	public static $allowed_actions = array(
		'tree'
	);
	
	/**
	 * @var FormField
	 */
	protected $fieldPageID = null;
	
	/**
	 * @var FormField
	 */
	protected $fieldCustomURL = null;
	
	function __construct($name, $title = null, $value = null, $form = null) {
		// naming with underscores to prevent values from actually being saved somewhere
		$this->fieldCustomURL = new TextField("{$name}[CustomURL]", ' URL', '', 300, $form);
		$this->fieldPageID = new TreeDropdownField("{$name}[PageID]", '', 'SiteTree', 'ID', 'Title');
		$this->fieldPageID->setForm($form);
		parent::__construct($name, $title, $value, $form);
	}
	
	function setForm($form) {
		$this->fieldPageID->setForm($form);
		$this->fieldCustomURL->setForm($form);
		return parent::setForm($form);
	}
	
	/**
	 * @return string
	 */
	function Field($properties = array()) {
		Requirements::javascript(self::$module_dir . '/js/LinkFormField.js');
		return "<div class=\"fieldgroup LinkFormField \">" .
			"<div class=\"fieldgroupField LinkFormFieldPageID\">" . 
				$this->fieldPageID->SmallFieldHolder() . 
			"</div>" . 
			"<div class=\"fieldgroupField LinkFormFieldCustomURL\">" . 
				$this->fieldCustomURL->SmallFieldHolder() . 
			"</div>" . 
		"</div>";
	}
	
	function setValue($val) {
		$this->value = $val;
		if(is_array($val)) {
			$this->fieldPageID->setValue($val['PageID']);
			$this->fieldCustomURL->setValue($val['CustomURL']);
		} elseif($val instanceof LinkField) {
			$this->fieldPageID->setValue($val->getPageID());
			$this->fieldCustomURL->setValue($val->getCustomURL());
		}
	}
	
	/**
	 * SaveInto checks if set-methods are available and use them instead of setting the values directly. saveInto
	 * initiates a new LinkField class object to pass through the values to the setter method.
	 */
	function saveInto(DataObjectInterface $dataObject) {
		
		$fieldName = $this->name;
		if($dataObject->hasMethod("set$fieldName")) {
			$dataObject->$fieldName = DBField::create('LinkField', array(
				"PageID" => $this->fieldPageID->Value(),
				"CustomURL" => $this->fieldCustomURL->Value()
			));
		} else {
			$dataObject->$fieldName->setPageID($this->fieldPageID->Value()); 
			$dataObject->$fieldName->setCustomURL($this->fieldCustomURL->Value());
		}
	}

	/**
	 * Returns a readonly version of this field.
	 */
	public function performReadonlyTransformation() {
		return new ReadonlyField($this->Name, $this->Title, $this->Value);
	}
	
	/**
	 * @todo Implement removal of readonly state with $bool=false
	 * @todo Set readonly state whenever field is recreated, e.g. in setAllowedCurrencies()
	 */
	public function setReadonly($bool) {
		parent::setReadonly($bool);
		
		if($bool) {
			$this->fieldPageID = $this->fieldPageID->performReadonlyTransformation();
			$this->fieldCustomURL = $this->fieldCustomURL->performReadonlyTransformation();
		}
	}
	
	public function tree($request) {
		return str_replace(
			"<ul class=\"tree\">\n", 
			"<ul class=\"tree\">\n" . '<li id="selector-' . $this->name . '[PageID]-0"><a>(None / Custom URL)</a></li>',
			$this->fieldPageID->tree($request)
		);
	}
}

