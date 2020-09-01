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
use FurqanSiddiqui\BIP32\Extend\PrivateKeyInterface;
use FurqanSiddiqui\ECDSA\ECC\EllipticCurveInterface;
use FurqanSiddiqui\Ethereum\Accounts\Account;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;

/**
 * Class PublicKey
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class PublicKey extends \FurqanSiddiqui\BIP32\KeyPair\PublicKey
{
    /** @var Ethereum */
    private Ethereum $eth;
    /** @var Account|null */
    private ?Account $account = null;

    /**
     * PublicKey constructor.
     * @param Ethereum $eth
     * @param PrivateKeyInterface|null $privateKey
     * @param EllipticCurveInterface|null $curve
     * @param Base16|null $publicKey
     * @param bool|null $pubKeyArgIsCompressed
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     */
    public function __construct(Ethereum $eth, ?PrivateKeyInterface $privateKey, ?EllipticCurveInterface $curve = null, ?Base16 $publicKey = null, ?bool $pubKeyArgIsCompressed = null)
    {
        $this->eth = $eth;
        parent::__construct($privateKey, $curve, $publicKey, $pubKeyArgIsCompressed);
    }

    /**
     * @return Ethereum
     */
    public function eth(): Ethereum
    {
        return $this->eth;
    }

    /**
     * @return Account
     * @throws \FurqanSiddiqui\Ethereum\Exception\AccountsException
     */
    public function getAccount(): Account
    {
        if (!$this->account) {
            $this->account = new Account($this->eth, $this->getAccountAddress());
        }

        return $this->account;
    }

    /**
     * @return string
     */
    public function getAccountAddress(): string
    {
        $pubKeyX = $this->eccPublicKeyObj->x();
        $pubKeyY = $this->eccPublicKeyObj->y();
        $pubKey = (new Binary())
            ->append($pubKeyX->binary())
            ->append($pubKeyY->binary());

        $keccakHash = new Binary(Keccak::hash($pubKey->raw(), 256, true));
        return $keccakHash->substr(-20)->base16()->hexits(true);
    }
}
