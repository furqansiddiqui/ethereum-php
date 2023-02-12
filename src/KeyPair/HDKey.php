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

use Comely\Buffer\Bytes32;
use FurqanSiddiqui\BIP32\BIP32;
use FurqanSiddiqui\BIP32\Buffers\BIP32_Provider;
use FurqanSiddiqui\BIP32\Buffers\Bits32;
use FurqanSiddiqui\BIP32\Buffers\SerializedBIP32Key;
use FurqanSiddiqui\BIP32\KeyPair\ExtendedKeyPair;
use FurqanSiddiqui\BIP32\KeyPair\PublicKeyInterface;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class KeyPair
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class HDKey extends ExtendedKeyPair
{
    /** @var \FurqanSiddiqui\Ethereum\Ethereum */
    public readonly Ethereum $eth;
    /** @var \FurqanSiddiqui\Ethereum\KeyPair\PublicKey|null */
    protected ?PublicKey $_public = null;

    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum|\FurqanSiddiqui\BIP32\Buffers\BIP32_Provider $bip32
     * @param \FurqanSiddiqui\BIP32\Buffers\SerializedBIP32Key $ser
     * @return static
     * @throws \FurqanSiddiqui\BIP32\Exception\UnserializeBIP32KeyException
     */
    public static function Unserialize(Ethereum|BIP32_Provider $bip32, SerializedBIP32Key $ser): static
    {
        if (!$bip32 instanceof Ethereum) {
            throw new \InvalidArgumentException('Expected instance of Ethereum for Unserialize method');
        }

        $hdKey = parent::Unserialize($bip32, $ser);
        $hdKey->eth = $bip32;
        return $hdKey;
    }

    /**
     * @param \FurqanSiddiqui\BIP32\BIP32 $bip32
     * @param \FurqanSiddiqui\Ethereum\KeyPair\PublicKey|\FurqanSiddiqui\Ethereum\KeyPair\PrivateKey $key
     * @param int $depth
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $childNum
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $parentPubFp
     * @param \Comely\Buffer\Bytes32 $chainCode
     * @param \FurqanSiddiqui\Ethereum\Ethereum|null $eth
     */
    public function __construct(
        BIP32                $bip32,
        PublicKey|PrivateKey $key,
        int                  $depth,
        Bits32               $childNum,
        Bits32               $parentPubFp,
        Bytes32              $chainCode,
        ?Ethereum            $eth = null,
    )
    {
        parent::__construct($bip32, $key, $depth, $childNum, $parentPubFp, $chainCode);
        if ($eth) {
            $this->eth = $eth;
        }
    }

    /**
     * @param int $index
     * @param bool $isHardened
     * @return $this
     * @throws \FurqanSiddiqui\BIP32\Exception\ChildKeyDeriveException
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function derive(int $index, bool $isHardened = false): HDKey
    {
        return HDKey::Unserialize($this->eth, $this->_derive($index, $isHardened));
    }

    /**
     * @param $path
     * @return $this
     * @throws \FurqanSiddiqui\BIP32\Exception\ChildKeyDeriveException
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function derivePath($path): HDKey
    {
        return parent::derivePath($path);
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\KeyPair\PublicKey|\FurqanSiddiqui\BIP32\KeyPair\PublicKeyInterface
     * @throws \FurqanSiddiqui\BIP32\Exception\KeyPairException
     * @throws \FurqanSiddiqui\Ethereum\Exception\KeyPairException
     */
    public function publicKey(): PublicKey|PublicKeyInterface
    {
        if (!$this->_public) {
            $this->_public = new PublicKey($this->eth, $this->privateKey()->eccPrivateKey->public());
        }

        return $this->_public;
    }
}
