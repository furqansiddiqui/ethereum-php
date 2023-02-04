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

namespace FurqanSiddiqui\Ethereum\KeyPair;

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\Bytes32;
use FurqanSiddiqui\BIP39\Mnemonic;
use FurqanSiddiqui\ECDSA\KeyPair;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class KeyPairFactory
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class KeyPairFactory
{
    /**
     * KeyPairFactory constructor.
     * @param Ethereum $eth
     */
    public function __construct(public readonly Ethereum $eth)
    {
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\KeyPair\BaseKeyPair
     * @throws \FurqanSiddiqui\BIP32\Exception\KeyPairException
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function generateSecurePrivateKey(): BaseKeyPair
    {
        return $this->privateKeyFromEntropy($this->eth->bip32->generateSecureEntropy());
    }

    /**
     * @param \Comely\Buffer\Bytes32 $entropy
     * @return \FurqanSiddiqui\Ethereum\KeyPair\BaseKeyPair
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function privateKeyFromEntropy(Bytes32 $entropy): BaseKeyPair
    {
        $pK = new PrivateKey($this->eth, new KeyPair($this->eth->ecc, $entropy));
        return new BaseKeyPair($this->eth, $pK);
    }

    /**
     * @param \FurqanSiddiqui\BIP39\Mnemonic $mnemonic
     * @param string|null $passphrase
     * @return \FurqanSiddiqui\Ethereum\KeyPair\BaseKeyPair
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function privateKeyFromMnemonic(Mnemonic $mnemonic, ?string $passphrase = null): BaseKeyPair
    {
        $entropy = new Bytes32($mnemonic->generateSeed($passphrase, 32));
        return $this->privateKeyFromEntropy($entropy);
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $publicKey
     * @return \FurqanSiddiqui\Ethereum\KeyPair\PublicKey
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     * @throws \FurqanSiddiqui\Ethereum\Exception\KeyPairException
     */
    public function publicKeyFromUncompressed(AbstractByteArray $publicKey): PublicKey
    {
        return new PublicKey($this->eth, \FurqanSiddiqui\ECDSA\ECC\PublicKey::fromDER($publicKey));
    }
}
