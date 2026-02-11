<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Transactions;

use Charcoal\Buffers\Buffer;
use Charcoal\Buffers\Types\Bytes32;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpCodec;
use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpEncodableInterface;
use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpSchema;
use FurqanSiddiqui\Ethereum\Crypto\Keccak256;

/**
 * An abstract base class that defines the structure and behavior of an Ethereum transaction.
 * It includes methods for encoding, decoding, and hashing transactions, as well as checking
 * whether the transaction is signed.
 */
abstract class AbstractEthereumTransaction implements
    RlpEncodableInterface,
    EthereumTransactionInterface
{
    protected(set) ?Bytes32 $signatureR = null;
    protected(set) ?Bytes32 $signatureS = null;

    abstract protected static function getRlpSchema(): RlpSchema;

    /**
     * @param int|null $chainId
     */
    public function __construct(public ?int $chainId = null)
    {
    }

    /**
     * @param int $chainId
     * @param ReadableBufferInterface $raw
     * @return static
     */
    public static function decodeRawTransaction(int $chainId, ReadableBufferInterface $raw): static
    {
        $rlpObject = static::getRlpSchema()->createObject(RlpCodec::decode($raw));
        $tx = new static();
        foreach ($rlpObject as $key => $value) {
            if (!property_exists($tx, $key)) {
                throw new \InvalidArgumentException(sprintf('Unknown property "%s" in %s', $key, static::class));
            }

            $tx->$key = $value;
        }

        return $tx;
    }

    /**
     * @return bool
     */
    public function isSigned(): bool
    {
        if ($this->signatureR !== null && $this->signatureS !== null) {
            return true;
        }

        return false;
    }

    /**
     * @param RlpSchema|null $schema
     * @return Buffer
     */
    public function encode(?RlpSchema $schema = null): Buffer
    {
        $schema = $schema ?? static::getRlpSchema();
        return $schema->encode($this);
    }

    /**
     * @return Bytes32
     */
    public function hash(): Bytes32
    {
        return new Bytes32(Keccak256::hash($this->encode()->bytes(), true));
    }
}
