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
use FurqanSiddiqui\Ethereum\Buffers\RLP_Encoded;

/**
 * Interface TransactionInterface
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
interface TransactionInterface
{
    /**
     * @param \Comely\Buffer\AbstractByteArray $raw
     * @return static
     */
    public static function DecodeRawTransaction(AbstractByteArray $raw): static;

    /**
     * @return \FurqanSiddiqui\Ethereum\Buffers\RLP_Encoded
     */
    public function encode(): RLP_Encoded;

    /**
     * @return \Comely\Buffer\Bytes32
     */
    public function hash(): Bytes32;
}

