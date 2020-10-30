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

namespace FurqanSiddiqui\Ethereum\Contracts;

use Comely\DataTypes\BcNumber;
use FurqanSiddiqui\Ethereum\Accounts\Account;
use FurqanSiddiqui\Ethereum\Exception\ContractsException;
use FurqanSiddiqui\Ethereum\Exception\RPCInvalidResponseException;
use FurqanSiddiqui\Ethereum\RPC\AbstractRPCClient;

/**
 * Class Contract
 * @package FurqanSiddiqui\Ethereum\Contracts
 */
class Contract
{
    /** @var ABI */
    private ABI $abi;
    /** @var Account */
    private Account $account;
    /** @var AbstractRPCClient|null */
    private ?AbstractRPCClient $rpcClient;

    /**
     * Contract constructor.
     * @param ABI $abi
     * @param Account $addr
     * @param AbstractRPCClient|null $rpcClient
     */
    public function __construct(ABI $abi, Account $addr, ?AbstractRPCClient $rpcClient = null)
    {
        $this->abi = $abi;
        $this->account = $addr;
        $this->rpcClient = $rpcClient;
    }

    /**
     * @param AbstractRPCClient $rpcClient
     * @return $this
     */
    public function useRPCClient(AbstractRPCClient $rpcClient): self
    {
        $this->rpcClient = $rpcClient;
        return $this;
    }

    /**
     * @return ABI
     */
    public function abi(): ABI
    {
        return $this->abi;
    }

    /**
     * @return AbstractRPCClient|null
     */
    public function rpcClient(): ?AbstractRPCClient
    {
        return $this->rpcClient;
    }

    /**
     * @param string $str
     * @return string
     */
    public function cleanStr(string $str): string
    {
        return preg_replace('/[^\w.-]/', '', trim($str));
    }

    /**
     * @param string $int
     * @param int $scale
     * @return string
     */
    protected function decimalValue(string $int, int $scale = 18): string
    {
        return (new BcNumber($int))->divide(pow(10, $scale), $scale)->value();
    }

    /**
     * @param string $func
     * @param array|null $args
     * @param string $block
     * @return array
     * @throws ContractsException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPCException
     */
    public function call(string $func, ?array $args = null, string $block = "latest"): array
    {
        if (!$this->rpcClient) {
            throw new ContractsException('RPC client is not configured');
        }

        $data = $this->abi->encodeCall($func, $args);
        $params = [
            "to" => $this->account->getAddress(),
            "data" => $data
        ];

        $res = $this->rpcClient->call("eth_call", [$params, $block]);
        if (!is_string($res)) {
            throw RPCInvalidResponseException::InvalidDataType("eth_call", "string", gettype($res));
        }

        if ($res === "0x") {
            return []; // Return empty Array
        }

        return $this->abi->decodeResponse($func, $res);
    }
}
