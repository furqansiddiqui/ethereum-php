<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Transactions;

use Charcoal\Buffers\Types\Bytes32;
use FurqanSiddiqui\Blockchain\Core\Signatures\EcdsaSignature256;
use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpSchema;
use FurqanSiddiqui\Ethereum\Crypto\Keccak256;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;
use FurqanSiddiqui\Ethereum\Unit\Wei;

/**
 * Represents a legacy Ethereum transaction.
 * This class provides functionality for managing and interacting with
 * Ethereum transactions that use the legacy transaction format, including
 * nonce, gas price, gas limit, recipient address, value, data, and signature.
 */
final class LegacyTx extends AbstractEthereumTransaction
{
    public ?int $nonce = null;
    public ?Wei $gasPrice = null;
    public ?int $gasLimit = null;
    public ?EthereumAddress $to = null;
    public ?Wei $value = null;
    public ?string $data = null;
    public ?int $signatureV = null;

    /**
     * @return RlpSchema
     */
    protected static function getRlpSchema(): RlpSchema
    {
        return TxRlpSchema::legacyTx();
    }

    /**
     * @param int $chainId
     */
    public function __construct(int $chainId)
    {
        parent::__construct($chainId);
        $this->signatureV = $chainId;
    }

    /**
     * @noinspection PhpUnnecessaryStaticReferenceInspection
     */
    public function getUnsigned(): static
    {
        return clone($this, [
            "signatureV" => $this->chainId,
            "signatureR" => null,
            "signatureS" => null
        ]);
    }

    /**
     * @return Bytes32
     */
    public function signPreImage(): Bytes32
    {
        $unSignedTx = $this->isSigned() ? $this->getUnsigned() : $this;
        return new Bytes32(Keccak256::hash($unSignedTx->encode()->bytes(), true));
    }

    /**
     * @param EcdsaSignature256 $signature
     * @return static
     * @noinspection PhpUnnecessaryStaticReferenceInspection
     */
    public function withSignature(EcdsaSignature256 $signature): static
    {
        if ($signature->recoveryId === null) {
            throw new \InvalidArgumentException("Signature recovery ID must be set");
        }

        return clone($this, [
            "signatureV" => ($this->chainId * 2) + ($signature->recoveryId + 35),
            "signatureR" => $signature->r,
            "signatureS" => $signature->s
        ]);
    }
}