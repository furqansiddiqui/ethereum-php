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

namespace FurqanSiddiqui\Ethereum\RPC;

use FurqanSiddiqui\Ethereum\Ethereum;

/**
 * Class GethRPC
 * @package FurqanSiddiqui\Ethereum\RPC
 */
class GethRPC extends AbstractRPCClient
{
    /** @var string */
    private string $hostname;
    /** @var int|null */
    private ?int $port;

    /**
     * GethRPC constructor.
     * @param Ethereum $eth
     * @param string $host
     * @param int|null $port
     */
    public function __construct(Ethereum $eth, string $host, ?int $port = null)
    {
        parent::__construct($eth);

        $this->hostname = $host;
        $this->port = $port && $port <= 0xffff ? $port : null;
    }

    /**
     * @return string
     */
    protected function getServerURL(): string
    {
        $url = $this->hostname;
        if (!preg_match('/^(http|https):\/\//i', $url)) {
            $url = "http://" . $url;
        }

        if ($this->port) {
            $url .= ":" . $this->port;
        }

        return $url;
    }
}
