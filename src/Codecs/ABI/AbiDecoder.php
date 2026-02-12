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

/**
 * Decodes the arguments from ABI-encoded data based on the provided type specifications.
 */
final class AbiDecoder extends AbstractAbiCodecs
{
    /**
     * @param array $types
     * @param string|ReadableBufferInterface $data
     * @return array
     */
    public static function decodeArgs(array $types, string|ReadableBufferInterface $data): array
    {
        $bin = self::normalizeInputString($data, allowEmpty: false);

        $n = count($types);
        $headSize = $n * 32;
        if (strlen($bin) < $headSize) {
            throw new \InvalidArgumentException("ABI data too short for head");
        }

        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $type = strtolower(trim((string)$types[$i]));
            $word = substr($bin, $i * 32, 32);

            $arr = self::parseArrayType($type);
            if ($arr !== null && $arr["length"] !== null && !self::isDynamicType($arr["base"])) {
                $count = $arr["length"];
                $need = $count * 32;
                $pos = $i * 32;

                if (strlen($bin) < $pos + $need) {
                    throw new \InvalidArgumentException("ABI data too short for fixed static array");
                }

                $result = [];
                for ($j = 0; $j < $count; $j++) {
                    $w = substr($bin, $pos + ($j * 32), 32);
                    $result[] = self::decodeStaticValue($arr["base"], $w);
                }

                $out[] = $result;
                $i += ($count - 1);
                continue;
            }

            if (self::isDynamicType($type)) {
                $offset = self::decodeInteger($word, 256, true);
                $offset = $offset instanceof \GMP ? (int)gmp_strval($offset, 10) : (int)$offset;

                if ($offset < 0 || (strlen($bin) - $offset) < 32) {
                    throw new \OutOfRangeException("Invalid dynamic offset");
                }

                $out[] = self::decodeDynamicValue($type, $bin, $offset);
            } else {
                $out[] = self::decodeStaticValue($type, $word);
            }
        }

