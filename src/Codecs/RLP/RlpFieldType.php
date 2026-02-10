<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Codecs\RLP;

/**
 * Represents the different types of RlpCodec (Recursive Length Prefix) encoded data.
 * Each case corresponds to a specific data type that can be processed or interpreted
 * within the context of RlpCodec encoding or decoding.
 */
enum RlpFieldType
{
    case Ignore;
    case Integer;
    case Address;
    case Wei;
    case String;
    case Bytes32;
    case Bool;
    case Include;
}