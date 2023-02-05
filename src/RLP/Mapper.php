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

use Comely\Buffer\BigInteger\BigEndian;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Buffers\WEIAmount;
use FurqanSiddiqui\Ethereum\Exception\RLP_MapperException;

/**
 * Class Mapper
 * @package FurqanSiddiqui\Ethereum\RLP
 */
class Mapper
{
    /** @var array */
    private array $structure = [];

    /**
     * @return $this
     */
    public function skip(): static
    {
        $this->structure[] = [
            "type" => "skip",
            "prop" => "",
        ];

        return $this;
    }

    /**
     * @param string $mapTo
     * @return $this
     */
    public function expectInteger(string $mapTo): static
    {
        $this->structure[] = [
            "type" => "int",
            "prop" => $mapTo
        ];
        return $this;
    }

    /**
     * @param string $mapTo
     * @return $this
     */
    public function expectAddress(string $mapTo): static
    {
        $this->structure[] = [
            "type" => "address",
            "prop" => $mapTo
        ];

        return $this;
    }

    /**
     * @param string $mapTo
     * @return $this
     */
    public function expectWEIAmount(string $mapTo): static
    {
        $this->structure[] = [
            "type" => "wei",
            "prop" => $mapTo
        ];

        return $this;
    }

    /**
     * @param string $mapTo
     * @return $this
     */
    public function expectString(string $mapTo): static
    {
        $this->structure[] = [
            "type" => "string",
            "prop" => $mapTo
        ];

        return $this;
    }

    /**
     * @param string $mapTo
     * @param \FurqanSiddiqui\Ethereum\RLP\Mapper $mapper
     * @return $this
     */
    public function expectMap(string $mapTo, Mapper $mapper): static
    {
        $this->structure[] = [
            "type" => $mapper,
            "prop" => $mapTo
        ];

        return $this;
    }

    /**
     * @param string $mapTo
     * @return $this
     */
    public function expectBool(string $mapTo): static
    {
        $this->structure[] = [
            "type" => "bool",
            "prop" => $mapTo
        ];

        return $this;
    }

    /**
     * @param string $mapTo
     * @return $this
     */
    public function mapAsIs(string $mapTo): static
    {
        $this->structure[] = [
            "type" => "raw",
            "prop" => $mapTo
        ];

        return $this;
    }

    /**
     * @param array $buffer
     * @return array
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     */
    public function decode(array $buffer): array
    {
        $result = [];
        foreach ($this->structure as $i => $prop) {
            /** @var string $key */
            $key = $prop["prop"];
            /** @var null|string|\FurqanSiddiqui\Ethereum\RLP\Mapper $type */
            $type = $prop["type"] ?? null;

            if (!array_key_exists($i, $buffer)) {
                throw new RLP_MapperException(
                    sprintf('Index %d for prop "%s" does not exist in RLP decoded buffer', $i, $key)
                );
            }

            if ($type === "skip") {
                continue;
            }

            $value = $buffer[$i];
            if ($type === "bool") {
                if (is_int($value) && in_array($value, [0, 1])) {
                    $result[$key] = (bool)$value;
                    continue;
                }
            }

            if ($type === "int") {
                if (is_int($value)) {
                    $result[$key] = $value;
                    continue;
                }

                if (is_string($value)) {
                    $value = BigEndian::GMP_Unpack($value);
                    $value = gmp_cmp($value, PHP_INT_MAX) <= 0 ? gmp_intval($value) : gmp_strval($value, 10);
                    $result[$key] = $value;
                    continue;
                }
            }

            if ($type === "wei") {
                try {
                    $result[$key] = new WEIAmount(is_int($value) ? $value : BigEndian::GMP_Unpack($value));
                    continue;
                } catch (\Throwable $t) {
                    throw new RLP_MapperException(
                        sprintf('Cannot map "%s" as WEIAmount; %s %s', $key, get_class($t), $t->getMessage())
                    );
                }
            }

            if ($type === "string") {
                if ($value === 0) {
                    $value = "";
                }

                if (is_string($value)) {
                    $result[$key] = $value;
                    continue;
                }
            }

            if ($type === "address") {
                try {
                    $result[$key] = new EthereumAddress($value);
                    continue;
                } catch (\Throwable $t) {
                    throw new RLP_MapperException(
                        sprintf('Cannot map "%s" as EthereumAddress; %s %s', $key, get_class($t), $t->getMessage())
                    );
                }
            }

            if ($type instanceof Mapper) {
                if (!is_array($value)) {
                    throw new RLP_MapperException(
                        sprintf('Property "%s" expects Array, got "%s"', $key, gettype($value))
                    );
                }

                $result[$key] = $type->decode($value);
                continue;
            }

            if ($type === "raw") {
                $result[$key] = $value;
                continue;
            }

            throw new RLP_MapperException(
                sprintf('Cannot find appropriate value for "%s"', $key)
            );
        }

        return $result;
    }
}
