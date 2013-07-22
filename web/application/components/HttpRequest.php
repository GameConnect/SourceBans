<?php
/**
 * @property string $serverHostAddress Server IP address.
 * @property string $serverHost Server host name, null if cannot be determined.
 */
class HttpRequest extends CHttpRequest
{
	/**
	 * Returns the server IP address.
	 * @return string server IP address
	 */
	public function getServerHostAddress()
	{
		if(isset($_SERVER['SERVER_ADDR']))
			return $_SERVER['SERVER_ADDR']!=='::1'?$_SERVER['SERVER_ADDR']:'127.0.0.1';
		
		if(isset($_SERVER['LOCAL_ADDR']))
			return $_SERVER['LOCAL_ADDR']!=='::1'?$_SERVER['LOCAL_ADDR']:'127.0.0.1';
		
		if(isset($_SERVER['SERVER_NAME']))
			return gethostbyname($_SERVER['SERVER_NAME']);
		
		// Running CLI
		if(stripos(PHP_OS,'WIN')!==false)
			return gethostbyname(php_uname('n'));
		
		$ifconfig=shell_exec('/sbin/ifconfig eth0');
		preg_match('/addr:([\d\.]+)/',$ifconfig,$match);
		return $match[1];
	}

	/**
	 * Returns the server host name, null if it cannot be determined.
	 * @return string server host name, null if cannot be determined
	 */
	public function getServerHost()
	{
		return isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:null;
	}
}