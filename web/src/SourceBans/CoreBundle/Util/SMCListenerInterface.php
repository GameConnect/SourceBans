<?php

namespace SourceBans\CoreBundle\Util;

/**
 * Listener for parsing a SourceMod Config file
 */
interface SMCListenerInterface
{
    /**
     * Called when entering a section
     *
     * @param string $name Section name
     * @return boolean Whether to continue parsing
     */
    public function onEnterSection($name);

    /**
     * Called when leaving a section
     *
     * @return boolean Whether to continue parsing
     */
    public function onLeaveSection();

    /**
     * Called when parsing a key value pair
     *
     * @param string $key Key
     * @param string $value Value
     * @return boolean Whether to continue parsing
     */
    public function onKeyValue($key, $value);
}
