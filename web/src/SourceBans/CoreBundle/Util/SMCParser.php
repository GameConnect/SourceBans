<?php

namespace SourceBans\CoreBundle\Util;

/**
 * Event-based SourceMod Config parser
 */
class SMCParser
{
    /**
     * Parses a SourceMod Config string or file
     *
     * @param string $string String or file to load
     * @param SMCListenerInterface $listener Listener to use for callbacks
     * @return boolean Whether the parsing was successful
     */
    public static function parse($string, SMCListenerInterface $listener)
    {
        if (is_readable($string)) {
            $string = file_get_contents($string);
        }

        // Use token_get_all() to easily ignore comments and whitespace
        $tokens = token_get_all("<?php\n" . $string . "\n?>");
        $level  = 0;
        $key    = null;

        foreach ($tokens as $token) {
            // Enter section
            if ($token == '{') {
                if ($level++ && $listener->onEnterSection($key) === false) {
                    return false;
                }

                $key = null;
            }
            // Leave section
            elseif ($token == '}') {
                if (--$level && $listener->onLeaveSection() === false) {
                    return false;
                }
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
                            if ($listener->onKeyValue($key, $value) === false) {
                                return false;
                            }

                            $key = null;
                        }
                }
            }
        }

        return true;
    }
}
