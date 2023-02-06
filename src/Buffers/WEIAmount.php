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

namespace FurqanSiddiqui\Ethereum\Buffers;

use FurqanSiddiqui\Ethereum\Exception\BadWEIAmountException;

/**
 * Class WEIAmount
 * @package FurqanSiddiqui\Ethereum\Buffers
 */
class WEIAmount
{
    /** @var \GMP */
    public readonly \GMP $wei;
    /** @var string */
    public readonly string $eth;
    /** @var string */
    public readonly string $gWei;

    /**
     * @param int|string $ethAmount
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\BadWEIAmountException
     */
    public static function fromETH(int|string $ethAmount): static
    {
        if (is_int($ethAmount)) {
            $ethAmount = strval($ethAmount);
        }

        if (!preg_match('/^(0|[1-9][0-9]*)(\.[0-9]+)?$/', $ethAmount)) {
            throw new BadWEIAmountException('Bad ETH value');
        }

        return new static(gmp_init(bcmul(bcmul($ethAmount, "1", 18), bcpow("10", "18", 0), 0), 10));
    }

    /**
     * @param int|string $gWeiAmount
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\BadWEIAmountException
     */
    public static function fromGWei(int|string $gWeiAmount): static
    {
        if (is_int($gWeiAmount)) {
            $gWeiAmount = strval($gWeiAmount);
        }

        if (!preg_match('/^(0|[1-9][0-9]+)(\.[0-9]+)?$/', $gWeiAmount)) {
            throw new BadWEIAmountException('Bad GWei value');
        }

        return new static(gmp_init(bcmul(bcmul($gWeiAmount, "1", 9), bcpow("10", "9", 0), 0), 10));
    }

    /**
     * @param int|string|\GMP $wei
     * @throws \FurqanSiddiqui\Ethereum\Exception\BadWEIAmountException
     */
    public function __construct(int|string|\GMP $wei)
    {
        $this->wei = $this->getWEIValue($wei);
        $this->eth = bcdiv(gmp_strval($this->wei, 10), bcpow("10", "18", 0), 18);
        $this->gWei = bcdiv(gmp_strval($this->wei, 10), bcpow("10", "9", 0), 9);
    }

    /**
     * @param int|string|\FurqanSiddiqui\Ethereum\Buffers\WEIAmount|\GMP $wei
     * @return \GMP
     * @throws \FurqanSiddiqui\Ethereum\Exception\BadWEIAmountException
     */
    private function getWEIValue(int|string|self|\GMP $wei): \GMP
    {
        if ($wei instanceof \GMP) {
            return $wei;
        }

        if ($wei instanceof self) {
            return $wei->wei;
        }

        if (is_string($wei)) {
            if (preg_match('/^(0|[1-9][0-9]+)$/', $wei)) {
                return gmp_init($wei, 10);
            } elseif (preg_match('/^(0x)?[a-f0-9]+$/i', $wei)) {
                return gmp_init($wei, 16);
            }
        }

        if (is_int($wei)) {
            return gmp_init($wei, 10);
        }

        throw new BadWEIAmountException();
    }
}
