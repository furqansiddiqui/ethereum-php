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
                $strLen = Integers::Unpack($byteReader->next($byteLen * $lenBytes))->value();
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
            $arrayLen = Integers::Unpack($byteReader->next($byteLen * $lenBytes))->value();
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
            return $this->encodeInteger($arg)->byteArray();
        } elseif (is_string($arg)) {
            if (preg_match('/^0x[a-f0-9]+$/i', $arg)) {
                return $this->encodeHex($arg)->byteArray();
            }

            return $this->encodeStr($arg)->byteArray();
        } elseif (is_array($arg)) {
            $buffer = [];
            foreach ($arg as $key => $value) {
                $buffer[] = $this->digest($value)->byteArray();
            }

            return $this->completeRLPEncodedObject($buffer);
        }

        throw new RLPEncodeException(sprintf('Cannot RLP encode value of type "%s"', ucfirst(gettype($arg))));
    }

    /**
     * @param array $obj
     * @return array
     */
    public function completeRLPEncodedObject(array $obj): array
    {
        $arraySize = $this->arrayCountBytes($obj);
        if ($arraySize >= 56) {
            $arraySizeLen = $this->intSize($arraySize);
            array_unshift($obj, $this->packInteger(247 + $arraySizeLen), $this->packInteger($arraySize));
        } else {
            array_unshift($obj, $this->packInteger(192 + $arraySize));
        }

        return $obj;
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
     * @return RLPEncoded
     */
    public function encodeHex(string $hex): RLPEncoded
    {
        return $this->_encodeString((new Base16($hex))->hexits(false), 2, false);
    }

    /**
     * @param string $str
     * @return RLPEncoded
     */
    public function encodeStr(string $str): RLPEncoded
    {
        return $this->_encodeString($str, 1);
    }

    /**
     * @param string $str
     * @param int $byteLen
     * @param bool|null $convert2Hex
     * @return RLPEncoded
     */
    private function _encodeString(string $str, int $byteLen = 1, ?bool $convert2Hex = null): RLPEncoded
    {
        if (!is_bool($convert2Hex)) {
            $convert2Hex = $this->convertAscii2Hex;
        }

        $strLen = (int)ceil(strlen($str) / $byteLen);
        if ($strLen === 1 && ord($str) < 0x80) {
            return new RLPEncoded([$convert2Hex ? ASCII::base16Encode($str)->value() : $str]);
        }

        if (!$strLen || !$str) {
            return new RLPEncoded([$this->packInteger(128)]);
        }

        $strArr = $convert2Hex ? str_split(ASCII::base16Encode($str)->value(), 2) : str_split($str, $byteLen);
        if ($strLen <= 55) {
            array_unshift($strArr, $this->packInteger(128 + $strLen));
            return new RLPEncoded($strArr);
        }

        $strLenSize = $this->intSize($strLen);
        array_unshift($strArr, $this->packInteger(183 + $strLenSize), str_split($this->packInteger($strLen), $byteLen));
        return new RLPEncoded($strArr);
    }

    /**
     * @param $dec
     * @return RLPEncoded
     */
    public function encodeInteger($dec): RLPEncoded
    {
        $dec = Integers::checkValidInt($dec);
        if ($dec == 0) {
            return new RLPEncoded([$this->packInteger(128)]);
        }

        if ($dec < 0x80) {
            $packed = $this->packInteger($dec);
        } else {
            $intSize = $this->intSize($dec);
            $packed = $this->packInteger(0x80 + $intSize) . $this->packInteger($dec);
        }

        return new RLPEncoded(str_split($packed, 2));
    }

    /**
     * @param int|string|BcNumber $dec
     * @return int
     */
    private function intSize($dec): int
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
