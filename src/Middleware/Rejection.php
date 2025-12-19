<?php

declare(strict_types=1);

namespace TheFrosty\WpX402\Middleware;

use TheFrosty\WpUtilities\Models\BaseModel;
use WP_Http;
use function __;

/**
 * This class is used to designate message & status of rejection.
 */
class Rejection extends BaseModel
{

    public const string MESSAGE = 'message';
    public const string STATUS = 'status';

    protected string $message;
    protected int|null $status = null;

    public static function unauthorized(): static
    {
        return new static([self::MESSAGE => __('Unauthorized', 'wp-x402')]);
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getStatus(): int
    {
        return $this->status ?? WP_Http::UNAUTHORIZED;
    }

    public function setStatus(int|null $status): void
    {
        $this->status = $status;
    }
}
