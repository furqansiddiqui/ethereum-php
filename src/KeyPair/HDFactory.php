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
use Comely\DataTypes\DataTypes;
use FurqanSiddiqui\BIP39\Mnemonic;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class HDFactory
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class HDFactory
{
    /** @var Ethereum */
    private Ethereum $eth;
    /** @var string|null */
    private ?string $hmacKey = null;

    /**
     * HDFactory constructor.
     * @param Ethereum $eth
     */
    public function __construct(Ethereum $eth)
    {
        $this->eth = $eth;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setHMACKey(string $key = "Bitcoin seed"): self
    {
        $this->hmacKey = $key ? $key : null;
        return $this;
    }

    /**
     * @param $seed
     * @return MasterHDKey
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function useEntropyAsSeed($seed): MasterHDKey
    {
        if (!$seed instanceof Base16) {
            if (!is_string($seed) || !DataTypes::isBase16($seed)) {
                throw new \InvalidArgumentException(
                    'MKD/CKD entropy must be Hexadecimal string or instance of Binary buffer'
                );
            }

            $seed = new Base16($seed);
        }

        return new MasterHDKey($this->eth, $seed, $this->hmacKey ?? Ethereum::HD_MKD_HMAC_KEY);
    }

    /**
     * @param Mnemonic $mnemonic
     * @return MasterHDKey
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function useMnemonicEntropy(Mnemonic $mnemonic): MasterHDKey
    {
        return $this->useEntropyAsSeed($mnemonic->entropy);
    }

    /**
     * @param Mnemonic $mnemonic
     * @param string|null $passphrase
     * @return MasterHDKey
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function useMnemonicSeed(Mnemonic $mnemonic, ?string $passphrase = null): MasterHDKey
    {
        $seed = $mnemonic->generateSeed($passphrase);
        return new MasterHDKey($this->eth, new Base16($seed), null); // Not applying HMAC
    }
}
