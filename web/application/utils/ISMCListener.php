<?php
/**
 * Listener to use when parsing a SourceMod Config file
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
interface ISMCListener
{
	/**
	 * Called when a section ends
	 *
	 * @return boolean Whether to continue parsing
	 */
	public function EndSection();

	/**
	 * Called when a key value pair is parsed
	 *
	 * @param string $key Key
	 * @param string $value Value
	 * @return boolean Whether to continue parsing
	 */
	public function KeyValue($key, $value);

	/**
	 * Called when a new section begins
	 *
	 * @param string $name Section name
	 * @return boolean Whether to continue parsing
	 */
	public function NewSection($name);
}