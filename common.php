<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'markdown/Markdown.inc.php';
use \Michelf\Markdown;

$dataset    = 'default';
$datafolder = "../data";
$dataset_qs = '';


if (isset($_GET['dataset'])) {
    if (!preg_match('@[^a-z0-9-_ ]@i', $_GET['dataset'])) {
        if (is_dir("$datafolder/" . $_GET['dataset'])) {
            $dataset    = $_GET['dataset'];
            $dataset_qs = "?dataset=$dataset";
        }
    }
}

function get_html_docs($obj) {
    global $config, $data, $dataset, $errors, $datafolder;

    $name = str_replace('/', '_', $obj['name']);
    $filename = "$datafolder/$dataset/$name.mkdn";

    $name = str_replace('_', '\_', $obj['name']);
    $type = $obj['type'];
    if ($config['types'][$type]) {
        $type = $config['types'][$type]['long'];
    }

    $markdown = "## $name *$type*\n\n";

    if (file_exists($filename)) {
        $markdown .= "### Documentation\n\n";
        $markdown .= file_get_contents($filename);
    } else {
        $markdown .= 
        "<div class=\"alert alert-warning\">No documentation for this object
        $.ajax()
        <script> alert('hello'); </script>
        
        </div>";
    }

    if ($obj) {
        $markdown .= "\n\n";
        $markdown .= get_depends_markdown('Depends on ('.count($obj['depends']).')',     $obj['depends']);
        $markdown .= get_depends_markdown('Depended on by ('.count($obj['dependedOnBy']).')',  $obj['dependedOnBy']);
    }

    // Use {{object_id}} to link to an object
    $arr      = explode('{{', $markdown);
    $markdown = $arr[0];
    for ($i = 1; $i < count($arr); $i++) {
        $pieces    = explode('}}', $arr[$i], 2);
        $name      = $pieces[0];
        $id_string = get_id_string($name);
        $name_esc  = str_replace('_', '\_', $name);
        $class     = 'select-object';
        if (!isset($data[$name])) {
            $class .= ' missing';
            $errors[] = "Object '$obj[name]' links to unrecognized object '$name'";
        }
        $markdown .= "<a href=\"#$id_string\" class=\"$class\" data-name=\"$name\">$name_esc</a>";
        $markdown .= $pieces[1];
    }

    $html = Markdown::defaultTransform($markdown);
    // IE can't handle <pre><code> (it eats all the line breaks)
    $html = str_replace('<pre><code>'  , '<pre>' , $html);
    $html = str_replace('</code></pre>', '</pre>', $html);
    return $html;
}

function get_depends_markdown($header, $arr) {
    $markdown = "### $header";
    if (count($arr)) {
        $markdown .= "\n\n";
        foreach ($arr as $name) {
            $markdown .= "* {{" . $name . "}}\n";
        }
        $markdown .= "\n";
    } else {
        $markdown .= " *(none)*\n\n";
    }
    return $markdown;
}

function get_id_string($name) {
    return 'obj-' . preg_replace('@[^a-z0-9]+@i', '-', $name);
}

function read_config() {
    global $config, $dataset, $dataset_qs, $datafolder;
    //echo "$datafolder/$dataset/config.json";
    $config = json_decode(file_get_contents("$datafolder/$dataset/config.json" ), true);
    $config['jsonUrl'] = "json.php$dataset_qs";
}

function read_data() {
    global $config, $data, $dataset, $errors, $datafolder;
    
    

    if (!$config) read_config();
    
    $json = array();
    
    $skipDependencies = array('');
    
    if(file_exists("$datafolder/$dataset/objects.csv")){
        $csv = array_map('str_getcsv', file("$datafolder/$dataset/objects.csv"));

        foreach ($csv as $csvobj) {
            
            $depends = array();
            if(!in_array(trim($csvobj[1]), $skipDependencies)) array_push($depends, trim($csvobj[1]));
            if(!in_array(trim($csvobj[2]), $skipDependencies)) array_push($depends, trim($csvobj[2]));
            if(!in_array(trim($csvobj[3]), $skipDependencies)) array_push($depends, trim($csvobj[3]));
            
            $jsonobj = array(
                "type" => trim($csvobj[4]),
                "name" => trim($csvobj[0]),
                "depends" => $depends
            );
            array_push($json, $jsonobj);
        }
    } else {
        $json   = json_decode(file_get_contents("$datafolder/$dataset/objects.json"), true);
    }
    
    $data   = array();
    $errors = array();

    foreach ($json as $obj) {
        $data[$obj['name']] = $obj;
    }

    foreach ($data as &$obj) {
        $obj['dependedOnBy'] = array();
    }
    unset($obj);
    foreach ($data as &$obj) {
        foreach ($obj['depends'] as $name) {
            if ($data[$name]) {
                $data[$name]['dependedOnBy'][] = $obj['name'];
            } else {
                $data[$name] = array(
                    "type" => "default",
                    "name" => trim($name),
                    "depends" => array()
                );
                
            }
        }
    }
    unset($obj);
    foreach ($data as &$obj) {
        $obj['docs'] = get_html_docs($obj);
    }
    unset($obj);
}
?>
