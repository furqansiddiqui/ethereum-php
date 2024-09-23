<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\RPC\Result;

use Charcoal\OOP\Vectors\StringVector;
use FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException;

/**
 * Class TxReceiptLog
 * @package FurqanSiddiqui\Ethereum\RPC\Result
 */
readonly class TxReceiptLog
{
    public string $address;
    public StringVector $topics;
    public string $data;
    public int $blockNumber;
    public string $transactionHash;
    public int $transactionIndex;
    public string $blockHash;
    public int $logIndex;
    public bool $removed;

    /**
     * @param int $index
     * @param array $result
     * @throws RPC_ResponseException
     */
    public function __construct(int $index, array $result)
    {
        if (!isset($result["address"], $result["topics"], $result["data"], $result["blockNumber"],
            $result["transactionHash"], $result["transactionIndex"], $result["blockHash"], $result["logIndex"],
            $result["removed"])) {
            throw new RPC_ResponseException("Incomplete tx receipt log # $index data");
        }

        $this->address = $result["address"];
        $this->topics = new StringVector(...$result["topics"]);
        $this->data = $result["data"];
        $this->blockNumber = gmp_intval(gmp_init($result["blockNumber"], 16));
        $this->transactionHash = $result["transactionHash"];
        $this->transactionIndex = gmp_intval(gmp_init($result["transactionIndex"], 16));
        $this->blockHash = $result["blockHash"];
        $this->logIndex = gmp_intval(gmp_init($result["logIndex"], 16));
        $this->removed = $result["removed"];
    }
}