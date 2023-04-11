<?php
namespace APP\Extend;
class SocketIOtest
{
    public $fd;

    public function init($host = null, $port = null, $address = "/socket.io/?EIO=2", $transport = 'websocket'){
        $fd = fsockopen($host, $port, $errno, $errstr);
        $this->fd = $fd;
        if (!$this->fd) {
            return false;
        } //Can't connect tot server
        $key = $this->generateKey();
        $out = "GET $address&transport=$transport HTTP/1.1\r\n";
        $out .= "Host: http://$host:$port\r\n";
        $out .= "Upgrade: WebSocket\r\n";
        $out .= "Connection: Upgrade\r\n";
        $out .= "Sec-WebSocket-Key: $key\r\n";
        $out .= "Sec-WebSocket-Version: 13\r\n";
        $out .= "Origin: *\r\n\r\n";
        // var_dump($out);
        // echo "\r\n";
        fwrite($this->fd, $out);
        // 101 switching protocols, see if echoes key
        $result = fread($this->fd, 10000);
        // var_dump($result);
        // echo "\r\n";
        preg_match('#Sec-WebSocket-Accept:\s(.*)$#mU', $result, $matches);
        $keyAccept = trim($matches[1]);
        $expectedResonse = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $handshaked = ($keyAccept === $expectedResonse) ? true : false;
        if ($handshaked) {
            // fwrite($this->fd, $this->hybi10Encode(addslashes(null)));
            return $this->fd;
        } else {
            return false;
        }
    }
    public function send( $action = "message", $data = null)
    {

        if ($action){
            fwrite($this->fd, $this->hybi10Encode('42["' . $action . '", "' . addslashes($data) . '"]'));
        }else{
            return;
            // fwrite($this->fd, $this->hybi10Encode(addslashes($data)));
        }
         fwrite($this->fd, $this->hybi10Encode(addslashes($data)));
        $content = '';
        while (!feof($this->fd)) {
            stream_set_timeout($this->fd, 1);
            $content .= fread($this->fd, 1024);
            $info = stream_get_meta_data($this->fd);
            if ($info['timed_out']) {
                break;
            }
        }
        //fread($fd,1000000);
         fclose($this->fd);
        return true;
    }

    private function generateKey($length = 16)
    {
        $c = 0;
        $tmp = '';
        while ($c++ * 16 < $length) {
            $tmp .= md5(mt_rand(), true);
        }
        return base64_encode(substr($tmp, 0, $length));
    }

    private function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);
        switch ($type) {
            case 'text':
                $frameHead[0] = 129;
                break;
            case 'close':
                $frameHead[0] = 136;
                break;
            case 'ping':
                $frameHead[0] = 137;
                break;
            case 'pong':
                $frameHead[0] = 138;
                break;
        }
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            if ($frameHead[2] > 127) {
                $this->close(1004);
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }
}
