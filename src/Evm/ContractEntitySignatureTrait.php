<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Provides functionality to calculate and retrieve a signature representation of a contract entity.
 */
trait ContractEntitySignatureTrait
{
    private ?string $signature = null;

    /**
     * @param bool $refresh
     * @return string
     */
    public function signature(bool $refresh = false): string
    {
        if (!$refresh && $this->signature !== null) {
            return $this->signature;
        }

        return $this->signature = $this->name . "("
            . implode(",", array_map(fn(AbiParam $param) => $param->type, $this->inputs))
            . ")";
    }
}