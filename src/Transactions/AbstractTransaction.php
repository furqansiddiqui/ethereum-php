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
use Comely\Buffer\BigInteger\BigEndian;
use Comely\Buffer\Bytes32;
use FurqanSiddiqui\Ethereum\Buffers\RLP_Encoded;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Exception\TxDecodeException;
use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;
use FurqanSiddiqui\Ethereum\RLP\Mapper;
use FurqanSiddiqui\Ethereum\RLP\RLP;
use FurqanSiddiqui\Ethereum\RLP\RLP_Mappable;

/**
 * Class AbstractTransaction
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
abstract class AbstractTransaction implements RLP_Mappable, TransactionInterface
{
    /**
     * @return \FurqanSiddiqui\Ethereum\RLP\Mapper
     */
    abstract protected static function Mapper(): Mapper;

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
        $rlpDecode = RLP::Decode($raw);
        if (!is_array($rlpDecode)) {
            throw new TxDecodeException(sprintf('Expected Array from decoded RLP, got "%s"', gettype($rlpDecode)));
        }

        $tx = new static($eth);
        $rlpArray = static::Mapper()->createArray($rlpDecode);
        foreach ($rlpArray as $prop => $value) {
            if (!property_exists($tx, $prop)) {
                throw new TxDecodeException(
                    sprintf('Property "%s" does not exist in %s tx class', $prop, static::class)
                );
            }

            $tx->$prop = $value;
        }

        return $tx;
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     */
    public function __construct(public readonly Ethereum $eth)
    {
    }

    /**
     * @return string[]
     */
    public function __debugInfo(): array
    {
        return [static::class];
    }

    /**
     * @return bool
     */
    public function isSigned(): bool
    {
        if ($this->signatureR || $this->signatureS) {
            return true;
        }

        return false;
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Buffers\RLP_Encoded
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     */
    public function encode(): RLP_Encoded
    {
        $encoded = static::Mapper()->encode($this);
        $encodedLen = $encoded->len();

        if ($encodedLen <= 55) {
            $encoded->prependUInt8(0xc0 + $encodedLen);
            return $encoded;
        }

        $encoded->prependUInt8($encodedLen);
        $encoded->prependUInt8(0xf7 + strlen(BigEndian::GMP_Pack($encodedLen)));
        return $encoded;
    }

    /**
     * @return \Comely\Buffer\Bytes32
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     */
    public function hash(): Bytes32
    {
        return new Bytes32(Keccak::hash($this->encode()->raw(), 256, true));
    }
}
