<?php

/**
 * Dynamic table
 *
 * @author tportelange
 */
class _table
{

    const MODE_APPEND = 'append';
    const MODE_QS = 'qs';
    const MODE_REPLACE = 'replace';

    protected static $instances = 0;
    protected static $script_inserted = false;
    protected $identifier = 'id';
    protected $selectable;
    protected $selectable_actions = array();
    protected $headers;
    protected $pagination;
    protected $data;
    protected $html = '';
    protected $class;
    protected $id;
    protected $indent;
    protected $actions;
    protected $actions_mode = 'append';
    protected $replace_level = 1;
    protected $searchable_headers;
    protected $searchable_key = 'filters';
    protected $searchable_input = '<input type="submit" value="filter">';
    protected $form_method = 'post';
    protected $columns_width = array();
    protected $row_style = '';

    public function __construct()
    {
        self::$instances++;
    }

    public function get_row_style()
    {
        return $this->row_style;
    }

    /**
     * Can set a background-color array[1] if data has a field name like array[0]
     */
    public function set_row_style($row_style)
    {
        $this->row_style = $row_style;
        return $this;
    }

    public function get_identifier()
    {
        return $this->identifier;
    }

    public function set_identifier($id)
    {
        $this->identifier = $id;
        return $this;
    }

    public function get_replace_level()
    {
        return $this->replace_level;
    }

    public function set_replace_level($replace_level)
    {
        $this->replace_level = $replace_level;
        return $this;
    }

    public function get_selectable()
    {
        return $this->selectable;
    }

    public function set_selectable($v = true)
    {
        $this->selectable = $v;
        return $this;
    }

    public function get_selectable_actions()
    {
        return $this->selectable_actions;
    }

    public function set_selectable_actions($v = array())
    {
        $this->selectable = true;
        $this->selectable_actions = _::arrayify($v);
        return $this;
    }

    public function get_pagination()
    {
        return $this->pagination;
    }

    public function set_pagination($current, $total, $collapse = null)
    {
        $this->pagination = compact('current', 'total', 'collapse');
        return $this;
    }

    public function get_headers()
    {
        return $this->headers;
    }

    public function set_headers($headers = null)
    {
        $headers = _::arrayify($headers);
        $this->headers = $headers;
        return $this;
    }

    public function get_searchable_headers()
    {
        return $this->searchable_headers;
    }

    public function set_searchable_headers($headers = null)
    {
        $headers = _::arrayify($headers);
        $this->searchable_headers = $headers;
        return $this;
    }

    public function get_data()
    {
        return $this->data;
    }

    public function set_data($data)
    {
        $this->data = $data;
        return $this->data;
    }

    public function get_class()
    {
        return $this->class;
    }

    public function set_class($class)
    {
        $this->class = _::stringify($class, ' ');
        return $this;
    }

    public function get_id()
    {
        return $this->id;
    }

    public function set_id($class)
    {
        $this->id = $class;
        return $this;
    }

    public function get_actions()
    {
        return $this->actions;
    }

    public function set_actions($actions, $mode = 'append')
    {
        $this->actions = $actions;
        $this->actions_mode = $mode;
        return $this;
    }

    public function get_actions_mode()
    {
        return $this->actions_mode;
    }

    public function set_actions_mode($mode)
    {
        $this->actions_mode = $mode;
        return $this;
    }

    public function get_form_method()
    {
        return $this->form_method;
    }

    public function set_form_method($v)
    {
        $this->form_method = $v;
        return $this;
    }

    public function get_columns_width()
    {
        return $this->columns_width;
    }

    public function set_columns_width($columns_width)
    {
        $this->columns_width = $columns_width;
        return $this;
    }

    public function set_column_width($k, $v)
    {
        $this->columns_width[$k] = $v;
    }

