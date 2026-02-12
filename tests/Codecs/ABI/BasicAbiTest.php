<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Tests\Codecs\ABI;

use FurqanSiddiqui\Ethereum\Codecs\ABI\AbiDecoder;
use FurqanSiddiqui\Ethereum\Codecs\ABI\AbiEncoder;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;
use PHPUnit\Framework\TestCase;

/**
 * BasicAbiTest
 */
class BasicAbiTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicAbiVectors(): void
    {
        $vectorsFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "Codecs" . DIRECTORY_SEPARATOR . "ABI" . DIRECTORY_SEPARATOR . "basic_abi_tests.json";
        if (!file_exists($vectorsFile)) {
            $this->fail("Test vectors file not found at " . $vectorsFile);
        }

        $vectors = json_decode(file_get_contents($vectorsFile), true);
        if (!is_array($vectors)) {
            $this->fail("Failed to decode basic_abi_tests.json");
        }

        foreach ($vectors as $name => $vector) {
            $types = $vector["types"];
            $args = $vector["args"];
            $expectedResult = $vector["result"];

            // Test Encoding
            $encoded = AbiEncoder::encodeArgs($types, $args);
            $this->assertSame(
                $expectedResult,
                bin2hex($encoded),
                sprintf("Encoding failed for vector: %s", $name)
            );

            // Test Decoding
            // Use 0x prefix to satisfy normalizeInputString
            $decoded = AbiDecoder::decodeArgs($types, "0x" . $expectedResult);

            // Convert EthereumAddress objects and large integers to comparable values
            $decodedNormalized = $this->normalizeDecodedValues($decoded);
            $argsNormalized = $this->normalizeExpectedValues($args);

            $this->assertEquals(
                $argsNormalized,
                $decodedNormalized,
                sprintf("Decoding failed for vector: %s", $name)
            );
        }
    }

    /**
     * @param array $values
     * @return array
     */
    private function normalizeDecodedValues(array $values): array
    {
        return array_map(function ($value) {
            if ($value instanceof EthereumAddress) {
                return $value->address;
            }
            if (is_array($value)) {
                return $this->normalizeDecodedValues($value);
            }

            return $value;
        }, $values);
    }

    /**
     * @param array $values
     * @return array
     */
    private function normalizeExpectedValues(array $values): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->normalizeExpectedValues($value);
            }

            return $value;
        }, $values);
    }
}
