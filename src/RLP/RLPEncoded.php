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

namespace FurqanSiddiqui\Ethereum\RLP;

/**
 * Class RLPEncoded
 * @package FurqanSiddiqui\Ethereum\RLP
 */
class RLPEncoded
{
    /** @var array */
    private array $byteArray;

    /**
     * RLPEncoded constructor.
     * @param array $byteArray
     */
    public function __construct(array $byteArray)
    {
        $this->byteArray = $byteArray;
    }

    /**
     * @return array
     */
    public function byteArray(): array
    {
        return $this->byteArray;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->_toString($this->byteArray);
    }

    /**
     * @param array $byteArray
     * @return string
     */
    private function _toString(array $byteArray): string
    {
        $str = "";
        foreach ($byteArray as $i => $value) {
            if (is_array($value)) {
                $str .= $this->_toString($value);
                continue;
            }

            $str .= $value;
        }

        return $str;
    }
}
