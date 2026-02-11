<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc\Traits;

use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;
use FurqanSiddiqui\Ethereum\Rpc\Result\Transaction;
use FurqanSiddiqui\Ethereum\Rpc\Result\TxReceipt;
use FurqanSiddiqui\Ethereum\Unit\Wei;

/**
 * Provides an interface for Ethereum JSON-RPC API methods.
 * Includes methods for chain ID retrieval, gas price queries, block and transaction handling,
 * and account-related information such as balances and transaction counts.
 */
trait EthereumApiTrait
{
    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_chainId(): int
    {
        $chainId = $this->normalizeBase16($this->call("eth_chainId"));
        if (!$chainId) {
            $this->throwBadResultType("eth_chainId", "Base16", gettype($chainId));
        }

        return gmp_intval(gmp_init($chainId, 16));
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    private function eth_gasPriceInGmp(): \GMP
    {
        $gasPrice = $this->normalizeBase16($this->call("eth_gasPrice"));
        if (!$gasPrice) {
            $this->throwBadResultType("eth_gasPrice", "Base16", gettype($gasPrice));
        }

        return gmp_init($gasPrice, 16);
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_gasPriceInWei(): Wei
    {
        return new Wei($this->eth_gasPriceInGmp());
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_gasPrice(): int
    {
        return gmp_intval($this->eth_gasPriceInGmp());
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_syncing(): bool
    {
        $syncing = $this->call("eth_syncing");
        if (!is_bool($syncing) && !is_array($syncing)) {
            $this->throwBadResultType("eth_syncing", "Bool|Object", gettype($syncing));
        }

        return (bool)$syncing;
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_blockNumber(): int
    {
        $blockNumber = $this->normalizeBase16($this->call("eth_blockNumber"));
        if (!$blockNumber) {
            $this->throwBadResultType("eth_blockNumber", "Base16", gettype($blockNumber));
        }

        return gmp_intval(gmp_init($blockNumber, 16));
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_getBlockByNumber(?int $num, bool $fullTx = false): ?array
    {
        $tag = $num < 0 ? "latest" : "0x" . gmp_strval(gmp_init($num), 16);
        $block = $this->call("eth_getBlockByNumber", [$tag, $fullTx]);
        if ($block === null) {
            return null;
        }

        if (!is_array($block)) {
            $this->throwBadResultType("eth_getBlockByNumber", "Object", gettype($block));
        }

        return $block;
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_getTransactionByHash(string $txId, bool $returnObject = false): null|array|Transaction
    {
        $txn = $this->call("eth_getTransactionByHash", [$txId]);
        if (is_null($txn)) {
            return null;
        }

        if (!is_array($txn)) {
            $this->throwBadResultType("eth_getTransactionByHash", "Object", gettype($txn));
        }

        return $returnObject ? new Transaction($txn) : $txn;
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_getTransactionReceipt(string $txId, bool $returnObject = false): null|array|TxReceipt
    {
        $receipt = $this->call("eth_getTransactionReceipt", [$txId]);
        if (is_null($receipt)) {
            return null;
        }

        if (!is_array($receipt)) {
            $this->throwBadResultType("eth_getTransactionReceipt", "Object", gettype($receipt));
        }

        return $returnObject ? new TxReceipt($receipt) : $receipt;
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_getTransactionCount(EthereumAddress|string $accountId, int $height = -1): int
    {
        $height = $height < 0 ? "latest" : "0x" . gmp_strval(gmp_init($height), 16);
        $txCount = $this->normalizeBase16($this->call("eth_getTransactionCount", [$accountId, $height]));
        if (!$txCount) {
            $this->throwBadResultType("eth_getTransactionCount", "Base16", gettype($txCount));
        }

        return gmp_intval(gmp_init($txCount, 16));
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function eth_getBalance(EthereumAddress|string $accountId, string $scope = "latest"): Wei
    {
        if (!in_array($scope, ["latest", "earliest", "pending"])) {
            throw new \InvalidArgumentException("Invalid block scope; " .
                'Valid values are "latest", "earliest" and "pending"');
        }

        $balance = $this->normalizeBase16($this->call("eth_getBalance", [$accountId, $scope]));
        if (!$balance) {
            $this->throwBadResultType("eth_getBalance", "Base16", gettype($balance));
        }

        return new Wei(gmp_init($balance, 16));
    }
}