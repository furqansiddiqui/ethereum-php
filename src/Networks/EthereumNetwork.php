<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Networks;

/**
 * Represents an Ethereum network configuration.
 */
final readonly class EthereumNetwork implements EthereumNetworkInterface
{
    public function __construct(
        private(set) int $chainId,
        private(set) string $name,
        private(set) bool $isTestnet,
    )
    {
    }
}