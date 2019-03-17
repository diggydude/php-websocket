<?php

  class TcpClient
  {

    protected

      $address,
      $port,
      $socket,
      $lastError;

    public function __construct($params)
    {
      $this->address   = $params->address;
      $this->port      = $params->port;
      $this->socket    = null;
      $descriptor      = "tcp://" .$this->address . ":" . $this->port;
      if (($this->socket = stream_socket_client($descriptor, $errNum, $errMsg, 5, STREAM_CLIENT_CONNECT)) === false) {
        $this->lastError = $errMsg;
      }
      stream_set_blocking($this->socket, false);
    } // __construct

    public function send($data)
    {
      @fputs($this->socket, $data);
      return true;
    } // send

    protected function receive()
    {
	  $data    = "";
	  $timeout = microtime(true) + .001;
	  while (microtime(true) < $timeout) {
		if (strlen($data) > 0) {
		  break;
		}
        $data = stream_get_contents($this->socket);
		usleep(10000);
	  }
      if (strlen($data) == 0) {
        return false;
      }
      return trim($data);
    } // receive

    public function disconnect()
    {
      stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
      fclose($this->socket);
      $this->socket = null;
    } // disconnect

    public function isConnected()
    {
      if (@feof($this->socket)) {
        $this->lastError = "Not connected.";
        return false;
      }
      return true;
    } // isConnected

    public function getLastError()
    {
      $message = $this->lastError;
      $this->lastError = "";
      return $message;
    } // getLastError

  } // TcpClient

?>