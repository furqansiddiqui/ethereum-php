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

/**
 * Class Integers
 * @package FurqanSiddiqui\Ethereum\Math
 */
class Integers
{
    /**
     * @param string $hex
     * @param int|null $size
     * @return int
     */
    public static function Unpack_UInt_BE(string $hex, ?int $size = null): int
    {
        if (strlen($hex) % 2 !== 0) {
            $hex = "0" . $hex;
        }

        if (!$size) {
            $size = strlen($hex) / 2;
        }

        if ($size < 1 || $size > 8) {
            throw new \OutOfRangeException('Invalid unpack integer size');
        }

        switch ($size) {
            case 8:
                return unpack("J", $hex)[0];
            case 4:
                return unpack("N", $hex)[0];
            case 2:
                return unpack("n", $hex)[1];
            case 1:
                return hexdec($hex);
            default:
                throw new \InvalidArgumentException('Failed to unpack %d byte integer', $size);
        }
    }

    /**
     * @param int $dec
     * @param bool $ltrim
     * @return string
     */
    public static function Pack_UInt_BE(int $dec, bool $ltrim = true): string
    {
        if ($dec <= 0xff) {
            $packed = dechex($dec);
        } elseif ($dec <= 0xffff) {
            $packed = bin2hex(pack("n", $dec));
        } elseif ($dec <= 0xffffffff) {
            $packed = bin2hex(pack("N", $dec));
        } else {
            $packed = bin2hex(pack("J", $dec));
        }

        return self::HexitPads($packed, $ltrim);
    }

    /**
     * @param string $hex
     * @param bool $ltrim remove leading 0s on left side of packed hexadecimal string?
     * @return string
     */
    public static function HexitPads(string $hex, bool $ltrim = true): string
    {
        if ($ltrim && strlen($hex) > 2) {
            $hex = ltrim($hex, "0");
        }

        if (strlen($hex) % 2 !== 0) {
            $hex = "0" . $hex;
        }

        return $hex;
    }
}
