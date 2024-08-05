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

use Charcoal\Buffers\ByteOrder\BigEndian;
use FurqanSiddiqui\Ethereum\Buffers\EthereumAddress;
use FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethod;
use FurqanSiddiqui\Ethereum\Contracts\ABI\ContractMethodParam;
use FurqanSiddiqui\Ethereum\Exception\Contract_ABIException;
use FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException;
use FurqanSiddiqui\Ethereum\Packages\Keccak\Keccak;
use FurqanSiddiqui\Ethereum\RPC\Abstract_RPC_Client;

/**
 * Class Contract
 * @package FurqanSiddiqui\Ethereum\Contracts
 */
readonly class DeployedContract
{
    /**
     * @param \FurqanSiddiqui\Ethereum\Contracts\Contract $contract
     * @param \FurqanSiddiqui\Ethereum\Buffers\EthereumAddress $deployedAt
     * @param \FurqanSiddiqui\Ethereum\RPC\Abstract_RPC_Client $rpc
     */
    public function __construct(
        public Contract            $contract,
        public EthereumAddress     $deployedAt,
        public Abstract_RPC_Client $rpc
    )
    {
    }

    /**
     * @param string $name
     * @param array|null $args
     * @param bool $strictMode
     * @return string
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     */
    public function encodeCall(string $name, ?array $args = null, bool $strictMode = true): string
    {
        $method = $this->contract->functions()[strtolower($name)] ?? null;
        if (!$method instanceof ContractMethod) {
            throw new Contract_ABIException(sprintf('Call method "%s" is undefined in contract ABI', $name));
        }

        $givenArgs = $args;
        $givenArgsCount = is_array($givenArgs) ? count($givenArgs) : 0;
        $methodParams = $method->inputs();
        $methodParamsCount = count($methodParams);

        if ($strictMode) {
            if ($methodParamsCount || $givenArgsCount) {
                if ($methodParamsCount !== $givenArgsCount) {
                    throw new Contract_ABIException(
                        sprintf('Method "%s" requires %d args, given %d', $name, $methodParamsCount, $givenArgsCount)
                    );
                }
            }
        }

        $encoded = "";
        $methodParamsTypes = [];
        for ($i = 0; $i < $methodParamsCount; $i++) {
            /** @var ContractMethodParam $param */
            $param = $methodParams[$i];
            $arg = $givenArgs[$i];
            $encoded .= $this->encodeArg($param->type, $arg);
            $methodParamsTypes[] = $param->type;
        }

        $encodedMethodCall = Keccak::hash(sprintf("%s(%s)", $method->name, implode(",", $methodParamsTypes)), 256);
        return "0x" . substr($encodedMethodCall, 0, 8) . $encoded;
    }

    /**
     * @param string $type
     * @param $value
     * @return string
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     */
    protected function encodeArg(string $type, $value): string
    {
        // Changes types of uint8 or uint256 to simply uint
        $len = preg_replace('/[^0-9]/', '', $type);
        if (!$len) {
            $len = null;
        }

        $type = preg_replace('/[^a-z]/', '', $type);
        switch ($type) {
            case "hash":
            case "address":
                if (str_starts_with($value, "0x")) {
                    $value = substr($value, 2);
                }
                break;
            case "uint":
            case "int":
                $value = bin2hex(BigEndian::GMP_Pack($value));
                break;
            case "bool":
                $value = $value === true ? 1 : 0;
                break;
            case "string":
                $value = $this->asciiEncode($value);
                break;
            default:
                throw new Contract_ABIException(sprintf('Cannot encode value of type "%s"', $type));
        }

        return substr(str_pad(strval($value), 64, "0", STR_PAD_LEFT), 0, 64);
    }

    /**
     * @param string $name
     * @param string $encoded
     * @return array
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     */
    public function decodeResponse(string $name, string $encoded): array
    {
        $method = $this->contract->functions()[strtolower($name)] ?? null;
        if (!$method instanceof ContractMethod) {
            throw new Contract_ABIException(sprintf('Call method "%s" is undefined in ABI', $name));
        }

        // Remove suffix "0x"
        if (str_starts_with($encoded, '0x')) {
            $encoded = substr($encoded, 2);
        }

        // Output params
        $methodResponseParams = $method->outputs();
        $methodResponseParamsCount = count($methodResponseParams);

        // What to expect
        if ($methodResponseParamsCount <= 0) {
            return [];
        } elseif ($methodResponseParamsCount === 1) {
            // Put all in a single chunk
            $chunks = [$encoded];
        } else {
            // Split in chunks of 64 bytes
            $chunks = str_split($encoded, 64);
        }

        $result = []; // Prepare
        for ($i = 0; $i < $methodResponseParamsCount; $i++) {
            /** @var ContractMethodParam $param */
            $param = $methodResponseParams[$i];
            $chunk = $chunks[$i];
            $decoded = $this->decodeArg($param->type, $chunk);

            if ($param->name) {
                $result[$param->name] = $decoded;
            } else {
                $result[] = $decoded;
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @param string $encoded
     * @return string|bool
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     */
    protected function decodeArg(string $type, string $encoded): string|bool
    {
        $len = preg_replace('/[^0-9]/', '', $type);
        if (!$len) {
            $len = null;
        }
        $type = preg_replace('/[^a-z]/', '', $type);

        return match ($type) {
            "hash", "address" => "0x" . $encoded,
            "uint", "int" => gmp_strval(BigEndian::GMP_Unpack(hex2bin($encoded)), 10),
            "bool" => boolval($encoded),
            "string" => $this->asciiDecode($encoded),
            default => throw new Contract_ABIException(sprintf('Cannot encode value of type "%s"', $type)),
        };
    }

    /**
     * @param string $hex
     * @return string
     */
    protected function asciiDecode(string $hex): string
    {
        if (str_starts_with($hex, "0x")) {
            $hex = substr($hex, 2); // If any, removes the "0x" prefix
        }

        if (!preg_match('/^[a-f0-9]+$/i', $hex)) {
            throw new \InvalidArgumentException('Cannot decoded non-hexadecimal value to ASCII');
        }

        if (strlen($hex) % 2 !== 0) {
            $hex = "0" . $hex;
        }

        $str = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $str;
    }

    /**
     * @param string $ascii
     * @return string
     */
    protected function asciiEncode(string $ascii): string
    {
        if (!preg_match('/^[\x00-\x7F]*$/', $ascii)) {
            throw new \InvalidArgumentException('Cannot encode UTF-8 string into hexadecimals');
        }

        $hex = "";
        for ($i = 0; $i < strlen($ascii); $i++) {
            $hex .= str_pad(dechex(ord($ascii[$i])), 2, "0", STR_PAD_LEFT);
        }

        return $hex;
    }

    /**
     * @param string $str
     * @return string
     */
    protected function cleanOutputASCII(string $str): string
    {
        return preg_replace('/[^\w.-]/', '', trim($str));
    }

    /**
     * @param string $func
     * @param array|null $args
     * @param string $block
     * @return array
     * @throws \FurqanSiddiqui\Ethereum\Exception\Contract_ABIException
     * @throws \FurqanSiddiqui\Ethereum\Exception\InvalidAddressException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_CurlException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_RequestException
     * @throws \FurqanSiddiqui\Ethereum\Exception\RPC_ResponseException
     */
    public function call(string $func, ?array $args = null, string $block = "latest"): array
    {
        $encoded = $this->encodeCall($func, $args);
        $params = [
            "to" => $this->deployedAt->toString(false),
            "data" => $encoded
        ];

        $res = $this->rpc->apiCall("eth_call", [$params, $block]);
        if (!is_string($res)) {
            throw RPC_ResponseException::InvalidResultDataType("eth_call", "string", gettype($res));
        }

        if ($res === "0x") {
            return []; // Return empty Array
        }

        return $this->decodeResponse($func, $res);
    }
}
