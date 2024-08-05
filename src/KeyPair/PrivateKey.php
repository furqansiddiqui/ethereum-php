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

use FurqanSiddiqui\ECDSA\KeyPair;
use FurqanSiddiqui\Ethereum\Buffers\Signature;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Transactions\AbstractTransaction;

/**
 * Class PrivateKey
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
readonly class PrivateKey extends \FurqanSiddiqui\BIP32\KeyPair\PrivateKey
{
    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     * @param \FurqanSiddiqui\ECDSA\KeyPair $eccPrivateKey
     */
    public function __construct(
        public Ethereum $eth,
        KeyPair         $eccPrivateKey
    )
    {
        parent::__construct($this->eth->bip32, $eccPrivateKey);
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Transactions\AbstractTransaction $tx
     * @return \FurqanSiddiqui\Ethereum\Buffers\Signature
     * @throws \FurqanSiddiqui\ECDSA\Exception\SignatureException
     */
    public function signTransaction(AbstractTransaction $tx): Signature
    {
        return new Signature($this->eccPrivateKey->signRecoverable($tx->signPreImage()), $this->eth);
    }
}
