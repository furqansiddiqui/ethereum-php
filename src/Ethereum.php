<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum;

use FurqanSiddiqui\Blockchain\Core\Crypto\Curves\Secp256k1Interface;
use FurqanSiddiqui\Ethereum\Keypair\KeypairFactory;
use FurqanSiddiqui\Ethereum\Networks\EthereumNetworkInterface;

/**
 * Represents the Ethereum class, which provides functionality for interacting
 * with the Ethereum blockchain network.
 */
final readonly class Ethereum
{
    public KeypairFactory $keypair;

    public function __construct(
        public EthereumNetworkInterface $network,
        public Secp256k1Interface       $ecc
    )
    {
        $this->keypair = new KeypairFactory($this);
    }
}
