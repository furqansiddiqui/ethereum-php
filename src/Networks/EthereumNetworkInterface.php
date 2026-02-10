<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Networks;

use FurqanSiddiqui\Blockchain\Core\Networks\BlockchainNetworkInterface;

/**
 * Represents an interface for interacting with the Ethereum network.
 * Extends the functionality of the BlockchainNetworkInterface.
 */
interface EthereumNetworkInterface extends BlockchainNetworkInterface
{
    /** @var int Ethereum chain ID */
    public int $chainId {
        get;
    }
}