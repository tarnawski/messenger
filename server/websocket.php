<?php

declare(strict_types=1);

use App\MessageRepository;
use App\Messenger;
use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;

require 'vendor/autoload.php';

$server = new Server("0.0.0.0", 9502);

$server->on("Start", function (Server $server) {
    echo "WebSocket Server is started.\n";
});

$server->on('Open', function (Server $server, Request $request) {
    echo "Connection open: {$request->fd}\n";

    $messenger = new Messenger(new MessageRepository(new PDO(
        'mysql:dbname=messenger;host=mysql',
        'admin',
        'secret'
    )));

    $server->tick(5000, function () use ($server, $request, $messenger) {
        $timestamp = date('Y-m-d H:i:s', strtotime('-5 seconds'));
        $messages = $messenger->fetch($timestamp, Messenger::DEFAULT_LIMIT);
        foreach ($messages as $message) {
            $server->push($request->fd, (string) $message);
        }
    });
});

$server->on('Message', function (Server $server, Frame $frame) {
    echo "Received message: {$frame->data}\n";
});

$server->on('Close', function (Server $server, int $fd) {
    echo "Connection close: {$fd}\n";
});

$server->on('Disconnect', function (Server $server, int $fd) {
    echo "Connection disconnect: {$fd}\n";
});

$server->start();
