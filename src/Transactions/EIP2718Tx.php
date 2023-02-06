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

use Comely\Buffer\AbstractByteArray;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Buffers\RLP_Encoded;
use FurqanSiddiqui\Ethereum\Buffers\WEIAmount;
use FurqanSiddiqui\Ethereum\Exception\TxDecodeException;
use FurqanSiddiqui\Ethereum\RLP\Mapper;

/**
 * Class EIP2718Tx
 * @package FurqanSiddiqui\Ethereum\Transactions
 */
class EIP2718Tx extends AbstractTransaction
{
    public ?int $chainId = null;
    public ?int $nonce = null;
    public ?WEIAmount $gasPrice = null;
    public ?int $gasLimit = null;
    public ?EthereumAddress $to = null;
    public ?WEIAmount $value = null;
    public ?string $data = null;
    public ?array $accessList = null;
    public ?bool $signatureParity = null;
    public ?string $signatureR = null;
    public ?string $signatureS = null;

    /**
     * @param \Comely\Buffer\AbstractByteArray $raw
     * @return static
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_DecodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     * @throws \FurqanSiddiqui\Ethereum\Exception\TxDecodeException
     */
    public static function DecodeRawTransaction(AbstractByteArray $raw): static
    {
        $raw = $raw->copy();
        $prefix = $raw->pop(1);
        if ($prefix !== "\x01") {
            throw new TxDecodeException(sprintf('Bad prefix "%s" for Type1/EIP2718 transaction', bin2hex($prefix)));
        }

        // pop method removed the first prefix byte from buffer.
        return parent::DecodeRawTransaction($raw);
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\RLP\Mapper
     */
    protected static function Mapper(): Mapper
    {
        return TxRLPMapper::EIP2718Tx();
    }

    /**
     * @return \FurqanSiddiqui\Ethereum\Buffers\RLP_Encoded
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_EncodeException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RLP_MapperException
     */
    public function encode(): RLP_Encoded
    {
        $buffer = parent::encode();
        return $buffer->prepend("\x01");
    }
}
