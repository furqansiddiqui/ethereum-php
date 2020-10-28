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
 * Class Block
 * @package FurqanSiddiqui\Ethereum\RPC\Models
 */
class Block extends AbstractRPCResponseModel
{
    /** @var int|null Block height param */
    private ?int $_height = null;

    /** @var string|null */
    public ?string $number;
    /** @var string|null */
    public ?string $hash;
    /** @var string */
    public string $parentHash;
    /** @var string */
    public string $nonce;
    /** @var string */
    public string $sha3Uncles;
    /** @var string */
    public string $logsBloom;
    /** @var string */
    public string $transactionsRoot;
    /** @var string */
    public string $statesRoot;
    /** @var string */
    public string $receiptsRoot;
    /** @var string */
    public string $miner;
    /** @var string */
    public string $difficulty;
    /** @var string */
    public string $totalDifficulty;
    /** @var string */
    public string $extraData;
    /** @var string */
    public string $size;
    /** @var string */
    public string $gasLimit;
    /** @var string */
    public string $gasUsed;
    /** @var string */
    public string $timestamp;
    /** @var array */
    public array $transactions;
    /** @var array */
    public array $uncles;

    /**
     * Block constructor.
     * @param array $obj
     * @throws RPCResponseParseException
     */
    public function __construct(array $obj)
    {
        $this->number = $obj["number"] ?? null;
        $this->hash = $obj["hash"] ?? null;
        if (is_string($this->number)) {
            $this->_height = hexdec($this->number);
        }

        // Exception Prefix
        $this->parseExceptionPrefix = sprintf('Ethereum Block [%s]: ', $this->_height ? $this->_height : "pending");

        if (!is_string($this->number) && !is_null($this->number)) {
            throw $this->unexpectedParamValue("number", "hexdec", gettype($this->number));
        }

        if (!is_string($this->hash) && !is_null($this->hash)) {
            throw $this->unexpectedParamValue("hash", "hash", gettype($this->hash));
        }

        // Hash strings
        $stringParams = [
            "parentHash",
            "nonce",
            "sha3Uncles",
            "logsBloom",
            "transactionsRoot",
            "stateRoot",
            "receiptsRoot",
            "miner",
            "difficulty",
            "totalDifficulty",
            "extraData",
            "size",
            "gasLimit",
            "gasUsed",
            "timestamp"
        ];

        foreach ($stringParams as $param) {
            $value = $obj[$param] ?? null;
            if (!is_string($value) || !preg_match('/^0x[a-f0-9]*$/i', $value)) {
                throw $this->unexpectedParamValue($param, "string", gettype($value));
            }

            $this->$param = $value;
        }
        unset($param, $value);

        // Uncles
        $uncles = $obj["uncles"] ?? null;
        if (!is_array($uncles)) {
            throw $this->unexpectedParamValue("uncles", "array", gettype($uncles));
        }

        $this->uncles = $uncles;

        // Transactions
        $txs = $obj["transactions"] ?? null;
        if (!is_array($txs)) {
            throw $this->unexpectedParamValue("transactions", "array", gettype($txs));
        }

        $this->transactions = $txs;
    }

    /**
     * @return int|null
     */
    public function height(): ?int
    {
        return $this->_height;
    }
}
