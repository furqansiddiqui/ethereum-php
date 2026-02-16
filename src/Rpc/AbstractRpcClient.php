<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc;

use Charcoal\Http\Client\ClientConfig;
use Charcoal\Http\Client\HttpClient;
use Charcoal\Http\Commons\Enums\HttpMethod;
use FurqanSiddiqui\Ethereum\Rpc\Traits\EthereumApiTrait;
use FurqanSiddiqui\Ethereum\Rpc\Traits\NetworkApiTrait;
use FurqanSiddiqui\Ethereum\Rpc\Traits\NormalizeBase16Trait;
use FurqanSiddiqui\Ethereum\Rpc\Traits\Web3ApiTrait;

/**
 * Abstract JSON-RPC 2.0 client for Ethereum RPC calls.
 */
abstract class AbstractRpcClient
{
    use NetworkApiTrait;
    use EthereumApiTrait;
    use Web3ApiTrait;
    use NormalizeBase16Trait;

    protected private(set) HttpClient $httpClient;
    private(set) int $rpcCount = 0;
    public bool $verifyResultIds = true;

    /**
     * @param ClientConfig $httpClientConfig
     * @param string $serverUrl
     */
    public function __construct(
        ClientConfig           $httpClientConfig,
        public readonly string $serverUrl
    )
    {
        $this->httpClient = new HttpClient($httpClientConfig);
    }

    /**
     * @throws EthereumRpcException
     */
    #[\NoDiscard]
    final protected function call(
        string  $method,
        array   $params = [],
        ?string $requestId = null
    ): null|bool|int|float|array|string
    {
        $this->rpcCount++;
        $requestId ??= $this->rpcCount;
        try {
            $result = $this->httpClient->request(HttpMethod::POST, $this->serverUrl, [
                "Content-Type" => "application/json"
            ], [
                "jsonrpc" => "2.0",
                "method" => $method,
                "id" => $requestId,
                "params" => $params
            ])->send()->payload->getArray();
        } catch (\Exception $e) {
            throw new EthereumRpcException("Failed to dispatch RPC request", previous: $e);
        }

        if ($this->verifyResultIds) {
            if (!isset($result["id"]) || (string)$result["id"] !== (string)$requestId) {
                throw new EthereumRpcException("RPC request ID mismatch");
            }
        }

        if (isset($result["error"])) {
            throw new EthereumRpcException(
                $result["error"]["message"] ?? "RPC called returned error",
                intval($result["error"]["code"] ?? 0)
            );
        }

        if (!array_key_exists("result", $result)) {
            throw new EthereumRpcException("RPC called returned no result");
        }

        return $result["result"];
    }

    /**
     * @throws EthereumRpcException
     */
    final protected function throwBadResultType(string $method, string $expected, string $actual): never
    {
        throw new EthereumRpcException(sprintf('Invalid result for "%s": expected "%s", got "%s"',
            $method, $expected, $actual));
    }
}