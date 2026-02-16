# Ethereum PHP Library


[![Tests Passing](https://github.com/furqansiddiqui/ethereum-php/actions/workflows/tests.yml/badge.svg)](https://github.com/furqansiddiqui/ethereum-php/actions)
[![MIT License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A modern, dependency-light Ethereum library for PHP 8.5+, powered by a clean cryptographic core. This library provides a comprehensive suite of tools for interacting with the Ethereum blockchain, including account management, transaction handling, and smart contract interaction.

## Features

- **Account Management**: Generate keypairs, derive Ethereum addresses, and handle EIP-55 checksums.
- **JSON-RPC Client**: Full support for standard Ethereum RPC methods via a clean, extensible client.
- **Transaction Types**: Support for Legacy, EIP-2930 (Type 1), and EIP-1559 (Type 2) transactions.
- **Smart Contracts (EVM)**: Encode/decode ABI data, call contract methods, and handle contract events.
- **RLP & ABI Codecs**: High-performance implementations of Recursive Length Prefix (RLP) and Application Binary Interface (ABI) encoding/decoding.
- **Unit Management**: Easy conversion between Wei, GWei, and Ether denominations using the `Wei` class.

## Requirements

- **PHP**: ^8.5 (64-bit)
- **Extensions**: `ext-openssl`, `ext-gmp`, `ext-bcmath`, `ext-curl`, `ext-json`
- **Dependencies**: 
    - `furqansiddiqui/blockchain-core-php`
    - `charcoal-dev/http-client`

## Installation

Install via Composer:

```bash
composer require furqansiddiqui/ethereum-php
```

## Quick Start

### 1. Keypairs and Addresses

```php
use FurqanSiddiqui\Ethereum\Ethereum;
use Charcoal\Buffers\Types\Bytes32;

/** @var Ethereum $eth */
$privateKey = new Bytes32("..."); // 32-byte private key
$publicKey = $eth->keypair->generatePublicKey($privateKey);
$address = $eth->keypair->addressFromPublicKey($publicKey, withChecksum: true);

echo $address->address; // 0x...
```

### 2. JSON-RPC Client

```php
use FurqanSiddiqui\Ethereum\Rpc\GethRpc;
use Charcoal\Http\Client\ClientConfig;

$rpc = new GethRpc("127.0.0.1", 8545, new ClientConfig());
$balance = $rpc->eth_getBalance("0x...", "latest");

echo $balance->eth(); // Balance in ETH
```

### 3. Smart Contract Interaction

```php
use FurqanSiddiqui\Ethereum\Evm\SmartContract;
use FurqanSiddiqui\Ethereum\Evm\DeployedContract;
use FurqanSiddiqui\Ethereum\Keypair\EthereumAddress;

// Load ABI from DTO (Array)
$abi = SmartContract::fromDto($abiArray);

// Initialize deployed contract
$contract = new DeployedContract(
    new EthereumAddress("0x..."),
    $abi,
    $rpc
);

// Call a constant method
$method = $contract->methodFromSignature("symbol()");
$result = $contract->call($method);

print_r($result);
```

### 4. Transactions

```php
// Create a Type 2 (EIP-1559) transaction
$tx = $eth->tx->type2();
$tx->nonce = 1;
$tx->to = new EthereumAddress("0x...");
$tx->value = \FurqanSiddiqui\Ethereum\Unit\Wei::fromETH("0.1");
$tx->gasLimit = 21000;
$tx->maxFeePerGas = \FurqanSiddiqui\Ethereum\Unit\Wei::fromGWei(50);
$tx->maxPriorityFeePerGas = \FurqanSiddiqui\Ethereum\Unit\Wei::fromGWei(2);

// Sign the transaction
$signedTx = $tx->withSignature($ecdsaSignature);

// Get raw hex for broadcasting
echo $signedTx->encode()->serialize();
```

## Unit Management

The `Wei` class provides a safe way to handle Ethereum's large numbers and unit conversions.

```php
use FurqanSiddiqui\Ethereum\Unit\Wei;

$value = Wei::fromETH("1.5");
echo $value->wei->getArray(); // Raw Wei (GMP)
echo $value->gWei();          // 1500000000
echo $value->eth();           // 1.5
```

## License

This package is open-source software licensed under the [MIT license](LICENSE).
