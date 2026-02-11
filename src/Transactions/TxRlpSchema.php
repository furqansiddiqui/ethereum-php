<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Transactions;

use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpFieldType;
use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpSchema;

/**
 * Defines reusable RLP schemas for various Ethereum transaction types, including Legacy, EIP-1559, and EIP-2930
 * (both signed and unsigned variants).
 */
final class TxRlpSchema
{
    private static ?RlpSchema $legacyTx = null;
    private static ?RlpSchema $eip1559TxSigned = null;
    private static ?RlpSchema $eip1559TxUnsigned = null;
    private static ?RlpSchema $eip2930TxSigned = null;
    private static ?RlpSchema $eip2930TxUnsigned = null;

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

    /**
     * @return RlpSchema
     */
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
     * @return RlpSchema
     */
    public static function eip2930Tx(): RlpSchema
    {
        if (self::$eip2930TxSigned) {
            return self::$eip2930TxSigned;
        }

        return self::$eip2930TxSigned = new RlpSchema()
            ->add(RlpFieldType::Integer, "chainId")
            ->add(RlpFieldType::Integer, "nonce")
            ->add(RlpFieldType::Wei, "gasPrice")
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
    public static function eip2930TxUnsigned(): RlpSchema
    {
        if (self::$eip2930TxUnsigned) {
            return self::$eip2930TxUnsigned;
        }

        return self::$eip2930TxUnsigned = new RlpSchema()
            ->add(RlpFieldType::Integer, "chainId")
            ->add(RlpFieldType::Integer, "nonce")
            ->add(RlpFieldType::Wei, "gasPrice")
            ->add(RlpFieldType::Integer, "gasLimit")
            ->add(RlpFieldType::AddressNullable, "to")
            ->add(RlpFieldType::Wei, "value")
            ->add(RlpFieldType::String, "data")
            ->add(RlpFieldType::Include, "accessList");
    }
}
