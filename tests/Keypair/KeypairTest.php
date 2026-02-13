<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Tests\Keypair;

use Charcoal\Base\Encoding\Encoding;
use Charcoal\Buffers\Types\Bytes32;
use FurqanSiddiqui\Blockchain\Core\Crypto\Curves\Secp256k1;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Networks\EthereumNetwork;
use PHPUnit\Framework\TestCase;

/**
 * Class KeypairTest
 * @package FurqanSiddiqui\Ethereum\Tests\Keypair
 */
final class KeypairTest extends TestCase
{
    public function testBasicTests(): void
    {
        // Test Vector from:
        // https://github.com/ethereum/tests/blob/develop/KeyStoreTests/basic_tests.json
        $ethereum = new Ethereum(
            new EthereumNetwork(1, "Ethereum Mainnet", false),
            new Secp256k1()
        );

        $pubKey = $ethereum->keypair->generatePublicKey(Bytes32::decode(Encoding::Base16,
            "05a4d3eb46c742cb8850440145ce70cbc80b59f891cf5f50fd3e9c280b50c4e4"));
        $this->assertEquals(
            "0x460121576cc7df020759730751f92bd62fd78dd6",
            $ethereum->keypair->addressFromPublicKey($pubKey, false)->address
        );
    }
}