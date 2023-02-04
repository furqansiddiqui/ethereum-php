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

namespace FurqanSiddiqui\Ethereum\Networks;

/**
 * Class Ethereum
 * @package FurqanSiddiqui\Ethereum\Networks
 */
class Ethereum extends AbstractNetworkConfig
{
    /**
     * @return static
     */
    public static function Mainnet(): static
    {
        return static::CustomNetwork(1, 1);
    }

    /**
     * @return static
     */
    public static function Goerli(): static
    {
        return static::CustomNetwork(5, 5);
    }

    /**
     * @return static
     */
    public static function Sepolia(): static
    {
        return static::CustomNetwork(11155111, 11155111);
    }
}
