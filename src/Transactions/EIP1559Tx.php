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

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\Bytes32;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Buffers\RLP_Encoded;
use FurqanSiddiqui\Ethereum\Buffers\WEIAmount;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Exception\TxDecodeException;
use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;
use FurqanSiddiqui\Ethereum\RLP\Mapper;

/**
 * Class EIP1559Tx
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
class EIP1559Tx extends AbstractTransaction
{
    public ?int $chainId = null;
    public ?int $nonce = null;
    public ?WEIAmount $maxPriorityFeePerGas = null;
    public ?WEIAmount $maxFeePerGas = null;
    public ?int $gasLimit = null;
    public ?EthereumAddress $to = null;
    public ?WEIAmount $value = null;
    public ?string $data = null;
    public ?array $accessList = null;
    public ?bool $signatureParity = null;
    public ?string $signatureR = null;
    public ?string $signatureS = null;

    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     * @param \Comely\Buffer\AbstractByteArray $raw
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_DecodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     * @throws \FurqanSiddiqui\Ethereum\Exception\TxDecodeException
     */
    public static function DecodeRawTransaction(Ethereum $eth, AbstractByteArray $raw): static
    {
        $raw = $raw->copy();
        $prefix = $raw->pop(1);
        if ($prefix !== "\x02") {
            throw new TxDecodeException(sprintf('Bad prefix "%s" for Type2/EIP1559 transaction', bin2hex($prefix)));
        }

        // pop method removed the first prefix byte from buffer.
        return parent::DecodeRawTransaction($eth, $raw);
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     */
    public function __construct(Ethereum $eth)
    {
        parent::__construct($eth);
        $this->chainId = $this->eth->network->chainId;
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\RLP\Mapper
     */
    protected static function Mapper(): Mapper
    {
        return TxRLPMapper::EIP1559Tx();
    }

    /**
     * @return \Comely\Buffer\Bytes32
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     */
    public function signPreImage(): Bytes32
    {
        $unSignedTx = $this->isSigned() ? $this->getUnsigned() : $this;
        $encoded = substr($unSignedTx->encode()->raw(), 0, -3); // Remove signature yParity, r & s
        return new Bytes32(Keccak::hash($encoded, 256, true));
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Buffers\RLP_Encoded
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     */
    public function encode(): RLP_Encoded
    {
        $buffer = parent::encode();
        return $buffer->prepend("\x02");
    }

    /**
     * @return $this
     */
    public function getUnsigned(): static
    {
        $unSigned = clone $this;
        $unSigned->signatureParity = false;
        $unSigned->signatureR = null;
        $unSigned->signatureS = null;
        return $unSigned;
    }
}
