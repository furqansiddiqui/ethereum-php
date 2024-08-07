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

/**
 * Class Infura
 * @package FurqanSiddiqui\Ethereum\RPC
 */
class Infura extends Abstract_RPC_Client
{
    public readonly string $serverURL;

    /**
     * @param string $apiKey
     * @param string $apiSecret
     * @param string $networkId
     * @param string $apiVersion
     * @param string|null $caRootFile
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_ClientException
     */
    public function __construct(
        public readonly string $apiKey,
        public readonly string $apiSecret,
        public readonly string $networkId = "mainnet",
        public readonly string $apiVersion = "v3",
        ?string                $caRootFile = null
    )
    {
        parent::__construct($caRootFile);
        $this->serverURL = sprintf('https://%s.infura.io/%s/%s', $this->networkId, $this->apiVersion, $this->apiKey);
        if ($this->apiSecret) {
            $this->httpAuthPass = $this->apiSecret;
        }
    }

    /**
     * @return string
     */
    protected function getServerURL(): string
    {
        return $this->serverURL;
    }
}
