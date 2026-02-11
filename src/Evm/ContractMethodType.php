<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

/**
 * Represents the different types of contract methods in a smart contract.
 */
enum ContractMethodType: string
{
    case Function = "function";
    case Constructor = "constructor";
    case Receive = "receive";
    case Fallback = "fallback";
}