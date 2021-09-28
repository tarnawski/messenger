<?php

declare(strict_types=1);

namespace App;

use PDO;

class MessageRepository implements MessageRepositoryInterface
{
    public function __construct(private PDO $connection)
    {
    }

    public function find(string $timestamp, int $limit): array
    {
        $sth = $this->connection->prepare('
            SELECT `identity`, `content`, `created_at` 
            FROM `message` 
            WHERE `created_at` > :timestamp 
            ORDER BY created_at desc 
            LIMIT :limit
        ');
        $sth->bindValue(':timestamp', $timestamp, PDO::PARAM_STR);
        $sth->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sth->execute();
        $results = $sth->fetchAll();

        return array_map(function (array $result): Message {
            return Message::createFromArray($result);
        }, $results);
    }

    public function save(Message $message): void
    {
        $sth = $this->connection->prepare(
            'INSERT INTO `message` (`identity`, `content`) VALUES (:identity, :content)'
        );
        $sth->bindValue(':identity', $message->getIdentity());
        $sth->bindValue(':content', $message->getContent());
        $sth->execute();
    }
}
