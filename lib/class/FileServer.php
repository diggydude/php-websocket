<?php

  require_once(__DIR__ . '/TcpServer.php');

  class FileServer extends TcpServer
  {

    protected

      $transfers,
      $documentRoot;

    public function __construct($params)
    {
      parent::__construct($params);
      $this->transfers    = array();
      $this->documentRoot = $params->documentRoot;
    } // __construct

    protected function onIterate()
    {
      usleep(10000);
    } // onIterate

    protected function onConnect($socket, $peerName)
    {
      echo "Client at " . $peerName . " connected.\n";
    } // onConnect

    protected function onDisconnect($socket, $peerName)
    {
      echo "Client at " . $peerName . " disconnected.\n";
    } // onDisconnect

    protected function onReceive($socket, $data)
    {
      if (stripos($data, 'GET ') === 0) {
        $filename = substr($data, 4);
        if ($this->startDownload($socket, $filename) == false) {
          $this->disconnect($socket);
        }
        return;
      }
      if (($data == '<Ready>') && array_key_exists((int) $socket, $this->transfers)) {
        if (feof($this->transfers[(int) $socket])) {
          $this->finishDownload($socket);
          $this->disconnect($socket);
          return;
        }
        $this->sendChunk($socket);
        return;
      }
      if (is_resource($this->transfers[(int) $socket])) {
        fclose($this->transfers[(int) $socket]);
      }
      $this->send($socket, '<Error>');
      $this->disconnect($socket);
    } // onReceive

    protected function startDownload($socket, $filename)
    {
      $path = $this->documentRoot . "/" . $filename;
      if (!file_exists($path)) {
        $this->send($socket, '<File Not Found>');
        return false;
      }
      $this->transfers[(int) $socket] = fopen($path, 'r');
      $this->send($socket, '<Continue>');
      return true;
    } // startDownload

    protected function sendChunk($socket)
    {
      $handle = $this->transfers[(int) $socket];
      $data   = fread($handle, 2048);
      $data   = base64_encode($data);
      $data   = gzcompress($data);
      $this->send($socket, $data);
    } // sendChunk

    protected function finishDownload($socket)
    {
      $this->send($socket, '<Transfer Complete>');
      fclose($this->transfers[(int) $socket]);
      unset($this->transfers[(int) $socket]);
    } // finishDownload

  } // FileServer

?>