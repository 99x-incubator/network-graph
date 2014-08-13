<?php

    $dataset = "default";

    $csv = array_map('str_getcsv', file("data/$dataset/objects.csv"));
    print_r($csv);

    echo "-----------------------------------------------------------------------------------------------------------------------------";

    $json = json_decode(file_get_contents("data/$dataset/objects.json"), true);
    print_r($json);

    $json_new = array();

    foreach ($csv as $csvobj) {
        $jsonobj = array(
            "type" => "default",
            "name" => $csvobj[0],
            "depends" => array($csvobj[1], $csvobj[2], $csvobj[3])
        );
        array_push($json_new, $jsonobj);
    }

    echo "-----------------------------------------------------------------------------------------------------------------------------";
    print_r($json_new);

?>