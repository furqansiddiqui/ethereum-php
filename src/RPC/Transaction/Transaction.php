<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\RPC\Transaction;


use Comely\DataTypes\DataTypes;
use FurqanSiddiqui\Ethereum\Exception\RPCInvalidResponseException;
use FurqanSiddiqui\Ethereum\RPC\AbstractRPCClient;

class Transaction extends AbstractRPCClient
{
    /**
     * @return string
     */
    protected function getServerURL(): string
    {
        return sprintf('https://%s.infura.io/%s/%s', $this->network, $this->ver, $this->projectId);
    }

    public function eth_getTransactionByBlockHashAndIndex(string $blockHash,string  $transactionIndex)
    {
        $transaction = $this->call("eth_getTransactionByBlockHashAndIndex", [$blockHash, $transactionIndex]);
       
        return $transaction;
    }

}