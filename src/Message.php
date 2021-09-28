<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Stringable;

class Message implements Stringable
{
    private const MIN_CONTENT_LENGTH = 5;
    private const MAX_CONTENT_LENGTH = 500;
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private function __construct(
        private string $identity,
        private string $content,
        private DateTimeInterface $createdAt
    ) {
        if (false == preg_match(self::UUID_PATTERN, $identity)) {
            throw new InvalidArgumentException(sprintf('Identity "%s" is not valid UUID.', $identity));
        }
        if (self::MIN_CONTENT_LENGTH > strlen($content)) {
            throw new InvalidArgumentException(sprintf('Content "%s" is to short.', $content));
        }
        if (self::MAX_CONTENT_LENGTH < strlen($content)) {
            throw new InvalidArgumentException(sprintf('Content "%s" is to long.', $content));
        }
    }

    public static function create(string $identity, string $content): self
    {
        return new self($identity, $content, new DateTimeImmutable('now'));
    }

    public static function createFromArray(array $data): self
    {
        return new self($data['identity'], $data['content'], new DateTimeImmutable($data['created_at']));
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function toArray(): array
    {
        return [
            'identity' => $this->identity,
            'content' => $this->content,
            'created_at' => $this->createdAt->format(self::DATETIME_FORMAT),
        ];
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
