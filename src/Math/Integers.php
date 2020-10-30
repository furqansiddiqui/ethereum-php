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

use Comely\DataTypes\BcNumber;

/**
 * Class Integers
 * @package FurqanSiddiqui\Ethereum\Math
 */
class Integers
{
    /**
     * @param string $hex
     * @return BcNumber
     */
    public static function Unpack(string $hex): BcNumber
    {
        if (substr($hex, 0, 2) === "0x") {
            $hex = substr($hex, 2);
        }

        if (!$hex) {
            return new BcNumber(0);
        }

        $hex = self::HexitPads($hex);
        return new BcNumber(gmp_strval(gmp_init($hex, 16), 10));
    }

    /**
     * @param string|int $dec
     * @return string
     */
    public static function Pack_UInt_BE($dec): string
    {
        $dec = self::checkValidInt($dec);
        return self::HexitPads(bin2hex(gmp_export(gmp_init($dec, 10), 1, GMP_MSW_FIRST | GMP_NATIVE_ENDIAN)));
    }

    /**
     * @param $dec
     * @return string
     */
    public static function Pack_UInt_LE($dec): string
    {
        $dec = self::checkValidInt($dec);
        return self::HexitPads(bin2hex(gmp_export(gmp_init($dec, 10), 1, GMP_LSW_FIRST | GMP_NATIVE_ENDIAN)));
    }

    /**
     * @param string $hex
     * @return string
     */
    public static function HexitPads(string $hex): string
    {
        if (strlen($hex) % 2 !== 0) {
            $hex = "0" . $hex;
        }

        return $hex;
    }

    /**
     * @param int|string|BcNumber $dec
     * @return int|string
     */
    public static function checkValidInt($dec)
    {
        if ($dec instanceof BcNumber && $dec->isInteger()) {
            $dec = $dec->value();
        }

        if (!is_int($dec)) {
            if (!is_string($dec) || !preg_match('/^-?(0|[1-9]+[0-9]*)$/', $dec)) {
                throw new \InvalidArgumentException('Argument must be a valid INT');
            }
        }

        return $dec;
    }
}
