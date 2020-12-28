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
use FurqanSiddiqui\ECDSA\Signature\SignatureInterface;

/**
 * Class Signature
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class Signature implements SignatureInterface
{
    /** @var Base16 */
    private Base16 $r;
    /** @var Base16 */
    private Base16 $s;
    /** @var int */
    private int $v;

    /**
     * Signature constructor.
     * @param Base16 $r
     * @param Base16 $s
     * @param int $v
     */
    public function __construct(Base16 $r, Base16 $s, int $v)
    {
        $this->r = $r;
        $this->s = $s;
        $this->v = $v;
    }

    /**
     * @return Base16
     */
    public function r(): Base16
    {
        return $this->r;
    }

    /**
     * @return Base16
     */
    public function s(): Base16
    {
        return $this->s;
    }

    /**
     * @return int
     */
    public function v(): int
    {
        return $this->v;
    }
}
