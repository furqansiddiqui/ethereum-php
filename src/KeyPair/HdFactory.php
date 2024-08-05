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

use Charcoal\Buffers\AbstractByteArray;
use Charcoal\Buffers\Frames\Bytes32;
use FurqanSiddiqui\BIP32\Buffers\Bits32;
use FurqanSiddiqui\BIP32\Buffers\Bits512;
use FurqanSiddiqui\BIP32\Buffers\SerializedBIP32Key;
use FurqanSiddiqui\BIP39\Mnemonic;
use FurqanSiddiqui\ECDSA\KeyPair;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class HDFactory
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
readonly class HdFactory
{
    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     */
    public function __construct(public Ethereum $eth)
    {
    }

    /**
     * @param \FurqanSiddiqui\BIP32\Buffers\SerializedBIP32Key|\Charcoal\Buffers\AbstractByteArray $ser
     * @param bool $isMaster
     * @return \FurqanSiddiqui\Ethereum\KeyPair\HdKey|\FurqanSiddiqui\Ethereum\KeyPair\MasterHdKey
     * @throws \FurqanSiddiqui\BIP32\Exception\UnserializeBIP32KeyException
     */
    public function unserialize(SerializedBIP32Key|AbstractByteArray $ser, bool $isMaster = false): HdKey|MasterHdKey
    {
        if (!$ser instanceof SerializedBIP32Key) {
            $ser = new SerializedBIP32Key($ser->raw());
        }

        return $isMaster ? MasterHdKey::Unserialize($this->eth, $ser) : HdKey::Unserialize($this->eth, $ser);
    }

    /**
     * @param \Charcoal\Buffers\AbstractByteArray $prv
     * @param string|null $overrideSeed
     * @return \FurqanSiddiqui\Ethereum\KeyPair\MasterHdKey
     * @throws \FurqanSiddiqui\BIP32\Exception\UnserializeBIP32KeyException
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function masterKeyFromEntropy(AbstractByteArray $prv, ?string $overrideSeed = null): MasterHdKey
    {
        return $this->masterKeyFromSeed($this->eth->bip32->hmacEntropy($prv, $overrideSeed));
    }

    /**
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits512 $seed
     * @return \FurqanSiddiqui\Ethereum\KeyPair\MasterHdKey
     * @throws \FurqanSiddiqui\BIP32\Exception\UnserializeBIP32KeyException
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function masterKeyFromSeed(Bits512 $seed): MasterHdKey
    {
        $seed = $seed->raw();
        return new MasterHdKey(
            $this->eth->bip32,
            new PrivateKey($this->eth, new KeyPair($this->eth->ecc, new Bytes32(substr($seed, 0, 32)))),
            0,
            Bits32::fromInteger(0),
            new Bits32(str_repeat("\0", 4)),
            new Bytes32(substr($seed, 32)),
            $this->eth
        );
    }

    /**
     * @param \FurqanSiddiqui\BIP39\Mnemonic $mnemonic
     * @param string $passphrase
     * @return \FurqanSiddiqui\Ethereum\KeyPair\MasterHdKey
     * @throws \FurqanSiddiqui\BIP32\Exception\UnserializeBIP32KeyException
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function masterKeyFromMnemonic(Mnemonic $mnemonic, string $passphrase = ""): MasterHdKey
    {
        return $this->masterKeyFromSeed(new Bits512($mnemonic->generateSeed($passphrase)));
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\KeyPair\PrivateKey|\FurqanSiddiqui\Ethereum\KeyPair\PublicKey $key
     * @param \Charcoal\Buffers\Frames\Bytes32 $chainCode
     * @param int $depth
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $childNum
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $parentPubFp
     * @return \FurqanSiddiqui\Ethereum\KeyPair\HdKey
     */
    public function childKey(
        PrivateKey|PublicKey $key,
        Bytes32              $chainCode,
        int                  $depth,
        Bits32               $childNum,
        Bits32               $parentPubFp
    ): HdKey
    {
        return new HdKey(
            $this->eth->bip32,
            $key,
            $depth,
            $childNum,
            $parentPubFp,
            $chainCode,
            $this->eth
        );
    }
}
