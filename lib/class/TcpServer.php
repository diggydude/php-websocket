<?php

  abstract class TcpServer
  {

    protected

      $address,
      $port,
      $clients,
      $socket,
      $listening,
      $running,
      $lastError;

    public function __construct($params)
    {
      $this->address   = $params->address;
      $this->port      = $params->port;
      $this->clients   = array();
      $this->listening = false;
      $this->running   = false;
      $this->lastError = "";
      $descriptor      = "tcp://" .$this->address . ":" . $this->port;
      if (($this->socket = @stream_socket_server($descriptor, $errNum, $errMsg)) === false) {
        $this->lastError = $errMsg;
        return;
      }
      stream_set_blocking($this->socket, false);
      $this->listening = true;
    } // __construct

    abstract protected function onIterate();

    abstract protected function onConnect($socket, $peerName);

    abstract protected function onDisconnect($socket, $peerName);

    abstract protected function onReceive($socket, $data);

    public function run()
    {
      echo "Starting server...\n";
      if (!$this->isListening()) {
        echo "The listening socket wasn't initialized:\n";
        echo $this->lastError();
        return false;
      }
      $this->running = true;
      echo "The server is running.\n";
      while ($this->isRunning()) {
        $this->onIterate();
        $read   = $this->clients;
        $read[] = $this->socket;
        $write  = array();
        $except = array();
        if (stream_select($read, $write, $except, 0, 200000) !== false) {
          foreach ($read as $socket) {
            if ($socket == $this->socket) {
              if (($socket = @stream_socket_accept($this->socket, 0.25, $peerName)) !== false) {
                $this->clients[] = $socket;
                $this->onConnect($socket, $peerName);
              }
            }
            else {
              if (!$this->isConnected($socket)) {
                $this->disconnect($socket);
                continue;
              }
              $data = $this->receive($socket);
              if (strlen($data) > 0) {
                $this->onReceive($socket, $data);
              }
            }
          }
        }
      }
      $this->listening = false;
      foreach ($this->clients as $socket) {
        $this->disconnect($socket);
      }
      stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
    } // run

    public function stop()
    {
      $this->running = false;
    } // stop

    public function isListening()
    {
      return $this->listening;
    } // isListening

    public function isRunning()
    {
      return $this->running;
    } // isRunning

    public function isConnected($socket)
    {
      return (!(@feof($socket)));
    } // isConnected

    public function getLastError()
    {
      $message = $this->lastError;
      $this->lastError = "";
      return $message;
    } // getLastError

    protected function send($socket, $data)
    {
      @fputs($socket, $data);
      return true;
    } // send

    protected function receive($socket)
    {
	  $data    = "";
	  $timeout = microtime(true) + .001;
	  while (microtime(true) < $timeout) {
		if (strlen($data) > 0) {
		  break;
		}
        $data = stream_get_contents($socket);
		usleep(10000);
	  }
      if (strlen($data) == 0) {
        return false;
      }
      return trim($data);
    } // receive

    protected function disconnect($socket)
    {
      $peerName = stream_socket_get_name($socket, true);
      $this->onDisconnect($socket, $peerName);
      $key = array_search($socket, $this->clients);
      stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
      fclose($socket);
      unset($this->clients[$key]);
    } // _disconnect

  } // TcpServer

?>