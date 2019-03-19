<?php

  require_once(__DIR__ . '/lib/class/Downloader.php');

  $filename = $argv[1];

  $client   = new Downloader(
                (object) array(
                  'address'     => '127.0.0.1',
                  'port'        => 8088,
                  'downloadDir' => __DIR__ . '/downloads'
                )
              );

  $client->download($filename);

?>