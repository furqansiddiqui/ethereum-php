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

use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;
use FurqanSiddiqui\Ethereum\RLP\RLPEncoded;

/**
 * Class RLPEncodedTx
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
class RLPEncodedTx
{
    /** @var string */
    private string $encodedStr;
    /** @var bool */
    private bool $signed;
    /** @var string */
    private string $hash;

    /**
     * RLPEncodedTx constructor.
     * @param RLPEncoded $encoded
     */
    public function __construct(RLPEncoded $encoded)
    {
        $this->encodedStr = $encoded->toString();
        $this->signed = substr($this->encodedStr, -6) !== "018080";
        $this->hash = Keccak::hash(hex2bin($this->encodedStr), 256);
    }

    /**
     * @return string
     */
    public function serialized(): string
    {
        return $this->encodedStr;
    }

    /**
     * @return bool
     */
    public function isSigned(): bool
    {
        return $this->signed;
    }

    /**
     * @return string
     */
    public function hash(): string
    {
        return $this->hash;
    }
}
