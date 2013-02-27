<?php
/**
 * Send and receive RCON packets
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class ServerRcon
{
	const SERVERDATA_EXECCOMMAND    = 02;
	const SERVERDATA_AUTH           = 03;
	const SERVERDATA_RESPONSE_VALUE = 00;
	const SERVERDATA_AUTH_RESPONSE  = 02;
	
	private $_hl1  = false;
	private $_id   = 0;
	private $_password;
	private $_sock = null;
	
	
	/**
	 * Constructor
	 * 
	 * @param string $host IP address or domain name
	 * @param integer $port port number
	 * @param string $password RCON password
	 */
	function __construct($host, $port, $password)
	{
		$this->_password = $password;
		$this->_sock     = @fsockopen($host, $port, $errno, $errstr, 2);
		
		// HL1
		if(!$this->_sock)
		{
			$this->_hl1  = true;
			$this->_sock = @fsockopen('udp://' . $host, $port, $errno, $errstr, 2);
		}
		
		stream_set_timeout($this->_sock, 2);
	}
	
	
	/**
	 * Authenticates to the server
	 * 
	 * @return boolean whether the authentication was successful
	 */
	public function auth()
	{
		if($this->_hl1)
			return true;
		
		// HL2
		$PackID = $this->_write(self::SERVERDATA_AUTH, $this->_password);
		$ret    = $this->_PacketRead();
		return !(isset($ret[1]['ID']) && $ret[1]['ID'] == -1);
	}
	
	
	/**
	 * Executes an RCON command
	 * 
	 * @param string $command the command to execute
	 * @return string the result
	 */
	public function execute($command)
	{
		// HL2
		if(!$this->hl1)
		{
			$this->_write(self::SERVERDATA_EXECCOMMAND, $command, '');
			$ret = $this->_read();
			return $ret[2]['S1'];
		}
		
		// HL1
		fputs($this->_sock, $command, strlen($command));
		
		// Get results from server
		$buffer  = fread($this->socket, 1);
		$status  = socket_get_status($this->socket);
		$buffer .= fread($this->socket, $status['unread_bytes']);
		
		// If there is another package waiting
		if(substr($buffer, 0, 4) == "\xfe\xff\xff\xff")
		{
			// Get results from server
			$buffer2  = fread($this->socket, 1);
			$status   = socket_get_status($this->socket);
			$buffer2 .= fread($this->socket, $status['unread_bytes']);
			
			// If the second one came first
			if(strlen($buffer) > strlen($buffer2))
			{
				$buffer = substr($buffer,  14) . substr($buffer2, 9);
			}
			else
			{
				$buffer = substr($buffer2, 14) . substr($buffer,  9);
			}
		}
		// In case there is only one package
		else
		{
			$buffer = substr($buffer, 5);
		}
		
		// Return unformatted result
		return $buffer;
	}
	
	
	private function _PacketRead()
	{
		$retarray = array();
		while($size = @fread($this->_sock, 4)) 
		{
			$size = unpack('V1Size', $size);
			if($size['Size'] > 4096)
			{
				$packet = "\x00\x00\x00\x00\x00\x00\x00\x00" . fread($this->_sock, 4096);
			}
			else
			{
				$packet = fread($this->_sock, $size['Size']);
			}
			$retarray[] = unpack("V1ID/V1Reponse/a*S1/a*S2", $packet);
		}
		return $retarray;
	}
	
	private function _read()
	{
		$packets = $this->_PacketRead();
		foreach($packets as $pack)
		{
			if(isset($ret[$pack['ID']])) 
			{
				$ret[$pack['ID']]['S1'] .= $pack['S1'];
				$ret[$pack['ID']]['S2'] .= $pack['S1'];
			}
			else
			{
				$ret[$pack['ID']] = array(
					'Reponse' => $pack['Reponse'],
					'S1'      => $pack['S1'],
					'S2'      => $pack['S2'],
				);
			}
		}
		return $ret;
	}
	
	private function _write($cmd, $s1 = '', $s2 = '')
	{
		$id   = ++$this->_id;
		$data = pack('VV', $id, $cmd) . $s1 . chr(0) . $s2 . chr(0);
		$data = pack('V',  strlen($data)) . $data;
		fwrite($this->_sock, $data, strlen($data));
		return $id;
	}
}