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
 * Class RPCInvalidResponseException
 * @package FurqanSiddiqui\Ethereum\Exception
 */
class RPCInvalidResponseException extends RPCException
{
    /**
     * @param string $method
     * @param string $expected
     * @param string $got
     * @return static
     */
    public static function InvalidDataType(string $method, string $expected, string $got): self
    {
        return new self(sprintf('RPC method [%s], Expected result data type "%s", got "%s"', $method, ucfirst($expected), ucfirst($got)));
    }
}
