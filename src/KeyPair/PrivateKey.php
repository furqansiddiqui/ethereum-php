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
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Transactions\RLPEncodedTx;
use FurqanSiddiqui\Ethereum\Transactions\TxBuilder;

/**
 * Class PrivateKey
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class PrivateKey extends \FurqanSiddiqui\BIP32\KeyPair\PrivateKey
{
    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     * @param \FurqanSiddiqui\ECDSA\KeyPair $eccPrivateKey
     */
    public function __construct(
        public readonly Ethereum $eth,
        KeyPair                  $eccPrivateKey
    )
    {
        parent::__construct($this->eth->bip32, $eccPrivateKey);
    }

    /**
     * @param RLPEncodedTx $serializedTx
     * @return RLPEncodedTx
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     * @throws \FurqanSiddiqui\Ethereum\Exception\AccountsException
     * @throws \FurqanSiddiqui\Ethereum\Exception\IncompleteTxException
     */
    public function signTransaction(RLPEncodedTx $serializedTx): RLPEncodedTx
    {
        $curve = Curves::getInstanceOf($this->getEllipticCurveId());
        $signature = $curve->sign($this->base16(), $serializedTx->hash());

        // Check parity of Y coord of R
        // $pointR = $signature->curvePointR();
        // $parity = strlen(str_replace("0", "", gmp_strval($pointR->y(), 2))) % 2 === 0 ? 0 : 1;
        // $sigV = $this->eth->networkConfig()->chainId * 2 + (35 + $parity);

        $recId = $curve->findRecoveryId(
            $this->publicKey()->getEllipticCurvePubKeyObj(),
            $signature,
            $serializedTx->hash(),
            true
        );

        $sigV = $this->eth->networkConfig()->chainId * 2 + (4 + $recId);

        $txn = TxBuilder::Decode($this->eth, $serializedTx);
        $txn->signature($sigV, $signature->r(), $signature->s());

        return $txn->serialize();
    }
}
