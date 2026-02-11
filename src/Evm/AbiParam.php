<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Represents a parameter definition in the ABI (Application Binary Interface).
 * This class encapsulates information about the type, name, and indexed status
 * of a parameter used in the context of interacting with smart contracts.
 */
final readonly class AbiParam
{
    public function __construct(
        public string  $type,
        public ?string $name = null,
        public ?bool   $indexed = null,
    )
    {
        if (!self::isValidType($type)) {
            throw new \InvalidArgumentException("Invalid ABI parameter type: " . $this->type);
        }
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isValidType(string $type): bool
    {
        return preg_match(
                "/\A(address|bool|string|bytes([1-9]|[12]\d|3[0-2])?|function|u?int(8|16|32|64|128|256)?|tuple)(\[(\d+)?])*\z/",
                $type
            ) === 1;
    }
}