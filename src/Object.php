<?php
namespace Bpaulsen314\Perfecto;

class Object
{
    protected static $_magicCallMethods = null;

    public static function getMagicCallMethods()
    {
        if (!is_array(static::$_magicCallMethods)) {
            static::$_magicCallMethods = array();
        }
        return static::$_magicCallMethods;
    }

    public function __call($method_name, $arguments) 
    {
        $result = null;

        $magicCallMethods = static::getMagicCallMethods();

        if (array_key_exists($method_name, $magicCallMethods)) {
            $instructions = $this->_getMagicCallInstructions($method_name);
            $i_method_name = $instructions["method_name"];
            $i_property_name = $instructions["property_name"];
            if (
                in_array(
                    $i_method_name, array("_addToProperty", "_setProperty")
                )
            ) {
                if (count($arguments) != 1) {
                    $class_name = get_class($this);
                    $message = "Too few or too many arguments given: ";
                    $message .= "{$class_name}->{$method_name}(...)";
                    // TODO: Re-evaluate exception type thrown.
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
            // TODO: Re-evaluate exception type thrown.
            throw new Exception($message);
        }

        return $result;
    }

    public function getOid()
    {
        return spl_object_hash($this);
    }

    public function dump($return = false)
    {
        // TODO: Align this method signature with var_export signature.
        $result = var_export($this, $return);
        return $result;
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
            // TODO: Re-evaluate exception type thrown.
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
            // TODO: Re-evaluate exception type thrown.
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
            // TODO: Re-evaluate exception type thrown.
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
            // TODO: Re-evaluate exception type thrown.
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
                $string_helper->camelNotate($matches["property_name"], true);
        } else if  (
            preg_match(
                "#^get(?P<property_name>[A-Z][A-Za-z0-9]*)$#", 
                $method_name, 
                $matches
            )
        ) {
            $instructions["method_name"] = "_getProperty";
            $instructions["property_name"] = "_" . 
                $string_helper->camelNotate($matches["property_name"], true);
        } else if (
            preg_match(
                "#^(evaluate|is)(?P<property_name>[A-Z][A-Za-z0-9]*)$#",
                $method_name,
                $matches
            )
        ) {
            $instructions["method_name"] = "_evaluateProperty";
            $instructions["property_name"] = "_" . 
                $string_helper->camelNotate($matches["property_name"], true);
        } else if (
            preg_match(
                "#^add(?P<property_name>[A-Z][A-Za-z0-9]*)$#",
                $method_name,
                $matches
            )
        ) {
            $instructions["method_name"] = "_addToProperty";
            $instructions["property_name"] = "_" . 
                $string_helper->camelNotate($matches["property_name"], true);
            $instructions["property_name"] = $string_helper->pluralize(
                $instructions["property_name"]
            );
        }

        return $instructions;
    }
}
