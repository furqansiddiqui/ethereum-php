<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Keypair;

use Charcoal\Buffers\Types\Bytes20;
use FurqanSiddiqui\Blockchain\Core\Keypair\SecPublicKey256;
use FurqanSiddiqui\Ethereum\Crypto\Keccak256;

/**
 * Represents an Ethereum address, either as raw binary data or a hexadecimal string.
 * Handles validation, checksum computation, and address formatting.
 */
final readonly class EthereumAddress implements \Stringable
{
    public Bytes20 $binary;
    public string $address;
    private ?string $withChecksum;

    /**
     * @param string|Bytes20 $address
     * @param bool $checksum
     */
    public function __construct(
        string|Bytes20 $address,
        bool           $checksum = false
    )
    {
        if ($address instanceof Bytes20) {
            $this->binary = $address;
            if ($checksum) {
                $this->withChecksum = self::_calculateChecksum("0x" . bin2hex($address->bytes()));
                $this->address = $this->withChecksum;
            } else {
                $this->address = "0x" . bin2hex($address->bytes());
            }

            return;
        }

        if (!self::isValidHexAddress($address)) {
            throw new \InvalidArgumentException("Invalid Ethereum Address");
        }

        $this->binary = new Bytes20(hex2bin(substr($address, 2)));
        if ($checksum) {
            $this->withChecksum = self::_calculateChecksum($address);
            $this->address = $this->withChecksum;
        } else {
            $this->address = (string)$address;
        }
    }

    /**
     * @param SecPublicKey256 $publicKey256
     * @param bool $withChecksum
     * @return self
     */
    public static function fromPublicKey(
        SecPublicKey256 $publicKey256,
        bool            $withChecksum = false
    ): self
    {
        if ($publicKey256->isCompressed()) {
            throw new \RuntimeException("Cannot create Ethereum Address from compressed public key");
        }

        return new self(
            new Bytes20(substr(Keccak256::hash(substr($publicKey256->toExpanded(), 1), rawOutput: true), -20)),
            checksum: $withChecksum
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->withChecksum ?? $this->address;
    }

    /**
     * @param mixed $address
     * @return bool
     */
    public static function isValidHexAddress(mixed $address): bool
    {
        return is_string($address)
            && strlen($address) === 42
            && preg_match("/^0x[a-fA-F0-9]{40}$/", $address);
    }

    /**
     * @param mixed $address
     * @return bool
     */
    public static function isValidChecksumAddress(mixed $address): bool
    {
        return self::isValidHexAddress($address)
            && $address === self::_calculateChecksum($address);
    }

    /**
     * @param string $address
     * @return string
     */
    public static function calculateChecksum(string $address): string
    {
        if (!self::isValidHexAddress($address)) {
            throw new \InvalidArgumentException("Invalid Ethereum Address");
        }

        return self::_calculateChecksum($address);
    }

    /**
     * @param string $address
     * @return string
     */
    private static function _calculateChecksum(string $address): string
    {
        $address = strtolower(substr($address, 2));
        $keccak = Keccak256::hash($address, false);
        $checksum = "";
        for ($i = 0; $i < 40; $i++) {
            $checksum .= intval($keccak[$i], 16) >= 8 ?
                strtoupper($address[$i]) : $address[$i];
        }

        return "0x" . $checksum;
    }
}