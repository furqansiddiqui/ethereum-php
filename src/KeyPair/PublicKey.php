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

use Comely\Buffer\Buffer;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Exception\KeyPairException;
use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;

/**
 * Class PublicKey
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class PublicKey extends \FurqanSiddiqui\BIP32\KeyPair\PublicKey
{
    /** @var \FurqanSiddiqui\Ethereum\Buffers\EthereumAddress|null */
    private ?EthereumAddress $address = null;

    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     * @param \FurqanSiddiqui\ECDSA\ECC\PublicKey $eccPublicKey
     * @throws \FurqanSiddiqui\Ethereum\Exception\KeyPairException
     */
    public function __construct(
        public readonly Ethereum            $eth,
        \FurqanSiddiqui\ECDSA\ECC\PublicKey $eccPublicKey
    )
    {
        parent::__construct($this->eth->bip32, $eccPublicKey);
        if (!$eccPublicKey->y) {
            throw new KeyPairException('Cannot instantiate public key with Y coordinate');
        }
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Buffers\EthereumAddress
     */
    public function address(): EthereumAddress
    {
        if ($this->address) {
            return $this->address;
        }

        $bn = Buffer::fromBase16($this->eccPublicKey->x . $this->eccPublicKey->y);
        $this->address = new EthereumAddress(substr(Keccak::hash($bn->raw(), 256, true), -20));
        return $this->address;
    }
}
