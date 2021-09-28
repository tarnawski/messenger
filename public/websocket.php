<?php

declare(strict_types=1);

use App\Kernel;
use App\MessageRepository;
use App\Messenger;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

// Create WebSocket.
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, '0.0.0.0', 9502);
socket_listen($socket);
$client = socket_accept($socket);

// Send WebSocket handshake headers.
$request = socket_read($client, 5000);
preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
$key = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
$headers = "HTTP/1.1 101 Switching Protocols\r\n";
$headers .= "Upgrade: websocket\r\n";
$headers .= "Connection: Upgrade\r\n";
$headers .= "Sec-WebSocket-Version: 13\r\n";
$headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";
socket_write($client, $headers, strlen($headers));

// Build Messenger service.
$messenger = new Messenger(new MessageRepository(new PDO(
    'mysql:dbname=messenger;host=mysql',
    'admin',
    'secret'
)));

// Send messages into WebSocket in a loop.
while (true) {
    $timestamp = date('Y-m-d H:i:s', strtotime('-5 seconds'));
    $messages = $messenger->fetch($timestamp, Messenger::DEFAULT_LIMIT);

    foreach ($messages as $message) {
        $content = json_encode($message->toArray());
        $content = match (true) {
            strlen($content) <= 125 => pack('CC', 0x80 | (0x1 & 0x0f), strlen($content)) . $content,
            strlen($content) <= 65535 => pack('CCn', 0x80 | (0x1 & 0x0f), 126, strlen($content)) . $content,
            default => pack('CCNN', 0x80 | (0x1 & 0x0f), 127, strlen($content)) . $content,
        };
        socket_write($client, $content, strlen($content));
    }
    sleep(5);
}

