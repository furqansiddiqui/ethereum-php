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
final class SmartContract
{
    private(set) ?ContractMethod $ctor = null;
    private(set) ?ContractMethod $receive = null;
    private(set) ?ContractMethod $fallback = null;

    /** @var array<string, ContractMethod> */
    private(set) array $methods = [];
    /** @var array<string, ContractEvent> */
    private(set) array $events = [];

    /**
     * @param ContractMethod ...$methods
     */
    public function __construct(ContractMethod ...$methods)
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
    public function append(
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
            if (isset($this->$bindsTo)) {
                throw new \LogicException("SmartContract already has a method bound to: " . $bindsTo);
            }

            $this->$bindsTo = $item;
            return;
        }

        $this->methods[$item->signature($forceSignatureRefresh)] = $item;
    }
}