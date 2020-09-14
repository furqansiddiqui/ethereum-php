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

namespace FurqanSiddiqui\Ethereum\Math;

use Comely\DataTypes\BcMath\BcMath;
use Comely\DataTypes\BcNumber;

/**
 * Class WEIValue
 * @package FurqanSiddiqui\Ethereum\Math
 */
class WEIValue
{
    /** @var string */
    private string $eth;
    /** @var string */
    private string $gWei;
    /** @var string */
    private string $wei;

    /**
     * WEIValue constructor.
     * @param $eth
     */
    public function __construct($eth)
    {
        $eth = BcMath::isNumeric($eth);
        if (!$eth) {
            throw new \InvalidArgumentException('Invalid value (Wei/Gwei/Eth)');
        }

        $eth = $eth->scale(18)->mul(1);
        if ($eth->isNegative()) {
            throw new \InvalidArgumentException('Negative/signed values (Wei/Gwei/Eth) not supported');
        }

        $this->eth = $eth->value();
        $this->gWei = bcmul($this->eth, bcpow("10", "9", 0), 9);
        $this->wei = bcmul($this->eth, bcpow("10", "18", 0), 0);
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            "eth" => $this->eth,
            "Gwei" => $this->gWei,
            "wei" => $this->wei,
        ];
    }

    /**
     * @return string
     */
    public function eth(): string
    {
        return $this->eth;
    }

    /**
     * @return string
     */
    public function gWei(): string
    {
        return $this->gWei;
    }

    /**
     * @return string
     */
    public function wei(): string
    {
        return $this->wei;
    }

    /**
     * @return float
     */
    public function gWei_Double(): float
    {
        $dec = floatval($this->gWei);
        $bcVal = (new BcNumber($dec))->scale(9);
        if (!$bcVal->equals($this->gWei)) {
            throw new \OverflowException('Gwei value as float/double overflows');
        }

        return $dec;
    }

    /**
     * @return int
     */
    public function wei_Int(): int
    {
        $dec = intval($this->wei);
        $bcVal = (new BcNumber($dec))->scale(0);
        if (!$bcVal->equals($this->wei)) {
            throw new \OverflowException('Wei value as integer overflows');
        }

        return $dec;
    }
}
