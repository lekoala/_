<?php

/**
 * _form
 *
 * @author lekoala
 */
class _form {

	public static $use_html5 = false;
	public static $auto_id = true;
	protected $fields = array();
	protected $enable_parsley;
	protected $classes;

	public function __construct($fields = array()) {
		$this->fields = $fields;
	}

	public function render() {
		$html = '';
		foreach ($this->fields as $f) {
			$html .= (string) $f;
		}
		return $html;
	}

	public function __toString() {
		try {
			return $this->render();
		} catch (Exception $ex) {
			return $ex->getMessage();
		}
	}

	/**
	 * Set form data
	 * @param array|object $data
	 */
	public function set_data($data) {
		if (is_object($data)) {
			if (method_exists($data, 'to_array')) {
				$data = $data->to_array();
			} else {
				$data = get_object_vars($data);
			}
		}
		foreach ($data as $k => $v) {
			$field = $this->get_field($k);
			if ($field) {
				$field->value($v);
			}
		}
	}

	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Get field by name
	 * 
	 * @param string $name
	 * @return _field_element
	 */
	public function get_field($name) {
		foreach ($this->fields as $field) {
			if ($field->name() == $name) {
				return $field;
			}
		}
		return false;
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form
	 */
	public function enable_parsley($v = true) {
		$this->enable_parsley = $v;
		return $this;
	}
	
	public function classes($v) {
		$this->classes = $v;
		return $this;
	}
	
	/**
	 * @param string $action
	 * @param string $method
	 * @param string $enctype
	 * @return string
	 */
	public function open($action = '', $method = "POST", $enctype = null) {
		$html = '<form action="' . $action . '" method="' . $method . '" class="'.$this->classes.'"';
		if ($enctype) {
			$html .= ' enctype="multipart/form-data"';
		}
		if($this->enable_parsley) {
			$html .= ' data-parsley-validate';
		}
		$html .= '>';
		return $html;
	}

	/**
	 * 
	 * @return string
	 */
	public function close() {
		return '</form>';
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @param type $options
	 * @return _form_select
	 */
	public static function select($name = null, $value = null, $options = array()) {
		return _form_select::inst($name, $value)->options($options);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @param type $options
	 * @return _form_multicheckboxes
	 */
	public static function multicheckboxes($name = null, $value = null, $options = array()) {
		return _form_multicheckboxes::inst($name, $value)->options($options);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_input
	 */
	public static function input($name = null, $value = null) {
		return new _form_input($name, $value);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_input
	 */
	public static function hidden($name = null, $value = null) {
		return self::input($name, $value)->type('hidden');
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_input
	 */
	public static function date($name = null, $value = null) {
		return new _form_date($name, $value);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_daterange
	 */
	public static function daterange($name = null, $value = null) {
		return new _form_daterange($name, $value);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_autocomplete
	 */
	public static function autocomplete($name = null, $value = null) {
		return new _form_autocomplete($name, $value);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_element
	 */
	public static function element($name = null, $value = null) {
		return new _form_element($name, $value);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_literal
	 */
	public static function literal($name = null, $value = null) {
		return new _form_literal($name, $value);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_checkbox
	 */
	public static function checkbox($name = null, $value = null) {
		return new _form_checkbox($name, $value);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_textarea
	 */
	public static function textarea($name = null, $value = null) {
		return new _form_textarea($name, $value);
	}

	/**
	 * 
	 * @param type $name
	 * @param type $value
	 * @return \_form_file
	 */
	public static function file($name = null, $value = null) {
		return new _form_file($name, $value);
	}

}

/**
 * An arbitrary form element
 */
class _form_element extends _tag {

	protected $attributes = array();

	public function __construct($name = null, $value = null) {
		$this->name($name);
		$this->value($value);
	}

	public function value($v = null) {
		return $this->attr('value', $v);
	}

	public function setValue($v) {
		return $this->setAttr('value', $v);
	}

	public function name($v = null) {
		if ($v !== null) {
			if (_form::$auto_id && !$this->id()) {
				$id = str_replace(array('[', ']'), array('-', ''), $v);
				$this->id('input-' . $id);
			}
		}
		return $this->attr('name', $v);
	}

	//parsley shortcuts

	public function required($v = true) {
		return $this->attr('data-parsley-required', $v);
	}

	public function is_email() {
		return $this->attr('data-parsley-type', 'email');
	}

	public function is_number() {
		return $this->attr('data-parsley-type', 'number');
	}

	public function is_integer() {
		return $this->attr('data-parsley-type', 'integer');
	}

	public function is_digits() {
		return $this->attr('data-parsley-type', 'digits');
	}

	public function is_alphanum() {
		return $this->attr('data-parsley-type', 'alphanum');
	}

	public function minlength($x = 6) {
		return $this->attr('data-parsley-minlength', $x);
	}

	public function maxlength($x = 6) {
		return $this->attr('data-parsley-maxlength', $x);
	}

	public function rangelength($min, $max) {
		return $this->attr('data-parsley-length', "[$min,$max]");
	}

	public function pattern($pattern) {
		$pattern = '\\' . $pattern;
		return $this->attr('data-parsley-pattern', "$pattern");
	}

	public function is_equalto($id) {
		if (strpos($id, '#') !== 0) {
			$id = '#' . $id;
		}
		return $this->attr('data-parsley-equalto', "$id");
	}

}

class _form_literal extends _form_element {

	public function render($attrs = null) {
		return '<div class="literal-field">' . $this->value() . '</div>';
	}

}

/**
 * An input is an <input> tag. It can contain a label. All elements that should
 * work with labels need to extend _form_input
 */
class _form_input extends _form_element {

	protected $tag = 'input';
	protected $self_closed = true;
	protected $label;

	public function __construct($name = null, $value = null) {
		parent::__construct($name, $value);
		$this->type('text');
	}

	/**
	 * 
	 * @param type $v
	 * @return type
	 */
	public function placeholder($v = null) {
		return $this->attr('placeholder', $v);
	}

	/**
	 * 
	 * @param type $v
	 * @return type
	 */
	public function type($v = null) {
		return $this->attr('type', $v);
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_input
	 */
	public function label($v = true) {
		$this->label = $v;
		return $this;
	}

	/**
	 * Build a label based on the name of the field
	 * @return type
	 */
	protected function autolabel() {
		$label = $this->name();
		$label = ucwords(str_replace(array('[', ']', '_'), array(' ', '', ' '), $label));
		return $label;
	}

	/**
	 * Default attributes
	 * @return array
	 */
	protected function getDefaultAttributes() {
		return array(
			'name' => '',
			'value' => ''
		);
	}

	public function getAttributes() {
		$attrs = parent::getAttributes();
		$attrs = array_merge($this->getDefaultAttributes(), $attrs);
		return $attrs;
	}

	/**
	 * 
	 * @return string
	 */
	protected function getLabelHtml() {
		$html = '<label';
		$id = $this->id();
		$label = $this->label;
		if ($label === true) {
			$label = $this->autolabel();
		}
		if ($id) {
			$html .= ' for="' . $id . '"';
		}
		$html .= '>' . $label . '</label>';
		return $html;
	}

	/**
	 * Overriden render method to include label if exists
	 * @return string
	 */
	public function render($attrs = null) {
		$html = parent::render($attrs);
		if ($this->label) {
			$html = $this->getLabelHtml() . "\n" . $html;
		}
		return $html;
	}

}

class _form_date extends _form_input {

	protected $use_alt_field = true;
	protected $use_js_datepicker = true;
	public static $default_options = array(
		'changeMonth' => true,
		'changeYear' => true,
		'dateFormat' => 'Y-m-d',
		'userFormat' => 'd/m/Y'
	);
	protected $datepicker_options = array();
	protected $custom_options;

	public function __construct($name = null, $value = null) {
		parent::__construct($name, $value);
		$this->cls('date');
		if (_form::$use_html5) {
			$this->type('date');
		}
	}

	public function get_custom_options() {
		return $this->custom_options;
	}

	public function set_custom_options($custom_options) {
		$this->use_alt_field = false;
		$this->use_js_datepicker = true;
		$this->custom_options = $custom_options;
		return $this;
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_date
	 */
	public function use_alt_field($v) {
		if ($v === null) {
			return $this->use_alt_field;
		}
		$this->use_alt_field = $v;
		return $this;
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_date
	 */
	public function use_js_datepicker($v) {
		if ($v === null) {
			return $this->use_js_datepicker;
		}
		$this->use_js_datepicker = $v;
		return $this;
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_date
	 */
	public function datepicker_options($v) {
		if ($v === null) {
			return $this->datepicker_options;
		}
		$this->use_js_datepicker(true);
		$this->datepicker_options = $v;
		return $this;
	}

	protected function initJsDatepicker($options = array()) {
		$default_options = self::$default_options;
		$options = array_merge($default_options, $options);

		$options['dateFormat'] = self::dateStringToDatepickerFormat($options['dateFormat']);
		if ($this->use_alt_field) {
			$options['altField'] = 'input[rel=' . $this->id() . ']';
			$options['altFormat'] = $options['dateFormat'];
			if (isset($default_options['userFormat'])) {
				$options['dateFormat'] = self::dateStringToDatepickerFormat($default_options['userFormat']);
				unset($options['userFormat']);
			}
		}

//		if($this->value()) {
//			$options['defaultDate'] = (string)$this->value();
//		}

		return "$('#" . $this->id() . "').datepicker(" . json_encode($options, JSON_UNESCAPED_SLASHES) . ");";
	}

	public static function dateStringToDatepickerFormat($dateString) {
		$pattern = array(
			//day
			'd', //day of the month
			'j', //3 letter name of the day
			'l', //full name of the day
			'z', //day of the year
			//month
			'F', //Month name full
			'M', //Month name short
			'n', //numeric month no leading zeros
			'm', //numeric month leading zeros
			//year
			'Y', //full numeric year
			'y'  //numeric year: 2 digit
		);
		$replace = array(
			'dd', 'd', 'DD', 'o',
			'MM', 'M', 'm', 'mm',
			'yy', 'y'
		);
		foreach ($pattern as &$p) {
			$p = '/' . $p . '/';
		}
		return preg_replace($pattern, $replace, $dateString);
	}

	/**
	 * Overriden render method to include label if exists
	 * @return string
	 */
	public function render($attrs = null) {
		if ($this->use_alt_field) {
			$attrs = $this->getAttributes();
			$html = '<input type="hidden" name="' . $attrs['name'] . '" value="' . $attrs['value'] . '" rel="' . $this->id() . '">';
			$attrs['name'] = $attrs['name'] . '_formatted';
			$attrs['name'] = str_replace(']_formatted', '_formatted]', $attrs['name']); //fix closing brackets
//			if($this->use_js_datepicker) {
//				unset($attrs['value']);
//			}
			if (isset(self::$default_options['userFormat']) && $attrs['value']) {
				$attrs['value'] = date(self::$default_options['userFormat'], strtotime((string) $attrs['value']));
			}
			$html .= parent::render($attrs);
		} else {
			$html = parent::render($attrs);
		}
		if ($this->use_js_datepicker) {
			$html .= '<script>' . $this->initJsDatepicker($this->datepicker_options) . '</script>';
		}
		return $html;
	}

}

/**
 * @link https://rawgithub.com/longbill/jquery-date-range-picker/master/index.html
 */
class _form_daterange extends _form_input {

	public static $default_options = array(
		'separator' => ' - ',
		'startOfWeek' => 'monday',
		'format' => 'YYYY-MM-DD',
//		'autoClose' => true
	);
	protected $datepicker_options = array();
	protected $custom_options;
	protected $two_inputs;

	public function __construct($name = null, $value = null) {
		parent::__construct($name, $value);
		$this->cls('daterange');
	}

	public function get_custom_options() {
		return $this->custom_options;
	}

	public function set_custom_options($custom_options) {
		$this->custom_options = $custom_options;
		return $this;
	}

	public function get_two_inputs() {
		return $this->two_inputs;
	}

	public function set_two_inputs($two_inputs) {
		$names = array();
		$values = array();
		foreach ($two_inputs as $k => $v) {
			if (is_int($k)) {
				//we only have names
				$names[] = $v;
			} else {
				$values[] = $v;
				$names[] = $k;
			}
		}
		$this->two_inputs = $names;
		if (!empty($values)) {
			$this->value($values);
		}
		$this->datepicker_options['getValue'] = 'function() { if($(\'#' . $this->id() . '-start\').val() && $(\'#' . $this->id() . '-end\').val()) { return $(\'#' . $this->id() . '-start\').val() + \' - \' + $(\'#' . $this->id() . '-end\').val()} return \'\'; }';
		$this->datepicker_options['setValue'] = 'function(s,s1,s2) { $(\'#' . $this->id() . '-start\').val(s1); $(\'#' . $this->id() . '-end\').val(s2); }';
		return $this;
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_datepicker
	 */
	public function datepicker_options($v) {
		if ($v === null) {
			return $this->datepicker_options;
		}
		$this->datepicker_options = $v;
		return $this;
	}

	protected function initJsDatepicker($options = array()) {
		if ($this->custom_options) {
			$opts = $this->custom_options;
		} else {
			$default_options = self::$default_options;
			$options = array_merge($default_options, $options);
			$opts = json_encode($options, JSON_UNESCAPED_SLASHES);
			//json functions
			$opts = preg_replace('/:"function\((.*?)\) \{(.*?)}"/', ':function($1) {$2}', $opts);
		}
		return "$('#" . $this->id() . "').dateRangePicker(" . $opts . ");";
	}

	/**
	 * Overriden render method to include label if exists
	 * @return string
	 */
	public function render($attrs = null) {
		if ($this->two_inputs) {
			$name_start = $this->two_inputs[0];
			$name_end = $this->two_inputs[1];

			$attrs = $this->getAttributes();
			$attrs_start = $attrs_end = $attrs;
			$attrs_start['id'] .= '-start';
			$attrs_start['name'] = $name_start;
			if ($attrs['value'] && is_array($attrs['value'])) {
				$attrs_start['value'] = $attrs['value'][0];
			}
			$attrs_end['id'] .= '-end';
			$attrs_end['name'] = $name_end;
			if ($attrs['value'] && is_array($attrs['value'])) {
				$attrs_end['value'] = $attrs['value'][1];
			}
			$attrsHtmlStart = $this->getAttrsHtml($attrs_start);
			$attrsHtmlEnd = $this->getAttrsHtml($attrs_end);
			$tag = $this->tag();
			$html = '<span id="' . $this->id() . '"><' . $tag . $attrsHtmlStart . '/> - ' . '<' . $tag . $attrsHtmlEnd . '/></span>';
			if ($this->label) {
				$html = $this->getLabelHtml() . "\n" . $html;
			}
		} else {
			$html = parent::render($attrs);
		}
		//@todo : remove coupling
		if (class_exists('app\requirements')) {
			app\requirements::js('js/daterangepicker/daterangepicker.js');
			app\requirements::css('js/daterangepicker/daterangepicker.css');
		}
		$html .= '<script>' . $this->initJsDatepicker($this->datepicker_options) . '</script>';
		return $html;
	}

}

/**
 */
class _form_autocomplete extends _form_input {

	public static $default_options = array(
	);
	protected $autocomplete_options = array();
	protected $custom_options;
	protected $source;
	protected $hidden;

	public function __construct($name = null, $value = null) {
		parent::__construct($name, $value);
		$this->cls('autocomplete');
	}

	public function get_custom_options() {
		return $this->custom_options;
	}

	public function set_custom_options($custom_options) {
		$this->custom_options = $custom_options;
		return $this;
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_autocomplete
	 */
	public function autocomplete_options($v) {
		if ($v === null) {
			return $this->autocomplete_options;
		}
		$this->autocomplete_options = $v;
		return $this;
	}

	protected function initJsAutocomplete($options = array()) {
		if ($this->custom_options) {
			$opts = $this->custom_options;
		} else {
			$default_options = self::$default_options;
			$options = array_merge($default_options, $options);
			if ($this->source) {
				$options['source'] = $this->source;
			}
			if ($this->hidden) {
				$options['select'] = 'function(event,ui) { $(\'#' . $this->id() . '\').val(ui.item.label) ; $(\'input[rel=' . $this->id() . ']\').val(ui.item.id) ; }';
			}
			$opts = json_encode($options, JSON_UNESCAPED_SLASHES);
			//json functions
			$opts = preg_replace('/:"function\((.*?)\) \{(.*?)}"/', ':function($1) {$2}', $opts);
		}
		return "$('#" . $this->id() . "').autocomplete(" . $opts . ");";
	}

	public function source($v = null) {
		if ($v === null) {
			return $this->source;
		}
		$this->source = $v;
		return $this;
	}

	public function hidden($name = null) {
		if ($name === null) {
			return $this->hidden;
		}
		$this->hidden = $name;
		return $this;
	}

	/**
	 * Overriden render method to include label if exists
	 * @return string
	 */
	public function render($attrs = null) {
		$html = parent::render($attrs);
		if ($this->hidden) {
			$html .= '<input type="hidden" name="' . $this->hidden . '" value="' . $attrs['value'] . '" rel="' . $this->id() . '">';
		}
		$html .= '<script>' . $this->initJsAutocomplete($this->autocomplete_options) . '</script>';
		return $html;
	}

}

/**
 * A select dropdown
 */
class _form_select extends _form_input {

	protected $tag = 'select';
	protected $options = array();
	protected $first_empty = true;
	protected $no_numeric_key = false;
	protected $self_closed = false;

	/**
	 * 
	 * @param type $v
	 * @return \_form_select
	 */
	public function options($v = null) {
		if ($v === null) {
			return $this->options;
		}
		$this->options = $v;
		return $this;
	}

	/**
	 * 
	 * @param array $arr
	 * @param string $key
	 * @param string $value
	 * @return _form_select
	 */
	public function optionsMap(array $arr, $key, $value) {
		$map = array();
		foreach ($arr as $v) {
			if (is_object($v) && method_exists($v, $value)) {
				if (method_exists($v, $value)) {
					$val = $v->$value();
				} else {
					$val = $v->$value;
				}
			} else {
				$val = $v[$value];
			}
			$map[$v[$key]] = $val;
		}
		return $this->options($map);
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_select
	 */
	public function first_empty($v) {
		if ($v === null) {
			return $this->first_empty;
		}
		$this->first_empty = $v;
		return $this;
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_select
	 */
	public function no_numeric_key($v) {
		if ($v === null) {
			return $this->no_numeric_key;
		}
		$this->no_numeric_key = $v;
		return $this;
	}

	/**
	 * 
	 * @param type $v
	 * @return \_form_select
	 */
	public function self_closed($v) {
		if ($v === null) {
			return $this->no_numeric_key;
		}
		$this->no_numeric_key = $v;
		return $this;
	}

	public function getAttributes() {
		$res = parent::getAttributes();
		//remove value since the option is checked
		$res = array_diff($res, array('value'));
		return $res;
	}

	/**
	 * 
	 * @return string
	 */
	protected function getContentHtml() {
		$options = array();
		if ($this->first_empty) {
			$options[] = '<option></option>';
		}
		foreach ($this->options as $k => $v) {
			if ($this->no_numeric_key && is_int($k)) {
				$k = $v;
			}
			$selected = ($this->value() == $k) ? ' selected="selected"' : '';
			$options[] = '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
		}
		return implode("\n", $options);
	}

}

/**
 * A checkbox set
 */
class _form_multicheckboxes extends _form_select {

	protected $tag = 'input';
	protected $self_closed = true;

	public function __construct($name = null, $value = null) {
		parent::__construct($name, $value);
		$this->type('checkbox');
	}

	public function getAttributes() {
		$attrs = $this->attributes;
		$attrs = array_merge($this->getDefaultAttributes(), $attrs);
		return $attrs;
	}

	/**
	 * Overriden render method to include label if exists
	 * @return string
	 */
	public function render($attrs = null) {
		if ($attrs === null) {
			$attrs = $this->getAttributes();
		}
		$value = $attrs['value'];
		if(!is_array($value)) {
			$value = array();
		}
		$attrs['value'] = 1;
		$attrsHtml = $this->getAttrsHtml($attrs);
		$tag = $this->tag();
		
		$html = '';
		foreach ($this->options as $k => $v) {
			if ($this->no_numeric_key && is_int($k)) {
				$k = $v;
			}
			$attrsHtmlTag =  $attrsHtml;
			if (in_array($k, $value)) {
				$attrsHtmlTag .=  ' checked="checked"';
			}
			$taghtml = '<input type="hidden" value="0" name="' . $attrs['name'] . '[' . $k . ']' . '">' . '<' . $tag . $attrsHtmlTag . '/>';
			$html .= '<label class="checkbox">' . $taghtml . ' ' . $v . '</label>';
		}


		return $html;
	}

}

class _form_checkbox extends _form_input {

	protected $right_label = true;
    protected $inline = false;

	public function __construct($name = null, $value = null) {
		parent::__construct($name, $value);
		$this->type('checkbox');
	}

    public function inline($v = null) {
        if($v === null) {
            return $this->inline;
        }
        $this->inline = $v;
        return $this;
    }

	/**
	 * 
	 * @param type $v
	 * @return \_form_checkbox
	 */
	public function right_label($v) {
		if ($v === null) {
			return $this->right_label;
		}
		$this->right_label = $v;
		return $this;
	}

	/**
	 * Overriden render method to include label if exists
	 * @return string
	 */
	public function render($attrs = null) {
		if ($attrs === null) {
			$attrs = $this->getAttributes();
		}
		$value = $attrs['value'];
		$attrs['value'] = 1;
		$attrsHtml = $this->getAttrsHtml($attrs);
		$tag = $this->tag();

		if ($value) {
			$attrsHtml .= ' checked="checked"';
		}

		$html = '<input type="hidden" value="0" name="' . $attrs['name'] . '">' . '<' . $tag . $attrsHtml . '/>';
		if ($this->label) {
            $class = 'checkbox';
            if($this->inline) {
                $class = 'checkbox-inline';
            }
			$html = '<label class="'.$class.'">' . $html . ' ' . $this->label . '</label>';
		}
		return $html;
	}

}

class _form_textarea extends _form_input {

	protected $tag = 'textarea';
	protected $self_closed = false;

}

class _form_file extends _form_input {

	public function __construct($name = null, $value = null) {
		parent::__construct($name, $value);
		$this->type('file');
	}

}
