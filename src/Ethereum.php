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

namespace FurqanSiddiqui\Ethereum;

use FurqanSiddiqui\BIP32\BIP32;
use FurqanSiddiqui\BIP32\Buffers\BIP32_Provider;
use FurqanSiddiqui\ECDSA\ECC\EllipticCurveInterface;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Contracts\ABI_Factory;
use FurqanSiddiqui\Ethereum\KeyPair\HDFactory;
use FurqanSiddiqui\Ethereum\KeyPair\KeyPairFactory;
use FurqanSiddiqui\Ethereum\Math\WEIConverter;
use FurqanSiddiqui\Ethereum\Networks\AbstractNetworkConfig;

/**
 * Class Ethereum
 * @package FurqanSiddiqui\Ethereum
 */
class Ethereum implements BIP32_Provider
{
    /** @var \FurqanSiddiqui\BIP32\BIP32 */
    public readonly BIP32 $bip32;


    /** @var WEIConverter */
    private WEIConverter $weiConverter;
    /** @var ABI_Factory */
    private ABI_Factory $contracts;

    /**
     * Ethereum constructor.
     */
    public function __construct(
        public readonly EllipticCurveInterface $ecc,
        public readonly AbstractNetworkConfig  $network,
        public readonly KeyPairFactory         $keyPair,
        public readonly HDFactory              $hdFactory
    )
    {
        $this->bip32 = new BIP32($this->ecc, $this->network);

        $this->weiConverter = new WEIConverter();
        $this->contracts = new ABI_Factory();
    }

    /**
     * @param string $addr
     * @return \FurqanSiddiqui\Ethereum\Buffers\EthereumAddress
     * @throws \FurqanSiddiqui\Ethereum\Exception\InvalidAddressException
     */
    public function getAddress(string $addr): EthereumAddress
    {
        return EthereumAddress::fromString($addr, EthereumAddress::hasChecksum($addr));
    }

    /**
     * @return HDFactory
     */
    public function hd(): HDFactory
    {
        return $this->hdFactory;
    }


    /**
     * @return WEIConverter
     */
    public function wei(): WEIConverter
    {
        return $this->weiConverter;
    }

    /**
     * @return ABI_Factory
     */
    public function contracts(): ABI_Factory
    {
        return $this->contracts;
    }

    /**
     * @return AbstractNetworkConfig
     */
    public function networkConfig(): AbstractNetworkConfig
    {
        return $this->network;
    }
}
