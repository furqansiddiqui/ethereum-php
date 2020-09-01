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

namespace FurqanSiddiqui\Ethereum\KeyPair;

use Comely\DataTypes\Buffer\Base16;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class MasterKeyPair
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class MasterHDKey extends HDKey
{
    /**
     * MasterHDKey constructor.
     * @param Ethereum $eth
     * @param Base16 $seed
     * @param string|null $hmacKey
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function __construct(Ethereum $eth, Base16 $seed, ?string $hmacKey = null)
    {
        $binary = $seed->binary();
        if (!in_array($binary->size()->bits(), [128, 256, 512])) {
            throw new \LengthException('Base16 seed must be 128, 256 or 512-bit long');
        }

        if ($hmacKey) {
            $binary = $binary->hash()->hmac("sha512", $hmacKey);
        }

        parent::__construct($eth, $binary, null, null);
    }
}
