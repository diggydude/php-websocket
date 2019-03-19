<?php

  require_once(__DIR__ . '/TcpClient.php');

  class Downloader extends TcpClient
  {

    protected

      $downloadDir,
      $handle;

    public function __construct($params)
    {
      parent::__construct($params);
      $this->downloadDir = $params->downloadDir;
      $this->handle = null;
    } // __construct

    public function download($filename)
    {
      $this->send('GET ' . $filename);
      while (($response = $this->receive()) === false);
      if ($response == '<File Not Found>') {
        echo "File not found.\n";
        $this->disconnect();
        return false;
      }
      if ($response == '<Continue>') {
        $this->handle = fopen($this->downloadDir . '/' . $filename, 'a');
        while (true) {
          $this->send('<Ready>');
          while (($response = $this->receive()) === false);
          if ($response == '<Transfer Complete>') {
            fclose($this->handle);
            echo "Transfer complete.\n";
            $this->disconnect();
            return true;
          }
          $data = gzuncompress($response);
          $data = base64_decode($data);
          fwrite($this->handle, $data);
          usleep(10000);
        }
      }
      echo "Server error.\n";
      $this->disconnect();
      return false;
    } // download

  } // Downloader

?>