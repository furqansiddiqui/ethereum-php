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
 * Represents an EIP-1559 Ethereum transaction.
 * This class extends the AbstractEthereumTransaction to handle the specifics
 * of EIP-1559 transactions, including fields such as maxPriorityFeePerGas,
 * maxFeePerGas, and the access list. It supports encoding, decoding, and
 * signing of transactions.
 */
class EIP1559Tx extends AbstractEthereumTransaction
{
    public ?int $nonce = null;
    public ?Wei $maxPriorityFeePerGas = null;
    public ?Wei $maxFeePerGas = null;
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
        if ($raw[0] !== "\x02") {
            throw new \InvalidArgumentException("Bad prefix for Type2/EIP1559 transaction: " . bin2hex($raw[0]));
        }

        return parent::decodeRawTransaction($chainId, new Buffer(substr($raw, 1)));
    }

    /**
     * @return RlpSchema
     */
    protected static function getRlpSchema(): RlpSchema
    {
        return TxRlpSchema::eip1559Tx();
    }

    /**
     * @return Bytes32
     */
    public function signPreImage(): Bytes32
    {
        $unSignedTx = $this->isSigned() ? $this->getUnsigned() : $this;
        $encoded = $unSignedTx->encode(TxRlpSchema::eip1559TxUnsigned())->bytes();
        return new Bytes32(Keccak256::hash($encoded, true));
    }

    /**
     * @param RlpSchema|null $schema
     * @return Buffer
     */
    public function encode(?RlpSchema $schema = null): Buffer
    {
        return parent::encode($schema)->prepend("\x02");
    }

    /**
     * @return $this
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
     * @return $this
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
