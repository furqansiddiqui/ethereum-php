<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Interface for ContractMethod and ContractEvent.
 */
interface ContractEntityInterface
{
    public function signature(bool $refresh = false): string;
}