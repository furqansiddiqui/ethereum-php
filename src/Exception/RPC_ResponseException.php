<?php
/*
 * This file is a part of "furqansiddiqui/ethereum-php" package.
 * https://github.com/furqansiddiqui/ethereum-php
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/ethereum-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Exception;

/**
 * Class RPC_ResponseException
 * @package FurqanSiddiqui\Ethereum\Exception
 */
class RPC_ResponseException extends RPC_ClientException
{
    /** @var string */
    public readonly string $method;

    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param string $method
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, string $method = "")
    {
        parent::__construct($message, $code, $previous);
        $this->method = $method;
    }

    /**
     * @param string $method
     * @param string $got
     * @param string|null $expected
     * @return static
     */
    public static function InvalidResultDataType(string $method, string $got, ?string $expected): static
    {
        if ($expected) {
            return new static(
                sprintf('Expected result from "%s" of type "%s" got "%s"', $method, $expected, $got),
                method: $method
            );
        }

        return new static(
            sprintf('Unexpected result of type "%s" from "%s" method', $method, $got),
            method: $method
        );
    }
}
