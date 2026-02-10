<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Codecs\RLP;

/**
 * Represents a schema for encoding and decoding data using the RlpCodec (Recursive Length Prefix) format.
 * Manages a collection of fields, each represented by a name and type.
 */
final class RlpSchema
{
    private(set) array $fields = [];

    /**
     * @param RlpFieldType $type
     * @param string $name
     * @return $this
     */
    public function add(RlpFieldType $type, string $name): self
    {
        $this->fields[] = new RlpField($type, $name);
        return $this;
    }
}