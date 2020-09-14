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

namespace FurqanSiddiqui\Ethereum\Transactions;

use FurqanSiddiqui\Ethereum\Accounts\Account;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Exception\IncompleteTxException;
use FurqanSiddiqui\Ethereum\Math\WEIValue;
use FurqanSiddiqui\Ethereum\RLP;

/**
 * Class TransactionConstructor
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
class TxBuilder
{
    public const EIP_155 = "018080";

    /** @var Ethereum */
    private Ethereum $eth;

    /** @var int */
    private int $nonce;
    /** @var int */
    private int $gasLimit;
    /** @var WEIValue */
    private WEIValue $gasPrice;
    /** @var Account */
    private Account $to;
    /** @var WEIValue */
    private WEIValue $value;
    /** @var string|null */
    private ?string $data = null;

    /**
     * TxBuilder constructor.
     * @param Ethereum $eth
     */
    public function __construct(Ethereum $eth)
    {
        $this->eth = $eth;
        $this->value($eth->wei()->fromWei(0)); // Set to 0.00 ETH default value
    }

    /**
     * @param int $nonce
     * @return $this
     */
    public function nonce(int $nonce): self
    {
        $this->nonce = $nonce > 0 ? $nonce : 0;
        return $this;
    }

    /**
     * @param WEIValue $gasPrice
     * @param int $gasLimit
     * @return $this
     */
    public function gas(WEIValue $gasPrice, int $gasLimit = 21000): self
    {
        $this->gasPrice = $gasPrice;
        $this->gasLimit = $gasLimit;
        return $this;
    }

    /**
     * @param Account $addr
     * @return $this
     */
    public function to(Account $addr): self
    {
        $this->to = $addr;
        return $this;
    }

    /**
     * @param WEIValue $value
     * @return $this
     */
    public function value(WEIValue $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function data(string $code): self
    {
        $this->data = $code;
        return $this;
    }

    public function encodeRLP(): array
    {
        $rlp = new RLP();
        $encoded = [];

        // Nonce
        if (!isset($this->nonce) || $this->nonce < 0) {
            throw new IncompleteTxException('Nonce value is not set or is invalid');
        }

        $encoded["nonce"] = $this->nonce;

        // Gas
        if (!isset($this->gasPrice, $this->gasLimit) || $this->gasLimit < 1) {
            throw new IncompleteTxException('Gas price/limit are not defined');
        }

        $encoded["gasPrice"] = $this->gasPrice->wei();
        $encoded["gasLimit"] = $this->gasLimit;

        // To
        if (!isset($this->to)) {
            throw new IncompleteTxException('To/Payee address is not set');
        }

        $encoded["to"] = $this->to->getAddress();

        // Value
        $encoded["value"] = $this->value->wei();

        // Data/Contract Code
        $encoded["data"] = $this->data;

        // EIP_155
        $encoded["EIP_155"] = self::EIP_155;

        var_dump($encoded);

        var_dump(RLP::Encode($encoded)->toString());
        return $encoded;
    }
}
