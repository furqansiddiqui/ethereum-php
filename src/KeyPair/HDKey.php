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
use Comely\DataTypes\Buffer\Binary;
use FurqanSiddiqui\BIP32\Extend\ExtendedKeyInterface;
use FurqanSiddiqui\BIP32\ExtendedKey;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class KeyPair
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class HDKey extends ExtendedKey
{
    /** @var Ethereum */
    private Ethereum $eth;

    /**
     * KeyPair constructor.
     * @param Ethereum $eth
     * @param Binary $seed
     * @param ExtendedKeyInterface|null $parent
     * @param Base16|null $childNumber
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function __construct(Ethereum $eth, Binary $seed, ?ExtendedKeyInterface $parent = null, ?Base16 $childNumber = null)
    {
        $this->eth = $eth;
        parent::__construct($seed, $parent, $childNumber);
    }


}
