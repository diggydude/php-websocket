<?php

  require_once(__DIR__ . '/lib/class/FileServer.php');

  $server = new FileServer(
              (object) array(
                'address'      => '127.0.0.1',
                'port'         => 8088,
                'documentRoot' => __DIR__ . '/shares'
              )
            );
  $server->run();

?>