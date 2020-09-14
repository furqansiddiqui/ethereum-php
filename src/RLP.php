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

namespace FurqanSiddiqui\Ethereum;

use Comely\DataTypes\BcNumber;
use Comely\DataTypes\Buffer\Base16;
use Comely\DataTypes\Buffer\Binary;
use Comely\DataTypes\Strings\ASCII;
use FurqanSiddiqui\Ethereum\Exception\RLPEncodeException;
use FurqanSiddiqui\Ethereum\Math\Integers;
use FurqanSiddiqui\Ethereum\RLP\RLPEncoded;

/**
 * Class RLP
 * @package FurqanSiddiqui\Ethereum
 */
class RLP
{
    /**
     * @param string $rlpEncodedStr
     * @param bool $base16Encoded
     * @return array
     */
    public static function Decode(string $rlpEncodedStr, bool $base16Encoded = true): array
    {
        $buffer = [];
        $binary = new Binary($rlpEncodedStr);
        $byteLen = $base16Encoded ? 2 : 1;
        $byteReader = $binary->read();
        while (!$byteReader->isEnd()) {
            unset($prefix, $bytes, $lenBytes, $strLen, $arrayLen, $arrayBytes);

            $prefix = hexdec($byteReader->next(2));
            if ($prefix < 128) { // Single byte
                $buffer[] = $prefix;
                continue;
            }

            if ($prefix === 128) {
                $buffer[] = "";
                continue;
            }

            if ($prefix < 184) { // String up to 55 bytes
                $strLen = $prefix - 128;
                $buffer[] = $byteReader->next($byteLen * $strLen);
                continue;
            }

            if ($prefix < 192) { // Long strings
                $lenBytes = $prefix - 183;
                $strLen = Integers::Unpack($byteReader->next($byteLen * $lenBytes));
                $buffer[] = $byteReader->next($byteLen * $strLen);
                continue;
            }

            if ($prefix === 192) {
                $buffer[] = [];
                continue;
            }

            // Arrays
            if ($prefix < 248) {
                $arrayLen = $prefix - 192;
                $arrayBytes = $byteReader->next($byteLen * $arrayLen);
                $buffer[] = self::Decode($arrayBytes);
                continue;
            }

            // Long Array
            $lenBytes = $prefix - 247;
            $arrayLen = Integers::Unpack($byteReader->next($byteLen * $lenBytes));
            $buffer[] = self::Decode($byteReader->next($byteLen * $arrayLen));
        }

        return $buffer;
    }

    /**
     * @param $args
     * @param bool $convertASCII
     * @return RLPEncoded
     * @throws RLPEncodeException
     */
    public static function Encode($args, bool $convertASCII = true): RLPEncoded
    {
        return (new self())->convertASCII($convertASCII)->digest($args);
    }

    /** @var bool */
    private bool $convertAscii2Hex = true;

    /**
     * @param bool $convert
     * @return $this
     */
    public function convertASCII(bool $convert): self
    {
        $this->convertAscii2Hex = $convert;
        return $this;
    }

    /**
     * @param $arg
     * @return RLPEncoded
     * @throws RLPEncodeException
     */
    public function digest($arg): RLPEncoded
    {
        return new RLPEncoded($this->_digest($arg));
    }

    /**
     * @param $arg
     * @return array|string[]
     * @throws RLPEncodeException
     */
    private function _digest($arg): array
    {
        if (is_null($arg)) {
            $arg = "";
        }

        if (is_int($arg)) {
            return $this->encodeInteger($arg);
        } elseif (is_string($arg)) {
            if (preg_match('/^0x[a-f0-9]+$/i', $arg)) {
                return $this->encodeHex($arg);
            }

            return $this->encodeStr($arg);
        } elseif (is_array($arg)) {
            $buffer = [];
            foreach ($arg as $key => $value) {
                $buffer[] = $this->digest($value)->byteArray();
            }

            $arraySize = $this->arrayCountBytes($buffer);
            if ($arraySize >= 56) {
                $arraySizeLen = $this->intSize($arraySize);
                array_unshift($buffer, $this->packInteger(247 + $arraySizeLen), $this->packInteger($arraySize));
            } else {
                array_unshift($buffer, $this->packInteger(192 + $arraySize));
            }

            return $buffer;
        }

        throw new RLPEncodeException(sprintf('Cannot RLP encode value of type "%s"', ucfirst(gettype($arg))));
    }

    /**
     * @param array $arr
     * @return int
     */
    private function arrayCountBytes(array $arr): int
    {
        $bytes = 0;
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $bytes += $this->arrayCountBytes($value);
                continue;
            }

            $bytes++;
        }

        return $bytes;
    }

    /**
     * @param string $hex
     * @return string[]
     */
    public function encodeHex(string $hex): array
    {
        return $this->_encodeString((new Base16($hex))->hexits(false), 2, false);
    }

    /**
     * @param string $str
     * @return string[]
     */
    public function encodeStr(string $str): array
    {
        return $this->_encodeString($str, 1);
    }

    /**
     * @param string $str
     * @param int $byteLen
     * @param bool|null $convert2Hex
     * @return string[]
     */
    public function _encodeString(string $str, int $byteLen = 1, ?bool $convert2Hex = null): array
    {
        if (!is_bool($convert2Hex)) {
            $convert2Hex = $this->convertAscii2Hex;
        }

        $strLen = (int)ceil(strlen($str) / $byteLen);
        if ($strLen === 1 && ord($str) < 0x80) {
            if ($convert2Hex) {
                return [ASCII::base16Encode($str)->value()];
            } else {
                return [$str];
            }
        }

        if (!$strLen || !$str) {
            return [$this->packInteger(128)];
        }

        $strArr = $convert2Hex ? str_split(ASCII::base16Encode($str)->value(), 2) : str_split($str, 1);
        if ($strLen <= 55) {
            array_unshift($strArr, $this->packInteger(128 + $strLen));
            return $strArr;
        }

        $strLenSize = $this->intSize($strLen);
        array_unshift($strArr, $this->packInteger(183 + $strLenSize), $this->packInteger($strLen));
        return $strArr;
    }

    /**
     * @param string|int $dec
     * @return string[]
     */
    public function encodeInteger($dec): array
    {
        $dec = Integers::checkValidInt($dec);
        if ($dec === 0) {
            return [$this->packInteger(128)];
        }

        if ($dec < 0x80) {
            $packed = $this->packInteger($dec);
        } else {
            $intSize = $this->intSize($dec);
            $packed = $this->packInteger(0x80 + $intSize) . $this->packInteger($dec);
        }

        return str_split($packed, 2);
    }

    /**
     * @param int $dec
     * @return int
     */
    private function intSize(int $dec): int
    {
        return strlen($this->packInteger($dec)) / 2;
    }

    /**
     * @param int|string|BcNumber $dec
     * @return string
     */
    private function packInteger($dec): string
    {
        return Integers::Pack_UInt_BE($dec);
    }
}
