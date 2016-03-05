<?php

namespace SourceBans\CoreBundle\Util;

/**
 * Query Source and GoldSrc servers for info, players and rules
 */
class SourceQuery
{
    // Constants of the data we need for a query
    const QUERY_HEADER                          = "\xFF\xFF\xFF\xFF";
    const A2A_PING                              = "\x69";
    const A2A_PING_RESPONSE                     = "\x6A";
    const A2S_SERVERQUERY_GETCHALLENGE          = "\x57";
    const A2S_SERVERQUERY_GETCHALLENGE_RESPONSE = "\x41";
    const A2S_INFO                              = "\x54Source Engine Query\0";
    const A2S_INFO_RESPONSE                     = "\x49";
    const A2S_INFO_RESPONSE_OLD                 = "\x6D";
    const A2S_PLAYER                            = "\x55";
    const A2S_PLAYER_RESPONSE                   = "\x44";
    const A2S_RULES                             = "\x56";
    const A2S_RULES_RESPONSE                    = "\x45";

    private $host;
    private $port;
    private $socket    = false;
    private $challenge = self::QUERY_HEADER;
    private $raw;

    /**
     * Constructor
     *
     * @param string $host IP address or domain name
     * @param int $port port number
     */
    public function __construct($host, $port = 27015)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Returns the A2S_INFO response
     *
     * @return array
     */
    public function getInfo()
    {
        $socket = $this->getSocket();
        $packet = $this->request($socket, self::A2S_INFO);
        if (empty($packet)) {
            return [];
        }

        $this->raw = $packet;
        $type      = $this->getraw(1);
        $info      = [];

        if ($type == self::A2S_INFO_RESPONSE) { // New protocol for Source and Goldsrc
            $this->getbyte();   // Version
            $info['hostname']   = $this->getnullstr();
            $info['map']        = $this->getnullstr();
            $info['gamename']   = $this->getnullstr();
            $info['gamedesc']   = $this->getnullstr();
            $this->getushort(); // AppId
            $info['numplayers'] = $this->getbyte();
            $info['maxplayers'] = $this->getbyte();
            $info['botcount']   = $this->getbyte();
            $info['dedicated']  = $this->getraw(1);
            $info['os']         = $this->getraw(1);
            $info['password']   = $this->getbyte();
            $info['secure']     = $this->getbyte();
        } elseif ($type == self::A2S_INFO_RESPONSE_OLD) { // Legacy Goldsrc support
            $this->getnullstr(); // GameIP
            $info['hostname']   = $this->getnullstr();
            $info['map']        = $this->getnullstr();
            $info['gamename']   = $this->getnullstr();
            $info['gamedesc']   = $this->getnullstr();
            $info['numplayers'] = $this->getbyte();
            $info['maxplayers'] = $this->getbyte();
            $this->getbyte();    // Version
            $info['dedicated']  = $this->getraw(1);
            $info['os']         = $this->getraw(1);
            $info['password']   = $this->getbyte();
            if ($this->getbyte()) { // IsMod
                $this->getnullstr();
                $this->getnullstr();
                $this->getbyte();
                $this->getlong();
                $this->getlong();
                $this->getbyte();
                $this->getbyte();
            }
            $info['secure']     = $this->getbyte();
            $info['botcount']   = $this->getbyte();
        }

        return $info;
    }

    /**
     * Returns the A2S_PLAYER response
     *
     * @return array
     */
    public function getPlayers()
    {
        $socket = $this->getSocket();
        $packet = $this->requestWithChallenge($socket, self::A2S_PLAYER, self::A2S_PLAYER_RESPONSE);
        if (empty($packet)) {
            return [];
        }

        $this->raw = $packet;
        $count     = $this->getbyte();
        $players   = [];

        for ($i = 0; $i < $count; $i++) {
            $this->getbyte();    // Index

            $player = [
                'name'  => $this->getnullstr(),
                'score' => $this->getlong(),
                'time'  => (int)$this->getfloat(),
            ];

            if ($player['name'] != '') {
                $players[] = $player;
            }
        }

        return $players;
    }

    /**
     * Returns the A2S_RULES response
     *
     * @return array
     */
    public function getRules()
    {
        $socket = $this->getSocket();
        $packet = $this->requestWithChallenge($socket, self::A2S_RULES, self::A2S_RULES_RESPONSE);
        if (empty($packet)) {
            return [];
        }

        $this->raw = $packet;
        $count      = $this->getushort();
        $rules      = [];

        for ($i = 0; $i < $count; $i++) {
            $name  = $this->getnullstr();
            $value = $this->getnullstr();

            if (!empty($name)) {
                $rules[$name] = $value;
            }
        }

        ksort($rules);
        return $rules;
    }

    private function getSocket()
    {
        if ($this->socket !== false) {
            return $this->socket;
        }

        $this->socket = fsockopen('udp://' . $this->host, $this->port);
        if ($this->socket === false) {
            return false;
        }

        stream_set_timeout($this->socket, 1);
        return $this->socket;
    }

