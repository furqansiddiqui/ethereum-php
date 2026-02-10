<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Codecs\RLP;

use Charcoal\Buffers\Buffer;
use Charcoal\Buffers\Support\ByteReader;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Ethereum\Unit\Wei;

/**
 * Provides functionality for encoding and decoding data using the RLP (Recursive Length Prefix) encoding scheme.
 * RLP is primarily used in Ethereum to encode nested arrays of binary data and is designed to encode structures
 * in a compact and deterministic way.
 */
final readonly class RlpCodec
{
    /**
     * @param string|ReadableBufferInterface $encoded
     * @return array
     */
    public static function decode(string|ReadableBufferInterface $encoded): array
    {
        $buffer = [];
        $parse = new ByteReader($encoded instanceof ReadableBufferInterface ? $encoded->bytes() : $encoded);
        while (!$parse->isEnd()) {
            unset($prefix, $bytes, $lenBytes, $strLen, $arrayLen, $arrayBytes);
            $prefix = $parse->readUInt8();
            if ($prefix < 128) {
                $buffer[] = chr($prefix);
                continue;
            }

            if ($prefix === 128) { // Empty String / Zero
                $buffer[] = "";
                continue;
            }

            if ($prefix < 184) { // String up to 55 bytes
                $strLen = $prefix - 128;
                $buffer[] = $parse->next($strLen);
                continue;
            }

            if ($prefix < 192) { // Long strings
                $longStrLen = $prefix - 183;
                $strLen = gmp_intval(gmp_import($parse->next($longStrLen), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN));
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
                $buffer[] = self::decode($parse->next($arrayLen));
                continue;
            }

            // Long Array
            $lenBytes = $prefix - 247;
            $arrayLen = gmp_intval(gmp_import($parse->next($lenBytes), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN));
            $buffer[] = self::decode($parse->next($arrayLen));
        }

        return $buffer;
    }

    /**
     * @param array $data
     * @return ReadableBufferInterface
     */
    public static function encode(array $data): ReadableBufferInterface
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

            if ($value instanceof Wei) {
                $bigIntValue = $value->wei;
            } elseif ($value instanceof \GMP) {
                $bigIntValue = $value;
            }

            if (isset($bigIntValue)) {
                if (gmp_cmp($bigIntValue, 128) < 0) {
                    $value = gmp_intval($bigIntValue);
                }
            }

            if (is_int($value)) {
                if ($value < 0) {
                    throw new \UnderflowException("Unsigned integer required");
                }

                if ($value === 0) {
                    $buffer->writeUInt8(0x80);
                    continue;
                }

                if ($value < 0x80) {
                    $buffer->writeUInt8($value);
                    continue;
                }

                $value = gmp_export($value, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
            }

            if (isset($bigIntValue)) {
                $value = gmp_export($bigIntValue, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
            }

            if (is_string($value) || $value instanceof ReadableBufferInterface) {
                if ($value instanceof ReadableBufferInterface) {
                    $value = $value->bytes();
                }

                $strlen = strlen($value);
                if ($strlen === 0) {
                    $buffer->writeUInt8(0x80);
                    continue;
                }

                if ($strlen <= 55) {
                    $buffer->writeUInt8(0x80 + $strlen);
                    $buffer->append($value);
                    continue;
                }

                $strLenBytes = gmp_export($strlen, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
                $buffer->writeUInt8(183 + strlen($strLenBytes));
                $buffer->append($strLenBytes);
                $buffer->append($value);
                continue;
            }

            if (is_array($value)) {
                $arrayCount = count($value);
                if ($arrayCount === 0) {
                    $buffer->writeUInt8(192); // 0xc0
                    continue;
                }

                $arrayEncoded = self::encode($value);
                $arrayEncodedLen = $arrayEncoded->length();
                if ($arrayEncodedLen <= 55) {
                    $buffer->writeUInt8(0xc0 + $arrayEncodedLen);
                    $buffer->append($arrayEncoded);
                    continue;
                }

                $arrayLenBytes = gmp_export($arrayEncodedLen, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
                $buffer->writeUInt8(247 + strlen($arrayLenBytes));
                $buffer->append($arrayLenBytes);
                $buffer->append($arrayEncoded);
                continue;
            }

            throw new \RuntimeException(sprintf('Cannot RLP encode the value of type "%s"',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return $buffer;
    }
}
