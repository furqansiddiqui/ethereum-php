<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Codecs\ABI;

use Charcoal\Buffers\Types\Bytes32;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Ethereum\Crypto\Keccak256;

/**
 * Encodes a list of arguments into their ABI-encoded representation.
 */
final class AbiEncoder extends AbstractAbiCodecs
{
    /**
     * @param array $types
     * @param array $values
     * @return string
     */
    public static function encodeArgs(array $types, array $values): string
    {
        if (count($types) !== count($values)) {
            throw new \InvalidArgumentException("Types/values count mismatch");
        }

        $head = "";
        $tail = "";
        $n = count($types);
        $headSize = $n * 32;
        for ($i = 0; $i < $n; $i++) {
            $type = strtolower(trim((string)$types[$i]));
            $value = $values[$i];
            if (self::isDynamicType($type)) {
                $offset = $headSize + strlen($tail);
                $head .= self::encodeInteger($offset, 256, true)->bytes();
                $tail .= self::encodeDynamicValue($type, $value);
            } else {
                $head .= self::encodeStaticValue($type, $value);
            }
        }

        return $head . $tail;
    }

    /**
     * @param string $signature
     * @param array $types
     * @param array $values
     * @return string
     */
    public static function encodeCall(string $signature, array $types, array $values): string
    {
        return substr(Keccak256::hash($signature, true), 0, 4) . self::encodeArgs($types, $values);
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return string
     */
    private static function encodeStaticValue(string $type, mixed $value): string
    {
        $arr = self::parseArrayType($type);
        if ($arr !== null && $arr["length"] !== null) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid value for fixed array type");
            }

            if (count($value) !== $arr["length"]) {
                throw new \InvalidArgumentException(sprintf(
                    "Invalid fixed array length; Expected %d, got: %d",
                    $arr["length"],
                    count($value)
                ));
            }

            $out = "";
            foreach ($value as $item) {
                $out .= self::encodeStaticValue($arr["base"], $item);
            }
            return $out;
        }

        if (str_starts_with($type, "(")) {
            throw new \InvalidArgumentException("Tuple encoding is not implemented");
        }

        if (preg_match("/^u?int([0-9]{0,3})$/", $type, $m)) {
            $unsigned = str_starts_with($type, "uint");
            $bits = ($m[1] !== "") ? (int)$m[1] : 256;
            return self::encodeInteger($value, $bits, $unsigned)->bytes();
        }

        if ($type === "bool") {
            return self::encodeBool($value)->bytes();
        }

        if ($type === "address") {
            return self::encodeAddress($value)->bytes();
        }

        if ($type === "bytes32") {
            return self::encodeFixedBytes($value, 32)->bytes();
        }

        if (preg_match("/^bytes([0-9]{1,2})$/", $type, $m)) {
            $len = (int)$m[1];
            return self::encodeFixedBytes($value, $len)->bytes();
        }

        throw new \InvalidArgumentException("Unsupported static ABI type: " . $type);
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return string
     */
    private static function encodeDynamicValue(string $type, mixed $value): string
    {
        $arr = self::parseArrayType($type);
        if ($arr !== null && $arr["length"] === null) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid value for dynamic array type");
            }

            $count = count($value);
            $head = self::encodeInteger($count, 256, true)->bytes();
            $base = $arr["base"];
            if (self::isDynamicType($base)) {
                return $head . self::encodeList($value, $base, $count);
            }

            $out = $head;
            foreach ($value as $item) {
                $out .= self::encodeStaticValue($base, $item);
            }

