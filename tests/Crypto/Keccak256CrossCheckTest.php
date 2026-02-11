<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Tests\Crypto;

use FurqanSiddiqui\Ethereum\Crypto\Keccak256;
use PHPUnit\Framework\TestCase;

/**
 * Keccak256CrossCheckTest
 *
 * This test uses test vectors from an old stable keccak256 source to cross-check
 * and ensure that the current new Keccak256 implementation is correct.
 */
class Keccak256CrossCheckTest extends TestCase
{
    /**
     * @return void
     */
    public function testCrossCheckWithVectors(): void
    {
        $vectorsFile = __DIR__ . DIRECTORY_SEPARATOR . "keccak256_vectors.json";
        if (!file_exists($vectorsFile)) {
            $this->fail("Test vectors file not found at " . $vectorsFile);
        }

        $vectors = json_decode(file_get_contents($vectorsFile), true);
        if (!is_array($vectors)) {
            $this->fail("Failed to decode keccak256_vectors.json");
        }

        foreach ($vectors as $index => $vector) {
            $data = $vector["data"];
            if (($vector["encoding"] ?? null) === "base64") {
                $data = base64_decode($data);
            }

            $expectedHash = $vector["hash"];
            $actualHash = Keccak256::hash($data, false);

            $this->assertSame(
                $expectedHash,
                $actualHash,
                sprintf("Vector at index %d failed cross-check", $index)
            );
        }
    }
}
