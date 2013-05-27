<?php

/**
 * A link field which will store a link in the database.
 * 
 * @author Simon Elvery
 * @package silverstripe-link-field
 */
class LinkField extends DBField implements CompositeDBField {
	
	/**
	 * @var int The PageID for this link.
	 */
	protected $page_id;

	/**
	 * @var string A custom URL for this link
	 */
	protected $custom_url;

	/**
	 * @var boolean Is this record changed or not?
	 */
	protected $isChanged = false;
	
	
	/**
	 * Similiar to {@link DataObject::$db},
	 * holds an array of composite field names.
	 * Don't include the fields "main name",
	 * it will be prefixed in {@link requireField()}.
	 * 
	 * @var array $composite_db
	 */
	static $composite_db = array(
		'PageID' => 'Int',
		'CustomURL' => 'Varchar(2000)'
	);
	
	/**
	 * Set the value of this field in various formats.
	 * Used by {@link DataObject->getField()}, {@link DataObject->setCastedField()}
	 * {@link DataObject->dbObject()} and {@link DataObject->write()}.
	 * 
	 * As this method is used both for initializing the field after construction,
	 * and actually changing its values, it needs a {@link $markChanged}
	 * parameter. 
	 * 
	 * @param DBField|array $value
	 * @param array $record Map of values loaded from the database
	 * @param boolean $markChanged Indicate wether this field should be marked changed. 
	 *  Set to FALSE if you are initializing this field after construction, rather
	 *  than setting a new value.
	 */
	function setValue($value, $record = null, $markChanged = true){
		
		if ($value instanceof LinkField && $value->hasValue($this)) {
			$this->setPageID($value->getPageID(), $markChanged);
			$this->setCustomURL($value->getCustomURL(), $markChanged);
		} elseif ( 
			$record && 
			( isset($record[$this->name . 'PageID']) || isset($record[$this->name . 'CustomURL']) ) 
		) {
			$this->setPageID(
				(isset($record[$this->name . 'PageID'])) ? $record[$this->name . 'PageID'] : null, 
				$markChanged
			);
			$this->setCustomURL(
				(isset($record[$this->name . 'CustomURL'])) ? $record[$this->name . 'CustomURL'] : null,
				$markChanged
			);
		} else if (is_array($value)) {
			if (array_key_exists('PageID', $value)) {
				$this->setPageID($value['PageID'], $markChanged);
			}
			
			if (array_key_exists('CustomURL', $value)) {
				$this->setCustomURL($value['CustomURL'], $markChanged);
			}
		} else {
//			user_error('Invalid value in LinkField->setValue()', E_USER_ERROR);
		}
	}
	
	/**
	 * Used in constructing the database schema.
	 * Add any custom properties defined in {@link $composite_db}.
	 * Should make one or more calls to {@link DB::requireField()}.
	 */
	function requireField(){
		$fields = $this->compositeDatabaseFields();
		if($fields) foreach($fields as $name => $type){
			DB::requireField($this->tableName, $this->name.$name, $type);
		}
	}
	
	/**
	 * Add the custom internal values to an INSERT or UPDATE
	 * request passed through the ORM with {@link DataObject->write()}.
	 * Fields are added in $manipulation['fields']. Please ensure
	 * these fields are escaped for database insertion, as no
	 * further processing happens before running the query.
	 * Use {@link DBField->prepValueForDB()}.
	 * Ensure to write NULL or empty values as well to allow 
	 * unsetting a previously set field. Use {@link DBField->nullValue()}
	 * for the appropriate type.
	 * 
	 * @param array $manipulation
	 */
	function writeToManipulation(&$manipulation) {
		if($this->getPageID()) {
			$manipulation['fields'][$this->name.'PageID'] = $this->prepValueForDB((int)$this->getPageID());
		} else {
			$manipulation['fields'][$this->name.'PageID'] = 
					DBField::create_field('Int', $this->getPageID())->nullValue();
		}
		
		if($this->getCustomURL()) {
			$manipulation['fields'][$this->name.'CustomURL'] = $this->prepValueForDB($this->getCustomURL());
		} else {
			$manipulation['fields'][$this->name.'CustomURL'] = 
					DBField::create_field('Varchar', $this->getCustomURL())->nullValue();
		}
	}
	
	/**
	 * Add all columns which are defined through {@link requireField()}
	 * and {@link $composite_db}, or any additional SQL that is required
	 * to get to these columns. Will mostly just write to the {@link SQLQuery->select}
	 * array.
	 * 
	 * @param SQLQuery $query
	 */
	function addToQuery(&$query) {
		parent::addToQuery($query);
	}
	
	/**
	 * Return array in the format of {@link $composite_db}.
	 * Used by {@link DataObject->hasOwnDatabaseField()}.
	 * @return array
	 */
	function compositeDatabaseFields(){
		return self::$composite_db;
	}
	
	/**
	 * Determines if the field has been changed since its initialization.
	 * Most likely relies on an internal flag thats changed when calling
	 * {@link setValue()} or any other custom setters on the object.
	 * 
	 * @return boolean
	 */
	function isChanged(){
		return $this->isChanged;
	}
	
	/**
	 * Determines if any of the properties in this field have a value,
	 * meaning at least one of them is not NULL.
	 * 
	 * @return boolean
	 */
	function hasValue($field, $arguments = null, $cache = true){
		return ($this->page_id || $this->custom_url);
	}
	
	public function getPageID() {
		return $this->page_id;
	}
	
	public function setPageID($page_id, $markChanged = true) {
		$this->isChanged = $markChanged;
		$this->page_id = $page_id;
	}
	
	public function getCustomURL() {
		return $this->custom_url;
	}
	
	public function setCustomURL($url, $markChanged = true) {
		$this->isChanged = $markChanged;
		$this->custom_url = $url;
	}
	
	/**
	 * Returns a CompositeField instance used as a default
	 * for form scaffolding.
	 *
	 * Used by {@link SearchContext}, {@link ModelAdmin}, {@link DataObject::scaffoldFormFields()}
	 * 
	 * @param string $title Optional. Localized title of the generated instance
	 * @return FormField
	 */
	public function scaffoldFormField($title = null) {
		$field = new LinkFormField($this->name);
		return $field;
	}
	
	public function Page() {
		if ($this->getPageID() && $page = DataObject::get_by_id('Page', $this->getPageID())) {
			return $page;
		}
		return null;
	}
	
	public function getURL() {
		return ( $this->Page() ) ? $this->Page()->Link() : Convert::raw2htmlatt($this->getCustomURL());
	}
	
	public function __toString() {
		return (string) $this->getURL();
	}
	
	function forTemplate() {
		return $this->getURL();
	}
	
	function Absolute() {
		$relative = $this->getURL();
		return (Director::is_site_url($relative) && Director::is_relative_url($relative)) 
			? Controller::join_links(Director::protocolAndHost(), $relative) 
			: $relative;
	}
	
}