<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Transactions;

use Charcoal\Buffers\Types\Bytes32;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Represents a standardized structure for handling transactions.
 * Provides methods to decode raw transactions, retrieve an unsigned version,
 * check the signature status, and generate transaction-related hashes.
 */
interface TransactionInterface
{
    /**
     * @param Ethereum $eth
     * @param ReadableBufferInterface $raw
     * @return static
     */
    public static function decodeRawTransaction(Ethereum $eth, ReadableBufferInterface $raw): static;

    /**
     * @return static
     */
    public function getUnsigned(): static;

    /**
     * @return bool
     */
    public function isSigned(): bool;

    /**
     * @return Bytes32
     */
    public function signPreImage(): Bytes32;

    /**
     * @return Bytes32
     */
    public function hash(): Bytes32;
}

