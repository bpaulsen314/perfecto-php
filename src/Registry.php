<?php
namespace W3glue\Perfecto;

class Registry extends AbstractSingleton
{
    protected $_data;

    public function clear()
    {
        return $this->delete();
    }

    public function delete($key = null)
    {
        $deleted = true;
        if (is_null($key)) {
            $this->_data = [];
        } else {
            $helper = ArrayHelper::getInstance();
            $deleted = $helper->delete($this->_data, $key);
        }
        return $deleted;
    }

    public function dump($key = null, $return = false)
    {
        $data = $this->get($key);
        $result = var_export($data, $return);
        if ($return) {
            $result .= "\n";
        } else {
            echo "\n";
        }
        return $result;
    }

    public function get($key = null)
    {
        $value = $this->_data;
        if (!is_null($key)) {
            $helper = ArrayHelper::getInstance();
            $value = $helper->getValue($this->_data, $key);
        }
        return $value;
    }

    public function import($filename, $prefix = "", $ignore_unsupported = false, $root = true)
    {
        $files_imported = [];

        if (file_exists($filename)) {
            $filename = realpath($filename);

            if (is_dir($filename)) {
                // directory import
                if (!$root) {
                    if ($prefix !== "") {
                        $prefix .= ".";
                    }
                    $prefix .= preg_replace("#\..*$#", "", basename($filename));
                }
                $entries = scandir($filename);
                foreach ($entries as $entry) {
                    if (!preg_match("#^\.#", $entry)) {
                        $entries_imported = $this->import(
                            "$filename/$entry", $prefix, true, false
                        );
                        $files_imported = array_merge(
                            $files_imported, $entries_imported
                        );
                    }
                }
            } else if ($filename) {
                // file import
                preg_match("#\.(?<ext>[A-Za-z0-9]+)$#", $filename, $matches);
                $ext = isset($matches["ext"]) ? $matches["ext"] : "unrecognized";

                $import_method = "_import" . ucfirst(strtolower($ext));
                if (method_exists($this, $import_method)) {
                    $this->{$import_method}($filename, $prefix);
                    $files_imported[] = $filename;
                } else if (!$ignore_unsupported) {
                    // TODO: Re-evaluate exception usage.
                    $message = "Can not import unsupported file: $filename";
                    throw new Exception($message);
                }
            }
        } else {
            // TODO: Re-evaluate exception usage.
            $message = "File / directory does not exist: $filename";
            throw new Exception($message);
        }

        return $files_imported;
    }

    public function set($key, $value)
    {
        $current = $this->get($key);
        if ($current !== false) {
            // TODO: Re-evalute exception usage.
            $message  = "Can not set the value of an already defined key: $key";
            throw new Exception($message);
        } else {
            $helper = ArrayHelper::getInstance();
            $helper->setValue($this->_data, $key, $value, true);
        }
    }

    protected function __construct()
    {
        $this->clear();
    }

    private function _importYaml($filename, $prefix = "")
    {
        $data = yaml_parse_file($filename);
        if ($data) {
            $key = preg_replace("#\..*$#", "", basename($filename));
            if ($prefix !== "") {
                $key = "{$prefix}.$key";
            }
            $this->set($key, $data);
        } else {
            // TODO: Re-evaluate exception usage.
            $message = "Could not parse file: $filename";
            throw new Exception($message);
        }
    }

    private function _importYml($filename, $prefix = "")
    {
        $this->_importYaml($filename, $prefix);
    }
}
