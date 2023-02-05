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

use FurqanSiddiqui\Ethereum\RLP\Mapper;

/**
 * Class TxRLPMapper
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
class TxRLPMapper
{
    /** @var \FurqanSiddiqui\Ethereum\RLP\Mapper|null */
    private static null|Mapper $legacyTx = null;
    /** @var \FurqanSiddiqui\Ethereum\RLP\Mapper|null */
    private static null|Mapper $eip1559Tx = null;

    /**
     * @return \FurqanSiddiqui\Ethereum\RLP\Mapper
     */
    public static function LegacyTx(): Mapper
    {
        if (static::$legacyTx) {
            return static::$legacyTx;
        }

        static::$legacyTx = (new Mapper())
            ->expectInteger("none")
            ->expectWEIAmount("gasPrice")
            ->expectInteger("gasLimit")
            ->expectAddress("to")
            ->expectWEIAmount("value")
            ->expectString("data")
            ->expectInteger("signatureV")
            ->expectString("signatureR")
            ->expectString("signatureS");
        return static::$legacyTx;
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\RLP\Mapper
     */
    public static function EIP1559Tx(): Mapper
    {
        if (static::$eip1559Tx) {
            return static::$eip1559Tx;
        }

        static::$eip1559Tx = (new Mapper())
            ->expectInteger("chainId")
            ->expectInteger("nonce")
            ->expectWEIAmount("maxPriorityFeePerGas")
            ->expectWEIAmount("maxFeePerGas")
            ->expectInteger("gasLimit")
            ->expectAddress("to")
            ->expectWEIAmount("value")
            ->mapAsIs("data")
            ->mapAsIs("accessList")
            ->expectBool("signatureParity")
            ->expectString("signatureR")
            ->expectString("signatureS");
        return static::$eip1559Tx;
    }
}
