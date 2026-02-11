<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Transactions;

use Charcoal\Buffers\Types\Bytes32;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;

/**
 * Represents a standardized structure for handling transactions.
 * Provides methods to decode raw transactions, retrieve an unsigned version,
 * check the signature status, and generate transaction-related hashes.
 */
interface EthereumTransactionInterface
{
    /**
     * @param string|ReadableBufferInterface $raw
     * @return static
     */
    public static function decodeRawTransaction(string|ReadableBufferInterface $raw): static;

    /**
     * @return string
     */
    public function encode(): string;

    /**
     * @return static The unsigned version of this transaction.
     */
    public function getUnsigned(): static;

    /**
     * @return bool Whether the transaction is signed or not.
     */
    public function isSigned(): bool;

    /**
     * @return Bytes32 Keccak256 hash of the UNSIGNED transaction bytes.
     */
    public function signPreImage(): Bytes32;

    /**
     * @return Bytes32 Keccak256 hash of the transaction bytes.
     */
    public function hash(): Bytes32;
}