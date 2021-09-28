<?php

declare(strict_types=1);

namespace App;

class Messenger
{
    public const DEFAULT_TIMESTAMP = '0001-01-01 00:00:00';
    public const DEFAULT_LIMIT = 10;

    public function __construct(private MessageRepositoryInterface $messageRepository)
    {
    }

    public function fetch(string $timestamp = self::DEFAULT_TIMESTAMP, int $limit = self::DEFAULT_LIMIT): array
    {
        return $this->messageRepository->find($timestamp, $limit);
    }

    public function save(Message $message): void
    {
        $this->messageRepository->save($message);
    }
}
