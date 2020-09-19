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
 * Class InfuraAPI
 * @package FurqanSiddiqui\Ethereum\RPC
 */
class InfuraAPI extends AbstractRPCClient
{
    /** @var string */
    private string $projectId;
    /** @var string */
    private string $projectSecret;
    /** @var string */
    private string $network;
    /** @var string */
    private string $ver;

    /**
     * InfuraAPI constructor.
     * @param Ethereum $eth
     * @param string $projectId
     * @param string $projectSecret
     * @param string $network
     * @param string $ver
     */
    public function __construct(Ethereum $eth, string $projectId, string $projectSecret, string $network = "mainnet", string $ver = "v3")
    {
        parent::__construct($eth);

        $this->projectId = $projectId;
        $this->projectSecret = $projectSecret;
        $this->network = $network;
        $this->ver = $ver;


        // Set HTTP auth basic
        $this->httpAuthBasic("", $this->projectSecret);
    }

    /**
     * @return string
     */
    protected function getServerURL(): string
    {
        return sprintf('https://%s.infura.io/%s/%s', $this->network, $this->ver, $this->projectId);
    }
}
