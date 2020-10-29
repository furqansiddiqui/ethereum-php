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

use FurqanSiddiqui\Ethereum\Math\Integers;

/**
 * Class TransactionReceipt
 * @package FurqanSiddiqui\Ethereum\RPC\Models
 */
class TransactionReceipt extends AbstractRPCResponseModel
{
    /** @var string */
    public string $transactionHash;
    /** @var int */
    public int $transactionIndex;
    /** @var string */
    public string $blockHash;
    /** @var int */
    public int $blockNumber;
    /** @var int */
    public int $cumulativeGasUsed;
    /** @var int */
    public int $gasUsed;
    /** @var null|string */
    public ?string $contractAddress;
    /** @var array */
    public array $logs;
    /** @var string */
    public string $logsBloom;
    /** @var null|string */
    public string $status;

    /** @var array */
    private array $raw;

    /**
     * TransactionReceipt constructor.
     * @param array $obj
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCResponseParseException
     */
    public function __construct(array $obj)
    {
        // Primary param
        $hash = $obj["transactionHash"];
        if (!is_string($hash) && !preg_match('/^0x[a-f0-9]{66}$/i', $hash)) {
            throw $this->unexpectedParamValue("transactionHash", "hash", gettype($hash));
        }

        $this->transactionHash = $hash;
        $shortTxHash = sprintf("%s...%s", substr($this->transactionHash, 0, 6), substr($this->transactionHash, -4));
        $this->parseExceptionPrefix = sprintf('Ethereum TxReceipt [%s]: ', $shortTxHash);

        // Props (prop => (bool)nullable)
        $props = [
            "transactionIndex" => false,
            "blockHash" => false,
            "blockNumber" => false,
            "cumulativeGasUsed" => false,
            "gasUsed" => false,
            "contractAddress" => true,
            "status" => false,
            "logsBloom" => false,
        ];

        $decProps = ["transactionIndex", "blockNumber", "gasUsed", "cumulativeGasUsed"];

        foreach ($props as $prop => $nullable) {
            $value = isset($obj[$prop]) ? $obj[$prop] : null;
            if (!is_string($value) || !preg_match('/^0x[a-f0-9]*$/i', $value)) {
                if (is_null($value) && !$nullable) {
                    $this->unexpectedParamValue($prop, "hash", gettype($value));
                } else {
                    $this->unexpectedParamValue($prop, "hash", gettype($value));
                }
            }

            if (in_array($prop, $decProps)) {
                $value = (int)Integers::Unpack($value)->value();
            }

            $this->$prop = $value;
        }
        unset($prop, $value, $nullable);

        // Logs
        $logs = $obj["logs"];
        if (!is_array($logs)) {
            $this->unexpectedParamValue($logs, "Array", gettype($logs));
        }

        $this->logs = $logs;

        // Raw
        $this->raw = $obj;
    }

    /**
     * @return array
     */
    public function raw(): array
    {
        return $this->raw;
    }
}
