<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Keypair;

use Charcoal\Buffers\Types\Bytes32;
use FurqanSiddiqui\Blockchain\Core\Keypair\SecPublicKey256;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * A factory class for handling cryptographic key pair operations.
 */
final readonly class KeypairFactory
{
    public function __construct(private Ethereum $eth)
    {
    }

    /**
     * Generate a public key from a private key.
     * @api
     */
    public function generatePublicKey(
        #[\SensitiveParameter]
        Bytes32 $privateKey
    ): SecPublicKey256
    {
        return $this->eth->ecc->generatePublicKey($privateKey);
    }
}