            return $out;
        }

        if ($arr !== null && $arr["length"] !== null) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid value for fixed array type");
            }

            if (count($value) !== $arr["length"]) {
                throw new \InvalidArgumentException(sprintf(
                    "Invalid fixed array length; Expected %d, got: %d",
                    $arr["length"],
                    count($value)
                ));
            }

            $count = $arr["length"];
            $base = $arr["base"];

            if (!self::isDynamicType($base)) {
                $out = "";
                foreach ($value as $item) {
                    $out .= self::encodeStaticValue($base, $item);
                }
                return $out;
            }

            return self::encodeList($value, $base, $count);
        }

        if ($type === "bytes") {
            if ($value instanceof ReadableBufferInterface) {
                $bin = $value->bytes();
            } else {
                $bin = is_string($value) ? $value : (string)$value;
                if (str_starts_with($bin, "0x")) {
                    $bin = self::normalizeInputString($bin, allowEmpty: true);
                }
            }

            return self::encodeLengthPrefixedBytes($bin);
        }

        if ($type === "string") {
            $bin = is_string($value) ? $value : (string)$value;
            return self::encodeLengthPrefixedBytes($bin);
        }

        if (str_starts_with($type, "(")) {
            throw new \InvalidArgumentException("Tuple encoding is not implemented");
        }

        throw new \InvalidArgumentException("Unsupported dynamic ABI type: " . $type);
    }

    /**
     * @param array $list
     * @param string $type
     * @param int $count
     * @return string
     */
    public static function encodeList(array $list, string $type, int $count): string
    {
        if (count($list) !== $count) {
            throw new \InvalidArgumentException("List count mismatch");
        }

        $head = "";
        $buffer = "";
        $elemHeadSize = $count * 32;
        for ($i = 0; $i < $count; $i++) {
            $offset = $elemHeadSize + strlen($buffer);
            $head .= self::encodeInteger($offset, 256, true)->bytes();
            $buffer .= self::encodeDynamicValue($type, $list[$i]);
        }

        return $head . $buffer;
    }

    /**
     * @param string $bin
     * @return string
     */
    private static function encodeLengthPrefixedBytes(string $bin): string
    {
        $len = strlen($bin);
        $out = self::encodeInteger($len, 256, true)->bytes();
        $pad = ($len % 32) === 0 ? 0 : (32 - ($len % 32));
        return $out . $bin . str_repeat("\0", $pad);
    }

    /**
     * @param int|string|\GMP $value
     * @param int $bits
     * @param bool $unSigned
     * @return Bytes32
     */
    public static function encodeInteger(
        int|string|\GMP $value,
        int             $bits = 256,
        bool            $unSigned = false,
    ): Bytes32
    {
        if ($bits < 8 || $bits > 256 || ($bits % 8) !== 0) {
            throw new \InvalidArgumentException("Invalid bits length");
        }

        $value = $value instanceof \GMP ? $value : gmp_init((string)$value, 10);
        $min = $unSigned ? 0 : gmp_neg(gmp_pow(2, $bits - 1));
        $max = $unSigned ? gmp_sub(gmp_pow(2, $bits), 1) : gmp_sub(gmp_pow(2, $bits - 1), 1);
        if (gmp_cmp($value, $min) < 0 || gmp_cmp($value, $max) > 0) {
            throw new \InvalidArgumentException(sprintf(
                "Integer value out of range; Type %sInt%d",
                $unSigned ? "U" : "",
                $bits
            ));
        }

        if (!$unSigned && gmp_cmp($value, 0) < 0) {
            $value = gmp_add($value, gmp_pow(2, $bits));
        }

        $bytes = gmp_export($value, 1, GMP_BIG_ENDIAN | GMP_MSW_FIRST);
        if ($bytes === "" || $bytes === false) {
            $bytes = "\0";
        }

        return new Bytes32(str_pad($bytes, 32, "\0", STR_PAD_LEFT));
    }

    /**
     * @param mixed $value
     * @return Bytes32
     */
    public static function encodeBool(mixed $value): Bytes32
    {
        if (!is_bool($value) && ($value !== 0 && $value !== 1)) {
            throw new \InvalidArgumentException("bool expects true/false/0/1");
        }

        return new Bytes32(str_pad(($value ? "\1" : "\0"), 32, "\0", STR_PAD_LEFT));
    }

    /**
     * @param mixed $value
     * @return Bytes32
     */
    public static function encodeAddress(mixed $value): Bytes32
    {
        $binary = self::normalizeInputString($value, allowEmpty: false);
        if (strlen($binary) !== 20) {
            throw new \InvalidArgumentException("Invalid EVM address");
        }

        return new Bytes32(str_pad($binary, 32, "\0", STR_PAD_LEFT));
    }

    /**
     * @param mixed $value
     * @param int $length
     * @return Bytes32
     */
    public static function encodeFixedBytes(mixed $value, int $length = 32): Bytes32
    {
        if ($length < 1 || $length > 32) {
            throw new \InvalidArgumentException("Invalid bytes length; Expected 1-32, got: " . $length);
        }

        $binary = self::normalizeInputString($value, allowEmpty: false);
        if (strlen($binary) !== $length) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid bytes length; Expected %d bytes, got: %d",
                $length,
                strlen($binary)
            ));
        }

        return new Bytes32(str_pad($binary, 32, "\0", STR_PAD_RIGHT));
    }
}