<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Codecs\ABI;

use Charcoal\Contracts\Buffers\ReadableBufferInterface;

/**
 * Abstract base class providing utility methods for ABI (Application Binary Interface)
 * encoding and decoding.
 */
abstract class AbstractAbiCodecs
{
    /**
     * @param string $type
     * @return bool
     */
    protected static function isDynamicType(string $type): bool
    {
        $type = strtolower(trim($type));
        if ($type === "bytes" || $type === "string") {
            return true;
        }

        $arr = self::parseArrayType($type);
        if ($arr !== null) {
            if ($arr["length"] === null) {
                return true;
            }

            return self::isDynamicType($arr["base"]);
        }

        if (str_starts_with($type, "(")) {
            return true;
        }

        return false;
    }

    /**
     * @param string $type
     * @return array|null
     */
    protected static function parseArrayType(string $type): ?array
    {
        $type = strtolower(trim($type));
        if (!preg_match("/^(.+)\[(\d*)]$/", $type, $m)) {
            return null;
        }

        $base = $m[1];
        $lenRaw = $m[2];
        if ($lenRaw === "") {
            return [
                "base" => $base,
                "length" => null
            ];
        }

        if ($lenRaw === "0" || $lenRaw[0] === "0") {
            throw new \InvalidArgumentException("Invalid fixed array length");
        }

        return [
            "base" => $base,
            "length" => (int)$lenRaw
        ];
    }

    /**
     * Normalize input string to binary.
     */
    protected static function normalizeInputString(mixed $value, bool $allowEmpty = false): string
    {
        if ($value instanceof ReadableBufferInterface) {
            $bytes = $value->bytes();
            if ($bytes === "" && !$allowEmpty) {
                throw new \InvalidArgumentException("Empty input is not allowed");
            }
            return $bytes;
        }

        $hex = is_string($value) ? $value : (string)$value;
        if (str_starts_with($hex, "0x")) {
            $hex = substr($hex, 2);
        }

        if ($hex === "") {
            if ($allowEmpty) {
                return "";
            }

            throw new \InvalidArgumentException("Invalid hex string");
        }

        if (!ctype_xdigit($hex) || (strlen($hex) % 2) !== 0) {
            throw new \InvalidArgumentException("Invalid hex string");
        }

        $binary = hex2bin($hex);
        if ($binary === false) {
            throw new \InvalidArgumentException("Invalid hex string");
        }

        return $binary;
    }
}