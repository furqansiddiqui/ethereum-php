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
 * Class ContractEvent
 * @package FurqanSiddiqui\Ethereum\Contracts\ABI
 */
class ContractEvent
{
    /** @var array */
    private array $inputs = [];

    /**
     * @param string $name
     * @param bool $isAnonymous
     */
    public function __construct(
        public readonly string $name,
        public readonly bool   $isAnonymous,
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
     * @param \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethodParam $param
     * @return void
     */
    public function appendInput(ContractMethodParam $param): void
    {
        $this->inputs[] = $param;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            "type" => "event",
            "name" => $this->name,
        ];

        if (is_bool($this->isAnonymous)) {
            $array["anonymous"] = $this->isAnonymous;
        }

        $array["inputs"] = [];
        /** @var \FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethodParam $input */
        foreach ($this->inputs as $input) {
            $array["inputs"][] = $input->toArray();
        }

        return $array;
    }

    /**
     * @param array $event
     * @return static
     */
    public static function fromArrayNC(array $event): static
    {
        $contractEvent = new static($event["name"], $event["anonymous"] ?? null);
        $inputs = $method["inputs"] ?? null;
        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                $contractEvent->appendInput(ContractMethodParam::fromArrayNC($input));
            }
        }

        return $contractEvent;
    }

    /**
     * @param array $event
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     */
    public static function fromArray(array $event): static
    {
        // Type
        $type = $event["type"] ?? null;
        if ($type !== "event") {
            throw new Contract_ABIException(sprintf('Cannot create event from type "%s"', $type));
        }

        // Name
        $name = $event["name"] ?? null;
        if (!is_string($name) && !$name) {
            throw new Contract_ABIException('Invalid event name');
        }

        // Anonymous
        if (array_key_exists("anonymous", $event)) {
            $isAnonymous = $event["anonymous"];
            if (!is_bool($isAnonymous)) {
                throw new Contract_ABIException(sprintf(
                        'Invalid value of type "%s" for "anonymous" property of event "%s"',
                        gettype($isAnonymous),
                        $name
                    )
                );
            }
        }

        $contractEvent = new static($name, $isAnonymous ?? null);

        // Inputs
        $inputs = $event["inputs"] ?? null;
        if (!is_array($inputs) && !is_null($inputs)) {
            throw new Contract_ABIException(sprintf(
                    'Invalid value of type "%s" for "inputs" property of event "%s"',
                    gettype($inputs),
                    $name
                )
            );
        }

        if (is_array($inputs)) {
            $inI = -1;
            foreach ($inputs as $input) {
                $inI++;
                $contractEvent->appendInput(ContractMethodParam::fromArray($name, "inputs", $inI, $input));
            }
        }

        return $contractEvent;
    }
}
