<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc\Result;

use Charcoal\Buffers\BufferImmutable;
use Charcoal\Buffers\Types\Bytes32;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;
use FurqanSiddiqui\Ethereum\Rpc\Traits\NormalizeBase16Trait;

/**
 * Represents a transaction receipt within a blockchain context, providing details
 * about a transaction's execution and its resulting effects.
 */
final readonly class TxReceipt
{
    use NormalizeBase16Trait;

    public Bytes32 $transactionHash;
    public int $transactionIndex;
    public Bytes32 $blockHash;
    public int $blockNumber;
    public int $cumulativeGasUsed;
    public int $gasUsed;
    public ?EthereumAddress $contractAddress;
    public bool $status;
    public BufferImmutable $logsBloom;

    /** @var TxReceiptLog[] */
    public array $logs;

    /**
     * @param array $result
     */
    public function __construct(array $result)
    {
        if (!isset($result["transactionHash"], $result["transactionIndex"], $result["blockHash"],
            $result["blockNumber"], $result["cumulativeGasUsed"], $result["gasUsed"],
            $result["logs"], $result["logsBloom"])) {
            throw new \InvalidArgumentException("Incomplete tx receipt data");
        }

        $this->transactionHash = new Bytes32(hex2bin($this->normalizeBase16($result["transactionHash"], true)));
        $this->transactionIndex = gmp_intval(gmp_init($result["transactionIndex"], 16));
        $this->blockHash = new Bytes32(hex2bin($this->normalizeBase16($result["blockHash"], true)));
        $this->blockNumber = gmp_intval(gmp_init($result["blockNumber"], 16));
        $this->cumulativeGasUsed = gmp_intval(gmp_init($result["cumulativeGasUsed"], 16));
        $this->gasUsed = gmp_intval(gmp_init($result["gasUsed"], 16));
        $this->contractAddress = ($result["contractAddress"] ?? null)
            ? new EthereumAddress($result["contractAddress"])
            : null;

        $this->logsBloom = new BufferImmutable(hex2bin($this->normalizeBase16($result["logsBloom"], true)));
        if (isset($result["status"])) {
            $this->status = $result["status"] === "0x1";
        } elseif (isset($result["root"])) {
            $this->status = true;
        } else {
            throw new \InvalidArgumentException("Missing receipt status/root");
        }

        $receiptLogs = [];
        foreach ($result["logs"] as $index => $log) {
            $receiptLogs[] = new TxReceiptLog($index, $log);
        }

        $this->logs = $receiptLogs;
    }
}