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

use Charcoal\Buffers\AbstractByteArray;
use Charcoal\Buffers\Frames\Bytes32;
use FurqanSiddiqui\BIP32\BIP32;
use FurqanSiddiqui\BIP32\Buffers\BIP32_Provider;
use FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface;
use FurqanSiddiqui\ECDSA\ECC\EllipticCurveInterface;
use FurqanSiddiqui\ECDSA\KeyPair;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Contracts\ABI_Factory;
use FurqanSiddiqui\Ethereum\Exception\KeyPairException;
use FurqanSiddiqui\Ethereum\KeyPair\HdFactory;
use FurqanSiddiqui\Ethereum\KeyPair\KeyPairFactory;
use FurqanSiddiqui\Ethereum\KeyPair\PrivateKey;
use FurqanSiddiqui\Ethereum\KeyPair\PublicKey;
use FurqanSiddiqui\Ethereum\Networks\AbstractNetworkConfig;
use FurqanSiddiqui\Ethereum\Transactions\TxFactory;

/**
 * Class Ethereum
 * @package FurqanSiddiqui\Ethereum
 */
readonly class Ethereum implements BIP32_Provider
{
    /** @var \FurqanSiddiqui\BIP32\BIP32 */
    public BIP32 $bip32;
    /** @var \FurqanSiddiqui\Ethereum\KeyPair\KeyPairFactory */
    public KeyPairFactory $keyPair;
    /** @var \FurqanSiddiqui\Ethereum\KeyPair\HdFactory */
    public HdFactory $hdKeyPair;
    /** @var \FurqanSiddiqui\Ethereum\Contracts\ABI_Factory */
    public ABI_Factory $abi;
    /** @var \FurqanSiddiqui\Ethereum\Transactions\TxFactory */
    public TxFactory $tx;

    /**
     * Ethereum constructor.
     */
    public function __construct(
        public EllipticCurveInterface $ecc,
        public AbstractNetworkConfig  $network,
    )
    {
        $this->bip32 = new BIP32($this->ecc, $this->network);
        $this->abi = new ABI_Factory();
        $this->keyPair = new KeyPairFactory($this);
        $this->hdKeyPair = new HdFactory($this);
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
     * @param \Charcoal\Buffers\Frames\Bytes32 $entropy
     * @return \FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function privateKeyFromEntropy(Bytes32 $entropy): PrivateKeyInterface
    {
        return new PrivateKey($this, new KeyPair($this->ecc, $entropy));
    }

    /**
     * @param \Charcoal\Buffers\AbstractByteArray $compressedPubKey
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
