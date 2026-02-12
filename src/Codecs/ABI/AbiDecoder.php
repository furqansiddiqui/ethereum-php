<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Codecs\ABI;

use Charcoal\Buffers\Types\Bytes20;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;

final class AbiDecoder extends AbstractAbiCodecs
{
    /**
     * @param string $value
     * @param int $bits
     * @param bool $unSigned
     * @return int|string|\GMP
     */
    public static function decodeInteger(
        string $value,
        int    $bits = 8,
        bool   $unSigned = false
    ): int|string|\GMP
    {
        if ($bits < 8 || $bits > 256 || ($bits % 8) !== 0) {
            throw new \InvalidArgumentException("Invalid bits length");
        }

        $value = self::decodeFixedBytes($value, 32);
        if (strlen($value) !== 32) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid ABI word length; Expected 32 bytes, got: %d",
                strlen($value)
            ));
        }

        $g = gmp_import($value, 1, GMP_BIG_ENDIAN | GMP_MSW_FIRST);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$g) {
            throw new \RuntimeException("Failed to decode integer");
        }

        if ($bits < 256) {
            $mask = gmp_sub(gmp_pow(2, $bits), 1);
            $low = gmp_and($g, $mask);

            if ($unSigned) {
                if (gmp_cmp($g, $low) !== 0) {
                    throw new \UnexpectedValueException(sprintf(
                        "Decoded value exceeds %d bits for uint%d",
                        $bits,
                        $bits
                    ));
                }

                $g = $low;
            } else {
                $signBit = gmp_pow(2, $bits - 1);
                $isNeg = gmp_cmp(gmp_and($low, $signBit), 0) !== 0;

                if ($isNeg) {
                    $expected = gmp_add($low, gmp_sub(gmp_pow(2, 256), gmp_pow(2, $bits)));
                    if (gmp_cmp($g, $expected) !== 0) {
                        throw new \UnexpectedValueException(sprintf(
                            "Invalid sign extension for int%d",
                            $bits
                        ));
                    }
                } else {
                    if (gmp_cmp($g, $low) !== 0) {
                        throw new \UnexpectedValueException(sprintf(
                            "Invalid sign extension for int%d",
                            $bits
                        ));
                    }
                }

                if ($isNeg) {
                    $g = gmp_sub($low, gmp_pow(2, $bits));
                } else {
                    $g = $low;
                }
            }
        } else {
            if (!$unSigned) {
                $signBit = gmp_pow(2, 255);
                if (gmp_cmp(gmp_and($g, $signBit), 0) !== 0) {
                    $g = gmp_sub($g, gmp_pow(2, 256));
                }
            }
        }

        $min = gmp_init((string)PHP_INT_MIN, 10);
        $max = gmp_init((string)PHP_INT_MAX, 10);
        if (gmp_cmp($g, $min) >= 0 && gmp_cmp($g, $max) <= 0) {
            return (int)gmp_strval($g, 10);
        }

        return gmp_strval($g, 10);
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function decodeBool(string $value): bool
    {
        $bytes = self::decodeFixedBytes($value, 32);
        if (substr($bytes, 0, 31) !== str_repeat("\0", 31)) {
            throw new \InvalidArgumentException("Invalid bool encoding");
        }

        return match ($bytes[31]) {
            "\0" => false,
            "\1" => true,
            default => throw new \InvalidArgumentException("Invalid bool value"),
        };
    }

    /**
     * @param mixed $value
     * @return EthereumAddress
     */
    public static function decodeAddress(mixed $value): EthereumAddress
    {
        $bytes = self::decodeFixedBytes($value, 32);
        return new EthereumAddress(new Bytes20(substr($bytes, -20)));
    }

    /**
     * @param string|ReadableBufferInterface $value
     * @param int $length
     * @return string
     */
    public static function decodeFixedBytes(string|ReadableBufferInterface $value, int $length = 32): string
    {
        if ($length < 1 || $length > 32) {
            throw new \InvalidArgumentException("Invalid bytes length; Expected 1-32, got: " . $length);
        }

        $binary = self::normalizeInputString($value, allowEmpty: false);
        if (strlen($binary) !== 32) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid bytes length; Expected 32 bytes, got: %d",
                strlen($binary)
            ));
        }

        return substr($binary, 0, $length);
    }
}