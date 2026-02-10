<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Codecs\RLP;

use Charcoal\Buffers\Types\Bytes20;
use Charcoal\Buffers\Types\Bytes32;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;
use FurqanSiddiqui\Ethereum\Unit\Wei;

/**
 * Represents a schema for encoding and decoding data using the RlpCodec (Recursive Length Prefix) format.
 * Manages a collection of fields, each represented by a name and type.
 */
final class RlpSchema
{
    /** @var RlpField[] */
    private(set) array $fields = [];

    /**
     * @param RlpFieldType|RlpSchema $type
     * @param string $name
     * @return $this
     */
    public function add(RlpFieldType|self $type, string $name): self
    {
        $this->fields[] = new RlpField($type, $name);
        return $this;
    }

    /**
     * @param RlpEncodableInterface $object
     * @return ReadableBufferInterface
     */
    public function encode(RlpEncodableInterface $object): ReadableBufferInterface
    {
        $items = [];
        foreach ($this->fields as $field) {
            if ($field->type instanceof RlpFieldType && $field->type === RlpFieldType::Ignore) {
                continue;
            }

            if (!property_exists($object, $field->name)) {
                throw new \OutOfBoundsException(sprintf('Property "%s" not found in %s', $field->name, $object::class));
            }

            $items[] = $object->{$field->name};
        }

        return RlpCodec::encode($items);
    }

    /**
     * @param array $rlpObject
     * @return array
     */
    public function createObject(array $rlpObject): array
    {
        $result = [];
        foreach ($this->fields as $i => $field) {
            if (!array_key_exists($i, $rlpObject)) {
                throw new \OutOfBoundsException(
                    sprintf('Index %d for field "%s" does not exist in RlpCodec decoded buffer',
                        $i, $field->name));
            }

            $value = $rlpObject[$i];

            // Child Schemas
            if ($field->type instanceof RlpSchema) {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" expects Array, got "%s"',
                        $field->name, gettype($value)));
                }

                $result[$field->name] = $field->type->createObject($value);
                continue;
            }

            // Ignore/Skip fields
            if ($field->type === RlpFieldType::Ignore) {
                continue;
            }

            // Booleans
            if ($field->type === RlpFieldType::Bool) {
                if ($value === "") {
                    $result[$field->name] = false;
                    continue;
                }

                if ($value === "\x01") {
                    $result[$field->name] = true;
                    continue;
                }
            }

            // Integers
            if ($field->type === RlpFieldType::Integer) {
                if (!is_string($value)) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" expects decoded value string, got "%s"',
                        $field->name, gettype($value)));
                }

                if ($value === "") {
                    $result[$field->name] = 0;
                    continue;
                }

                $g = gmp_import($value, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
                $result[$field->name] = gmp_cmp($g, PHP_INT_MAX) <= 0 ? gmp_intval($g) : gmp_strval($g, 10);
                continue;
            }

            // WEI Unit
            if ($field->type === RlpFieldType::Wei) {
                $result[$field->name] = new Wei($value === ""
                    ? 0
                    : gmp_import($value, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN));

                continue;
            }

            // String
            if ($field->type->isString()) {
                if (is_string($value)) {
                    if ($field->type === RlpFieldType::Bytes32) {
                        if (strlen($value) !== 32) {
                            throw new \InvalidArgumentException(
                                sprintf('Field "%s" expects 32-byte string', $field->name));
                        }

                        $result[$field->name] = new Bytes32($value);
                        continue;
                    }

                    $result[$field->name] = $value;
                    continue;
                }
            }

            // Address
            if ($field->type === RlpFieldType::Address) {
                if (!is_string($value)) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" expects decoded value string, got "%s"',
                        $field->name, gettype($value)));
                }

                $result[$field->name] = new EthereumAddress(new Bytes20($value));
            }

            // Include (As-is)
            if ($field->type === RlpFieldType::Include) {
                $result[$field->name] = $value;
                continue;
            }

            throw new \InvalidArgumentException(sprintf('Field "%s" has unsupported type "%s"', $field->name,
                $field->type->name));
        }

        return $result;
    }
}