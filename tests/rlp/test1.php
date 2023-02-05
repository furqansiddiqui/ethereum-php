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

require "../../vendor/autoload.php";

use FurqanSiddiqui\Ethereum\RLP\RLP as RLP;
use Comely\Buffer\Buffer as Buffer;

function utf8ize($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}

$writeReportFile = true;
$vector = json_decode(file_get_contents("rlptest.json"), true);
$report = [
    "total" => count($vector),
    "passed" => [
        "count" => 0,
        "test" => []
    ],
    "failed" => [
        "count" => 0,
        "test" => []
    ],
];

foreach ($vector as $testName => $test) {
    $part = array_merge([
        "name" => $testName,
        "encodeSuccess" => false,
        "decodeSuccess" => false,
        "result" => [
            "encoded" => null,
            "decoded" => null,
        ]
    ], ["expected" => $test]);

    $testIn = $test["in"];
    $testOut = $test["out"];

    if (is_string($testIn)) {
        if (preg_match('/^#[0-9]+$/', $testIn)) {
            $testIn = new \Comely\Buffer\BigInteger(substr($testIn, 1));
        }
    }

    $part["result"]["encoded"] = "0x" . RLP::Encode($testIn)->toBase16();
    if ($part["result"]["encoded"] === $testOut) {
        $part["encodeSuccess"] = true;
    }


    $part["result"]["decoded"] = RLP::Decode(Buffer::fromBase16($testOut));
    if ($testIn instanceof \Comely\Buffer\BigInteger) {
        $part["result"]["decoded"] = "#" . gmp_strval(\Comely\Buffer\BigInteger\BigEndian::GMP_Unpack($part["result"]["decoded"]), 10);
    }

    if ($testOut === "0x80") {
        if (in_array($part["result"]["decoded"], ["", null, 0])) {
            $part["decodeSuccess"] = true;
        }
    } elseif (is_int($part["result"]["decoded"]) && $part["result"]["decoded"] <= 127) {
        $matchHexByte = "0x" . bin2hex(chr($part["result"]["decoded"]));
    } elseif (is_string($part["result"]["decoded"]) && strlen($part["result"]["decoded"]) === 1) {
        $matchHexByte = "0x" . bin2hex($part["result"]["decoded"]);
    }

    if (isset($matchHexByte)) {
        if ($matchHexByte === $test["out"]) {
            $part["decodeSuccess"] = true;
        }
    }

    if (!$part["decodeSuccess"]) {
        if (is_string($part["result"]["decoded"]) && strlen($part["result"]["decoded"]) <= 8) { // Possibly an integer?
            $asIntByte = gmp_intval(\Comely\Buffer\BigInteger\BigEndian::GMP_Unpack($part["result"]["decoded"]));
            if ($asIntByte === $test["in"]) {
                $part["result"]["decoded"] = $asIntByte;
            }
        }
    }

    if (!$part["decodeSuccess"]) {
        if (json_encode($test["in"]) === json_encode($part["result"]["decoded"])) {
            $part["decodeSuccess"] = true;
        }
    }

    if ($part["encodeSuccess"] && $part["decodeSuccess"]) {
        $report["passed"]["count"]++;
        $report["passed"]["test"][] = $part;
    } else {
        $report["failed"]["count"]++;
        $report["failed"]["test"][] = $part;
    }
}

header("Content-type: application/json");
$json = json_encode(utf8ize($report), flags: JSON_THROW_ON_ERROR);
if ($writeReportFile) {
    file_put_contents("test1_result.json", $json);
}
exit($json);
