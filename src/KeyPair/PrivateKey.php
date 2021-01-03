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
use FurqanSiddiqui\BIP32\ECDSA\Curves;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Transactions\RLPEncodedTx;
use FurqanSiddiqui\Ethereum\Transactions\TxBuilder;

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
     * @param HDKey|null $extendedKey
     */
    public function __construct(Ethereum $eth, Base16 $entropy, ?HDKey $extendedKey = null)
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
