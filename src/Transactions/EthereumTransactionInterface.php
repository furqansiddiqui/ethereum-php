<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Transactions;

use Charcoal\Buffers\Types\Bytes32;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Blockchain\Core\Signatures\EcdsaSignature256;

/**
 * Represents a standardized structure for handling transactions.
 * Provides methods to decode raw transactions, retrieve an unsigned version,
 * check the signature status, and generate transaction-related hashes.
 */
interface EthereumTransactionInterface
{
    /**
     * @param int $chainId
     * @param ReadableBufferInterface $raw
     * @return static
     */
    public static function decodeRawTransaction(int $chainId, ReadableBufferInterface $raw): static;

    /**
     * @return ReadableBufferInterface
     */
    public function encode(): ReadableBufferInterface;

    /**
     * @return static The unsigned version of this transaction.
     */
    public function getUnsigned(): static;

    /**
     * @return Bytes32 Keccak256 hash of the UNSIGNED transaction bytes.
     */
    public function signPreImage(): Bytes32;

    /**
     * @return bool Whether the transaction is signed or not.
     */
    public function isSigned(): bool;

    /**
     * @return Bytes32 Keccak256 hash of the transaction bytes.
     */
    public function hash(): Bytes32;

    /**
     * @param EcdsaSignature256 $signature
     * @return static New instance of this transaction with signature.
     */
    public function withSignature(EcdsaSignature256 $signature): static;
}