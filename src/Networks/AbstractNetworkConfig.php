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

namespace FurqanSiddiqui\Ethereum\Networks;

use FurqanSiddiqui\BIP32\Buffers\Bits32;

/**
 * Class AbstractNetworkConfig
 * @package FurqanSiddiqui\Ethereum\Networks
 */
abstract class AbstractNetworkConfig extends \FurqanSiddiqui\BIP32\Networks\AbstractNetworkConfig
{
    /**
     * @param int $networkId
     * @param int $chainId
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $bip32_exportPrivateKeyPrefix
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $bip32_exportPublicKeyPrefix
     * @param int $bip32_hardenedIndexBeginsFrom
     * @param string $bip32_hmacSeed
     */
    final protected function __construct(
        public readonly int $networkId,
        public readonly int $chainId,
        Bits32              $bip32_exportPrivateKeyPrefix,
        Bits32              $bip32_exportPublicKeyPrefix,
        int                 $bip32_hardenedIndexBeginsFrom,
        string              $bip32_hmacSeed
    )
    {
        parent::__construct(
            $bip32_exportPrivateKeyPrefix,
            $bip32_exportPublicKeyPrefix,
            $bip32_hardenedIndexBeginsFrom,
            $bip32_hmacSeed,
            "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz",
            true
        );
    }

    /**
     * @param int $networkId
     * @param int $chainId
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32|null $bip32_exportPrivateKeyPrefix
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32|null $bip32_exportPublicKeyPrefix
     * @param int $bip32_hardenedIndexBeginsFrom
     * @param string $bip32_hmacSeed
     * @return static
     */
    public static function CustomNetwork(
        int     $networkId,
        int     $chainId,
        ?Bits32 $bip32_exportPrivateKeyPrefix = null,
        ?Bits32 $bip32_exportPublicKeyPrefix = null,
        int     $bip32_hardenedIndexBeginsFrom = 0x80000000,
        string  $bip32_hmacSeed = "Bitcoin seed"
    ): static
    {
        return new static(
            networkId: $networkId,
            chainId: $chainId,
            bip32_exportPrivateKeyPrefix: $bip32_exportPrivateKeyPrefix ?? new Bits32(hex2bin("0488ADE4")),
            bip32_exportPublicKeyPrefix: $bip32_exportPublicKeyPrefix ?? new Bits32(hex2bin("0488B21E")),
            bip32_hardenedIndexBeginsFrom: $bip32_hardenedIndexBeginsFrom,
            bip32_hmacSeed: $bip32_hmacSeed,
        );
    }

    /**
     * @return static
     */
    public static function createConfigInstance(): static
    {
        throw new \DomainException('This method is not available');
    }
}
