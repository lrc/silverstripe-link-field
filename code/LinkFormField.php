<?php
/**
 * Description of LinkFormField
 *
 * @author Simon
 */
class LinkFormField extends FormField {
	
	public static $module_dir = ''; // This is initially set in _config.php
	
	private static $url_handlers = array(
		'$Action!/$ID' => '$Action'
	);

	private static $allowed_actions = array(
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
	
	public function __construct($name, $title = null, $value = null, $form = null) {
		// naming with underscores to prevent values from actually being saved somewhere
		$this->fieldCustomURL = new TextField("{$name}[CustomURL]", _t('LinkField.URL', 'URL').'<br/>', '', 300, $form);
		$this->fieldPageID = new TreeDropdownField("{$name}[PageID]", _t('LinkField.SITE', 'Site'), 'SiteTree', 'ID', 'Title');
		$this->fieldPageID->setForm($form);
		parent::__construct($name, $title, $value, $form);
	}

	public function setForm($form) {
		$this->fieldPageID->setForm($form);
		$this->fieldCustomURL->setForm($form);
		return parent::setForm($form);
	}

	public function setName($name){
		$this->fieldPageID->setName("{$name}[PageID]");
		$this->fieldCustomURL->setName("{$name}[CustomURL]");
		return parent::setName($name);
	}

    public function validate($validator){
        if(!empty($this->value['CustomURL'])) {
            if(!filter_var($this->value['CustomURL'], FILTER_VALIDATE_URL)){
                $validator->validationError(
                    $this->name,
                    _t('LinkField.VALIDATION', "Please enter a valid URL (e.g http://mywebsite.com)."),
                    "validation"
                );
                return false;
            }
        }
        return true;
    }
	
	/**
	 * @return string
	 */
	public function Field($properties = array()) {
		Requirements::javascript(self::$module_dir . '/js/LinkFormField.js');
		return "<div class=\"fieldgroup LinkFormField \">" .
			"<div class=\"fieldgroupField LinkFormFieldPageID\">" . 
				$this->fieldPageID->SmallFieldHolder() . 
			"</div>" .
            "<p/>".
			"<div class=\"fieldgroupField LinkFormFieldCustomURL\">" . 
				$this->fieldCustomURL->SmallFieldHolder() . 
			"</div>" . 
		"</div>";
	}

	public function setValue($val) {
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
	public function saveInto(DataObjectInterface $dataObject) {
		
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

