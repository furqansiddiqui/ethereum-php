<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Transactions;

use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpFieldType;
use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpSchema;

final class TxRlpSchema
{
    private static ?RlpSchema $legacyTx = null;
    private static ?RlpSchema $eip1559TxSigned = null;
    private static ?RlpSchema $eip1559TxUnsigned = null;

    /** @var \FurqanSiddiqui\Ethereum\RLP\Mapper|null */
    private static null|Mapper $eip1559Tx = null;
    /** @var \FurqanSiddiqui\Ethereum\RLP\Mapper|null */
    private static null|Mapper $eip1559Tx_Unsigned = null;
    /** @var \FurqanSiddiqui\Ethereum\RLP\Mapper|null */
    private static null|Mapper $eip2718Tx = null;
    /** @var \FurqanSiddiqui\Ethereum\RLP\Mapper|null */
    private static null|Mapper $eip2718Tx_Unsigned = null;

    /**
     * @return RlpSchema
     */
    public static function legacyTx(): RlpSchema
    {
        if (self::$legacyTx) {
            return self::$legacyTx;
        }

        return self::$legacyTx = new RlpSchema()
            ->add(RlpFieldType::Integer, "nonce")
            ->add(RlpFieldType::Wei, "gasPrice")
            ->add(RlpFieldType::Integer, "gasLimit")
            ->add(RlpFieldType::AddressNullable, "to")
            ->add(RlpFieldType::Wei, "value")
            ->add(RlpFieldType::String, "data")
            ->add(RlpFieldType::Integer, "signatureV")
            ->add(RlpFieldType::Bytes32, "signatureR")
            ->add(RlpFieldType::Bytes32, "signatureS");
    }

    public static function eip1559Tx(): RlpSchema
    {
        if (self::$eip1559TxSigned) {
            return self::$eip1559TxSigned;
        }

        return self::$eip1559TxSigned = new RlpSchema()
            ->add(RlpFieldType::Integer, "chainId")
            ->add(RlpFieldType::Integer, "nonce")
            ->add(RlpFieldType::Wei, "maxPriorityFeePerGas")
            ->add(RlpFieldType::Wei, "maxFeePerGas")
            ->add(RlpFieldType::Integer, "gasLimit")
            ->add(RlpFieldType::AddressNullable, "to")
            ->add(RlpFieldType::Wei, "value")
            ->add(RlpFieldType::String, "data")
            ->add(RlpFieldType::Include, "accessList")
            ->add(RlpFieldType::Bool, "yParity")
            ->add(RlpFieldType::Bytes32, "signatureR")
            ->add(RlpFieldType::Bytes32, "signatureS");
    }

    /**
     * @return RlpSchema
     */
    public static function eip1559TxUnsigned(): RlpSchema
    {
        if (self::$eip1559TxUnsigned) {
            return self::$eip1559TxUnsigned;
        }

        return self::$eip1559TxUnsigned = new RlpSchema()
            ->add(RlpFieldType::Integer, "chainId")
            ->add(RlpFieldType::Integer, "nonce")
            ->add(RlpFieldType::Wei, "maxPriorityFeePerGas")
            ->add(RlpFieldType::Wei, "maxFeePerGas")
            ->add(RlpFieldType::Integer, "gasLimit")
            ->add(RlpFieldType::AddressNullable, "to")
            ->add(RlpFieldType::Wei, "value")
            ->add(RlpFieldType::String, "data")
            ->add(RlpFieldType::Include, "accessList");
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\RLP\Mapper
     */
    public static function EIP2718Tx(): Mapper
    {
        if (static::$eip2718Tx) {
            return static::$eip2718Tx;
        }

        static::$eip2718Tx = (new Mapper())
            ->expectInteger("chainId")
            ->expectInteger("nonce")
            ->expectWEIAmount("gasPrice")
            ->expectInteger("gasLimit")
            ->expectAddress("to")
            ->expectWEIAmount("value")
            ->expectString("data")
            ->mapAsIs("accessList")
            ->expectBool("signatureParity")
            ->expectString("signatureR")
            ->expectString("signatureS");
        return static::$eip2718Tx;
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\RLP\Mapper
     */
    public static function EIP2718Tx_Unsigned(): Mapper
    {
        if (static::$eip2718Tx_Unsigned) {
            return static::$eip2718Tx_Unsigned;
        }

        static::$eip2718Tx_Unsigned = (new Mapper())
            ->expectInteger("chainId")
            ->expectInteger("nonce")
            ->expectWEIAmount("gasPrice")
            ->expectInteger("gasLimit")
            ->expectAddress("to")
            ->expectWEIAmount("value")
            ->expectString("data")
            ->mapAsIs("accessList");
        return static::$eip2718Tx_Unsigned;
    }
}
