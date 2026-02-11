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
final class ContractMethod implements ContractDtoInterface
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

    /**
     * @return array
     */
    public function toDto(): array
    {
        $abi = ["type" => $this->type->value, "inputs" => [], "outputs" => []];
        if ($this->name !== null) {
            $abi["name"] = $this->name;
        }

        if ($this->isConstant !== null) {
            $abi["constant"] = $this->isConstant;
        }

        if ($this->isPayable !== null) {
            $abi["payable"] = $this->isPayable;
        }

        foreach ($this->inputs as $input) {
            $abi["inputs"][] = $input->toDto();
        }

        foreach ($this->outputs as $output) {
            $abi["outputs"][] = $output->toDto();
        }

        return $abi;
    }

    /**
     * @param array $abi
     * @return self
     */
    public static function fromDto(array $abi): self
    {
        if (!$abi) {
            throw new \InvalidArgumentException("ContractMethod expects non-empty array");
        }

        // Resolve from enum
        $type = ContractMethodType::tryFrom(strval($abi["type"] ?? null));
        if (!$type) {
            throw new \InvalidArgumentException("Invalid type for ContractMethod");
        }

        // Name
        $name = $abi["name"] ?? null;
        if ($type === ContractMethodType::Function) {
            if (!is_string($name)) {
                throw new \InvalidArgumentException("Invalid name for ContractMethod");
            }
        } else {
            $name = is_string($name) ? $name : "";
        }

        // Constant & Payable
        if (array_key_exists("constant", $abi) && !is_bool($abi["constant"])) {
            throw new \InvalidArgumentException("Invalid value for constant for ContractMethod");
        } elseif (array_key_exists("payable", $abi) && !is_bool($abi["payable"])) {
            throw new \InvalidArgumentException("Invalid value for payable for ContractMethod");
        }

        $method = new self(
            type: $type,
            name: $name,
            isConstant: $abi["constant"] ?? null,
            isPayable: $abi["payable"] ?? null
        );

        // Method Inputs
        $inputs = $abi["inputs"] ?? [];
        if (!is_array($inputs)) {
            throw new \InvalidArgumentException("Invalid inputs for ContractMethod");
        }

        foreach ($inputs as $index => $input) {
            try {
                $method->appendInput(AbiParam::fromDto($input));
            } catch (\Throwable $t) {
                throw new \InvalidArgumentException(sprintf("Invalid input[%d] for ContractMethod: %s", $index, $name),
                    previous: $t);
            }
        }

        // Method Outputs
        $outputs = $abi["outputs"] ?? [];
        if (!is_array($outputs)) {
            throw new \InvalidArgumentException("Invalid outputs for ContractMethod");
        }

        foreach ($outputs as $index => $output) {
            try {
                $method->appendOutput(AbiParam::fromDto($output));
            } catch (\Throwable $t) {
                throw new \InvalidArgumentException(sprintf("Invalid output[%d] for ContractMethod: %s", $index, $name),
                    previous: $t);
            }
        }

        return $method;
    }
}