<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc\Traits;

use FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException;

/**
 * Provides methods for interacting with Ethereum network-related RPC calls.
 */
trait NetworkApiTrait
{
    /**
     * @return int
     * @throws EthereumRpcException
     */
    public function net_version(): int
    {
        return intval($this->call("net_version"));
    }

    /**
     * @return int
     * @throws EthereumRpcException
     */
    public function net_peerCount(): int
    {
        $peerCount = $this->normalizeBase16($this->call("net_peerCount"));
        if (!$peerCount) {
            $this->throwBadResultType("net_peerCount", "Base16", gettype($peerCount));
        }

        return gmp_intval(gmp_init($peerCount, 16));
    }

    /**
     * @return bool
     * @throws EthereumRpcException
     */
    public function net_listening(): bool
    {
        $listening = $this->call("net_listening");
        if (!is_bool($listening)) {
            $this->throwBadResultType("net_listening", "Bool", gettype($listening));
        }

        return $listening;
    }
}