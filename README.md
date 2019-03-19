# php-websocket
Hassle-free TCP and WebSocket programming in PHP!

This compact package eliminates many hassles associated with writing TCP client-server applications, including the ordeal of getting your code to work on both Linux and Windows. It provides two abstract base classes for socket servers, TcpServer and WebSocketServer, and a TcpClient class.

To create a regular TCP server, simply extend TcpServer and define the event handlers onIterate(), onConnect(), onDisconnect(), and onReceive(). To prevent the server from hogging the CPU, the onIterate() method should contain a call to usleep(). A value of 10000 usually works well, but you can experiment with different values for best overall system performance.

To create a WebSocket server, extend WebSocketServer and define its processRequest() method. An example server, TestServer, is provided. The example WebSocket client, index.html, works with the example server.

The TcpClient class may be extended or used as-is. TcpClient::receive() should be called in a while loop like so:

while (($data = $client->receive()) === false);
echo $data;

FileServer and Downloader extend TcpServer and TcpClient, respectively, to demonstrate a simple file download system. This system works with both text and binary files. Start file_server.php in one console window, then run download.php like so:

php download.php ice_cream.php
