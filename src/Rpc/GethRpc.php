<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc;

use Charcoal\Http\Client\ClientConfig;

/**
 * Represents a client for interacting with an Ethereum node
 * using the GethRpc (Go Ethereum) client via RPC.
 */
class GethRpc extends AbstractRpcClient
{
    public function __construct(
        public readonly string $hostname,
        public readonly ?int   $port,
        ClientConfig           $httpClientConfig
    )
    {
        $serverURL = is_int($this->port) && $this->port > 0
            ? $this->hostname . ":" . $this->port
            : $this->hostname;
        if (!preg_match('/^(http|https):\/\//i', $serverURL)) {
            $serverURL = "http://" . $serverURL;
        }

        parent::__construct($httpClientConfig, $serverURL);
    }
}
