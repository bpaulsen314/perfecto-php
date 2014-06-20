<?php
namespace Perfecto;

use \Exception as PhpException;

class StringHelper extends AbstractSingleton
{
    protected $_plural_map = array();

    public function camelNotate($str, $lowercase_first = false)
    {
        $result = "";
        $words = $this->_getWords($str);
        foreach ($words as $word) {
            if (empty($result) && $lowercase_first) {
                $result .= strtolower($word);
            } else {
                $result .= strtoupper(substr($word, 0, 1)) . 
                    strtolower(substr($word, 1));
            }
        }
        return $result; 
    }

    public function dashNotate($str)
    {
        $words = $this->_getWords($str);
        return strtolower(implode("-", $words));
    }

    public function depluralize($str)
    {
        static $cache = array();

        $result = $str;

        if (array_key_exists($str, $cache)) {
            $result = $cache[$str];
        } else {
            $words = $this->_getWords($str);
            $last_word = array_pop($words);

            $singular = null;
            $plural = strtolower($last_word);
            $from_plural_map = $this->_plural_map["from"];
            if (array_key_exists($plural, $from_plural_map)) {
                $singular = $from_plural_map[$plural];
            } else {
                if (preg_match("#(ch|[^aeiou]o|s|sh|x|z)es$#", $plural)) {
                    $singular = substr($plural, 0, -2);
                } else if (preg_match("#[aeiou]ves$#", $plural)) {
                    $singular = substr($plural, 0, -3) . "fe";
                } else if (preg_match("#[^aeiou]ves$#", $plural)) {
                    $singular = substr($plural, 0, -3) . "f";
                } else if (preg_match("#([^aeiou]|qu)ies$#", $plural)) {
                    $singular = substr($plural, 0, -3) . "y";
                } else if (preg_match("#s$#", $plural)) {
                    $singular = substr($plural, 0, -1);
                }
            }

            if ($singular) {
                $new_last_word = strtolower($singular);
                if (!preg_match("#[a-z]#", $last_word)) {
                    $new_last_word = strtoupper($singular);
                } else if (preg_match("#^[A-Z]#", $last_word)) {
                    $new_last_word = strtoupper(substr($singular, 0, 1)) . 
                        strtolower(substr($singular, 1));
                }
                $result = preg_replace("#{$last_word}$#", $new_last_word, $str);
            }

            $cache[$str] = $result;
        }

        return $result;
    }

    public function pluralize($str)
    {
        static $cache = array();

        $result = $str;

        if (array_key_exists($str, $cache)) {
            $result = $cache[$str];
        } else {
            $words = $this->_getWords($str);
            $last_word = array_pop($words);

            $plural = null;
            $singular = strtolower($last_word);
            $to_plural_map = $this->_plural_map["to"];
            if (array_key_exists($singular, $to_plural_map)) {
                $plural = $to_plural_map[$singular];
            } else {
                if (preg_match("#(ch|[^aeiou]o|s|sh|x|z)$#", $singular)) {
                    $plural = "{$singular}es";
                } else if (preg_match("#[^aeiou][aeiou]?f$#", $singular)) {
                    $plural = substr($singular, 0, -1) . "ves";
                } else if (preg_match("#[^aeiou][aeiou]?fe$#", $singular)) {
                    $plural = substr($singular, 0, -2) . "ves";
                } else if (preg_match("#([^aeiou]|qu)y$#", $singular)) {
                    $plural = substr($singular, 0, -1) . "ies";
                } else {
                    $plural = "{$singular}s";
                }
            }

            if ($plural) {
                $new_last_word = strtolower($plural);
                if (!preg_match("#[a-z]#", $last_word)) {
                    $new_last_word = strtoupper($plural);
                } else if (preg_match("#^[A-Z]#", $last_word)) {
                    $new_last_word = strtoupper(substr($plural, 0, 1)) . 
                        strtolower(substr($plural, 1));
                }
                $result = preg_replace("#{$last_word}$#", $new_last_word, $str);
            }

            $cache[$str] = $result;
        }

        return $result;
    }

    public function registerPlural($singular, $plural)
    {
        $singular = strtolower($singular);
        $plural = strtolower($plural);
        $this->_plural_map["from"][$plural] = $singular;
        $this->_plural_map["to"][$singular] = $plural;
    }

    public function underscoreNotate($str)
    {
        $words = $this->_getWords($str);
        return strtolower(implode("_", $words));
    }

    protected function __construct()
    {
        $this->_initializePluralMap();
    }

    protected function _initializePluralMap()
    {
        $this->_plural_map = array("from" => array(), "to" => array());
        $this->registerPlural("child", "children");
        $this->registerPlural("course", "courses");
        $this->registerPlural("man", "men");
        $this->registerPlural("person", "people");
        $this->registerPlural("woman", "women");
    }

    private function _getWords($str)
    {
        $str = preg_replace("#[A-Z]+[a-z]#", "_\\0", $str);
        $words = preg_split("#[_-]+#", $str);
        $real_words = array();
        foreach ($words as $word) {
            if (trim($word) !== "") {
                $real_words[] = $word;
            }
        }
        return $real_words;
    }
}
