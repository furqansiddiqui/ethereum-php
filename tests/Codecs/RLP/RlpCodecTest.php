<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Tests\Codecs\RLP;

use FurqanSiddiqui\Ethereum\Codecs\RLP\RlpCodec;
use PHPUnit\Framework\TestCase;

/**
 * RlpCodecTest
 * Test vectors are taken from: https://github.com/ethereum/tests/tree/develop/RLPTests
 */
final class RlpCodecTest extends TestCase
{
    public function testValidRLP(): void
    {
        $vectorsFile = __DIR__ . DIRECTORY_SEPARATOR . "rlptest.json";
        $vectors = json_decode(file_get_contents($vectorsFile), true);
        $this->assertIsArray($vectors, "Failed to decode rlptest.json");

        foreach ($vectors as $name => $vector) {
            $this->assertIsArray($vector, "Vector must be an object/assoc array: " . $name);
            $this->assertArrayHasKey("in", $vector, "Missing \"in\" in vector: " . $name);
            $this->assertArrayHasKey("out", $vector, "Missing \"out\" in vector: " . $name);

            $expectedHex = $vector["out"];
            $this->assertIsString($expectedHex, "\"out\" must be string: " . $name);

            if (str_starts_with($expectedHex, "0x")) {
                $expectedHex = substr($expectedHex, 2);
            }

            $expectedHex = strtolower($expectedHex);
            $input = $this->normalizeInput($vector["in"]);
            $encoded = bin2hex(RlpCodec::encode([$input])->bytes());
            $this->assertSame($expectedHex, $encoded, "Encoding failed: " . $name);
            $inputBn = is_int($input) ? gmp_export(gmp_init($input, 10), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN) : $input;
            $decoded = RlpCodec::decode(hex2bin($expectedHex))[0];
            $this->assertSame($inputBn, $decoded, "Decoding failed: " . $name);
        }
    }

    /**
     * @return void
     */
    public function testInvalidRLP(): void
    {
        $vectorsFile = __DIR__ . DIRECTORY_SEPARATOR . "invalidRLPTest.json";
        $vectors = json_decode(file_get_contents($vectorsFile), true);

        $this->assertIsArray($vectors, "Failed to decode invalidRLPTest.json");

        foreach ($vectors as $name => $vector) {
            $this->assertIsArray($vector, "Vector must be an object/assoc array: " . $name);
            $this->assertArrayHasKey("out", $vector, "Missing \"out\" in vector: " . $name);

            $encodedHex = $vector["out"];
            $this->assertIsString($encodedHex, "\"out\" must be string: " . $name);

            if (str_starts_with($encodedHex, "0x")) {
                $encodedHex = substr($encodedHex, 2);
            }

            $this->expectException(\Throwable::class);
            RlpCodec::decode(hex2bin($encodedHex));
        }
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    private function normalizeInput(mixed $input): mixed
    {
        if (is_array($input)) {
            array_walk($input, fn (&$item) => $item = $this->normalizeInput($item));
            return $input;
        }

        if (is_int($input)) {
            if ($input === 0) {
                return "";
            }

            if ($input >= 1 && $input <= 255) {
                return chr($input);
            }
        }

        if (is_string($input) && str_starts_with($input, "#")) {
            $bn = gmp_init(substr($input, 1), 10);
            return gmp_export($bn, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        }

        return $input;
    }
}
