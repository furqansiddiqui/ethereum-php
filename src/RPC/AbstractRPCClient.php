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
}
