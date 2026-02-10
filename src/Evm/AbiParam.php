<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Represents a parameter definition in the ABI (Application Binary Interface).
 * This class encapsulates information about the type, name, and indexed status
 * of a parameter used in the context of interacting with smart contracts.
 */
final readonly class AbiParam
{
    public function __construct(
        public string  $type,
        public ?string $name = null,
        public ?bool   $indexed = null,
    )
    {
    }
}