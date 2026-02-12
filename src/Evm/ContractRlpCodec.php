<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpCodec;

/**
 * The ContractRlpCodec class is a utility for encoding various types of contract-related
 * data structures into a readable buffer format. It provides methods for encoding
 * ABI parameters, contract events, contract methods, and complete smart contracts.
 */
final readonly class ContractRlpCodec
{
    /**
     * @param ContractDtoInterface $item
     * @return ReadableBufferInterface
     */
    public static function encode(ContractDtoInterface $item): ReadableBufferInterface
    {
        if ($item instanceof AbiParam) {
            return RlpCodec::encode(self::abiParamSchema($item));
        }

        if ($item instanceof ContractEvent) {
            return RlpCodec::encode(self::contractEventSchema($item));
        }

        if ($item instanceof ContractMethod) {
            return RlpCodec::encode(self::contractMethodSchema($item));
        }

        if ($item instanceof SmartContract) {
            return RlpCodec::encode(self::smartContractSchema($item));
        }

        throw new \InvalidArgumentException("Unsupported ABI item type");
    }

    /**
     * @param SmartContract $contract
     * @return array
     */
    public static function smartContractSchema(SmartContract $contract): array
    {
        $methods = [];
        foreach ($contract->methods as $method) {
            $methods[] = self::contractMethodSchema($method);
        }

        $events = [];
        foreach ($contract->events as $event) {
            $events[] = self::contractEventSchema($event);
        }

        return [
            $contract->ctor ? self::contractMethodSchema($contract->ctor) : null,
            $contract->receive ? self::contractMethodSchema($contract->receive) : null,
            $contract->fallback ? self::contractMethodSchema($contract->fallback) : null,
            $methods,
            $events
        ];
    }

    /**
     * @param ContractMethod $method
     * @return array
     */
    public static function contractMethodSchema(ContractMethod $method): array
    {
        $inputs = [];
        if ($method->inputs) {
            foreach ($method->inputs as $input) {
                $inputs[] = self::abiParamSchema($input);
            }
        }

        $outputs = [];
        if ($method->outputs) {
            foreach ($method->outputs as $output) {
                $outputs[] = self::abiParamSchema($output);
            }
        }

        return [
            $method->name ?? "",
            $inputs,
            $outputs,
        ];
    }

    /**
     * @param ContractEvent $event
     * @return array
     */
    public static function contractEventSchema(ContractEvent $event): array
    {
        $params = [];
        if ($event->inputs) {
            foreach ($event->inputs as $input) {
                $params[] = self::abiParamSchema($input);
            }
        }

        return [
            $event->name ?? "",
            $event->isAnonymous ?? false,
            $params
        ];
    }

    /**
     * @param AbiParam $param
     * @return array
     */
    public static function abiParamSchema(AbiParam $param): array
    {
        return [
            $param->name ?? "",
            $param->type,
            $param->indexed ?? false
        ];
    }
}