    private function request($socket, $code, $reply = null)
    {
        fwrite($socket, self::QUERY_HEADER . $code);
        $packet = $this->readsplit($socket);
        if (empty($packet)) {
            return '';
        }

        $this->raw = $packet;
        $magic     = $this->getlong();
        if ($magic != -1) {
            return '';
        }

        $response = $this->getraw(1);
        if ($reply == null) {
            return substr($packet, 4);  // Skip magic as it was checked
        }
        if ($response == $reply) {
            return substr($packet, 5);  // Skip magic and type as it was checked
        }

        return '';
    }

    private function requestWithChallenge($socket, $code, $reply = null)
    {
        $maxretries = 5;
        while (--$maxretries >= 0) {
            fwrite($socket, self::QUERY_HEADER . $code . $this->challenge); // do the request with challenge id = -1
            $packet = $this->readsplit($socket);
            if (empty($packet)) {
                return '';
            }

            $this->raw = $packet;
            $magic     = $this->getlong();
            if ($magic != -1) {
                return '';
            }

            $response = $this->getraw(1);
            if ($response == self::A2S_SERVERQUERY_GETCHALLENGE_RESPONSE) {
                $this->challenge = $this->getraw(4);
            } elseif ($reply == null) {
                return substr($packet, 4);  // Skip magic as it was checked
            } elseif ($response == $reply) {
                return substr($packet, 5);  // Skip magic and type as it was checked
            }
        }

        return '';
    }

    private function readsplit($socket)
    {
        $packet = fread($socket, 1480);
        if (empty($packet)) {
            return '';
        }

        $this->raw = $packet;
        $type      = $this->getlong();

        if ($type == -2) {
            // Parse first header
            $reqid      = $this->getlong();
            $packets    = $this->getushort();
            $numpackets = $packets & 0xFF;
            $curpacket  = $packets >> 8;
            if ($reqid >= 0) { // Dummy value telling how big the split is (hardcoded to 1248), Orangebox or later
                $this->skip(2);
            }
            $data   = [];
            $tstart = microtime(true);

            // Sanity
            if ($curpacket >= $numpackets) {
                return '';
            }

            // Compressed?
            if (!$curpacket && $reqid < 0) {
                $sizeuncompressed = $this->getlong();
                $crc              = $this->getlong();
            }

            while (true) {
                // Split already received (duplicate)?
                if (!array_key_exists($curpacket, $data)) {
                    $data[$curpacket] = $this->raw;
                }

                // Finished?
                if (count($data) >= $numpackets) {
                    // Join the parts
                    ksort($data);
                    $data = implode('', $data);

                    // Uncompress if necessary
                    if ($reqid < 0) {
                        $data = bzdecompress($data);
                        if (strlen($data) != $sizeuncompressed) {
                            return '';
                        }

                        // TODO: CRC32 check
                        return $data;
                    }

                    // Not compressed
                    return $data;
                }

                // Check the timeout over several receives
                if (microtime(true) - $tstart >= 2.0) { // 2s
                    return '';
                }

                // Receive next packet
                $packet = fread($socket, 1480);
                if (empty($packet)) {
                    return '';
                }

                // Parse packet
                $this->raw = $packet;
                $_type     = $this->getlong();
                if ($_type != -2) {
                    return '';
                }

                $_reqid      = $this->getlong();
                $_packets    = $this->getushort();
                $_numpackets = $_packets & 0xFF;
                $curpacket   = $_packets >> 8;
                if ($reqid >= 0) { // Dummy value telling how big the split is (hardcoded to 1248), OrangeBox or later
                    $this->skip(2);
                }

                // Sanity check
                if ($_reqid != $reqid || $_numpackets != $numpackets || $curpacket >= $numpackets) {
                    return '';
                }

                // Compressed?
                if (!$curpacket && $reqid < 0) {
                    $sizeuncompressed = $this->getlong();
                    $crc              = $this->getlong();
                }
            }
        } elseif ($type == -1) {
            // Non-split packet
            return $packet;
        }

        // Invalid
        return '';
    }

    private function getraw($count)
    {
        $data = substr($this->raw, 0, $count);
        $this->skip($count);
        return $data;
    }

    private function getbyte()
    {
        $byte = $this->getraw(1);
        return ord($byte);
    }

    private function getfloat()
    {
        $float = @unpack('f1float', $this->getraw(4));
        return $float['float'];
    }

    private function getlong()
    {
        $low  = $this->getushort();
        $high = $this->getushort();
        $long = ($high << 16) | $low;
        if ($long & 0x80000000 && $long > 0) { // This is special for register size >32 bits
            return -((~$long & 0xFFFFFFFF) + 1);
        }
        return $long;                          // 32-bit handles negative values implicitly
    }

    private function getnullstr()
    {
        if (empty($this->raw)) {
            return '';
        }

        $end = strpos($this->raw, "\0");
        $str = substr($this->raw, 0, $end);
        $this->skip($end + 1);
        return $str;
    }

    private function getushort()
    {
        $low   = $this->getbyte();
        $high  = $this->getbyte();
        $short = ($high << 8) | $low;
        return $short;
    }

    private function getshort()
    {
        $short = $this->getushort();
        if ($short & 0x8000) {
            return -((~$short & 0xFFFF) + 1);
        }
        return $short;
    }

    private function skip($count)
    {
        $this->raw = substr($this->raw, $count);
    }
}
