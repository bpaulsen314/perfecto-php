<?php
namespace W3glue\Perfecto;

use \Exception as PhpException;

class Exception extends PhpException
{
    protected $_error_number = null;

    public final function getErrorNumber()
    {
        return $this->_error_number;
    }

    public final function setErrorNumber($error_number)
    {
        $this->_error_number = $error_number;
        $this->setMessage($this->message);
    }

    public final function setMessage($message)
    {
        if ($this->_error_number) {
            $message_lines = explode(": ", $message);
            $error_name = $this->_getErrorName($this->_error_number);
            if ($message_lines[0] !== $error_name) {
                array_unshift($message_lines, $error_name);
            }
            $message = implode(": ", $message_lines);
        }

        $this->message = $message;
    }

    public final function setFile($file)
    {
        $this->file = $file;
    }

    public final function setLine($line)
    {
        $this->line = $line;
    }

    protected final function _getErrorName($error_number)
    {
        $name = "UNRECOGNIZED ERROR";

        $error_names = array(
            E_ERROR => "ERROR",
            E_WARNING => "WARNING",
            E_PARSE => "PARSING ERROR",
            E_NOTICE => "NOTICE",
            E_CORE_ERROR => "CORE ERROR",
            E_CORE_WARNING => "CORE WARNING",
            E_COMPILE_ERROR => "COMPILE ERROR",
            E_COMPILE_WARNING => "COMPILE WARNING",
            E_USER_ERROR => "USER ERROR",
            E_USER_WARNING => "USER WARNING",
            E_USER_NOTICE => "USER NOTICE",
            E_STRICT => "STRICT NOTICE",
            E_RECOVERABLE_ERROR => "RECOVERABLE ERROR",
            E_DEPRECATED => "DEPRECATED WARNING",
        );

        if (array_key_exists($error_number, $error_names)) {
            $name = "PHP " . $error_names[$error_number];
        }

        return $name;
    }
}
