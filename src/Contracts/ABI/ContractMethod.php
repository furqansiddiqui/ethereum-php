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

use FurqanSiddiqui\Ethereum\Exception\Contract_ABIException;

/**
 * Class Method
 * @package FurqanSiddiqui\Ethereum\Contracts\ABI
 */
class ContractMethod
{
    /** @var array */
    private array $inputs = [];
    /** @var array */
    private array $outputs = [];

    /**
     * @param string $type
     * @param string|null $name
     * @param bool|null $isConstant
     * @param bool|null $isPayable
     */
    public function __construct(
        public readonly string      $type,
        public readonly null|string $name,
        public readonly null|bool   $isConstant,
        public readonly null|bool   $isPayable,
    )
    {
    }

    /**
     * @return array
     */
    public function inputs(): array
    {
        return $this->inputs;
    }

    /**
     * @return array
     */
    public function outputs(): array
    {
        return $this->outputs;
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethodParam $param
     * @return void
     */
    public function appendInput(ContractMethodParam $param): void
    {
        $this->inputs[] = $param;
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethodParam $param
     * @return void
     */
    public function appendOutput(ContractMethodParam $param): void
    {
        $this->outputs[] = $param;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        $array = ["type" => $this->type];
        foreach (["name", "isConstant", "isPayable"] as $prop) {
            if (!is_null($this->$prop)) {
                $array[$prop] = $this->$prop;
            }
        }

        $array["inputs"] = [];
        /** @var \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethodParam $input */
        foreach ($this->inputs as $input) {
            $array["inputs"][] = $input->toArray();
        }

        $array["outputs"] = [];
        /** @var \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethodParam $input */
        foreach ($this->outputs as $output) {
            $array["outputs"][] = $output->toArray();
        }

        return $array;
    }

    /**
     * @param array $method
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     */
    public static function fromArray(array $method): static
    {
        // Type
        $type = $method["type"] ?? null;
        if (!is_string($type) || !in_array($type, ["function", "constructor", "fallback", "receive"])) {
            throw new Contract_ABIException(sprintf('Cannot create method for type "%s"', $type));
        }

        // Name
        $name = $method["name"] ?? null;
        if (!is_string($name) && !is_null($name)) { // Loosened for "constructor" and "fallback"
            throw new Contract_ABIException(sprintf('Unexpected value of type "%s" for method "name"', gettype($name)));
        }

        if ($type === "function" && !$name) {
            throw new Contract_ABIException('ABI method type "function" requires a valid name');
        }

        $methodId = $name ?? $type;

        // Constant
        if (array_key_exists("constant", $method)) {
            $isConstant = $method["constant"];
            if (!is_bool($isConstant)) {
                throw new Contract_ABIException(sprintf(
                        'Invalid value of type "%s" for "constant" property of method "%s"',
                        gettype($isConstant),
                        $methodId
                    )
                );
            }
        }

        // Payable
        if (array_key_exists("payable", $method)) {
            $isPayable = $method["payable"];
            if (!is_bool($isPayable)) {
                throw new Contract_ABIException(sprintf(
                        'Invalid value of type "%s" for "payable" property of method "%s"',
                        gettype($isPayable),
                        $methodId
                    )
                );
            }
        }

        // Create Object
        $contractMethod = new static(
            $type,
            $name,
            $isConstant ?? null,
            $isPayable ?? null,
        );

        // Inputs
        $inputs = $method["inputs"] ?? null;
        if (!is_array($inputs) && !is_null($inputs)) {
            throw new Contract_ABIException(sprintf(
                    'Invalid value of type "%s" for "inputs" property of method "%s"',
                    gettype($inputs),
                    $methodId
                )
            );
        }

        if (is_array($inputs)) {
            $inI = -1;
            foreach ($inputs as $input) {
                $inI++;
                $contractMethod->appendInput(ContractMethodParam::fromArray($methodId, "inputs", $inI, $input));
            }
        }

        // Outputs
        $outputs = $method["outputs"] ?? null;
        if (!is_array($outputs) && !is_null($outputs)) {
            throw new Contract_ABIException(sprintf(
                    'Invalid value of type "%s" for "outputs" property of method "%s"',
                    gettype($outputs),
                    $methodId
                )
            );
        }

        if (is_array($outputs)) {
            $outI = -1;
            foreach ($outputs as $output) {
                $outI++;
                $contractMethod->appendOutput(ContractMethodParam::fromArray($methodId, "outputs", $outI, $output));
            }
        }

        return $contractMethod;
    }

    /**
     * @param array $method
     * @return static
     */
    public static function fromArrayNC(array $method): static
    {
        $contractMethod = new static(
            $method["type"],
            $method["name"],
            $method["constant"] ?? null,
            $method["payable"] ?? null,
        );

        $inputs = $method["inputs"] ?? null;
        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                $contractMethod->appendInput(ContractMethodParam::fromArrayNC($input));
            }
        }

        $outputs = $method["outputs"] ?? null;
        if (is_array($outputs)) {
            foreach ($outputs as $output) {
                $contractMethod->appendOutput(ContractMethodParam::fromArrayNC($output));
            }
        }

        return $contractMethod;
    }
}
