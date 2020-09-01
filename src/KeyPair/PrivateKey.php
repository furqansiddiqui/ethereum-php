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
use FurqanSiddiqui\BIP32\Extend\ExtendedKeyInterface;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class PrivateKey
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class PrivateKey extends \FurqanSiddiqui\BIP32\KeyPair\PrivateKey
{
    /** @var Ethereum */
    private Ethereum $eth;

    /**
     * PrivateKey constructor.
     * @param Ethereum $eth
     * @param Base16 $entropy
     * @param ExtendedKeyInterface|null $extendedKey
     */
    public function __construct(Ethereum $eth, Base16 $entropy, ?ExtendedKeyInterface $extendedKey = null)
    {
        $this->eth = $eth;
        parent::__construct($entropy, $extendedKey);

        if (!$extendedKey) {
            $this->set("curve", Ethereum::ECDSA_CURVE);
        }
    }

    /**
     * @return PublicKey
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public function publicKey(): PublicKey
    {
        if (!$this->publicKey instanceof PublicKey) {
            $this->publicKey = new PublicKey($this->eth, $this);
        }

        return $this->publicKey;
    }
}
