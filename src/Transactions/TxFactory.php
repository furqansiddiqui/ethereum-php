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

use Charcoal\Buffers\AbstractByteArray;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Exception\TxDecodeException;

/**
 * Class TxFactory
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
readonly class TxFactory
{
    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     */
    public function __construct(public Ethereum $eth)
    {
    }

    /**
     * @param \Charcoal\Buffers\AbstractByteArray $rawTx
     * @return \FurqanSiddiqui\Ethereum\Transactions\LegacyTx|\FurqanSiddiqui\Ethereum\Transactions\Type1Tx|\FurqanSiddiqui\Ethereum\Transactions\Type2Tx|\FurqanSiddiqui\Ethereum\Transactions\TransactionInterface
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_DecodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     * @throws \FurqanSiddiqui\Ethereum\Exception\TxDecodeException
     */
    public function decode(AbstractByteArray $rawTx): LegacyTx|Type1Tx|Type2Tx|TransactionInterface
    {
        $prefix = substr($rawTx->raw(), 0, 1);
        if (ord($prefix) < 127) {
            return match ($prefix) {
                "\x01" => Type1Tx::DecodeRawTransaction($this->eth, $rawTx),
                "\x02" => Type2Tx::DecodeRawTransaction($this->eth, $rawTx),
                default => throw new TxDecodeException(
                    sprintf('Unsupported transaction envelope prefix "%s"', bin2hex($prefix))
                )
            };
        }

        return LegacyTx::DecodeRawTransaction($this->eth, $rawTx);
    }

    /**
     * @param \Charcoal\Buffers\AbstractByteArray $rawTx
     * @return \FurqanSiddiqui\Ethereum\Transactions\LegacyTx
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_DecodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     * @throws \FurqanSiddiqui\Ethereum\Exception\TxDecodeException
     */
    public function decodeLegacy(AbstractByteArray $rawTx): LegacyTx
    {
        return LegacyTx::DecodeRawTransaction($this->eth, $rawTx);
    }

    /**
     * @param \Charcoal\Buffers\AbstractByteArray $rawTx
     * @return \FurqanSiddiqui\Ethereum\Transactions\Type1Tx
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_DecodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     * @throws \FurqanSiddiqui\Ethereum\Exception\TxDecodeException
     */
    public function decodeType1(AbstractByteArray $rawTx): Type1Tx
    {
        return Type1Tx::DecodeRawTransaction($this->eth, $rawTx);
    }

    /**
     * @param \Charcoal\Buffers\AbstractByteArray $rawTx
     * @return \FurqanSiddiqui\Ethereum\Transactions\Type2Tx
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_DecodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     * @throws \FurqanSiddiqui\Ethereum\Exception\TxDecodeException
     */
    public function decodeType2(AbstractByteArray $rawTx): Type2Tx
    {
        return Type2Tx::DecodeRawTransaction($this->eth, $rawTx);
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Transactions\LegacyTx
     */
    public function legacyTx(): LegacyTx
    {
        return new LegacyTx($this->eth);
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Transactions\Type1Tx
     */
    public function type1(): Type1Tx
    {
        return new Type1Tx($this->eth);
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Transactions\Type2Tx
     */
    public function type2(): Type2Tx
    {
        return new Type2Tx($this->eth);
    }
}
