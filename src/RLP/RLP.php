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

namespace FurqanSiddiqui\Ethereum\RLP;

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\BigInteger;
use Comely\Buffer\BigInteger\BigEndian;
use Comely\Buffer\Buffer;
use Comely\Buffer\Exception\ByteReaderUnderflowException;
use FurqanSiddiqui\Ethereum\Buffers\WEIAmount;
use FurqanSiddiqui\Ethereum\Exception\RLP_DecodeException;
use FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException;

/**
 * Class RLP
 * @package FurqanSiddiqui\Ethereum
 */
class RLP
{
    /**
     * @param \Comely\Buffer\AbstractByteArray $encoded
     * @return mixed
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_DecodeException
     */
    public static function Decode(AbstractByteArray $encoded): mixed
    {
        try {
            return static::_Decode($encoded)[0];
        } catch (ByteReaderUnderflowException $e) {
            throw new RLP_DecodeException($e->getMessage());
        }
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $encoded
     * @return array
     * @throws \Comely\Buffer\Exception\ByteReaderUnderflowException
     */
    private static function _Decode(AbstractByteArray $encoded): array
    {
        $buffer = [];
        $parse = $encoded->read();
        while (!$parse->isEnd()) {
            unset($prefix, $bytes, $lenBytes, $strLen, $arrayLen, $arrayBytes);

            $prefix = $parse->readUInt8();
            if ($prefix < 128) {
                $buffer[] = $prefix;
                continue;
            }

            if ($prefix === 128) { // Empty String / Zero
                $buffer[] = 0;
                continue;
            }

            if ($prefix < 184) { // String up to 55 bytes
                $strLen = $prefix - 128;
                $buffer[] = $parse->next($strLen);
                continue;
            }

            if ($prefix < 192) { // Long strings
                $longStrLen = $prefix - 183;
                $strLen = gmp_intval(BigEndian::GMP_Unpack($parse->next($longStrLen)));
                $buffer[] = $parse->next($strLen);
                continue;
            }

            if ($prefix === 192) {
                $buffer[] = [];
                continue;
            }

            // Arrays
            if ($prefix < 248) {
                $arrayLen = $prefix - 192;
                $arrayBytes = $parse->next($arrayLen);
                $buffer[] = self::_Decode(new Buffer($arrayBytes));
                continue;
            }

            // Long Array
            $lenBytes = $prefix - 247;
            $arrayLen = gmp_intval(BigEndian::GMP_Unpack($parse->next($lenBytes)));
            $buffer[] = self::_Decode(new Buffer($parse->next($arrayLen)));
        }

        return $buffer;
    }

    /**
     * @param mixed $data
     * @return \Comely\Buffer\AbstractByteArray
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException
     */
    public static function Encode(mixed $data): AbstractByteArray
    {
        return static::_Encode([$data]);
    }

    /**
     * @param array $data
     * @return \Comely\Buffer\AbstractByteArray
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException
     */
    private static function _Encode(array $data): AbstractByteArray
    {
        $buffer = new Buffer();
        foreach ($data as $value) {
            unset($bigIntValue, $strLenBytes);

            if (is_bool($value)) {
                $value = (int)$value;
            }

            if (is_null($value)) {
                $value = "";
            }

            if (is_string($value) && strlen($value) === 1) {
                if (ord($value) <= 127) {
                    $buffer->append($value);
                    continue;
                }
            }

            if ($value instanceof WEIAmount) {
                $bigIntValue = $value->wei;
            } elseif ($value instanceof BigInteger) {
                $bigIntValue = $value->toGMP();
            }

            if (isset($bigIntValue)) {
                if (gmp_cmp($bigIntValue, gmp_init(0x80, 10)) < 0) {
                    $value = gmp_intval($bigIntValue);
                }
            }

            if (is_int($value)) {
                if ($value < 0x80) {
                    if ($value <= 0) {
                        $buffer->appendUInt8(0x80);
                        continue;
                    }

                    $buffer->appendUInt8($value);
                    continue;
                }

                $value = BigEndian::GMP_Pack($value);
            }

            if (isset($bigIntValue)) {
                $value = gmp_export($bigIntValue, 1, GMP_MSW_FIRST | GMP_NATIVE_ENDIAN);
            }

            if (is_string($value) || $value instanceof AbstractByteArray) {
                if ($value instanceof AbstractByteArray) {
                    $value = $value->raw();
                }

                $strlen = strlen($value);
                if ($strlen === 0) {
                    $buffer->appendUInt8(0x80);
                    continue;
                }

                if ($strlen <= 55) {
                    $buffer->appendUInt8(0x80 + $strlen);
                    $buffer->append($value);
                    continue;
                }

                $strLenBytes = BigEndian::GMP_Pack($strlen);
                $buffer->appendUInt8(183 + strlen($strLenBytes));
                $buffer->append($strLenBytes);
                $buffer->append($value);
                continue;
            }

            if (is_array($value)) {
                $arrayCount = count($value);
                if ($arrayCount === 0) {
                    $buffer->appendUInt8(192); // 0xc0
                    continue;
                }

                $arrayEncoded = self::_Encode($value);
                $arrayEncodedLen = $arrayEncoded->len();
                if ($arrayEncodedLen <= 55) {
                    $buffer->appendUInt8(0xc0 + $arrayEncodedLen);
                    $buffer->append($arrayEncoded);
                    continue;
                }

                $arrayLenBytes = BigEndian::GMP_Pack($arrayEncodedLen);
                $buffer->appendUInt8(247 + strlen($arrayLenBytes));
                $buffer->append($arrayLenBytes);
                $buffer->append($arrayEncoded);
                continue;
            }

            throw new RLP_EncodeException(sprintf(
                'Cannot RLP encode the value of type "%s"',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return $buffer;
    }
}
