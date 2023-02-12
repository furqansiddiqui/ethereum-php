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

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\Bytes32;
use FurqanSiddiqui\BIP32\BIP32;
use FurqanSiddiqui\BIP32\Buffers\BIP32_Provider;
use FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface;
use FurqanSiddiqui\ECDSA\ECC\EllipticCurveInterface;
use FurqanSiddiqui\ECDSA\KeyPair;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Contracts\ABI_Factory;
use FurqanSiddiqui\Ethereum\Exception\KeyPairException;
use FurqanSiddiqui\Ethereum\KeyPair\HDFactory;
use FurqanSiddiqui\Ethereum\KeyPair\KeyPairFactory;
use FurqanSiddiqui\Ethereum\KeyPair\PrivateKey;
use FurqanSiddiqui\Ethereum\KeyPair\PublicKey;
use FurqanSiddiqui\Ethereum\Networks\AbstractNetworkConfig;
use FurqanSiddiqui\Ethereum\Transactions\TxFactory;

/**
 * Class Ethereum
 * @package FurqanSiddiqui\Ethereum
 */
class Ethereum implements BIP32_Provider
{
    /** @var \FurqanSiddiqui\BIP32\BIP32 */
    public readonly BIP32 $bip32;
    /** @var \FurqanSiddiqui\Ethereum\KeyPair\KeyPairFactory */
    public readonly KeyPairFactory $keyPair;
    /** @var \FurqanSiddiqui\Ethereum\KeyPair\HDFactory */
    public readonly HDFactory $hdKeyPair;
    /** @var \FurqanSiddiqui\Ethereum\Contracts\ABI_Factory */
    public readonly ABI_Factory $abi;
    /** @var \FurqanSiddiqui\Ethereum\Transactions\TxFactory */
    public readonly TxFactory $tx;

    /**
     * Ethereum constructor.
     */
    public function __construct(
        public readonly EllipticCurveInterface $ecc,
        public readonly AbstractNetworkConfig  $network,
    )
    {
        $this->bip32 = new BIP32($this->ecc, $this->network);
        $this->abi = new ABI_Factory();
        $this->keyPair = new KeyPairFactory($this);
        $this->hdKeyPair = new HDFactory($this);
        $this->tx = new TxFactory($this);
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
     * @param \Comely\Buffer\Bytes32 $entropy
     * @return \FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function privateKeyFromEntropy(Bytes32 $entropy): PrivateKeyInterface
    {
        return new PrivateKey($this, new KeyPair($this->ecc, $entropy));
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $compressedPubKey
     * @return \FurqanSiddiqui\Ethereum\KeyPair\PublicKey
     * @throws \FurqanSiddiqui\Ethereum\Exception\KeyPairException
     */
    public function publicKeyFromIncomplete(AbstractByteArray $compressedPubKey): PublicKey
    {
        if ($compressedPubKey->len() !== 33) {
            throw new KeyPairException('Compressed public key must be 33 bytes long');
        }

        $compressedPubKey = $compressedPubKey->raw();
        if (!in_array($compressedPubKey[0], ["\x02", "\x03"])) {
            throw new KeyPairException('Invalid compressed public key prefix');
        }

        return new PublicKey(
            $this,
            new \FurqanSiddiqui\ECDSA\ECC\PublicKey(bin2hex(substr($compressedPubKey, 1)), "", bin2hex($compressedPubKey[0]))
        );
    }

    /**
     * @return \FurqanSiddiqui\BIP32\BIP32
     */
    public function bip32(): BIP32
    {
        return $this->bip32;
    }
}
