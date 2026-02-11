<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Represents a contract event, encapsulating its name, anonymity status,
 * and associated input parameters.
 */
final class ContractEvent
{
    use ContractEntitySignatureTrait;

    /** @var AbiParam[] */
    private(set) array $inputs = [];

    /**
     * @param string $name
     * @param bool $isAnonymous
     */
    public function __construct(
        public readonly string $name,
        public readonly bool   $isAnonymous,
    )
    {
        if ($this->name === "") {
            throw new \InvalidArgumentException("ContractEvent name cannot be empty");
        }
    }

    /**
     * @param AbiParam $param
     * @return void
     */
    public function appendInput(AbiParam $param): void
    {
        $this->inputs[] = $param;
    }
}