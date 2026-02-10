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
 * Contains unit tests for the Keccak256 hashing algorithm.
 */
class Keccak256Test extends TestCase
{
    /**
     * @return void
     */
    public function testKeccak256Vectors(): void
    {
        $vectors = [
            "" => "c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470",
            "a" => "3ac225168df54212a25c1c01fd35bebfea408fdac2e31ddd6f80a4bbf9a5f1cb",
            "abc" => "4e03657aea45a94fc7d47ba826c8d667c0d1e6e33a64a036ec44f58fa12d6c45",
            "The quick brown fox jumps over the lazy dog" =>
                "4d741b6f1eb29cb2a9b9911c82f56fa8d73b04959d3d9d222895df6c0b28aa15",
            "The quick brown fox jumps over the lazy dog." =>
                "578951e24efd62a3d63a86f7cd19aaa53c898fe287d2552133220370240b572d",
            "Hello World" => "592fa743889fc7f92ac2a37bb1f5ba1daf2a5c84741ca0e0061d243a2e6707ba"
        ];

        foreach ($vectors as $msg => $expectedHex) {
            $this->assertSame(
                $expectedHex,
                Keccak256::hash($msg, false),
                "Mismatch for: " . var_export($msg, true)
            );
        }
    }

    /**
     * @return void
     */
    public function testEmptyString(): void
    {
        $this->assertEquals(
            "c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470",
            Keccak256::hash("", false)
        );
    }

    /**
     * @return void
     */
    public function testAbc(): void
    {
        $this->assertEquals(
            "4e03657aea45a94fc7d47ba826c8d667c0d1e6e33a64a036ec44f58fa12d6c45",
            Keccak256::hash("abc", false)
        );
    }
}