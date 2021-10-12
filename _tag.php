<?php

/**
 * _tag
 *
 * @author lekoala
 */
class _tag
{
    protected $self_closed = false;
    protected $tag = 'div';
    protected $content;
    protected $attributes = array();

    /**
     *
     * @param string $tag
     * @param string $content
     */
    public function __construct($tag = null, $content = null)
    {
        if ($tag !== null) {
            $this->tag($tag);
        }
        if ($content !== null) {
            $this->content($content);
        }
    }

    /**
     *
     * @param string $tag
     * @param string $content
     * @return $this
     */
    public static function inst($tag = null, $content = null)
    {
        return new static($tag, $content);
    }

    /**
     *
     * @param string $v
     * @return $this
     */
    public function self_closed($v)
    {
        if ($v === null) {
            return $this->self_closed;
        }
        $this->self_closed = $v;
        return $this;
    }

    /**
     *
     * @param string $v
     * @return $this
     */
    public function tag($v = null)
    {
        if ($v === null) {
            return $this->tag;
        }
        $this->tag = $v;
        return $this;
    }

    /**
     *
     * @param string $v
     * @return $this
     */
    public function attributes($v = null)
    {
        if ($v === null) {
            return $this->attributes;
        }
        $this->attributes = $v;
        return $this;
    }

    /**
     *
     * @param string $v
     * @return $this
     */
    public function content($v = null)
    {
        if ($v === null) {
            return $this->content;
        }
        $this->content = $v;
        return $this;
    }

    /**
     *
     * @param string $k
     * @param string $v
     * @return string
     */
    public function data($k, $v = null)
    {
        $k = 'data-' . $k;
        return $this->attr($k, $v);
    }

    /**
     *
     * @param string $k
     * @param string $v
     * @return string
     */
    public function cls($v = null)
    {
        return $this->attr('class', $v);
    }
    /**
     *
     *
     * @param string $k
     * @param string $v
     * @return string
     */
    public function id($v = null)
    {
        return $this->attr('id', $v);
    }

    /**
     *
     * @param string $v
     * @return string
     */
    public function setId($v)
    {
        return $this->setAttr('id', $v);
    }

    /**
     *
     * @param string $v
     * @return string
     */
    public function appendClass($v = null)
    {
        return $this->appendAttr('class', $v);
    }

    /**
     *
     * @param string $v
     * @return string
     */
    public function removeClass($v = null)
    {
        if ($v === null) {
            return $this->removeAttr('class');
        }
        $class = $this->attr('class');
        $class = str_replace($v, '', $class);
        $class = str_replace('  ', ' ', $class);
        return $this->attr('class', $class);
    }

    /**
     *
     * @param string $k
     * @param string $v
     * @return $this
     */
    public function attr($k, $v = null)
    {
        if ($v === null) {
            return isset($this->attributes[$k]) ? $this->attributes[$k] : null;
        }
        $this->attributes[$k] = $v;
        return $this;
    }

    /**
     *
     * @param string $k
     * @param mixed $v
     * @return $this
     */
    public function setAttr($k, $v)
    {
        $this->attributes[$k] = $v;
        return $this;
    }

    /**
     *
     * @param string $k
     * @param string $v
     * @return $this
     */
    public function appendAttr($k, $v)
    {
        if (is_array($k)) {
            foreach ($k as $k1 => $k2) {
                $this->appendAttr($k1, $k2);
            }
            return $this;
        }
        $value = $this->attr($k);
        $value .= ' ' . $v;
        return $this->attr($k, trim($value));
    }

    /**
     *
     * @param string $k
     * @return $this
     */
    public function removeAttr($k)
    {
        if (isset($this->attributes[$k])) {
            unset($this->attributes[$k]);
        }
        return $this;
    }

    /**
     *
     * @return string
     */
    protected function getContentHtml()
    {
        return htmlentities($this->content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     *
     * @return string
     */
    protected function getAttrsHtml($attrs = null)
    {
        if ($attrs === null) {
            $attrs = $this->getAttributes();
        }
        $attrsHtml = array();
        foreach ($attrs as $k => $v) {
            $attrsHtml[] = $k . '="' . $v . '"';
        }
        $res = implode(' ', $attrsHtml);
        if ($res) {
            return ' ' . $res;
        }
        return $res;
    }

    /**
     *
     * @return string
     */
    public function render($attrs = null)
    {
        $attrsHtml = $this->getAttrsHtml($attrs);
        $tag = $this->tag();
        $content = $this->getContentHtml();

        if ($this->self_closed) {
            return '<' . $tag . $attrsHtml . '/>';
        }
        return '<' . $tag . $attrsHtml . '>' . $content . '</' . $tag . '>';
    }

    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
}
