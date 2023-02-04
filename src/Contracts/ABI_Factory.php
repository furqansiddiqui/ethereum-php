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

namespace FurqanSiddiqui\Ethereum\Contracts;

use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Exception\ContractsException;
use FurqanSiddiqui\Ethereum\RPC\Abstract_RPC_Client;

/**
 * Class ABI_Factory
 * @package FurqanSiddiqui\Ethereum\Contracts
 */
class ABI_Factory
{
    /**
     * @param string $filePath
     * @param bool $validate
     * @param array $errors
     * @return \FurqanSiddiqui\Ethereum\Contracts\Contract
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     * @throws \FurqanSiddiqui\Ethereum\Exception\ContractsException
     * @throws \Throwable
     */
    public function fromJSONFile(string $filePath, bool $validate, array &$errors): Contract
    {
        $fileBasename = basename($filePath);
        if (!file_exists($filePath)) {
            throw new ContractsException(sprintf('Contract ABI JSON file "%s" not found', $fileBasename));
        } elseif (!is_readable($filePath)) {
            throw new ContractsException(sprintf('Contract ABI JSON file "%s" is not readable', $fileBasename));
        }

        $source = file_get_contents($filePath);
        if (!$source) {
            throw new ContractsException(sprintf('Failed to read contract ABI file "%s"', $fileBasename));
        }

        try {
            $decoded = json_decode($source, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ContractsException(sprintf('Failed to JSON decode contract ABI file "%s"', $fileBasename));
        }

        return $this->fromArray($decoded, $validate, $errors);
    }

    /**
     * @param array $abi
     * @param bool $validate
     * @param array $errors
     * @return \FurqanSiddiqui\Ethereum\Contracts\Contract
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     * @throws \Throwable
     */
    public function fromArray(array $abi, bool $validate, array &$errors): Contract
    {
        return Contract::fromArray($abi, $validate, $errors);
    }

    /**
     * @param \FurqanSiddiqui\Ethereum\RPC\Abstract_RPC_Client $rpc
     * @param \FurqanSiddiqui\Ethereum\Contracts\Contract $contract
     * @param \FurqanSiddiqui\Ethereum\Buffers\EthereumAddress|string $address
     * @return \FurqanSiddiqui\Ethereum\Contracts\DeployedContract
     */
    public function deployedAt(Abstract_RPC_Client $rpc, Contract $contract, EthereumAddress|string $address): DeployedContract
    {
        return new DeployedContract($contract, $address, $rpc);
    }
}
