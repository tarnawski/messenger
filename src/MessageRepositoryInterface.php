<?php

namespace App;

interface MessageRepositoryInterface
{
    public function find(string $timestamp, int $limit): array;
    public function save(Message $message): void;
}
