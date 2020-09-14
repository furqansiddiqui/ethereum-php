<?php
require "../vendor/autoload.php";

//Reading JSON file
$data=readJsonFile("rlptest.json");

//Encoding With RLP
rlpEncode($data);

/**
 * @param string $fileName
 * @return mixed
 */
function readJsonFile(string $fileName)
{
    // Get the contents of the JSON file
    $strJsonFileContents = file_get_contents($fileName);
    //Convert To Array
    return json_decode($strJsonFileContents,true);
}

/**
 * @param array $data
 */
function rlpEncode(array $data)
{

    echo "<h1>Encoding</h1>";
    $rlp = new \FurqanSiddiqui\Ethereum\RLP();
    $rlp->convertASCII(true);

    echo "<table border='1' width='100%'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Test Name</th>";
    echo "<th>Input</th>";
    echo "<th>Expected</th>";
    echo "<th>Return</th>";
    echo "<th>Decoded</th>";
    echo "<th>Pass</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    //To Traverse The Array And Generate The output in table
    array_walk($data,"generateOutput",$rlp);

}

/**
 * @param $value
 * @param $key
 * @param $rlp
 */
function generateOutput($value, $key, $rlp)
{
        echo "<tr>";
            echo "<td>".$key."</td>";?>
            <td><?=var_dump($value["in"]); ?></td>
            <?php
            echo "<td style='word-break: break-all'>".$value["out"]."</td>";
            $encoded = $rlp->digest($value["in"])->toString();
            echo "<td style='word-break: break-all'>".$encoded."</td>";
            echo "<td style='word-break: break-all'>". json_encode(\FurqanSiddiqui\Ethereum\RLP::Decode($encoded)[0])."</td>";

            $result = "0x".$rlp->digest($value["in"])->toString()==$value["out"]?"true":"false";
            echo "<td>".  $result."</td>";
        echo "</tr>";

}
echo "</tbody>";
echo "</table>";