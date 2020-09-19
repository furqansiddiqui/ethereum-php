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

use FurqanSiddiqui\Ethereum\Exception\JSONReqException;
use FurqanSiddiqui\Ethereum\Exception\RPCRequestError;

/**
 * Class JSONRPC2
 * @package FurqanSiddiqui\Ethereum\RPC
 */
abstract class JSON_RPC_2
{
    /** @var bool */
    protected bool $ignoreSSL = false;
    /** @var int|null */
    protected ?int $connectTimeout = null;
    /** @var int|null */
    protected ?int $timeOut = null;
    /** @var string|null */
    protected ?string $requestNoncePrefix = null;
    /** @var string|null */
    protected ?string $httpAuthUser = null;
    /** @var string|null */
    protected ?string $httpAuthPass = null;
    /** @var bool */
    protected bool $crossCheckReqId = true;
    /** @var bool */
    protected bool $debug = false;

    /**
     * @return string
     */
    abstract protected function getServerURL(): string;

    /**
     * @return $this
     */
    public function debug(): self
    {
        $this->debug = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function ignoreSSL(): self
    {
        $this->ignoreSSL = true;
        return $this;
    }

    /**
     * @param int $connectTimeout
     * @param int|null $timeOut
     * @return $this
     */
    public function setTimeout(int $connectTimeout, ?int $timeOut = null): self
    {
        $this->connectTimeout = $connectTimeout;
        $this->timeOut = $timeOut;
        return $this;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setNoncePrefix(string $prefix): self
    {
        $this->requestNoncePrefix = $prefix;
        return $this;
    }

    /**
     * @param string $user
     * @param string $pass
     * @return $this
     */
    public function httpAuthBasic(string $user, string $pass): self
    {
        $this->httpAuthUser = $user;
        $this->httpAuthPass = $pass;
        return $this;
    }

    /**
     * @param bool $trigger
     * @return $this
     */
    public function crossCheckReqIds(bool $trigger): self
    {
        $this->crossCheckReqId = $trigger;
        return $this;
    }

    /**
     * @return string
     */
    protected function generateUniqueId(): string
    {
        return sprintf('%s_%s_%d', $this->requestNoncePrefix ?? "", microtime(true), mt_rand(1, 9999));
    }

    /**
     * @param string $method
     * @param array|null $params
     * @return mixed|null
     * @throws JSONReqException
     * @throws RPCRequestError
     */
    public function call(string $method, array $params = null)
    {
        $ch = curl_init(); // Init cURL handler
        $serverURL = $this->getServerURL();
        curl_setopt($ch, CURLOPT_URL, $serverURL); // Set URL

        // SSL?
        if (strtolower(substr($serverURL, 0, 5)) === "https") {
            if ($this->ignoreSSL) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
        }

        // JSON RPC 2.0 spec id
        $reqUniqueId = $this->generateUniqueId();

        // Payload
        $payload = [
            "jsonrpc" => "2.0",
            "method" => $method,
            "id" => $reqUniqueId
        ];

        if ($params) {
            $payload["params"] = $params;
        }

        $payload = json_encode($payload);
        if (!$payload) {
            throw new JSONReqException('Failed to JSON encode the payload');
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Headers
        $headers[] = "Content-type: application/json; charset=utf-8";
        $headers[] = "Content-length: " . strlen($payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Authentication
        if ($this->httpAuthUser || $this->httpAuthPass) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $this->httpAuthUser ?? "", $this->httpAuthPass ?? ""));
        }

        // Timeouts
        if ($this->timeOut) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut);
        }

        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }

        // Finalise request
        $responseHeaders = [];
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function (
            /** @noinspection PhpUnusedParameterInspection */
            $ch, $header) use (&$responseHeaders) {
            $responseHeaders[] = $header;
            return strlen($header);
        });

        // Execute cURL request
        $response = curl_exec($ch);
        if ($response === false) {
            throw new JSONReqException(
                sprintf('cURL error [%d]: %s', curl_error($ch), curl_error($ch))
            );
        }

        // Close cURL resource
        curl_close($ch);

        // Prepare response
        try {
            $body = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            if ($this->debug) {
                trigger_error(sprintf('[%s][%s] %s', get_class($e), $e->getCode(), $e->getMessage()), E_USER_WARNING);
            }

            throw new JSONReqException('Failed to decode JSON response');
        }

        // Error Msg/Code
        $error = $body["error"] ?? null;
        if ($error) {
            $errorCode = $error["code"];
            $errorMsg = $error["message"];
            throw new RPCRequestError($errorMsg, (int)$errorCode);
        }

        // Request IDs
        if ($this->crossCheckReqId) {
            $resId = $body["id"];
            if ($resId !== $reqUniqueId) {
                if ($this->debug) {
                    trigger_error(
                        sprintf('Expected JSON RPC 2.0 spec ID "%s" got "%s"', $reqUniqueId, $resId),
                        E_USER_WARNING
                    );
                }

                throw new JSONReqException('JSON RPC 2.0 spec ID does not match');
            }
        }

        return $body["result"] ?? null;
    }
}
