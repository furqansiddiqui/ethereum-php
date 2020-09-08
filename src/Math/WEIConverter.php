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

namespace FurqanSiddiqui\Ethereum\Math;

use Comely\DataTypes\BcMath\BcMath;

/**
 * Class WEIConverter
 * @package FurqanSiddiqui\Ethereum\Math
 */
class WEIConverter
{
    /**
     * @param string $eth
     * @return WEIValue
     */
    public function fromEth(string $eth): WEIValue
    {
        return new WEIValue($eth);
    }

    /**
     * @param $wei
     * @return WEIValue
     */
    public function fromWei($wei): WEIValue
    {
        $wei = BcMath::isNumeric($wei);
        if (!$wei) {
            throw new \InvalidArgumentException('Invalid wei value');
        }

        $wei = $wei->scale(0)->mul(1);
        return $this->fromEth($wei->divide(pow(10, 18), 18)->value());
    }

    /**
     * @param $gWei
     * @return WEIValue
     */
    public function fromGwei($gWei): WEIValue
    {
        $gWei = BcMath::isNumeric($gWei);
        if (!$gWei) {
            throw new \InvalidArgumentException('Invalid gWei value');
        }

        $gWei = $gWei->scale(9)->mul(1);
        return $this->fromEth($gWei->divide(pow(10, 9), 18)->value());
    }
}
