<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Unit;

/**
 * Immutable class to handle conversions between Ethereum denominations: Wei, GWei, and ETH.
 */
final readonly class Wei
{
    public \GMP $wei;
    private string $eth;
    private string $gWei;

    /**
     * @param int|string|\GMP $wei
     */
    public function __construct(int|string|\GMP $wei)
    {
        $this->wei = $this->getGMP($wei);
    }

    /**
     * @return string
     */
    public function eth(): string
    {
        return $this->eth ??= bcdiv(gmp_strval($this->wei, 10), "1000000000000000000", 18);
    }

    /**
     * @return string
     */
    public function gWei(): string
    {
        return $this->gWei ??= bcdiv(gmp_strval($this->wei, 10), "1000000000", 9);
    }

    /**
     * @param bool $getAll
     * @return array
     */
    public function toArray(bool $getAll): array
    {
        return [
            "wei" => gmp_strval($this->wei, 10),
            "eth" => $getAll ? $this->eth() : ($this->eth ?? null),
            "gWei" => $getAll ? $this->gWei() : ($this->gWei ?? null),
        ];
    }

    /**
     * @param int|string $value
     * @return self
     */
    public static function fromETH(int|string $value): self
    {
        if (is_int($value)) {
            $value = strval($value);
        }

        if (!preg_match("/^(0|[1-9][0-9]*)(\.[0-9]+)?$/", $value)) {
            throw new \InvalidArgumentException("Bad ETH value: " . $value);
        }

        return new self(gmp_init(bcmul($value, "1000000000000000000", 0)));
    }

    /**
     * @param int|string $gWeiAmount
     * @return self
     */
    public static function fromGWei(int|string $gWeiAmount): self
    {
        if (is_int($gWeiAmount)) {
            $gWeiAmount = strval($gWeiAmount);
        }

        if (!preg_match("/^(0|[1-9][0-9]*)(\.[0-9]+)?$/", $gWeiAmount)) {
            throw new \InvalidArgumentException("Bad GWei value: " . $gWeiAmount);
        }

        return new self(gmp_init(bcmul($gWeiAmount, "1000000000", 0)));
    }

    /**
     * @param int|string|Wei|\GMP $wei
     * @return \GMP
     */
    private function getGMP(int|string|self|\GMP $wei): \GMP
    {
        if ($wei instanceof \GMP) {
            return $wei;
        }

        if ($wei instanceof self) {
            return $wei->wei;
        }

        if (is_string($wei)) {
            if (preg_match("/^(0|[1-9][0-9]+)$/", $wei)) {
                return gmp_init($wei, 10);
            } elseif (preg_match("/^(0x)?[a-f0-9]+$/i", $wei)) {
                return gmp_init($wei, 16);
            }
        }

        if (is_int($wei)) {
            return gmp_init($wei, 10);
        }

        throw new \InvalidArgumentException("Invalid WEI value");
    }
}