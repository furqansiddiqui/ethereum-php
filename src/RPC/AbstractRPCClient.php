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
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
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
     * @param string $scope
     * @return string
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
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
     * @return string
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
     */
    public function eth_chainId(): int
    {

        $quantity = $this->call("eth_chainId");
        if (!DataTypes::isBase16($quantity)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_chainId", "Base16", gettype($quantity));
        }

        $quantity = $this->eth->wei()->fromWei(Integers::Unpack($quantity))->eth();
        if (!DataTypes::isNumeric($quantity)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_chainId", "Base10/Decimal", "Invalid");
        }

        return (int)Integers::Unpack($quantity)->value();
    }

    /**
     * @param string $to
     * @param string|null $from
     * @param string|null $gas
     * @param string|null $gasPrice
     * @param int|null $value
     * @param string|null $data
     * @return string
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
     */
    public function eth_estimateGas(string $to, string $from = null, string $gas = null, string $gasPrice = null, int $value = null, string $data = null): int
    {
        $gasUsed = $this->call("eth_estimateGas", [$to, $from, $gas, $gasPrice, $value, $data]);
        if (!DataTypes::isBase16($gasUsed)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_estimateGas", "Base16", gettype($gasUsed));
        }

        $gasUsed = $this->eth->wei()->fromWei(Integers::Unpack($gasUsed))->eth();
        if (!DataTypes::isNumeric($gasUsed)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_estimateGas", "Base10/Decimal", "Invalid");
        }
        return (int)Integers::Unpack($gasUsed)->value();

    }

    /**
     * @param string $blockHash
     * @param bool $flag
     * @return string
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
     */
    public function eth_getBlockByHash(string $blockHash, bool $flag)
    {
        $block = $this->call("eth_getBlockByHash", [$blockHash, $flag]);
        if (!DataTypes::isBase16($block)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBlockByHash", "Base16", gettype($block));
        }

        $block = $this->eth->wei()->fromWei(Integers::Unpack($block))->eth();
        if (!DataTypes::isNumeric($block)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBlockByHash", "Base10/Decimal", "Invalid");
        }

        return $block;

    }

    /**
     * @param int|null $blockNumber
     * @param bool $flag
     * @return string
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
     */
    public function eth_getBlockByNumber(?int $blockNumber, bool $flag)
    {
        $block = $this->call("eth_getBlockByNumber", [$blockNumber, $flag]);
        if (!DataTypes::isBase16($block)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBlockByNumber", "Base16", gettype($block));
        }

        $block = $this->eth->wei()->fromWei(Integers::Unpack($block))->eth();
        if (!DataTypes::isNumeric($block)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBlockByNumber", "Base10/Decimal", "Invalid");
        }

        return $block;
    }


    /**
     * @param string $blockHash
     * @return string
     * @throws RPCInvalidResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\JSONReqException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCRequestError
     */
    public function eth_getBlockTransactionCountByHash(string $blockHash)
    {
        $transactionCount = $this->call("eth_getBlockTransactionCountByHash", [$blockHash]);
        if (!DataTypes::isBase16($transactionCount)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBlockTransactionCountByHash", "Base16", gettype($transactionCount));
        }

        $transactionCount = $this->eth->wei()->fromWei(Integers::Unpack($transactionCount))->eth();
        if (!DataTypes::isNumeric($transactionCount)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_getBlockTransactionCountByHash", "Base10/Decimal", "Invalid");
//        }

        return $transactionCount;
    }

}
