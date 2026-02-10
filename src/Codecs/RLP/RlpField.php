<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Codecs\RLP;

/**
 * Represents a field in the RlpCodec (Recursive Length Prefix) encoding scheme.
 */
final readonly class RlpField
{
    public function __construct(
        public RlpFieldType $type,
        public string       $name
    )
    {
    }
}