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

use FurqanSiddiqui\BIP32\KeyPair\AbstractKeyPair;
use FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface;
use FurqanSiddiqui\BIP32\KeyPair\PublicKeyInterface;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class BaseKeyPair
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class BaseKeyPair extends AbstractKeyPair
{
    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     * @param \FurqanSiddiqui\Ethereum\KeyPair\PrivateKey|\FurqanSiddiqui\Ethereum\KeyPair\PublicKey $key
     */
    public function __construct(
        public readonly Ethereum $eth,
        PrivateKey|PublicKey     $key
    )
    {
        parent::__construct($this->eth->bip32, $key);
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\KeyPair\PublicKey|\FurqanSiddiqui\BIP32\KeyPair\PublicKeyInterface
     */
    public function publicKey(): PublicKey|PublicKeyInterface
    {
        return parent::publicKey();
    }

    /**
     * @return bool
     */
    public function hasPrivateKey(): bool
    {
        return isset($this->prv);
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\KeyPair\PrivateKey|\FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface
     * @throws \FurqanSiddiqui\BIP32\Exception\KeyPairException
     */
    public function privateKey(): PrivateKey|PrivateKeyInterface
    {
        return parent::privateKey();
    }
}
