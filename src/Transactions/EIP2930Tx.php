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
use FurqanSiddiqui\Blockchain\Core\Signatures\EcdsaSignature256;
use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpSchema;
use FurqanSiddiqui\Ethereum\Crypto\Keccak256;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;
use FurqanSiddiqui\Ethereum\Unit\Wei;

/**
 * Represents an Ethereum EIP-2930 typed transaction.
 */
class EIP2930Tx extends AbstractEthereumTransaction
{
    public ?int $nonce = null;
    public ?Wei $gasPrice = null;
    public ?int $gasLimit = null;
    public ?EthereumAddress $to = null;
    public ?Wei $value = null;
    public ?string $data = null;
    public ?array $accessList = null;
    public ?bool $yParity = null;

    /**
     * @param int $chainId
     * @param ReadableBufferInterface $raw
     * @return static
     */
    public static function decodeRawTransaction(int $chainId, ReadableBufferInterface $raw): static
    {
        $raw = $raw->bytes();
        if ($raw[0] !== "\x01") {
            throw new \InvalidArgumentException("Bad prefix for Type1/EIP2930 transaction: " . bin2hex($raw[0]));
        }

        return parent::decodeRawTransaction($chainId, new Buffer(substr($raw, 1)));
    }

    /**
     * @return RlpSchema
     */
    protected static function getRlpSchema(): RlpSchema
    {
        return TxRlpSchema::eip2930Tx();
    }

    /**
     * @param RlpSchema|null $schema
     * @return Buffer
     */
    public function encode(?RlpSchema $schema = null): Buffer
    {
        return parent::encode($schema)->prepend("\x01");
    }

    /**
     * @return Bytes32
     */
    public function signPreImage(): Bytes32
    {
        $unSignedTx = $this->isSigned() ? $this->getUnsigned() : $this;
        $encoded = $unSignedTx->encode(TxRlpSchema::eip2930TxUnsigned())->bytes();
        return new Bytes32(Keccak256::hash($encoded, true));
    }

    /**
     * @return static
     */
    public function getUnsigned(): static
    {
        return clone($this, [
            "yParity" => false,
            "signatureR" => null,
            "signatureS" => null
        ]);
    }

    /**
     * @param EcdsaSignature256 $signature
     * @return static
     */
    public function withSignature(EcdsaSignature256 $signature): static
    {
        if ($signature->recoveryId === null) {
            throw new \InvalidArgumentException("Signature recovery ID must be set");
        }

        return clone($this, [
            "yParity" => ($signature->recoveryId & 1) === 1,
            "signatureR" => $signature->r,
            "signatureS" => $signature->s
        ]);
    }
}