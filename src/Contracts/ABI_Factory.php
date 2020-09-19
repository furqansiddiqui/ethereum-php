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

use FurqanSiddiqui\Ethereum\Exception\ContractsException;

/**
 * Class ABI_Factory
 * @package FurqanSiddiqui\Ethereum\Contracts
 */
class ABI_Factory
{
    /**
     * @param string $filePath
     * @return Contract_ABI
     * @throws ContractsException
     */
    public function fromFile(string $filePath): Contract_ABI
    {
        $fileBasename = basename($filePath);
        if (!file_exists($filePath)) {
            throw new ContractsException(sprintf('ABI json file "%s" not found', $fileBasename));
        }

        $source = @file_get_contents($filePath);
        if (!$source) {
            throw new ContractsException(sprintf('Failed to read ABI file "%s"', $fileBasename));
        }

        $decoded = json_decode($source, true);
        if (!is_array($decoded)) {
            throw new ContractsException(sprintf('Failed to JSON decode ABI file "%s"', $fileBasename));
        }

        return $this->useArray($decoded);
    }

    /**
     * @param array $abi
     * @return Contract_ABI
     */
    public function useArray(array $abi): Contract_ABI
    {
        return new Contract_ABI(new ABI($abi));
    }
}
