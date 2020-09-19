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

use FurqanSiddiqui\Ethereum\Accounts\Account;
use FurqanSiddiqui\Ethereum\RPC\AbstractRPCClient;

/**
 * Class Contract_ABI
 * @package FurqanSiddiqui\Ethereum\Contracts
 */
class Contract_ABI
{
    /** @var ABI */
    private ABI $abi;
    /** @var AbstractRPCClient|null */
    private ?AbstractRPCClient $rpcClient;

    /**
     * Contract_ABI constructor.
     * @param ABI $abi
     * @param AbstractRPCClient|null $rpcClient
     */
    public function __construct(ABI $abi, ?AbstractRPCClient $rpcClient = null)
    {
        $this->abi = $abi;
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
     * @param Account $addr
     * @return Contract
     */
    public function deployedAt(Account $addr): Contract
    {
        return new Contract($this->abi, $addr, $this->rpcClient);
    }
}
