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

namespace FurqanSiddiqui\Ethereum\RPC;

use Comely\DataTypes\DataTypes;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Exception\RPCInvalidResponseException;
use FurqanSiddiqui\Ethereum\Math\Integers;
use FurqanSiddiqui\Ethereum\RPC\Models\Block;
use FurqanSiddiqui\Ethereum\RPC\Models\Transaction;
use FurqanSiddiqui\Ethereum\RPC\Models\TransactionReceipt;

/**
 * Class AbstractRPCClient
 * @package FurqanSiddiqui\Ethereum\RPC
 */
abstract class AbstractRPCClient extends JSON_RPC_2
{
    /** @var Ethereum */
    protected Ethereum $eth;

    /**
     * AbstractRPCClient constructor.
     * @param Ethereum $eth
     */
    public function __construct(Ethereum $eth)
    {
        $this->eth = $eth;
    }

    /**
     * @return int
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCException
     */
    public function eth_blockNumber(): int
    {
        $blockNum = $this->call("eth_blockNumber");
        if (!DataTypes::isBase16($blockNum)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_blockNumber", "Base16", gettype($blockNum));
        }

        return (int)Integers::Unpack($blockNum)->value();
    }

    /**
     * @param string $accountId
     * @param int|null $height
     * @return int
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
     */
    public function eth_getTransactionCount(string $accountId, ?int $height = null): int
    {
        $height = $height ? "0x" . dechex($height) : "latest";
        $txCount = $this->call("eth_getTransactionCount", [$accountId, $height]);
        if (!DataTypes::isBase16($txCount)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getTransactionCount", "Base16", gettype($txCount));
        }

        return (int)Integers::Unpack($txCount)->value();
    }

    /**
     * @param int|null $height
     * @return Block|null
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCException
     */
    public function eth_getBlock(?int $height = null): ?Block
    {
        $height = $height ? "0x" . dechex($height) : "latest";
        $block = $this->call("eth_getBlockByNumber", [$height, false]);
        if (is_null($block)) {
            return null; // Block not found/Out of range
        }

        if (!is_array($block)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBlockByNumber", "Object", gettype($block));
        }

        return new Block($block);
    }

    /**
     * @param string $txId
     * @return Transaction|null
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCException
     */
    public function eth_getTransaction(string $txId): ?Transaction
    {
        $txn = $this->call("eth_getTransactionByHash", [$txId]);
        if (is_null($txn)) {
            return null; // Transaction does not exist
        }

        if (!is_array($txn)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getTransactionByHash", "Object", gettype($txn));
        }

        return new Transaction($this->eth, $txn);
    }


    /**
     * @param string $txId
     * @return TransactionReceipt|null
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCException
     */
    public function eth_getTransactionReceipt(string $txId): ?TransactionReceipt
    {
        $txn = $this->call("eth_getTransactionReceipt", [$txId]);
        if (is_null($txn)) {
            return null; // Transaction does not exist
        }

        if (!is_array($txn)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getTransactionReceipt", "Object", gettype($txn));
        }

        return new TransactionReceipt($txn);
    }

    /**
     * @param string $accountId
     * @param string $scope
     * @return string
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCException
     */
    public function eth_getBalance(string $accountId, string $scope = "latest"): string
    {
        if (!in_array($scope, ["latest", "earliest", "pending"])) {
            throw new \InvalidArgumentException('Invalid block scope; Valid values are "latest", "earliest" and "pending"');
        }

        $balance = $this->call("eth_getBalance", [$accountId, $scope]);
        if (!DataTypes::isBase16($balance)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBalance", "Base16", gettype($balance));
        }

        $balance = $this->eth->wei()->fromWei(Integers::Unpack($balance))->eth();
        if (!DataTypes::isNumeric($balance)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBalance", "Base10/Decimal", "Invalid");
        }

        return $balance;
    }

    /**
     * @param string $txData
     * @return string
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
     */
    public function eth_sendRawTransaction(string $txData): string
    {
        $broadcastTx = $this->call("eth_sendRawTransaction", [$txData]);
        if (!DataTypes::isBase16($broadcastTx)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_sendRawTransaction", "Base16", gettype($broadcastTx));
        }

        return $broadcastTx;
    }
}
