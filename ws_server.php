<?php

  require_once(__DIR__ . '/lib/class/TestServer.php');

  $params = (object) array(
              'address' => '127.0.0.1',
              'port'    => 1222
            );
  $server = new TestServer($params);
  $server->run();

?>