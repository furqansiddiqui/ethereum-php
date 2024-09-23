<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\RPC\Result;

use FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException;

/**
 * Class TxReceipt
 * @package FurqanSiddiqui\Ethereum\RPC\Result
 */
readonly class TxReceipt
{
    public string $transactionHash;
    public int $transactionIndex;
    public string $blockHash;
    public int $blockNumber;
    public int $cumulativeGasUsed;
    public int $gasUsed;
    public ?string $contractAddress;
    public array $logs;
    public bool $status;
    public string $logsBloom;

    /**
     * @param array $result
     * @throws RPC_ResponseException
     */
    public function __construct(array $result)
    {
        if (!isset($result["transactionHash"], $result["transactionIndex"], $result["blockHash"],
            $result["blockNumber"], $result["cumulativeGasUsed"], $result["gasUsed"], $result["logs"],
            $result["status"], $result["logsBloom"])) {
            throw new RPC_ResponseException("Incomplete tx receipt data");
        }

        $this->transactionHash = $result["transactionHash"];
        $this->transactionIndex = gmp_intval(gmp_init($result["transactionIndex"], 16));
        $this->blockHash = $result["blockHash"];
        $this->blockNumber = gmp_intval(gmp_init($result["blockNumber"], 16));
        $this->cumulativeGasUsed = gmp_intval(gmp_init($result["cumulativeGasUsed"], 16));
        $this->gasUsed = gmp_intval(gmp_init($result["gasUsed"], 16));
        $this->contractAddress = $result["contractAddress"] ?? null;
        $this->logsBloom = $result["logsBloom"];
        $this->status = $result["status"] === "0x1";

        $receiptLogs = [];
        foreach ($result["logs"] as $index => $log) {
            $receiptLogs[] = new TxReceiptLog($index, $log);
        }

        $this->logs = $receiptLogs;
    }
}