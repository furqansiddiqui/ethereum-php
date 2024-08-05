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

namespace FurqanSiddiqui\Ethereum\Transactions;

use Charcoal\Buffers\Frames\Bytes32;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Buffers\WEIAmount;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;
use FurqanSiddiqui\Ethereum\RLP\Mapper;

/**
 * Class LegacyTx
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
class LegacyTx extends AbstractTransaction
{
    public ?int $nonce = null;
    public ?WEIAmount $gasPrice = null;
    public ?int $gasLimit = null;
    public ?EthereumAddress $to = null;
    public ?WEIAmount $value = null;
    public ?string $data = null;
    public ?int $signatureV = 1;
    public ?string $signatureR = null;
    public ?string $signatureS = null;

    /**
     * @return \FurqanSiddiqui\Ethereum\RLP\Mapper
     */
    protected static function Mapper(): Mapper
    {
        return TxRLPMapper::LegacyTx();
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Ethereum $eth
     */
    public function __construct(Ethereum $eth)
    {
        parent::__construct($eth);
        $this->signatureV = $this->eth->network->chainId;
    }

    /**
     * @return \Charcoal\Buffers\Frames\Bytes32
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     */
    public function signPreImage(): Bytes32
    {
        $unSignedTx = $this->isSigned() ? $this->getUnsigned() : $this;
        return new Bytes32(Keccak::hash($unSignedTx->encode()->raw(), 256, true));
    }

    /**
     * @return $this
     */
    public function getUnsigned(): static
    {
        $unSigned = clone $this;
        $unSigned->signatureV = $this->eth->network->chainId;
        $unSigned->signatureR = null;
        $unSigned->signatureS = null;
        return $unSigned;
    }
}
