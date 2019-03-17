<?php

  require_once(__DIR__ . '/WebSocketServer.php');

  class TestServer extends WebSocketServer
  {

    public function __construct($params)
    {
      parent::__construct($params);
    } // __construct

    protected function processRequest($request)
    {
      if ($request == "!STOP") {
        $this->stop();
        return "Stopping server...\n";
      }
      return "You sent: " . $request;
    } // processRequest

  } // TestServer

?>