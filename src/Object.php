<?php
namespace Perfecto;

class Object
{
    protected static $_magic_call_methods = null;

    public static function getMagicCallMethods()
    {
        if (!is_array(static::$_magic_call_methods)) {
            static::$_magic_call_methods = array();
        }
        return static::$_magic_call_methods;
    }

    public function __call($method_name, $arguments) 
    {
        $result = null;

        $magic_call_methods = static::getMagicCallMethods();

        if (array_key_exists($method_name, $magic_call_methods)) {
            $instructions = $this->_getMagicCallInstructions($method_name);
            $i_method_name = $instructions["method_name"];
            $i_property_name = $instructions["property_name"];
            if (
                in_array(
                    $i_method_name, 
                    array("_addToProperty", "_setProperty")
                )
            ) {
                if (count($arguments) != 1) {
                    $class_name = get_class($this);
                    $message = "Too few or too many arguments given: ";
                    $message .= "{$class_name}->{$method_name}(...)";
                    throw new Exception($message);
                } else {
                    $this->{$i_method_name}($i_property_name, $arguments[0]);
                }
            } else {
                $result = $this->{$i_method_name}($i_property_name);
            }
        } else {
            $class_name = get_class($this);
            $message = "Magic call method not defined: ";
            $message .= "{$class_name}->{$method_name}(...)";
            throw new Exception($message);
        }

        return $result;
    }

    public function printR()
    {
        print_r($this);
    }

    protected function _addToProperty($name, $value)
    {
        if (property_exists($this, $name)) {
            if (is_null($this->{$name})) {
                $this->{$name} = array();
            }
            $this->{$name}[] = $value;
        } else {
            $class_name = get_class($this);
            $message = "Cannot add to property; property does not exist: ";
            $message .= "{$class_name}->{$name}";
            throw new Exception($message);
        }
    }

    protected function _evaluateProperty($name)
    {
        $result = null;

        if (property_exists($this, $name)) {
            $result = ($this->{$name} ? true : false);
        } else {
            $class_name = get_class($this);
            $message = "Cannot evaluate property; property does not exist: ";
            $message .= "{$class_name}->{$name}";
            throw new Exception($message);
        }

        return $result;
    }

    protected function _getProperty($name)
    {
        $result = null;

        if (property_exists($this, $name)) {
            $result = $this->{$name};
        } else {
            $class_name = get_class($this);
            $message = "Cannot get property; property does not exist: ";
            $message .= "{$class_name}->{$name}";
            throw new Exception($message);
        }

        return $result;
    }

    protected function _setProperty($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        } else {
            $class_name = get_class($this);
            $message = "Cannot set property; property does not exist: ";
            $message .= "{$class_name}->{$name}";
            throw new Exception($message);
        }
    }

    private function _getMagicCallInstructions($method_name)
    {
        $instructions = array();

        $string_helper = StringHelper::getInstance();

        if (
            preg_match(
                "#^set(?P<property_name>[A-Z][A-Za-z0-9]*)$#", 
                $method_name, 
                $matches
            )
        ) {
            $instructions["method_name"] = "_setProperty";
            $instructions["property_name"] = "_" . 
                $string_helper->underscoreNotate($matches["property_name"]);
        } else if  (
            preg_match(
                "#^get(?P<property_name>[A-Z][A-Za-z0-9]*)$#", 
                $method_name, 
                $matches
            )
        ) {
            $instructions["method_name"] = "_getProperty";
            $instructions["property_name"] = "_" . 
                $string_helper->underscoreNotate($matches["property_name"]);
        } else if (
            preg_match(
                "#^(evaluate|is)(?P<property_name>[A-Z][A-Za-z0-9]*)$#",
                $method_name,
                $matches
            )
        ) {
            $instructions["method_name"] = "_evaluateProperty";
            $instructions["property_name"] = "_" . 
                $string_helper->underscoreNotate($matches["property_name"]);
        } else if (
            preg_match(
                "#^add(?P<property_name>[A-Z][A-Za-z0-9]*)$#",
                $method_name,
                $matches
            )
        ) {
            $instructions["method_name"] = "_addToProperty";
            $instructions["property_name"] = "_" . 
                $string_helper->underscoreNotate($matches["property_name"]);
            $instructions["property_name"] = $string_helper->pluralize(
                $instructions["property_name"]
            );
        }

        return $instructions;
    }
}
