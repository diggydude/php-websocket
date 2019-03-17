<?php

  require_once(__DIR__ . '/TcpServer.php');

  abstract class WebSocketServer extends TcpServer
  {

    protected

      $pending;

    public function __construct($params)
    {
      parent::__construct($params);
      $this->pending = array();
    } // __construct

    abstract protected function processRequest($request);

    protected function onIterate()
    {
      usleep(10000);
    } // onIterate

    protected function onConnect($socket, $peerName)
    {
      echo "Client at $peerName connected.\n";
      $this->pending[] = (int) $socket;
    } // onConnect

    protected function onDisconnect($socket, $peerName)
    {
      echo "Client at $peerName disconnected.\n";
    } // onDisconnect

    protected function onReceive($socket, $data)
    {
      if (in_array((int) $socket, $this->pending)) {
        if ($this->handshake($socket, $data) === false) {
          echo "Handshake failed.\n";
          $this->disconnect($socket);
        }
        $key = array_search($socket, $this->pending);
        unset($this->pending[$key]);
        return;
      }
      $request  = $this->unmask($data);
      $response = $this->processRequest($request);
      $this->send($socket, $this->encode($response));
    } // onReceive

    protected function handshake($client, $headers)
    {
      if (preg_match("/Sec-WebSocket-Version: (.*)\r\n/", $headers, $matches) === false) {
        echo "The client doesn't support WebSocket.\n";
        return false;
      }
      if ((int) $matches[1] != 13) {
        echo "WebSocket version " . $matches[1] . " is unsupported.\n";
        return false;
      }
      if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $matches) === false) {
        echo "Sec-WebSocket-Key header required.\n";
        return false;
      }
      $headers = "HTTP/1.1 101 Switching Protocols\r\n"
               . "Upgrade: websocket\r\n"
               . "Connection: Upgrade\r\n"
               . "Sec-WebSocket-Accept: "
               . base64_encode(sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true))
               . "\r\n\r\n";
      $this->send($client, $headers);
      return true;
    } // handshake

    protected function unmask($payload) {
      $length = ord($payload[1]) & 127;
      $text   = "";
      if ($length == 126) {
        $masks = substr($payload, 4, 4);
        $data  = substr($payload, 8);
      }
      else if ($length == 127) {
        $masks = substr($payload, 10, 4);
        $data  = substr($payload, 14);
      }
      else {
        $masks = substr($payload, 2, 4);
        $data  = substr($payload, 6);
      }
      for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
      }
      return $text;
    } // unmask

    protected function encode($text)
    {
      $byte   = 0x80 | (0x1 & 0x0f);
      $length = strlen($text);
      if ($length <= 125) {
        $header = pack('CC', $byte, $length);
      }
      else if ($length >= 65536) {
        $header = pack('CCN', $byte, 127, $length);
      }
      else {
        $header = pack('CCS', $byte, 126, $length);
      }
      return $header . $text;
    } // encode

  } // WebSocketServer

?>