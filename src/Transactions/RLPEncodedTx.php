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

use Comely\DataTypes\Buffer\Base16;
use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;
use FurqanSiddiqui\Ethereum\RLP\RLPEncoded;

/**
 * Class RLPEncodedTx
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
class RLPEncodedTx
{
    /** @var Base16 */
    private Base16 $encodedStr;
    /** @var bool */
    private bool $signed;
    /** @var Base16 */
    private Base16 $hash;

    /**
     * RLPEncodedTx constructor.
     * @param RLPEncoded $encoded
     */
    public function __construct(RLPEncoded $encoded)
    {
        $encodedStr = $encoded->toString();
        $this->encodedStr = new Base16($encodedStr);
        $this->encodedStr->readOnly(true);
        $this->signed = substr($encodedStr, -6) !== "018080";
        $this->hash = new Base16(Keccak::hash(hex2bin($encodedStr), 256));
        $this->hash->readOnly(true);
    }

    /**
     * @return Base16
     */
    public function serialized(): Base16
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
     * @return Base16
     */
    public function hash(): Base16
    {
        return $this->hash;
    }
}
