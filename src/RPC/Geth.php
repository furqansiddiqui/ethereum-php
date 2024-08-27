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
 * Class Geth
 * @package FurqanSiddiqui\Ethereum\RPC
 */
class Geth extends Abstract_RPC_Client
{
    /** @var string */
    public readonly string $serverURL;

    /**
     * @param string $hostname
     * @param int|null $port
     * @param string|null $caRootFile
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_ClientException
     */
    public function __construct(
        public readonly string $hostname,
        public readonly ?int   $port,
        ?string                $caRootFile = null,
    )
    {
        parent::__construct($caRootFile);
        $serverURL = $this->port ? $this->hostname . ":" . $this->port : $this->hostname;
        if (!preg_match('/^(http|https):\/\//i', $serverURL)) {
            $serverURL = "http://" . $serverURL;
        }

        $this->serverURL = $serverURL;
    }

    /**
     * @return string
     */
    protected function getServerURL(): string
    {
        return $this->serverURL;
    }
}
