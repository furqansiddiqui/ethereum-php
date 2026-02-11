<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Transactions;

use Charcoal\Buffers\Buffer;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * A factory class responsible for decoding raw Ethereum transactions and
 * creating instances of different transaction types.
 */
final readonly class TxFactory
{
    public function __construct(private Ethereum $eth)
    {
    }

    /**
     * @param Buffer $rawTx
     * @return EthereumTransactionInterface
     */
    public function decode(Buffer $rawTx): EthereumTransactionInterface
    {
        $prefix = $rawTx->subString(0, 1);
        if (ord($prefix) < 0x80) {
            return match ($prefix) {
                "\x01" => $this->decodeType1($rawTx),
                "\x02" => $this->decodeType2($rawTx),
                default => throw new \InvalidArgumentException("Unsupported transaction envelope prefix: "
                    . bin2hex($prefix))
            };
        }

        return $this->decodeLegacy($rawTx);
    }

    /**
     * @param ReadableBufferInterface $rawTx
     * @return LegacyTx
     */
    public function decodeLegacy(ReadableBufferInterface $rawTx): LegacyTx
    {
        return LegacyTx::decodeRawTransaction($this->eth->network->chainId, $rawTx);
    }

    /**
     * @param ReadableBufferInterface $rawTx
     * @return Type1Tx
     */
    public function decodeType1(ReadableBufferInterface $rawTx): Type1Tx
    {
        return Type1Tx::decodeRawTransaction($this->eth->network->chainId, $rawTx);
    }

    /**
     * @param ReadableBufferInterface $rawTx
     * @return Type2Tx
     */
    public function decodeType2(ReadableBufferInterface $rawTx): Type2Tx
    {
        return Type2Tx::decodeRawTransaction($this->eth->network->chainId, $rawTx);
    }

    /**
     * @return LegacyTx
     */
    public function legacyTx(): LegacyTx
    {
        return new LegacyTx($this->eth->network->chainId);
    }

    /**
     * @return Type1Tx
     */
    public function type1(): Type1Tx
    {
        return new Type1Tx($this->eth->network->chainId);
    }

    /**
     * @return Type2Tx
     */
    public function type2(): Type2Tx
    {
        return new Type2Tx($this->eth->network->chainId);
    }
}
