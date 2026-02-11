<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Represents the different types of contract methods in a smart contract.
 *
 * Enum values:
 * - Function: Indicates a standard function within the contract.
 * - Constructor: Represents the constructor method used during contract deployment.
 * - Receive: Denotes a special method to handle plain Ether transfers to the contract.
 * - Fallback: Used as a fallback function when no other matching function is found.
 */
enum ContractMethodType: string
{
    case Function = "function";
    case Constructor = "constructor";
    case Receive = "receive";
    case Fallback = "fallback";
}