        return $out;
    }

    /**
     * @param array $types
     * @param string|ReadableBufferInterface $data
     * @param bool $hasSelector
     * @return array
     */
    public static function decodeCall(array $types, string|ReadableBufferInterface $data, bool $hasSelector = true): array
    {
        $bin = self::normalizeInputString($data, allowEmpty: false);
        if ($hasSelector) {
            if (strlen($bin) < 4) {
                throw new \InvalidArgumentException("ABI call data too short for selector");
            }
            $bin = substr($bin, 4);
        }

        return self::decodeArgs($types, $bin);
    }

    /**
     * @param string $type
     * @param string $word 32-byte ABI word
     * @return mixed
     */
    private static function decodeStaticValue(string $type, string $word): mixed
    {
        $arr = self::parseArrayType($type);
        if ($arr !== null && $arr["length"] !== null) {
            if (self::isDynamicType($arr["base"])) {
                throw new \InvalidArgumentException("Fixed array of dynamic type must be decoded as dynamic");
            }

            throw new \InvalidArgumentException("Internal: fixed static arrays require slice decoding");
        }

        if (str_starts_with($type, "(")) {
            throw new \InvalidArgumentException("Tuple decoding is not implemented");
        }

        if (preg_match("/^u?int([0-9]{0,3})$/", $type, $m)) {
            $unsigned = str_starts_with($type, "uint");
            $bits = ($m[1] !== "") ? (int)$m[1] : 256;
            return self::decodeInteger($word, $bits, $unsigned);
        }

        if ($type === "bool") {
            return self::decodeBool($word);
        }

        if ($type === "address") {
            return self::decodeAddress($word);
        }

        if (preg_match("/^bytes([0-9]{1,2})$/", $type, $m)) {
            $len = (int)$m[1];
            return self::decodeFixedBytes($word, $len);
        }

        throw new \InvalidArgumentException("Unsupported static ABI type: " . $type);
    }

    /**
     * @param string $type
     * @param string $bin
     * @param int $offset
     * @return string|array
     */
    private static function decodeDynamicValue(string $type, string $bin, int $offset): string|array
    {
        $arr = self::parseArrayType($type);
        if ($arr !== null && $arr["length"] === null) {
            $base = $arr["base"];
            $lenWord = substr($bin, $offset, 32);
            $count = self::decodeInteger($lenWord, 256, true);
            $count = $count instanceof \GMP ? (int)gmp_strval($count, 10) : (int)$count;
            $cursor = $offset + 32;
            if ($count < 0) {
                throw new \InvalidArgumentException("Invalid array length");
            }

            $result = [];

            if (!self::isDynamicType($base)) {
                $need = $count * 32;
                if (strlen($bin) < $cursor + $need) {
                    throw new \InvalidArgumentException("ABI data too short for static array elements");
                }

                $baseArr = self::parseArrayType($base);
                $baseIsFixedStaticArray = $baseArr !== null && $baseArr["length"] !== null && !self::isDynamicType($baseArr["base"]);

                if ($baseIsFixedStaticArray) {
                    $k = $baseArr["length"];
                    $elemSize = $k * 32;
                    $need = $count * $elemSize;

                    if (strlen($bin) < $cursor + $need) {
                        throw new \InvalidArgumentException("ABI data too short for nested fixed static array elements");
                    }

                    for ($i = 0; $i < $count; $i++) {
                        $pos = $cursor + ($i * $elemSize);
                        $elem = [];
                        for ($j = 0; $j < $k; $j++) {
                            $w = substr($bin, $pos + ($j * 32), 32);
                            $elem[] = self::decodeStaticValue($baseArr["base"], $w);
                        }
                        $result[] = $elem;
                    }

                    return $result;
                }

                for ($i = 0; $i < $count; $i++) {
                    $word = substr($bin, $cursor + ($i * 32), 32);
                    $result[] = self::decodeStaticValue($base, $word);
                }

                return $result;
            }

            $headBytes = $count * 32;
            if (strlen($bin) < $cursor + $headBytes) {
                throw new \InvalidArgumentException("ABI data too short for dynamic array head");
            }

            for ($i = 0; $i < $count; $i++) {
                $offWord = substr($bin, $cursor + ($i * 32), 32);
                $elemOff = self::decodeInteger($offWord, 256, true);
                $elemOff = $elemOff instanceof \GMP ? (int)gmp_strval($elemOff, 10) : (int)$elemOff;

                $elemAbs = $cursor + $elemOff;
                if ($elemAbs < 0 || (strlen($bin) - $elemAbs) < 32) {
                    throw new \OutOfRangeException("Invalid element offset in dynamic array");
                }

                $result[] = self::decodeDynamicValue($base, $bin, $elemAbs);
            }

            return $result;
        }

        if ($arr !== null && $arr["length"] !== null && self::isDynamicType($arr["base"])) {
            $base = $arr["base"];
            $count = $arr["length"];

            $headBytes = $count * 32;
            if (strlen($bin) < $offset + $headBytes) {
                throw new \InvalidArgumentException("ABI data too short for fixed array head");
            }

            $result = [];
            for ($i = 0; $i < $count; $i++) {
                $offWord = substr($bin, $offset + ($i * 32), 32);
                $elemOff = self::decodeInteger($offWord, 256, true);
                $elemOff = $elemOff instanceof \GMP ? (int)gmp_strval($elemOff, 10) : (int)$elemOff;
                $elemAbs = $offset + $elemOff;
                if ($elemAbs < 0 || (strlen($bin) - $elemAbs) < 32) {
                    throw new \OutOfRangeException("Invalid element offset in fixed array");
                }

                $result[] = self::decodeDynamicValue($base, $bin, $elemAbs);
            }

            return $result;
        }

        if ($type === "bytes" || $type === "string") {
            $lenWord = substr($bin, $offset, 32);
            $len = self::decodeInteger($lenWord, 256, true);
            $len = $len instanceof \GMP ? (int)gmp_strval($len, 10) : (int)$len;

            if ($len < 0) {
                throw new \InvalidArgumentException("Invalid length");
            }

            $dataOff = $offset + 32;
            if (strlen($bin) < $dataOff + $len) {
                throw new \InvalidArgumentException("ABI data too short for bytes/string payload");
            }

            return substr($bin, $dataOff, $len);
        }

        if ($arr !== null && $arr["length"] !== null && !self::isDynamicType($arr["base"])) {
            $base = $arr["base"];
            $count = $arr["length"];
            $need = $count * 32;
            if (strlen($bin) < $offset + $need) {
                throw new \InvalidArgumentException("ABI data too short for fixed array");
            }

            $result = [];
            for ($i = 0; $i < $count; $i++) {
                $word = substr($bin, $offset + ($i * 32), 32);
                $result[] = self::decodeStaticValue($base, $word);
            }

            return $result;
        }

        if (str_starts_with($type, "(")) {
            throw new \InvalidArgumentException("Tuple decoding is not implemented");
        }

        throw new \InvalidArgumentException("Unsupported dynamic ABI type: " . $type);
    }

    /**
     * @param string $value
     * @param int $bits
     * @param bool $unSigned
     * @return int|string|\GMP
     */
    public static function decodeInteger(
        string $value,
        int    $bits = 256,
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