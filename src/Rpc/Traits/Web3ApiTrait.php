<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc\Traits;

/**
 * Provides methods to interact with the Web3 API.
 */
trait Web3ApiTrait
{
    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    public function web3_clientVersion(): string
    {
        $client = $this->call("web3_clientVersion");
        if (!is_string($client)) {
            $this->throwBadResultType("web3_clientVersion", "string", gettype($client));
        }

        return $client;
    }
}