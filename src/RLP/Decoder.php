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

/**
 * Class RLPDecoder
 * @package FurqanSiddiqui\Ethereum\RLP
 */
class Decoder
{
    /** @var array */
    private array $structure = [];

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
    public function expectString(string $mapTo): static
    {
        $this->structure[] = [
            "prop" => $mapTo
        ];

        return $this;
    }

    /**
     * @param int $index
     * @param string $key
     * @return $this
     */
    public function mapValue(int $index, string $key): self
    {
        $this->indexes["in_" . $index] = [
            "index" => $index,
            "type" => null,
            "key" => $key,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function decode(): array
    {
        return $this->_decode(RLP::Decode($this->encoded)[0]);
    }

    /**
     * @param array $decoded
     * @return array
     */
    private function _decode(array $decoded): array
    {
        $result = [];
        foreach ($decoded as $i => $value) {
            if (is_array($value)) {
                $result[] = $this->_decode($value);
                continue;
            }

            $expected = $this->indexes["in_" . $i] ?? null;
            if ($expected) {
                $key = $expected["key"] ?? null;
                if ($expected["type"] === "int" && !is_int($value)) {
                    $value = $value === "" ? 0 : Integers::Unpack($value)->value();
                }

                if ($key) {
                    $result[$key] = $value;
                } else {
                    $result[] = $value;
                }

                continue;
            }

            $result[] = $value;
        }

        return $result;
    }
}
