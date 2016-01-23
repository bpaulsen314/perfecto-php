<?php
namespace W3glue\Perfecto;

class ArrayHelper extends AbstractSingleton
{
    public function areIdentical($arr1, $arr2)
    {
        $arr1_hash = $this->hash($arr1);
        $arr2_hash = $this->hash($arr2);
        return ($arr1_hash === $arr2_hash);
    }

    public function camelNotateKeys($arr, $lowercaseFirst = true)
    {
        return $this->_notateKeys($arr, "camel", $lowercaseFirst);
    }

    public function castNumericStrings(&$arr)
    {
        $int_regex = "#^-?([1-9][0-9]*|0)$#";
        $float_regex = "#^-?([1-9][0-9]*|0)?\.(0|[0-9]*[1-9])$#";

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $this->castNumericStrings($arr[$key]);
            } else if (is_string($value)) {
                if (preg_match($int_regex, $value)) {
                    // make sure the integer value fits within
                    // the bounds of an integer type ... if not,
                    // this value is actually a float
                    $value = (float) $value;
                    $php_int_min = ~PHP_INT_MAX;
                    if ($value >= $php_int_min && $value <= PHP_INT_MAX) {
                        $value = (int) $value;
                    }
                } else if (preg_match($float_regex, $value)) {
                    $value = (float) $value;
                }
                $arr[$key] = $value;
            }
        }
    }

    public function delete(&$arr, $key)
    {
        $key = $this->_cleanKey($key);

        $deleted = false;

        $key_pieces = explode(".", $key);
        $current_key = array_shift($key_pieces);

        if (is_array($arr) && array_key_exists($current_key, $arr)) {
            if ($key_pieces) {
                $deleted = $this->delete(
                    $arr[$current_key], implode(".", $key_pieces)
                );
            } else {
                unset($arr[$current_key]);
                $deleted = true;
            }
        }

        return $deleted;
    }

    public function dashNotateKeys($arr)
    {
        return $this->_notateKeys($arr, "dash");
    }

    public function extractValues($arr, $key, $cast_numeric_strings = true)
    {
        // allow for arguments in wrong order ... it happens
        if (is_string($arr) && is_array($key)) {
            list($arr, $key) = array($key, $arr);
        }
        $key = $this->_cleanKey($key);

        $values = array();

        // perform the extraction
        if (is_array($arr) && is_string($key)) {
            $key_pieces = explode(".", $key);
            $key = array_shift($key_pieces);

            if (array_key_exists($key, $arr)) {
                $arr = array($arr);
            }

            foreach ($arr as $element) {
                $element_value = null;
                if (is_array($element)) {
                    if (array_key_exists($key, $element)) {
                        $element_value = $element[$key];
                    }
                } else if (is_object($element)) {
                    if (isset($element->{$key})) {
                        $element_value = $element->{$key};
                    }
                }

                if (!is_null($element_value)) {
                    if ($key_pieces) {
                        if (is_object($element_value)) {
                            $element_value = array($element_value);
                        }

                        if (is_array($element_value)) {
                            $more_values = $this->extractValues(
                                $element_value,
                                implode(".", $key_pieces),
                                $cast_numeric_strings
                            );
                            $values = array_merge(
                                $values, $more_values
                            );
                        }
                    } else {
                        $values[] = $element_value;
                    }
                }
            }
        } else {
            // TODO: Throw an exception ... Bad parameters.
        }

        // clean up / eliminate duplicates
        if ($cast_numeric_strings) {
            $this->castNumericStrings($values);
        }

        $unique_values = array();
        foreach ($values as $value) {
            if (is_object($value)) {
                // TODO: Figure out what to do about objects.
            } else if (is_array($value)) {
                $hash = $this->hash($value);
                $unique_values["a:$hash"] = $value;
            } else if (is_string($value)) {
                $unique_values["s:$value"] = $value;
            } else if (is_int($value)) {
                $unique_values["i:$value"] = $value;
            } else if (is_float($value)) {
                $unique_values["f:$value"] = $value;
            } else if (!is_null($value)) {
                $unique_values["nn:$value"] = $value;
            }
        }
        $values = array_values($unique_values);

        return $values;
    }

    public function getValue($arr, $key, $clean = true)
    {
        // allow for arguments in wrong order ... it happens
        if (is_string($arr) && is_array($key)) {
            list($arr, $key) = array($key, $arr);
        }
        $key = $this->_cleanKey($key);

        $value = false;

        if (is_string($key) && (is_array($arr) || is_object($arr))) {
            $key_pieces = explode(".", $key);
            $key = array_shift($key_pieces);

            if (is_array($arr) && array_key_exists($key, $arr)) {
                $value = $arr[$key];
            } else if (is_object($arr) && property_exists($arr, $key)) {
                $value = $arr->{$key};
            }

            if ($key_pieces) {
                if (is_array($value) || is_object($value)) {
                    $value = $this->getValue(
                        $value, implode(".", $key_pieces)
                    );
                } else {
                    $value = false;
                }
            }
        } else {
            // TODO: Throw an exception ... Bad parameters.
        }

        if ($clean) {
            if (is_string($value)) {
                $value = trim($value);
            }
        }

        return $value;
    }

    public function hash($arr)
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = $this->hash($value);
            }
        }

        if ($this->isHashTable($arr)) {
            ksort($arr);
        }

        $str = "";
        $first_element = true;
        foreach ($arr as $key => $value) {
            $str .= ($first_element ? "" : "|") . "$key:";

            if ($value === true) {
                $str .= "true";
            } else if ($value === false) {
                $str .= "false";
            } else if (is_null($value)) {
                $str .= "null";
            } else if (is_string($value)) {
                $str .= "\"$value\"";
            } else {
                $str .= "$value";
            }

            $first_element = false;
        }

        return sha1($str);
    }

    public function hashOn($arr, $key)
    {
        // allow for arguments in wrong order ... it happens
        if (is_string($arr) && is_array($key)) {
            list($arr, $key) = array($key, $arr);
        }
        $key = $this->_cleanKey($key);

        $hashed_arr = array();

        // perform the extraction
        if (is_array($arr) && is_string($key)) {
            $key_pieces = explode(".", $key);
            $key = array_shift($key_pieces);

            if (array_key_exists($key, $arr)) {
                $arr = array($arr);
            }

            foreach ($arr as $element) {
                $value = null;
                if (is_array($element)) {
                    $value = $this->getValue($element, $key);
                } else if (!$key_pieces && is_object($element)) {
                    if (property_exists($element, $key)) {
                        $value = $element->{$key};
                    }
                }

                if ($value) {
                    $hashed_arr["$value"] = $element;
                }
            }
        } else {
            // TODO: Throw an exception ... Bad parameters.
        }

        return $hashed_arr;
    }

    public function isHashTable($arr)
    {
        return !$this->isList($arr);
    }

    public function isList($arr)
    {
        $is_list = true;

        if (is_array($arr)) {
            $counter = 0;
            foreach ($arr as $k => $v) {
                if ($k !== $counter) {
                    $is_list = false;
                    break;
                }
                $counter++;
            }
        } else {
            $is_list = false;
        }

        return $is_list;
    }

    public function setValue(&$arr, $key, $value, $safe = false)
    {
        $key = $this->_cleanKey($key);
        $this->_setValueRecursive($arr, $key, $value, $safe);
    }

    public function underscoreNotateKeys($arr)
    {
        return $this->_notateKeys($arr, "underscore");
    }

    private function _cleanKey($key)
    {
        return preg_replace("#\.+#", ".", $key);
    }

    private function _notateKeys($arr, $style = "camel", $lowercase_first = null)
    {
        $transformed_arr = array();

        $string_helper = StringHelper::getInstance();

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $method = "{$style}NotateKeys";
                if (is_null($lowercase_first)) {
                    $value = $this->$method($value);
                } else {
                    $value = $this->$method($value, $lowercase_first);
                }
            }

            if (is_string($key)) {
                $method = "{$style}Notate";
                if (is_null($lowercase_first)) {
                    $key = $string_helper->$method($key);
                } else {
                    $key = $string_helper->$method($key, $lowercase_first);
                }
            }

            $transformed_arr[$key] = $value;
        }

        return $transformed_arr;
    }

    private function _setValueRecursive(&$arr, $key, $value, $safe = false, $prefix = "")
    {
        preg_match("#(?<current>[^.]*)(\.)?(?<next>.*)#", $key, $matches);
        
        if ($matches["current"]) {
            $current = $matches["current"];

            if ($matches["next"]) {
                $next = $matches["next"];

                if ($safe && array_key_exists($current, $arr) && !is_array($arr[$current])) {
                    // TODO: Re-evalute exception usage.
                    $message = "Can not address index in non array: ";
                    $message .= "{$prefix}{$current}";
                    throw new Exception($message);
                }

                if (!array_key_exists($current, $arr) || !is_array($arr[$current])) {
                    $arr[$current] = [];
                }
                $this->setValue(
                    $arr[$current], $next, $value, "{$prefix}{$current}."
                );
            } else if (
                $safe && array_key_exists($current, $arr) &&
                gettype($arr[$curr]) !== gettype($value)
            ) {
                // TODO: Re-evalute exception usage.
                $type1 = gettype($arr[$curr]);
                $type2 = gettype($value);
                $message = "Can not change value type [$type1 => $type2]: ";
                $message .= "{$prefix}{$current}";
                throw new Exception($message);
            } else {
                $arr[$current] = $value;
            }
        } else {
            $message = "Invalid key encountered: {$key}";
        }
    }
}
