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
        $this->signatureV = $chainId;
    }

    /**
     * @param int $chainId
     * @return $this
     * @noinspection PhpUnnecessaryStaticReferenceInspection
     */
    public function getUnsigned(int $chainId): static
    {
        return clone($this, [
            "signatureV" => $chainId,
            "signatureR" => null,
            "signatureS" => null
        ]);
    }

    /**
     * @param int $chainId
     * @return Bytes32
     */
    public function signPreImage(int $chainId): Bytes32
    {
        $unSignedTx = $this->isSigned() ? $this->getUnsigned($chainId) : $this;
        return new Bytes32(Keccak256::hash($unSignedTx->encode()->bytes(), true));
    }

    /**
     * @param int $chainId
     * @param EcdsaSignature256 $signature
     * @return static
     * @noinspection PhpUnnecessaryStaticReferenceInspection
     */
    public function withSignature(int $chainId, EcdsaSignature256 $signature): static
    {
        if ($signature->recoveryId === null) {
            throw new \InvalidArgumentException("Signature recovery ID must be set");
        }

        return clone($this, [
            "signatureV" => ($chainId * 2) + ($signature->recoveryId + 35),
            "signatureR" => $signature->r,
            "signatureS" => $signature->s
        ]);
    }
}