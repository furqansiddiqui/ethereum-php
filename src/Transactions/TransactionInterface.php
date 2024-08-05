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
use Charcoal\Buffers\Frames\Bytes32;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Interface TransactionInterface
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
interface TransactionInterface
{
    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     * @param \Charcoal\Buffers\AbstractByteArray $raw
     * @return static
     */
    public static function DecodeRawTransaction(Ethereum $eth, AbstractByteArray $raw): static;

    /**
     * @return $this
     */
    public function getUnsigned(): static;

    /**
     * @return bool
     */
    public function isSigned(): bool;

    /**
     * @return \Charcoal\Buffers\Frames\Bytes32
     */
    public function signPreImage(): Bytes32;

    /**
     * @return \Charcoal\Buffers\Frames\Bytes32
     */
    public function hash(): Bytes32;
}

