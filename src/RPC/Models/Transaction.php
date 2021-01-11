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

use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Math\Integers;
use FurqanSiddiqui\Ethereum\Math\WEIValue;

/**
 * Class Transaction
 * @package FurqanSiddiqui\Ethereum\RPC\Models
 */
class Transaction extends AbstractRPCResponseModel
{
    /** @var string */
    public string $hash;
    /** @var string */
    public string $nonce;
    /** @var null|string */
    public ?string $blockHash;
    /** @var null|string */
    public ?string $blockNumber;
    /** @var null|string */
    public ?string $transactionIndex;
    /** @var string */
    public string $from;
    /** @var string|null */
    public ?string $to;
    /** @var string */
    public string $value;
    /** @var string */
    public string $gasPrice;
    /** @var string */
    public string $gas;
    /** @var null|string */
    public ?string $input = null;

    /** @var string */
    private string $r;
    /** @var string */
    private string $s;
    /** @var string */
    private string $v;

    /** @var WEIValue */
    private WEIValue $_weiValue;
    /** @var WEIValue */
    private WEIValue $_gasPrice;
    /** @var array */
    private array $raw;

    /**
     * Transaction constructor.
     * @param Ethereum $eth
     * @param array $obj
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCResponseParseException
     */
    public function __construct(Ethereum $eth, array $obj)
    {
        // Primary param
        $hash = $obj["hash"];
        if (!is_string($hash) && !preg_match('/^0x[a-f0-9]{66}$/i', $hash)) {
            throw $this->unexpectedParamValue("hash", "hash", gettype($hash));
        }

        $this->hash = $hash;
        $shortTxHash = sprintf("%s...%s", substr($this->hash, 0, 6), substr($this->hash, -4));
        $this->parseExceptionPrefix = sprintf('Ethereum Tx [%s]: ', $shortTxHash);

        // Props (prop => (bool)nullable)
        $props = [
            "nonce" => false,
            "blockHash" => true,
            "blockNumber" => true,
            "transactionIndex" => true,
            "value" => false,
            "gasPrice" => false,
            "gas" => false,
            "input" => false,
            "from" => false,
            "to" => true,
            "r" => false,
            "s" => false,
            "v" => false,
        ];

        foreach ($props as $prop => $nullable) {
            $value = isset($obj[$prop]) ? $obj[$prop] : null;
            if (!is_string($value) || !preg_match('/^0x[a-f0-9]*$/i', $value)) {
                if (is_null($value) && !$nullable) {
                    $this->unexpectedParamValue($prop, "hash", gettype($value));
                } else {
                    $this->unexpectedParamValue($prop, "hash", gettype($value));
                }
            }

            $this->$prop = $value;
        }
        unset($prop, $value, $nullable);

        // From and To
        $this->from = $obj["from"];
        if (!is_string($this->from) || !preg_match('/^0x[a-f0-9]{40}$/i', $this->from)) {
            throw $this->unexpectedParamValue("from", "address");
        }

        $this->to = $obj["to"] ?? null;
        if (is_string($this->to)) {
            if (!preg_match('/^0x[a-f0-9]{40}$/i', $this->to)) {
                throw $this->unexpectedParamValue("to", "address");
            }
        }

        // Decimals
        $decProps = [
            "blockNumber",
            "gas",
            "gasPrice",
            "nonce",
            "transactionIndex",
            "value",
        ];

        foreach ($decProps as $decProp) {
            if($this->$decProp) {
                $this->$decProp = Integers::Unpack($this->$decProp)->value();
            }
        }

        // Finalise
        $this->_weiValue = $eth->wei()->fromWei($this->value);
        $this->_gasPrice = $eth->wei()->fromWei($this->gasPrice);
        $this->raw = $obj;
    }

    /**
     * @return int
     */
    public function gasLimit(): int
    {
        return (int)$this->gas;
    }

    /**
     * @return WEIValue
     */
    public function valueWEI(): WEIValue
    {
        return $this->_weiValue;
    }

    /**
     * @return WEIValue
     */
    public function gasPriceWEI(): WEIValue
    {
        return $this->_gasPrice;
    }

    /**
     * @return array
     */
    public function signature(): array
    {
        return [
            $this->r,
            $this->s,
            $this->v,
        ];
    }

    /**
     * @return array
     */
    public function raw(): array
    {
        return $this->raw;
    }
}
