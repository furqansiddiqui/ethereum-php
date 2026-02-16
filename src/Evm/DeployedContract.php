<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Evm;

use FurqanSiddiqui\Ethereum\Codecs\ABI\AbiDecoder;
use FurqanSiddiqui\Ethereum\Codecs\ABI\AbiEncoder;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;
use FurqanSiddiqui\Ethereum\Rpc\AbstractRpcClient;

/**
 * Represents a deployed Ethereum smart contract, providing methods to interact with it.
 * Encapsulates the contract's address, ABI, and an RPC client for communication.
 */
class DeployedContract
{
    public function __construct(
        protected readonly EthereumAddress   $address,
        protected readonly SmartContract     $abi,
        protected readonly AbstractRpcClient $rpcClient
    )
    {
    }

    /**
     * @throws \FurqanSiddiqui\Ethereum\Rpc\EthereumRpcException
     */
    final public function call(
        ContractMethod $method,
        array          $params = [],
        string         $scope = "latest"
    ): array
    {
        $response = $this->rpcClient->eth_call(
            $this->address,
            AbiEncoder::encodeCall($method->signature(), $method->inputTypes(), $params),
            $scope);
        if (!$response) {
            return [];
        }

        return AbiDecoder::decodeArgs($method->outputTypes(), $response);
    }

    /**
     * @param string $signature
     * @return ContractMethod
     */
    final public function methodFromSignature(string $signature): ContractMethod
    {
        if (!isset($this->abi->methods[$signature])) {
            throw new \OutOfBoundsException("Method signature not found in ABI");
        }

        return $this->abi->methods[$signature];
    }
}