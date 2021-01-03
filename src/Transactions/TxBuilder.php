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

use Comely\DataTypes\Buffer\Base16;
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
    /** @var int */
    private int $nonce;
    /** @var WEIValue */
    private WEIValue $gasPrice;
    /** @var int */
    private int $gasLimit;
    /** @var Account|null */
    private ?Account $to = null;
    /** @var WEIValue */
    private WEIValue $value;
    /** @var string|null */
    private ?string $data = null;
    /** @var array default value is based on EIP_155 */
    private array $signature = [
        "v" => 0,
        "r" => "",
        "s" => "",
    ];

    /**
     * @param Ethereum $eth
     * @param RLPEncodedTx $encoded
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\AccountsException
     */
    public static function Decode(Ethereum $eth, RLPEncodedTx $encoded): self
    {
        $decoder = new RLP\RLPDecoder($encoded->serialized()->hexits(false));
        $decoder->expectInteger(0, "nonce")
            ->expectInteger(1, "gasPrice")
            ->expectInteger(2, "gasLimit")
            ->mapValue(3, "to")
            ->expectInteger(4, "value")
            ->mapValue(5, "data")
            ->expectInteger(6, "signatureV")
            ->mapValue(7, "signatureR")
            ->mapValue(8, "signatureS");

        $decoded = $decoder->decode();

        $tx = new self($eth);
        $tx->nonce(intval($decoded["nonce"]))
            ->gas($eth->wei()->fromWei($decoded["gasPrice"]), intval($decoded["gasLimit"]));

        if ($decoded["to"]) {
            $tx->to($eth->getAccount($decoded["to"]));
        }

        $tx->data($decoded["data"]);

        $tx->value($eth->wei()->fromWei($decoded["value"]))
            ->signature(
                $decoded["signatureV"],
                new Base16($decoded["signatureR"]),
                new Base16($decoded["signatureS"])
            );

        return $tx;
    }

    /**
     * TxBuilder constructor.
     * @param Ethereum $eth
     */
    public function __construct(Ethereum $eth)
    {
        $this->value = new WEIValue("0");
        $this->signature["v"] = $eth->networkConfig()->chainId; // EIP-155 chain identifier
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

    /**
     * @param int $v
     * @param Base16|null $r
     * @param Base16|null $s
     * @return $this
     */
    public function signature(int $v, ?Base16 $r = null, ?Base16 $s = null): self
    {
        $this->signature["v"] = $v;
        $this->signature["r"] = $r ? $r->value() : "";
        $this->signature["s"] = $s ? $s->value() : "";
        return $this;
    }

    /**
     * @return RLPEncodedTx
     * @throws IncompleteTxException
     */
    public function serialize(): RLPEncodedTx
    {
        $rlp = new RLP();
        $txObj = new RLP\RLPObject();

        // Nonce
        if (!isset($this->nonce) || $this->nonce < 0) {
            throw new IncompleteTxException('Nonce value is not set or is invalid');
        }

        $txObj->encodeInteger($this->nonce);

        // Gas
        if (!isset($this->gasPrice, $this->gasLimit) || $this->gasLimit < 1) {
            throw new IncompleteTxException('Gas price/limit are not defined');
        }

        $txObj->encodeInteger($this->gasPrice->wei());
        $txObj->encodeInteger($this->gasLimit);

        // To
        $payee = isset($this->to) ? $this->to->getAddress() : "";
        $txObj->encodeHexString($payee);

        // Value
        $txObj->encodeInteger($this->value->wei());

        // Data/Contract Code
        $txObj->encodeHexString($this->data ?? "");

        // Signature
        $txObj->encodeInteger($this->signature["v"]);
        $txObj->encodeHexString($this->signature["r"]);
        $txObj->encodeHexString($this->signature["s"]);

        return new RLPEncodedTx($txObj->getRLPEncoded($rlp)->toString());
    }
}
