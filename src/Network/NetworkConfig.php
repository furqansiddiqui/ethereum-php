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

namespace FurqanSiddiqui\Ethereum\Network;

/**
 * Class NetworkConfig
 * @package FurqanSiddiqui\Ethereum\Network
 * @property-read int $networkId
 * @property-read int $chainId
 */
class NetworkConfig
{
    /** @var int Network Identifier */
    private int $networkId;
    /** @var int EIP-155 Chain Identifier */
    private int $chainId;

    /**
     * NetworkConfig constructor.
     */
    public function __construct()
    {
        $this->chainId = 1;
        $this->networkId = 1;
    }

    /**
     * @param string $prop
     * @return mixed
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "chainId":
            case "networkId":
                return $this->$prop;
            default:
                throw new \OutOfBoundsException('Cannot read inaccessible property of NetworkConfig');
        }
    }

    /**
     * @param int $networkId
     * @return $this
     */
    public function setNetworkId(int $networkId): self
    {
        $this->networkId = $networkId;
        return $this;
    }

    /**
     * @param int $chainId
     * @return $this
     */
    public function setChainId(int $chainId): self
    {
        $this->chainId = $chainId;
        return $this;
    }
}
