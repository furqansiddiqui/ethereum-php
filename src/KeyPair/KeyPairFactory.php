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

use Comely\DataTypes\Buffer\Base16;
use Comely\DataTypes\Buffer\Binary;
use Comely\DataTypes\DataTypes;
use FurqanSiddiqui\BIP32\ECDSA\Curves;
use FurqanSiddiqui\BIP39\Mnemonic;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Exception\KeyPairException;

/**
 * Class KeyPairFactory
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class KeyPairFactory
{
    /** @var Ethereum */
    private Ethereum $eth;

    /**
     * KeyPairFactory constructor.
     * @param Ethereum $eth
     */
    public function __construct(Ethereum $eth)
    {
        $this->eth = $eth;
    }

    /**
     * @return PrivateKey
     * @throws KeyPairException
     */
    public function generateSecurePrivateKey(): PrivateKey
    {
        $byteLength = Ethereum::PRIVATE_KEY_BITS / 8;

        try {
            $randomBytes = random_bytes($byteLength);
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            throw new KeyPairException('Failed to generate cryptographically secure pseudo-random bytes');
        }

        $entropy = (new Binary($randomBytes))->base16();
        return new PrivateKey($this->eth, $entropy, null);
    }

    /**
     * @param $entropy
     * @return PrivateKey
     */
    public function privateKeyFromEntropy($entropy): PrivateKey
    {
        if (!$entropy instanceof Base16) {
            if (!is_string($entropy) || !DataTypes::isBase16($entropy)) {
                throw new \InvalidArgumentException(
                    'Private key entropy must be Hexadecimal string or instance of Binary buffer'
                );
            }

            $entropy = new Base16($entropy);
        }

        return new PrivateKey($this->eth, $entropy, null);
    }

    /**
     * @param Mnemonic $mnemonic
     * @param string|null $passphrase
     * @return PrivateKey
     */
    public function privateKeyFromMnemonic(Mnemonic $mnemonic, ?string $passphrase = null): PrivateKey
    {
        $byteLength = Ethereum::PRIVATE_KEY_BITS / 4;
        $seed = $mnemonic->generateSeed($passphrase, $byteLength);
        if (!$seed instanceof Base16) {
            $seed = new Base16($seed);
        }

        return new PrivateKey($this->eth, $seed, null);
    }

    /**
     * @param Base16 $publicKey
     * @return PublicKey
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public function publicKeyFromUncompressed(Base16 $publicKey): PublicKey
    {
        $curve = Curves::getInstanceOf(Ethereum::ECDSA_CURVE);
        return new PublicKey($this->eth, null, $curve, $publicKey, false);
    }

    /**
     * @param Base16 $publicKey
     * @return PublicKey
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public function publicKeyFromCompressed(Base16 $publicKey): PublicKey
    {
        $curve = Curves::getInstanceOf(Ethereum::ECDSA_CURVE);
        return new PublicKey($this->eth, null, $curve, $publicKey, true);
    }
}
