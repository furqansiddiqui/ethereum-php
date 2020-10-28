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

namespace FurqanSiddiqui\Ethereum\RPC\Models;

use FurqanSiddiqui\Ethereum\Exception\RPCResponseParseException;

/**
 * Class AbstractRPCResponseModel
 * @package FurqanSiddiqui\Ethereum\RPC\Models
 */
abstract class AbstractRPCResponseModel
{
    /** @var string|null */
    protected ?string $parseExceptionPrefix = null;
    /** @var string|null */
    protected ?string $parseExceptionSuffix = null;

    /**
     * @param string $param
     * @param string|null $expected
     * @param string|null $got
     * @return RPCResponseParseException
     */
    protected function unexpectedParamValue(string $param, ?string $expected = null, ?string $got = null): RPCResponseParseException
    {
        $message = sprintf('Bad/unexpected value for param "%s"', $param);
        if ($expected) {
            $message .= sprintf(', expected "%s"', $expected);
        }

        if ($got) {
            $message .= sprintf(', got "%s"', $got);
        }

        // Throw
        return new RPCResponseParseException($this->parseExceptionPrefix . $message . $this->parseExceptionSuffix);
    }
}
