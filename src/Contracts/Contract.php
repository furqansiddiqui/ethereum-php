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

namespace FurqanSiddiqui\Ethereum\Contracts;

use FurqanSiddiqui\Ethereum\Contracts\ABI\ContractEvent;
use FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod;
use FurqanSiddiqui\Ethereum\Exception\Contract_ABIException;

/**
 * Class ABI
 * https://docs.soliditylang.org/en/develop/abi-spec.html
 * @package FurqanSiddiqui\Ethereum\Contracts
 */
class Contract
{
    /** @var \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod|null */
    private null|ContractMethod $constructor = null;
    /** @var \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod|null */
    private null|ContractMethod $receive = null;
    /** @var \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod|null */
    private null|ContractMethod $fallback = null;
    /** @var array */
    private array $functions = [];
    /** @var array */
    private array $events = [];

    /**
     * @param array $abi
     * @param bool $validate
     * @param array $errors
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     * @throws \Throwable
     */
    public static function fromArray(array $abi, bool $validate, array &$errors): static
    {
        $contract = new static();
        $index = -1;
        foreach ($abi as $block) {
            $index++;

            try {
                if ($validate) {
                    if (!is_array($block)) {
                        throw new Contract_ABIException(
                            sprintf('Unexpected data type "%s" at ABI array index %d, expecting Object', gettype($block), $index)
                        );
                    }

                    if (!isset($block["type"]) || !is_string($block["type"])) {
                        throw new Contract_ABIException(sprintf('ABI object at index %d has invalid "type"', $index));
                    }
                }

                switch ($block["type"]) {
                    case "constructor":
                    case "function":
                    case "receive":
                    case "fallback":
                        $contract->appendMethod(
                            $validate ? ContractMethod::fromArray($block) : ContractMethod::fromArrayNC($block)
                        );
                        break;
                    case "event":
                        $contract->appendEvent(
                            $validate ? ContractEvent::fromArray($block) : ContractEvent::fromArrayNC($block)
                        );
                        break;
                    default:
                        throw new Contract_ABIException(
                            sprintf('Invalid ABI block of type "%s" at index %d', $block["type"], $index)
                        );
                }
            } catch (\Throwable $t) {
                $errors[] = $t;
                if ($validate) {
                    throw $t;
                }
            }
        }

        return $contract;
    }

    /**
     * @return array
     */
    public function functions(): array
    {
        return $this->functions;
    }

    /**
     * @return array
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod|null
     */
    public function constructor(): null|ContractMethod
    {
        return $this->constructor;
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod|null
     */
    public function fallback(): null|ContractMethod
    {
        return $this->fallback;
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod|null
     */
    public function receive(): null|ContractMethod
    {
        return $this->receive;
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod $method
     * @return void
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     */
    public function appendMethod(ContractMethod $method): void
    {
        $id = $method->name ?? $method->type;
        if (in_array($method->type, ["constructor", "receive", "fallback"])) {
            $typeProp = $method->type;
            if ($this->$typeProp) {
                throw new Contract_ABIException(sprintf('Cannot override "%s" in contract ABI', $method->type));
            }

            $this->$typeProp = $method;
        }

        $this->functions[strtolower($id)] = $method;
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractEvent $event
     * @return void
     */
    public function appendEvent(ContractEvent $event): void
    {
        $this->events[strtolower($event->name)] = $event;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $abi = [];
        /** @var ContractMethod $function */
        foreach ($this->functions as $function) {
            $abi[] = $function->toArray();
        }

        /** @var ContractEvent $event */
        foreach ($this->events as $event) {
            $abi[] = $event->toArray();
        }

        return $abi;
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function ValidateDataType(string $type): bool
    {
        return (bool)preg_match('/^((hash|uint|int|string)(8|16|32|64|128|256)?|bool|address|bytes)$/', $type);
    }
}
