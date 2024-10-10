<?php
declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\RPC\Result;

use Charcoal\Buffers\Buffer;
use FurqanSiddiqui\Ethereum\Buffers\WEIAmount;
use FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException;

/**
 * Class Transaction
 * @package FurqanSiddiqui\Ethereum\RPC\Result
 */
readonly class Transaction
{
    public string $from;
    public ?string $to;
    public string $hash;
    public WEIAmount $value;
    public int $gas;
    public ?WEIAmount $gasPrice;
    public int $nonce;
    public Buffer $input;
    public ?string $blockHash;
    public ?int $blockNumber;
    public ?int $transactionIndex;

    /**
     * @param array $result
     * @throws RPC_ResponseException
     * @throws \FurqanSiddiqui\Ethereum\Exception\BadWEIAmountException
     */
    public function __construct(array $result)
    {
        if (!isset($result["from"], $result["hash"], $result["value"], $result["gas"],
            $result["nonce"], $result["input"])) {
            throw new RPC_ResponseException("Incomplete transaction data");
        }

        $this->from = $result["from"];
        $this->to = $result["to"] ?? null;
        $this->hash = $result["hash"];
        $this->value = new WEIAmount($result["value"]);
        $this->gas = gmp_intval(gmp_init($result["gas"], 16));
        $this->gasPrice = isset($result["gasPrice"]) ? new WEIAmount($result["gasPrice"]) : null;
        $this->nonce = gmp_intval(gmp_init($result["nonce"], 16));
        $this->input = !$result["input"] || $result["input"] === "0x" ? new Buffer() : Buffer::fromBase16($result["input"]);
        $this->blockHash = $result["blockHash"] ?? null;
        $this->blockNumber = isset($result["blockNumber"]) ? gmp_intval(gmp_init($result["blockNumber"], 16)) : null;
        $this->transactionIndex = isset($result["transactionIndex"]) ?
            gmp_intval(gmp_init($result["transactionIndex"], 16)) : null;
    }
}