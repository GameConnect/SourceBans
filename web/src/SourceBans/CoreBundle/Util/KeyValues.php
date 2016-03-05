<?php

namespace SourceBans\CoreBundle\Util;

/**
 * Tree-based Valve KeyValues parser
 */
class KeyValues extends \ArrayObject
{
    /**
     * @var string Root section name
     */
    protected $name;

    /**
     * Constructor
     *
     * @param string $name Root section name
     * @param array $data Optional key values data
     */
    public function __construct($name = null, $data = null)
    {
        $this->name = $name;

        parent::__construct($data);
    }

    /**
     * Return a string representation of the data
     *
     * @return string The string representation of the data
     */
    public function __toString()
    {
        $ret  = $this->name . "\n{\n";
        $ret .= $this->build($this->toArray());
        $ret .= '}';

        return $ret;
    }

    /**
     * Return an array representation of the data
     *
     * @return array The array representation of the data
     */
    public function toArray()
    {
        return parent::getArrayCopy();
    }

    /**
     * Return the root section name
     *
     * @return array The root section name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Load key values data from a string or file
     *
     * @param string $string String or file to load
     */
    public function load($string)
    {
        if (is_readable($string)) {
            $string = file_get_contents($string);
        }

        // Use token_get_all() to easily ignore comments and whitespace
        $tokens = token_get_all("<?php\n" . $string . "\n?>");
        $data   = $this->parse($tokens);
        // Strip root section
        $data   = reset($data);

        parent::exchangeArray($data);
    }

    /**
     * Save key values data to file
     *
     * @param string $file File to save to
     * @return boolean Whether saving was successful
     */
    public function save($file)
    {
        if (($handle = fopen($file, 'w+')) === false) {
            return false;
        }

        fwrite($handle, $this);
        fclose($handle);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize([
            'data' => parent::serialize(),
            'name' => $this->name,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($data)
    {
        $data       = unserialize($data);
        $this->name = $data['name'];

        parent::unserialize($data['data']);
    }

    /**
     * Recursively build key values string
     *
     * @param array $data Key values data to write
     * @param int $level Optional level to use for indenting
     * @return string
     */
    private function build($data, $level = null)
    {
        // Default level to 0
        if (!is_numeric($level)) {
            $level = 0;
        }

        $indent = str_repeat("\t", $level + 1);
        $ret    = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                reset($value);
                // If array is numerical, write key sub-value pairs
                if (is_int(key($value))) {
                    foreach ($value as $subValue) {
                        $ret .= sprintf("%s\"%s\"\t\"%s\"\n", $indent, $key, $subValue);
                    }
                }
                // Otherwise, recursively write section
                else {
                    $ret .= sprintf("%s\"%s\"\n%s{\n", $indent, $key, $indent);
                    $ret .= $this->build($value, $level + 1);
                    $ret .= $indent . "}\n";
                }
            }
            // Write key value pair
            else {
                $ret .= sprintf("%s\"%s\"\t\"%s\"\n", $indent, $key, $value);
            }
        }

        return $ret;
    }

    /**
     * Recursively parse key values data from tokens
     *
     * @param array $tokens Tokens received from token_get_all()
     * @return array
     */
    private function parse(&$tokens)
    {
        $data = [];
        $key  = null;

        // Use each() so the array cursor is also advanced
        // when the function is called recursively
        while (list(, $token) = each($tokens)) {
            // New section
            if ($token == '{') {
                // Recursively parse section
                $data[$key] = $this->parse($tokens);
                $key        = null;
            }
            // End section
            elseif ($token == '}') {
                return $data;
            }
            // Key or value
            else {
                $value = $token[1];
                switch ($token[0]) {
                    case T_CONSTANT_ENCAPSED_STRING:
                        // Strip surrounding quotes, then parse as a string
                        $value = substr($value, 1, -1);
                    case T_STRING:
                        // If key is not set, store
                        if (is_null($key)) {
                            $key = $value;
                        }
                        // Otherwise, it's a key value pair
                        else {
                            // If value is already set, treat as an array
                            // to allow multiple values per key
                            if (isset($data[$key])) {
                                // If value is not an array, cast
                                if (!is_array($data[$key])) {
                                    $data[$key] = (array)$data[$key];
                                }

                                // Add value to array
                                $data[$key][] = $value;
                            }
                            // Otherwise, store key value pair
                            else {
                                $data[$key] = $value;
                            }

                            $key = null;
                        }
                }
            }
        }

        return $data;
    }
}
