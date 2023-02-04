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
 * Class RPC_CurlException
 * @package FurqanSiddiqui\Ethereum\Exception
 */
class RPC_CurlException extends RPC_RequestException
{
    /** @var string */
    public readonly string $errorStr;
    /** @var int */
    public readonly int $errorNo;

    /**
     * @param \CurlHandle $ch
     */
    public function __construct(\CurlHandle $ch)
    {
        $this->errorStr = curl_error($ch);
        $this->errorNo = curl_errno($ch);
        curl_close($ch);
        parent::__construct('Curl request failed');
    }
}
