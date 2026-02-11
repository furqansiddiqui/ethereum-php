<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Rpc\Result;

use Charcoal\Buffers\Buffer;
use Charcoal\Buffers\BufferImmutable;
use Charcoal\Buffers\Types\Bytes32;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;
use FurqanSiddiqui\Ethereum\Rpc\Traits\NormalizeBase16Trait;
use FurqanSiddiqui\Ethereum\Unit\Wei;

/**
 * Represents a blockchain transaction with relevant details.
 */
final readonly class Transaction
{
    use NormalizeBase16Trait;

    public EthereumAddress $from;
    public ?EthereumAddress $to;
    public Bytes32 $hash;
    public Wei $value;
    public int $gas;
    public ?Wei $gasPrice;
    public int $nonce;
    public ?Buffer $input;
    public ?Bytes32 $blockHash;
    public ?int $blockNumber;
    public ?int $transactionIndex;

    /**
     * @param array $result
     */
    public function __construct(array $result)
    {
        if (!isset($result["from"], $result["hash"], $result["value"], $result["gas"],
            $result["nonce"], $result["input"])) {
            throw new \InvalidArgumentException("Incomplete transaction data");
        }

        $this->from = new EthereumAddress($result["from"]);
        $this->to = isset($result["to"]) && $result["to"] ? new EthereumAddress($result["to"]) : null;
        $this->hash = new Bytes32(hex2bin($this->normalizeBase16($result["hash"], true)));
        $this->value = new Wei(gmp_intval(gmp_init($result["value"], 16)));
        $this->gas = gmp_intval(gmp_init($result["gas"], 16));
        $this->gasPrice = isset($result["gasPrice"]) ? new Wei(gmp_init($result["gasPrice"], 16)) : null;
        $this->nonce = gmp_intval(gmp_init($result["nonce"], 16));
        $this->input = !$result["input"] || $result["input"] === "0x"
            ? null
            : new BufferImmutable(hex2bin($this->normalizeBase16($result["input"], true)));
        $this->blockHash = isset($result["blockHash"])
            ? new Bytes32(hex2bin($this->normalizeBase16($result["blockHash"], true)))
            : null;
        $this->blockNumber = isset($result["blockNumber"]) ? gmp_intval(gmp_init($result["blockNumber"], 16)) : null;
        $this->transactionIndex = isset($result["transactionIndex"]) ?
            gmp_intval(gmp_init($result["transactionIndex"], 16)) : null;
    }
}