<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Represents the core structure of a smart contract, including its methods and events, as well as
 * specific lifecycle methods such as constructor, receive, and fallback.
 */
class SmartContract implements ContractDtoInterface
{
    private(set) ?ContractMethod $ctor = null;
    private(set) ?ContractMethod $receive = null;
    private(set) ?ContractMethod $fallback = null;

    /** @var array<string, ContractMethod> */
    private(set) array $methods = [];
    /** @var array<string, ContractEvent> */
    private(set) array $events = [];

    /**
     * @param ContractMethod|ContractEvent ...$methods
     */
    public function __construct(ContractMethod|ContractEvent ...$methods)
    {
        if ($methods) {
            foreach ($methods as $method) {
                $this->append($method);
            }
        }
    }

    /**
     * @param ContractMethod|ContractEvent $item
     * @param bool $forceSignatureRefresh
     * @return void
     */
    protected function append(
        ContractMethod|ContractEvent $item,
        bool                         $forceSignatureRefresh = false
    ): void
    {
        if ($item instanceof ContractEvent) {
            $this->events[$item->signature($forceSignatureRefresh)] = $item;
            return;
        }

        $bindsTo = match ($item->type) {
            ContractMethodType::Constructor => "ctor",
            ContractMethodType::Receive => "receive",
            ContractMethodType::Fallback => "fallback",
            default => null,
        };

        if ($bindsTo) {
            if ($this->$bindsTo !== null) {
                throw new \LogicException("SmartContract already has a method bound to: " . $bindsTo);
            }

            $this->$bindsTo = $item;
            return;
        }

        $this->methods[$item->signature($forceSignatureRefresh)] = $item;
    }

    /**
     * @return array
     */
    public function toDto(): array
    {
        $abi = [];
        if ($this->ctor) $abi[] = $this->ctor->toDto();
        if ($this->receive) $abi[] = $this->receive->toDto();
        if ($this->fallback) $abi[] = $this->fallback->toDto();

        foreach ($this->methods as $method) {
            $abi[] = $method->toDto();
        }

        foreach ($this->events as $event) {
            $abi[] = $event->toDto();
        }

        return $abi;
    }

    /**
     * @param array $abi
     * @return self
     */
    public static function fromDto(array $abi): self
    {
        $entities = [];
        foreach ($abi as $i => $item) {
            if (!is_array($item)) {
                throw new \InvalidArgumentException("Invalid ABI item at index: " . $i);
            }

            if (!isset($item["type"]) || !is_string($item["type"])) {
                throw new \InvalidArgumentException("Invalid ABI item type at index: " . $i);
            }

            $entities[] = match ($item["type"]) {
                "constructor", "function", "receive", "fallback" => ContractMethod::fromDto($item),
                "event" => ContractEvent::fromDto($item),
                default => throw new \InvalidArgumentException(sprintf(
                    "ABI item at index %d is not a method or event: %s",
                    $i, $item["type"]
                ))
            };
        }

        return new self(...$entities);
    }
}