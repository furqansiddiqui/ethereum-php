<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc\Traits;

/**
 * Provides functionality to normalize hexadecimal strings (Base16 format).
 */
trait NormalizeBase16Trait
{
    final protected function normalizeBase16(mixed $in, bool $throw = false): ?string
    {
        if (!is_string($in) || !preg_match("/\A(0x)?[a-fA-F0-9]+\z/", $in)) {
            if ($throw) {
                throw new \InvalidArgumentException("Invalid hex string");
            }

            return null;
        }

        $in = strtolower($in);
        if (str_starts_with($in, "0x")) $in = substr($in, 2);
        if (strlen($in) % 2 !== 0) $in = "0" . $in;
        return $in;
    }
}