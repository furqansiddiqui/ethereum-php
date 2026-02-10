<?php
/*
 * Part of the "furqansiddiqui/ethereum-php" package.
 * @link https://github.com/furqansiddiqui/ethereum-php
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Crypto;

/**
 * Final readonly class implementing the Keccak-256 hashing algorithm.
 */
final readonly class Keccak256
{
    private const int RATE_BYTES = 136;
    private const int OUT_BYTES = 32;
    private const int MASK32 = 0xFFFFFFFF;

    /** @var int[] Rotation offsets r[x + 5*y] */
    private const array ROT = [
        0, 1, 62, 28, 27,
        36, 44, 6, 55, 20,
        3, 10, 43, 25, 39,
        41, 45, 15, 21, 8,
        18, 2, 61, 56, 14,
    ];

    /**
     * Round constants (24) as [lo32, hi32]
     * @var array<int, array{int,int}>
     */
    private const array RC = [
        [0x00000001, 0x00000000],
        [0x00008082, 0x00000000],
        [0x0000808A, 0x80000000],
        [0x80008000, 0x80000000],
        [0x0000808B, 0x00000000],
        [0x80000001, 0x00000000],
        [0x80008081, 0x80000000],
        [0x00008009, 0x80000000],
        [0x0000008A, 0x00000000],
        [0x00000088, 0x00000000],
        [0x80008009, 0x00000000],
        [0x8000000A, 0x00000000],
        [0x8000808B, 0x00000000],
        [0x0000008B, 0x80000000],
        [0x00008089, 0x80000000],
        [0x00008003, 0x80000000],
        [0x00008002, 0x80000000],
        [0x00000080, 0x80000000],
        [0x0000800A, 0x00000000],
        [0x8000000A, 0x80000000],
        [0x80008081, 0x80000000],
        [0x00008080, 0x80000000],
        [0x80000001, 0x00000000],
        [0x80008008, 0x80000000],
    ];

    /**
     * @param string $message
     * @param bool $rawOutput
     * @return string
     */
    public static function hash(string $message, bool $rawOutput = true): string
    {
        $state = self::zeroState();

        $msgLen = strlen($message);
        $offset = 0;

        while (($msgLen - $offset) >= self::RATE_BYTES) {
            self::xorBlockIntoState($state, substr($message, $offset, self::RATE_BYTES));
            self::keccakF1600($state);
            $offset += self::RATE_BYTES;
        }

        $remaining = substr($message, $offset);
        $block = $remaining . "\x01";
        $padLen = self::RATE_BYTES - strlen($block);
        if ($padLen < 0) {
            throw new \RuntimeException("Keccak padding overflow");
        }

        $block .= str_repeat("\x00", $padLen);
        $last = ord($block[self::RATE_BYTES - 1]);
        $block[self::RATE_BYTES - 1] = chr($last | 0x80);
        self::xorBlockIntoState($state, $block);
        self::keccakF1600($state);
        $out = self::stateToBytes($state, self::OUT_BYTES);
        return $rawOutput ? $out : bin2hex($out);
    }

    /**
     * @return array
     */
    private static function zeroState(): array
    {
        return array_fill(0, 25, [0, 0]);
    }

    /**
     * @param array $state
     * @param string $block
     * @return void
     */
    private static function xorBlockIntoState(array &$state, string $block): void
    {
        for ($lane = 0; $lane < 17; $lane++) {
            $chunk = substr($block, $lane * 8, 8);
            [$lo, $hi] = self::leBytesToU64($chunk);

            $state[$lane][0] = ($state[$lane][0] ^ $lo) & self::MASK32;
            $state[$lane][1] = ($state[$lane][1] ^ $hi) & self::MASK32;
        }
    }

    /**
     * @param array $state
     * @return void
     */
    private static function keccakF1600(array &$state): void
    {
        $C = array_fill(0, 5, [0, 0]);
        $D = array_fill(0, 5, [0, 0]);
        $B = array_fill(0, 25, [0, 0]);

        for ($round = 0; $round < 24; $round++) {
            // Theta
            for ($x = 0; $x < 5; $x++) {
                $C[$x] = self::xor64(
                    self::xor64(
                        self::xor64($state[$x], $state[$x + 5]),
                        self::xor64($state[$x + 10], $state[$x + 15])
                    ),
                    $state[$x + 20]
                );
            }

            for ($x = 0; $x < 5; $x++) {
                $rot = self::rotl64($C[($x + 1) % 5], 1);
                $D[$x] = self::xor64($C[($x + 4) % 5], $rot);
            }

            for ($x = 0; $x < 5; $x++) {
                for ($y = 0; $y < 5; $y++) {
                    $idx = $x + 5 * $y;
                    $state[$idx] = self::xor64($state[$idx], $D[$x]);
                }
            }

            for ($x = 0; $x < 5; $x++) {
                for ($y = 0; $y < 5; $y++) {
                    $idx = $x + 5 * $y;
                    $r = self::ROT[$idx];
                    $v = self::rotl64($state[$idx], $r);

                    $nx = $y;
                    $ny = (2 * $x + 3 * $y) % 5;
                    $B[$nx + 5 * $ny] = $v;
                }
            }

            for ($y = 0; $y < 5; $y++) {
                $row = 5 * $y;
                for ($x = 0; $x < 5; $x++) {
                    $a = $B[$row + $x];
                    $b = $B[$row + (($x + 1) % 5)];
                    $c = $B[$row + (($x + 2) % 5)];

                    $state[$row + $x] = self::xor64($a, self::and64(self::not64($b), $c));
                }
            }

            $state[0] = self::xor64($state[0], self::RC[$round]);
        }
    }

    /**
     * @param array $a
     * @param array $b
     * @return int[]
     */
    private static function xor64(array $a, array $b): array
    {
        return [
            ($a[0] ^ $b[0]) & self::MASK32,
            ($a[1] ^ $b[1]) & self::MASK32
        ];
    }

    /**
     * @param array $a
     * @param array $b
     * @return int[]
     */
    private static function and64(array $a, array $b): array
    {
        return [
            ($a[0] & $b[0]) & self::MASK32,
            ($a[1] & $b[1]) & self::MASK32
        ];
    }

    /**
     * @param array $a
     * @return int[]
     */
    private static function not64(array $a): array
    {
        return [
            (~$a[0]) & self::MASK32,
            (~$a[1]) & self::MASK32
        ];
    }

    /**
     * @param array $a
     * @param int $n
     * @return array|int[]
     */
    private static function rotl64(array $a, int $n): array
    {
        $n &= 63;
        if ($n === 0) {
            return [$a[0], $a[1]];
        }

        $lo = $a[0] & self::MASK32;
        $hi = $a[1] & self::MASK32;

        if ($n < 32) {
            $newLo = (($lo << $n) | ($hi >> (32 - $n))) & self::MASK32;
            $newHi = (($hi << $n) | ($lo >> (32 - $n))) & self::MASK32;
            return [$newLo, $newHi];
        }

        $n -= 32;
        $newLo = (($hi << $n) | ($lo >> (32 - $n))) & self::MASK32;
        $newHi = (($lo << $n) | ($hi >> (32 - $n))) & self::MASK32;
        return [$newLo, $newHi];
    }

    /**
     * @param string $bytes8
     * @return int[]
     */
    private static function leBytesToU64(string $bytes8): array
    {
        if (strlen($bytes8) !== 8) {
            throw new \LengthException("Expected 8 bytes");
        }

        $lo = (ord($bytes8[0]))
            | (ord($bytes8[1]) << 8)
            | (ord($bytes8[2]) << 16)
            | (ord($bytes8[3]) << 24);

        $hi = (ord($bytes8[4]))
            | (ord($bytes8[5]) << 8)
            | (ord($bytes8[6]) << 16)
            | (ord($bytes8[7]) << 24);

        return [$lo & self::MASK32, $hi & self::MASK32];
    }

    /**
     * @param array $state
     * @param int $length
     * @return string
     */
    private static function stateToBytes(array $state, int $length): string
    {
        $out = "";
        $lane = 0;

        while (strlen($out) < $length) {
            [$lo, $hi] = $state[$lane];
            $out .= chr($lo & 0xFF)
                . chr(($lo >> 8) & 0xFF)
                . chr(($lo >> 16) & 0xFF)
                . chr(($lo >> 24) & 0xFF)
                . chr($hi & 0xFF)
                . chr(($hi >> 8) & 0xFF)
                . chr(($hi >> 16) & 0xFF)
                . chr(($hi >> 24) & 0xFF);

            $lane++;
        }

        return substr($out, 0, $length);
    }
}