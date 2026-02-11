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
final class ContractEvent implements ContractDtoInterface
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

    /**
     * @return array
     */
    public function toDto(): array
    {
        $abi = ["name" => $this->name, "type" => "event", "inputs" => []];
        if (is_bool($this->isAnonymous)) {
            $abi["anonymous"] = $this->isAnonymous;
        }

        foreach ($this->inputs as $input) {
            $abi["inputs"][] = $input->toDto();
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
            throw new \InvalidArgumentException("ContractEvent expects non-empty array");
        } elseif (!isset($abi["type"]) || $abi["type"] !== "event") {
            throw new \InvalidArgumentException("Invalid type for ContractEvent");
        } elseif (!isset($abi["name"]) || !is_string($abi["name"]) || $abi["name"] === "") {
            throw new \InvalidArgumentException("Invalid name for ContractEvent");
        }

        if (array_key_exists("anonymous", $abi) && !is_bool($abi["anonymous"])) {
            throw new \InvalidArgumentException("Invalid value for anonymous for ContractEvent");
        }

        $event = new self($abi["name"], $abi["anonymous"] ?? null);
        $inputs = $abi["inputs"] ?? [];
        if (!is_array($inputs)) {
            throw new \InvalidArgumentException("Invalid inputs for ContractEvent");
        }

        foreach ($inputs as $index => $input) {
            try {
                $event->appendInput(AbiParam::fromDto($input));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf("Invalid input[%d] for ContractEvent: %s", $index, $abi["name"]),
                    previous: $e);
            }
        }

        return $event;
    }
}