    protected function format_xml($xml, $spaces = 4, $escape = false)
    {

        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

        $token = strtok($xml, "\n");
        $result = '';
        $pad = 0;
        $matches = array();

        while ($token !== false) :
            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
                $indent = 0;
            // 2. closing tag - outdent now
            elseif (preg_match('/^<\/\w/', $token, $matches)) :
                $pad -= $spaces;
            // 3. opening tag - don't pad this one, only subsequent tags
            elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
                $indent = 1;
            // 4. no indentation needed
            else :
                $indent = 0;
            endif;

            // pad the line with the required number of leading spaces
            $line = str_pad($token, strlen($token) + $pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n";
            $token = strtok("\n");
            $pad += $indent * $spaces;
        endwhile;

        $result = rtrim($result, "\n");

        if ($escape) {
            $result = htmlentities($result, ENT_QUOTES, "UTF-8");
        }

        return $result;
    }

    protected function tag($name, $value = null, $attributes = array())
    {
        //allow attr as 2nd element
        if (is_array($value)) {
            $attributes = $value;
            $value = null;
        }

        //sub elements
        $els = explode('>', $name);
        while (count($els) > 1) {
            //recursively call on each element
            $el = array_pop($els);
            $value = $this->tag($el, $value);
        }
        $name = $els[0];

        //siblings elements
        $els = explode('+', $name);
        $results = array();
        while (count($els) > 1) {
            //recursively call on each element
            $el = array_shift($els);
            $results[] = $this->tag($el);
        }
        $name = $els[0];

        //support multiple tags
        $pattern = '/
			\*				# times operator
			(?P<mul>\d*)	# digit
			\z				# end of line
		/x';
        preg_match($pattern, $name, $matches);
        $mul = 1;
        if (!empty($matches['mul'])) {
            $name = preg_replace($pattern, '', $name);
            $mul = $matches['mul'];
        }

        //attributes
        $parts = preg_split('/
			(?=\.|\#|\[|\{|\()				# separators
			(?=(?:[^"]*"[^"]*")*[^"]*$)		# no match between quotes
		/x', $name, 0, PREG_SPLIT_DELIM_CAPTURE);
        $name = $parts[0];
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }
            $mod = $part[0];
            $len = strlen($part) - 1;
            if (in_array($mod, array('[', '{'))) {
                $len--;
            }
            $v = substr($part, 1, $len);
            switch ($part[0]) {
                case '#':
                    $attributes['id'] = $v;
                    break;
                case '.':
                    if (!empty($attributes['class'])) {
                        $attributes['class'] .= ' ';
                    } else {
                        $attributes['class'] = '';
                    }
                    $attributes['class'] .= $v;
                    break;
                case '[':
                    $exp = explode('=', $v);
                    $v = '';
                    if (isset($exp[1])) {
                        $v = $exp[1];
                    }
                    $attributes[$exp[0]] = trim($v, '"');
                    break;
                case '{':
                    $value = $v;
                    break;
            }
        }

        //default as div
        if (empty($name)) {
            $name = 'div';
        }

        //prepare html
        $str = '';
        $i = 0;
        while ($mul--) {
            $i++;
            $attr = '';
            foreach ($attributes as $k => $v) {
                if (is_int($k)) {
                    $k = $v;
                    $v = '';
                }
                if (empty($v)) {
                    continue;
                }
                $attr .= ' ' . $k . '="' . $v . '"';
            }
            $attr = str_replace('$', $i, $attr);
            if ($value) {
                $str .= '<' . $name . $attr . '>' . $value . '</' . $name . '>';
            } else {
                $str .= '<' . $name . $attr . ' />';
            }
        }

        $results[] = $str;
        $str = implode("", $results);

        return $str;
    }

    protected function array_collapse(array $arr)
    {
        $a = array();
        foreach ($arr as $k => $v) {
            if (is_int($k)) {
                $k = $v;
            }
            $a[] = $k;
        }
        return $a;
    }

    protected function array_stretch(array $arr)
    {
        $a = array();
        foreach ($arr as $k => $v) {
            if (is_int($k) && is_string($v)) {
                $k = $v;
            }
            $a[$k] = $v;
        }
        return $a;
    }

    public function render($return = false)
    {
        if ($this->headers) {
            $headers = '';
            if ($this->selectable) {
                //un-check all
                $headers .= $this->tag('th', '<input type="checkbox" onclick="toggleSelectable(this,document.tableform' . self::$instances . ');" />');
            }
            foreach ($this->headers as $header) {
                $width = null;
                if (isset($this->columns_width[$header])) {
                    $width = $this->columns_width[$header];
                }
                $headers .= $this->tag('th', $header, array(
                    'width' => $width
                ));
            }
            if ($this->actions) {
                $headers .= $this->tag('th');
            }
            $headers = $this->tag('tr', $headers);
            if ($this->searchable_headers) {
                $searchable_headers = $this->make_searchable_headers();
                $headers .= $this->tag('tr', $searchable_headers);
            }
            $this->html .= $this->tag('thead', $headers);
        }
        if ($this->data) {
            $this->html .= '<tbody>';
            $i = 0;

            foreach ($this->data as $data) {
                $i++;
                $value = $i;
                if (isset($data[$this->identifier])) {
                    $value = $data[$this->identifier];
                }
                //auto table id
                if (is_object($data)) {
                    $this->id = 'table-' . strtolower(str_replace('\\', '-', get_class($data)));
                }


                if ($this->get_row_style()) {
                    $style = $this->get_row_style();
                    if (isset($data->$style[0]) && $data->$style[0] == 1) {
                        $this->html .= "<tr style='background-color:" . $style[1] . "'>";
                    } else {
                        $this->html .= '<tr>';
                    }
                } else {
                    $this->html .= '<tr>';
                }

                if ($this->selectable) {
                    //check item
                    $this->html .= $this->tag('td', '<input type="checkbox" name="selectable[]" value="' . $value . '" />');
                }
                if ($this->headers) {
                    //if we have headers, display only headers
                    foreach ($this->headers as $header) {
                        $v = isset($data[$header]) ? $data[$header] : null;
                        $this->html .= $this->tag('td', $v);
                    }
                } else {
                    //or display everything
                    foreach ($data as $k => $v) {
                        $this->html .= $this->tag('td', $v);
                    }
                }
                if ($this->actions) {
                    $actions = $this->make_actions($value);
                    $this->html .= $this->tag('td', $actions);
                }
                $this->html .= '</tr>';
            }
            $this->html .= '</tbody>';
        }

        //wrap table
        $class = $this->class;
        $id = $this->id;
        $table_attr = compact('class', 'id');
        $this->html = $this->tag('table', $this->html, $table_attr);

        if ($this->actions || $this->selectable) {
            //append selectable actions
            if ($this->selectable) {
                $selectable_actions = $this->make_selectable_actions();
                $this->html .= $selectable_actions;
            }

            $this->html = $this->tag('form[name=tableform' . self::$instances . '][method=' . $this->form_method . ']', $this->html);
        }

        //pagination
        if ($this->pagination) {
            $pagination = $this->make_pagination();
            $this->html = $pagination . $this->html . $pagination;
        }

        //format
        $this->html = $this->format_xml($this->html);

        //append script
        if ($this->selectable) {
            if (!self::$script_inserted) {
                $this->html .= <<<'SCRIPT'
<script type="text/javascript">
function toggleSelectable(el,fields) {
	for(var i=0; i < fields.length; i++) {
		if(fields[i].name === 'selectable[]') fields[i].checked = el.checked;
	}
}
</script>
SCRIPT;
                self::$script_inserted = true;
            }
        }

        //output
        if ($return) {
            return $this->html;
        }
        echo $this->html;
    }

    protected function make_searchable_headers()
    {
        $headers_keys = $this->array_collapse($this->headers);
        $searchable_headers = '';
        if ($this->selectable) {
            $searchable_headers .= $this->tag('th');
        }
        $global = $_GET;
        if ($this->form_method == 'post') {
            $global = $_POST;
        }
        foreach ($headers_keys as $header) {
            $input = '';
            if (in_array($header, $this->searchable_headers)) {
                $value = isset($global[$this->searchable_key][$header]) ? $global[$this->searchable_key][$header] : null;
                $width = 'auto';
                if (isset($this->columns_width[$header])) {
                    $width = $this->columns_width[$header] . 'px';
                }
                $input = '<input name="' . $this->searchable_key . '[' . $header . ']" value="' . $value . '" style="width:' . $width . '" />';
            }
            $searchable_headers .= $this->tag('th', $input);
        }
        if ($this->actions) {
            $searchable_headers .= $this->tag('th', $this->searchable_input);
        }
        return $searchable_headers;
    }

    protected function make_pagination()
    {
        $li = '';
        $current = $this->pagination['current'];
        $total = ceil($this->pagination['total']);
        if ($current > $total) {
            $current = $total;
        }
        $collapse = $this->pagination['collapse'];

        if ($current == 0) {
            $class = 'disabled';
        }
        $li .= $this->tag('li', $this->tag('a', '&laquo;', array('href' => _::querystring('p', $current - 1))), array('class' => $class));
        for ($i = 0; $i < $total; $i++) {
            if ($collapse) {
                if ($total > $collapse && ($i > $current + $collapse / 2 && $i > $collapse) || ($i < $current - $collapse / 2 && $i < $total - $collapse)) {
                    continue;
                }
            }
            $class = '';
            if ($i == $current) {
                $class = 'active';
            }
            $li .= $this->tag('li', $this->tag('a', $i + 1, array('href' => _::querystring('p', $i))), array('class' => $class));
        }
        if ($current == $total) {
            $class = 'disabled';
        }
        $li .= $this->tag('li', $this->tag('a', '&raquo;', array('href' => _::querystring('p', $current + 1))), array('class' => $class));
        $pagination = $this->tag('ul.pagination', $li);
        return $pagination;
    }

    protected function make_selectable_actions()
    {
        foreach ($this->selectable_actions as $action => $value) {
            if (is_int($action)) {
                if ($value instanceof _form_element) {
                    $actions[] = $value;
                    continue;
                }
                $action = $value;
                $value = ucwords(str_replace('_', ' ', $action));
            }
            $class = 'btn btn-default';
            $type = 'submit';
            $name = 'action[' . $action . ']';
            $btn = $this->tag('input', compact('type', 'class', 'name', 'value'));
            $actions[] = $btn;
        }
        $actions = implode('', $actions);
        return $actions;
    }

    protected function make_actions($value = null)
    {
        if (is_array($this->actions)) {
            $actions = array();
            foreach ($this->actions as $action => $label) {
                $arr = array();

                if (is_array($label)) {
                    $arr = $label;
                }

                //param was array('delete')
                if (is_int($action) && is_string($label)) {
                    $action = $label;
                    $arr['label'] = ucwords(str_replace('_', ' ', $action));
                } else {
                    //param was array('delete' => 'Delete me')
                    if (is_string($label)) {
                        $arr['label'] = $label;
                    }
                }

                if (strpos($action, '!') !== false) {
                    $action = str_replace('!', '', $action);
                    $arr['label'] = str_replace('!', '', $arr['label']);
                    $arr['onclick'] = "return confirm('Are you sure?');return false;";
                }

                //add attributes
                if (!isset($arr['href'])) {
                    $href = _::url(true);

                    $action_parts = explode('?', $action);
                    $char = '?';

                    if ($this->actions_mode == self::MODE_REPLACE) {
                        $i = $this->replace_level;
                        while ($i--) {
                            $href = dirname($href);
                        }
                    }
                    if ($this->actions_mode == self::MODE_QS) {
                        $href .= '?action=' . $action_parts[0];
                        if ($value) {
                            $href .= '&id=' . urlencode($value);
                        }
                        $char = '&';
                    } else {
                        $href .= '/' . $action_parts[0];
                        if ($value) {
                            $href .= '/' . urlencode($value);
                        }
                    }
                    if (isset($action_parts[1])) {
                        $href .= $char . $action_parts[1];
                    }
                    $arr['href'] = $href;
                }
                if (!isset($arr['class'])) {
                    $arr['class'] = 'btn btn-default btn-mini';
                }
                $btn = $this->tag('a', $arr['label'], $arr);
                $actions[] = $btn;
            }
            $actions = '<div class="btn-group">' . implode(' ', $actions) . '</div>';
        } else {
            $actions = $this->actions;
            preg_match_all('/{{(?P<var>.*)}}/', $actions, $matches);
            if (!empty($matches['var'])) {
                foreach ($matches['var'] as $var) {
                    if (isset($data[$var])) {
                        $actions = str_replace("{{" . $var . "}}", $data[$var], $actions);
                    }
                }
            }
        }
        return $actions;
    }

    public function __toString()
    {
        try {
            return $this->render(true);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
