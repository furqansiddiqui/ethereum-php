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

namespace FurqanSiddiqui\Ethereum\Buffers;

use Comely\Buffer\AbstractByteArray;
use FurqanSiddiqui\ECDSA\Signature\SignatureInterface;
use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class Signature
 * @package FurqanSiddiqui\Ethereum\KeyPair
 */
class Signature implements SignatureInterface
{
    /** @var null|int */
    public readonly ?int $v;
    /** @var null|bool */
    public readonly ?bool $yParity;

    /**
     * @param \Comely\Buffer\AbstractByteArray $signature
     * @return static
     * @throws \FurqanSiddiqui\ECDSA\Exception\ECDSA_Exception
     * @throws \FurqanSiddiqui\ECDSA\Exception\SignatureException
     */
    public static function fromDER(AbstractByteArray $signature): static
    {
        $eccSignature = \FurqanSiddiqui\ECDSA\Signature\Signature::fromDER($signature);
        return new static($eccSignature);
    }

    /**
     * @param \FurqanSiddiqui\ECDSA\Signature\Signature $eccSignature
     * @param \FurqanSiddiqui\Ethereum\Ethereum|null $eth
     */
    public function __construct(
        public readonly \FurqanSiddiqui\ECDSA\Signature\Signature $eccSignature,
        ?Ethereum                                                 $eth = null
    )
    {
        if ($this->eccSignature->recoveryId > -1) {
            $this->yParity = in_array($this->eccSignature->recoveryId, [1, 4]);
            $this->v = $eth->network->chainId * 2 + (35 + (int)$this->yParity);
            return;
        }

        $this->v = null;
        $this->yParity = null;
    }

    /**
     * @param int $paddedIntegerSize
     * @return \Comely\Buffer\AbstractByteArray
     */
    public function getDER(int $paddedIntegerSize = 0): AbstractByteArray
    {
        return $this->eccSignature->getDER($paddedIntegerSize);
    }
}
