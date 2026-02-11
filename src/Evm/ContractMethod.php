<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Represents a method within a contract, including its type, name, inputs, outputs, and properties such as
 * whether it is constant or payable.
 */
final class ContractMethod
{
    use ContractEntitySignatureTrait;

    /** @var AbiParam[] */
    private(set) array $inputs = [];
    /** @var AbiParam[] */
    private(set) array $outputs = [];

    public function __construct(
        public readonly ContractMethodType $type,
        public readonly string             $name,
        public readonly ?bool              $isConstant,
        public readonly ?bool              $isPayable,
    )
    {
    }

    /**
     * @param AbiParam $param
     * @return void
     */
    public function appendInput(AbiParam $param): void
    {
        $this->inputs[] = $param;
    }

    /**
     * @param AbiParam $param
     * @return void
     */
    public function appendOutput(AbiParam $param): void
    {
        $this->outputs[] = $param;
    }
}