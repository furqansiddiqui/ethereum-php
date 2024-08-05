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

namespace FurqanSiddiqui\Ethereum\Contracts\ABI;

use FurqanSiddiqui\Ethereum\Contracts\Contract;
use FurqanSiddiqui\Ethereum\Exception\Contract_ABIException;

/**
 * Class MethodParam
 * @package FurqanSiddiqui\Ethereum\Contracts\ABI
 */
readonly class ContractMethodParam
{
    /**
     * @param string $name
     * @param string $type
     * @param bool|null $indexed
     */
    public function __construct(
        public string    $name,
        public string    $type,
        public null|bool $indexed,
    )
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            "name" => $this->name,
            "type" => $this->type,
        ];

        if (is_bool($this->indexed)) {
            $array["indexed"] = $this->indexed;
        }

        return $array;
    }

    /**
     * @param string $methodId
     * @param string $object
     * @param int $index
     * @param array $param
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     */
    public static function fromArray(string $methodId, string $object, int $index, mixed $param): static
    {
        if (!is_array($param)) {
            throw new Contract_ABIException(
                sprintf('Expected an object in "%s" for "%s" at index %d', $object, $methodId, $index)
            );
        }

        $name = $param["name"] ?? null;
        if (!is_string($name)) {
            throw new Contract_ABIException(
                sprintf('Bad value for "%s" param "name" of "%s" at index %d', $object, $methodId, $index)
            );
        }

        $type = $param["type"] ?? null;
        if (!is_string($type) || !Contract::ValidateDataType($type)) {
            throw new Contract_ABIException(
                sprintf('Bad value for "%s" param "type" of "%s" at index %d', $object, $methodId, $index)
            );
        }

        if (array_key_exists("indexed", $param)) {
            if (!is_bool($param["indexed"])) {
                throw new Contract_ABIException(
                    sprintf('Bad value for "%s" param "indexed" of "%s" at index %d', $object, $methodId, $index)
                );
            }
        }

        return new static($name, $type, $param["indexed"] ?? false);
    }

    /**
     * @param array $param
     * @return static
     */
    public static function fromArrayNC(array $param): static
    {
        return new static(
            $param["name"],
            $param["type"],
            $param["indexed"] ?? false
        );
    }
}
