<?php

namespace SourceBans\CoreBundle\Util;

/**
 * Send and receive Source RCON packets
 */
class SourceRcon
{
    const SERVERDATA_EXECCOMMAND    = 02;
    const SERVERDATA_AUTH           = 03;
    const SERVERDATA_RESPONSE_VALUE = 00;
    const SERVERDATA_AUTH_RESPONSE  = 02;

    private $fsock  = false;
    private $hl1    = false;
    private $id     = 0;
    private $password;
    private $socket = null;

    /**
     * Constructor
     *
     * @param string $host IP address or domain name
     * @param int $port port number
     * @param string $password RCON password
     */
    public function __construct($host, $port = 27015, $password = null)
    {
        $this->password = $password;

        if (!$this->connect($host, $port) && $this->connect($host, $port, SOL_UDP)) {
            $this->hl1 = true;
        }
    }

    /**
     * Authenticates to the server
     *
     * @return boolean whether the authentication was successful
     */
    public function auth()
    {
        if ($this->hl1) {
            return true;
        }

        // HL2
        $this->write(self::SERVERDATA_AUTH, $this->password); // PackID
        $ret = $this->readPacket();
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
        if (!$this->hl1) {
            $this->write(self::SERVERDATA_EXECCOMMAND, $command, '');
            $ret = $this->read();
            return $ret[2]['S1'];
        }

        // HL1
        fputs($this->socket, $command, strlen($command));

        // Get results from server
        $buffer  = fread($this->socket, 1);
        $status  = socket_get_status($this->socket);
        $buffer .= fread($this->socket, $status['unread_bytes']);

        // If there is another package waiting
        if (substr($buffer, 0, 4) == "\xfe\xff\xff\xff") {
            // Get results from server
            $buffer2  = fread($this->socket, 1);
            $status   = socket_get_status($this->socket);
            $buffer2 .= fread($this->socket, $status['unread_bytes']);

            // If the second one came first
            if (strlen($buffer) > strlen($buffer2)) {
                return substr($buffer, 14) . substr($buffer2, 9);
            }
            return substr($buffer2, 14) . substr($buffer, 9);
        }

        // In case there is only one package
        return substr($buffer, 5);
    }

    private function connect($host, $port, $protocol = SOL_TCP)
    {
        try {
            if (defined('BIND_IP') && function_exists('socket_create') && function_exists('socket_bind')) {
                if (!($this->socket = socket_create(AF_INET, SOCK_STREAM, $protocol))) {
                    return false;
                }

                socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
                socket_bind($this->socket, BIND_IP);

                if (!socket_connect($this->socket, $host, $port)) {
                    return false;
                }

                socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 2, 'usec' => 0]);
                socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);
            } else {
                if ($protocol == SOL_UDP) {
                    $host = 'udp://' . $host;
                }

                if (!($this->socket = @fsockopen($host, $port, $errno, $errstr, 2))) {
                    return false;
                }

                stream_set_timeout($this->socket, 2);
                $this->fsock = true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    private function readPacket()
    {
        $packets = [];
        while ($size = $this->readSocket(4)) {
            $size = unpack('V1Size', $size);
            if ($size['Size'] > 4096) {
                $packet = "\x00\x00\x00\x00\x00\x00\x00\x00" . $this->readSocket(4096);
            } else {
                $packet = $this->readSocket($size['Size']);
            }
            $packets[] = unpack("V1ID/V1Reponse/Z*S1/Z*S2", $packet);
        }
        return $packets;
    }

    private function readSocket($size)
    {
        if ($this->fsock) {
            return @fread($this->socket, $size);
        }

        return socket_read($this->socket, $size);
    }

    private function read()
    {
        $packets = $this->readPacket();
        foreach ($packets as $pack) {
            if (isset($ret[$pack['ID']])) {
                $ret[$pack['ID']]['S1'] .= $pack['S1'];
                $ret[$pack['ID']]['S2'] .= $pack['S2'];
            } else {
                $ret[$pack['ID']] = [
                    'Reponse' => $pack['Reponse'],
                    'S1'      => $pack['S1'],
                    'S2'      => $pack['S2'],
                ];
            }
        }
        return $ret;
    }

    private function write($cmd, $s1 = '', $s2 = '')
    {
        $id   = ++$this->id;
        $data = pack('VV', $id, $cmd) . $s1 . chr(0) . $s2 . chr(0);
        $data = pack('V', strlen($data)) . $data;

        if ($this->fsock) {
            fwrite($this->socket, $data, strlen($data));
        } else {
            socket_write($this->socket, $data, strlen($data));
        }

        return $id;
    }
}
