<?php
/*
 * This file is a part of "furqansiddiqui/ethereum-php" package.
 * https://github.com/furqansiddiqui/ethereum-php
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/ethereum-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\Accounts;

use Comely\DataTypes\Buffer\Base16;
use Comely\DataTypes\DataTypes;
use FurqanSiddiqui\Ethereum\Ethereum;
use FurqanSiddiqui\Ethereum\Exception\AccountsException;
use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;

/**
 * Class Account
 * @package FurqanSiddiqui\Ethereum\Accounts
 */
class Account
{
    /** @var Ethereum */
    private Ethereum $eth;
    /** @var Base16 */
    private Base16 $addr;

    /**
     * @param $addr
     * @return string
     * @throws AccountsException
     */
    public static function CalculateChecksum($addr): string
    {
        $addrLc = strtolower(self::ValidateAddrArg($addr)->hexits(false));
        $addrKeccak = Keccak::hash($addrLc, 256);
        $checksum = "";
        for ($i = 0; $i < strlen($addrLc); $i++) {
            $addrChar = $addrLc[$i];
            $keccakChar = $addrKeccak[$i];
            $checksum .= intval($keccakChar, 16) >= 8 ? strtoupper($addrChar) : $addrChar;
        }

        return "0x" . $checksum;
    }

    /**
     * Account constructor.
     * @param Ethereum $eth
     * @param $addr
     * @throws AccountsException
     */
    public function __construct(Ethereum $eth, $addr)
    {
        $this->eth = $eth;
        $addr = self::ValidateAddrArg($addr);
        $addrChecksum = self::CalculateChecksum($addr);

        // Validate checksum?
        $addrStr = $addr->hexits(false);
        if (preg_match('/[a-f]+/', $addrStr) && preg_match('/[A-F]+/', $addrStr)) {
            if (substr($addrChecksum, 2) !== $addrStr) {
                throw new AccountsException('Account checksum match failure');
            }
        }

        $this->addr = new Base16($addrChecksum);
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->addr->hexits(true);
    }

    /**
     * @return string
     */
    public function bytes(): string
    {
        return $this->addr->binary()->raw();
    }

    /**
     * @param $addr
     * @return Base16
     * @throws AccountsException
     */
    private static function ValidateAddrArg($addr): Base16
    {
        if (!$addr instanceof Base16) {
            if (!is_string($addr) || !DataTypes::isBase16($addr)) {
                throw new AccountsException('Invalid Ethereum address');
            }

            $addr = new Base16($addr);
        }

        if ($addr->sizeInBytes !== 40) {
            throw new AccountsException('Ethereum address must be precisely 20 bytes long');
        }

        return $addr;
    }
}
