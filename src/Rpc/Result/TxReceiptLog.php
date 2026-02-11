<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc\Result;

/**
 * Represents a transaction receipt log on the blockchain. This class encapsulates
 * details about a specific log entry within a transaction receipt, providing access
 * to its address, topics, data, and other metadata such as block and transaction
 * details.
 */
final readonly class TxReceiptLog
{
    public string $address;
    public array $topics;
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
     */
    public function __construct(int $index, array $result)
    {
        if (!isset($result["address"], $result["topics"], $result["data"], $result["blockNumber"],
            $result["transactionHash"], $result["transactionIndex"], $result["blockHash"], $result["logIndex"],
            $result["removed"])) {
            throw new \InvalidArgumentException("Incomplete TxReceiptLog data at index: " . $index);
        } elseif (!is_bool($result["removed"])) {
            throw new \InvalidArgumentException("Invalid removed flag at index: " . $index);
        } elseif (!is_array($result["topics"])) {
            throw new \InvalidArgumentException("Invalid topics at index: " . $index);
        }

        $this->address = $result["address"];
        $this->topics = $result["topics"];
        $this->data = $result["data"];
        $this->blockNumber = gmp_intval(gmp_init($result["blockNumber"], 16));
        $this->transactionHash = $result["transactionHash"];
        $this->transactionIndex = gmp_intval(gmp_init($result["transactionIndex"], 16));
        $this->blockHash = $result["blockHash"];
        $this->logIndex = gmp_intval(gmp_init($result["logIndex"], 16));
        $this->removed = $result["removed"];
    }
}