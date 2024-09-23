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

use Charcoal\Buffers\ByteOrder\BigEndian;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Buffers\WEIAmount;
use FurqanSiddiqui\Ethereum\Exception\BadWEIAmountException;
use FurqanSiddiqui\Ethereum\Exception\RPC_RequestException;
use FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException;

/**
 * Class AbstractRPCClient
 * @package FurqanSiddiqui\Ethereum\RPC
 */
abstract class Abstract_RPC_Client extends Abstract_JSON_RPC_2
{
    /**
     * @return int
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function net_version(): int
    {
        return intval($this->apiCall("net_version"));
    }

    /**
     * @return int
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function net_peerCount(): int
    {
        $peerCount = $this->getCleanHexadecimal($this->apiCall("net_peerCount"));
        if (!$peerCount) {
            throw RPC_ResponseException::InvalidResultDataType("net_peerCount", "Base16", gettype($peerCount));
        }

        return gmp_intval(gmp_init($peerCount, 16));
    }

    /**
     * @return bool
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function net_listening(): bool
    {
        $listening = $this->apiCall("net_listening");
        if (!is_bool($listening)) {
            throw RPC_ResponseException::InvalidResultDataType("net_listening", "Bool", gettype($listening));
        }

        return $listening;
    }

    /**
     * @return int
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function eth_chainId(): int
    {
        $chainId = $this->getCleanHexadecimal($this->apiCall("eth_chainId"));
        if (!$chainId) {
            throw RPC_ResponseException::InvalidResultDataType("eth_chainId", "Base16", gettype($chainId));
        }

        return gmp_intval(gmp_init($chainId, 16));
    }

    /**
     * @return int
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function eth_gasPrice(): int
    {
        $gasPrice = $this->getCleanHexadecimal($this->apiCall("eth_gasPrice"));
        if (!$gasPrice) {
            throw RPC_ResponseException::InvalidResultDataType("eth_gasPrice", "Base16", gettype($gasPrice));
        }

        return gmp_intval(gmp_init($gasPrice, 16));
    }

    /**
     * @return false|array
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function eth_syncing(): false|array
    {
        $syncing = $this->apiCall("eth_syncing");
        if (!is_bool($syncing) && !is_array($syncing)) {
            throw RPC_ResponseException::InvalidResultDataType("eth_syncing", "bool|Object", gettype($syncing));
        }

        return $syncing;
    }

    /**
     * @return int
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_RequestException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException
     */
    public function eth_blockNumber(): int
    {
        $blockNum = $this->getCleanHexadecimal($this->apiCall("eth_blockNumber"));
        if (!$blockNum) {
            throw RPC_ResponseException::InvalidResultDataType("eth_blockNumber", "Base16", gettype($blockNum));
        }

        return gmp_intval(gmp_init($blockNum, 16));
    }

    /**
     * @param string $txId
     * @return array|null
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function eth_getTransactionByHash(string $txId): ?array
    {
        return $this->apiCall("eth_getTransactionByHash", [$txId]);
    }

    /**
     * @param string $txId
     * @return array|null
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function eth_getTransactionReceipt(string $txId): ?array
    {
        return $this->apiCall("eth_getTransactionReceipt", [$txId]);
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Buffers\EthereumAddress|string $accountId
     * @param int $height
     * @return int
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_RequestException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException
     */
    public function eth_getTransactionCount(EthereumAddress|string $accountId, int $height = 0): int
    {
        $height = $height > 0 ? $this->int2hex($height) : "latest";
        $txCount = $this->getCleanHexadecimal($this->apiCall("eth_getTransactionCount", [$accountId, $height]));
        if (!is_string($txCount)) {
            throw RPC_ResponseException::InvalidResultDataType("eth_getTransactionCount", "Base16", gettype($txCount));
        }

        return gmp_intval(gmp_init($txCount, 16));
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\Buffers\EthereumAddress|string $accountId
     * @param string $scope
     * @return \FurqanSiddiqui\Ethereum\Buffers\WEIAmount
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_RequestException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException
     */
    public function eth_getBalance(EthereumAddress|string $accountId, string $scope = "latest"): WEIAmount
    {
        if (!in_array($scope, ["latest", "earliest", "pending"])) {
            throw new RPC_RequestException('Invalid block scope; Valid values are "latest", "earliest" and "pending"');
        }

        $balance = $this->getCleanHexadecimal($this->apiCall("eth_getBalance", [$accountId, $scope]));
        if (!is_string($balance)) {
            throw RPC_ResponseException::InvalidResultDataType("eth_getBalance", "Base16", gettype($balance));
        }

        try {
            return new WEIAmount($balance);
        } catch (BadWEIAmountException) {
            throw new RPC_ResponseException('Cannot decode wei amount', method: "eth_getBalance");
        }
    }

    /**
     * @return string
     * @throws RPC_RequestException
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     */
    public function web3_clientVersion(): string
    {
        $web3Client = $this->apiCall("web3_clientVersion");
        if (!$web3Client || !is_string($web3Client)) {
            throw RPC_ResponseException::InvalidResultDataType("web3_clientVersion", "string", gettype($web3Client));
        }

        return $web3Client;
    }

    /**
     * @param mixed $in
     * @return string|null
     */
    private function getCleanHexadecimal(mixed $in): ?string
    {
        if (!is_string($in) || !preg_match('/(0x)?[a-f0-9]+/i', $in)) {
            return null;
        }

        if (str_starts_with($in, "0x")) {
            $in = substr($in, 2);
        }

        if (strlen($in) % 2 !== 0) {
            $in = "0" . $in;
        }

        return $in;
    }

    /**
     * @param int|string $num
     * @return string
     */
    private function int2hex(int|string $num): string
    {
        $hex = bin2hex(BigEndian::GMP_Pack($num));
        if (strlen($hex) % 2 !== 0) {
            $hex = "0" . $hex;
        }

        return "0x" . $hex;
    }
}
