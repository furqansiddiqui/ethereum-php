<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Defines methods for transforming an object to and from a Data Transfer Object (DTO) format.
 */
interface ContractDtoInterface
{
    /**
     * @param array $abi
     * @return static
     */
    public static function fromDto(array $abi): self;

    /**
     * @return array
     */
    public function toDto(): array;
}