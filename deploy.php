<?php

interface CustomFunction {
    public function processPath(&$lines, $path, $input, $output);
}

class Repository {

    private $lines;
    private $path;

    function __construct($inputPath)
    {
        $this->lines = array();
        if (substr($inputPath, -1) != "/"){
            $inputPath .= "/";
        }
        $this->path = $inputPath;
    }

    public function processLine($line){
        $lineParts = explode(" ", $line);
        if (count($lineParts) < 2){
            return false;
        }
        $val = trim($lineParts[1]);
        switch ($lineParts[0]){
            case "*":
                $this->addRecursively($val);
                break;
            case "D":
                $this->addSingleDir($val);
                break;
            case "+":
                $this->addSingleEntry($val);
                break;
            case "-":
                $this->removeSingleEntry($val);
                break;
            default:
                $this->customFunction($lineParts[0], $val);
        }
    }

    public function getResult(){
        return $this->lines;
    }

    private function addRecursively($line)
    {
        $line = $this->path.$line;
        if (file_exists($line)) {
            $files = $this->getDirContents($line);
            foreach ($files as $file){
                $this->lines[] = $file;
            }
        } else {
            echo "Cannot add $line recursively, it's not accessible or doesn't exist\r\n";
        }
    }

//    private function customFunction($function, $entry)
//    {
//        try {
//            $customFilename = "custom/".$function.".php";
//            if (file_exists($customFilename)) {
//                require_once $customFilename;
//                $fun = new $function;
//                $fun->processPath($this->lines, $entry, $this->path);
//            }
//        } catch (Exception $ex){
//            echo "Exception when processing $entry with $function custom function - $ex";
//            return false;
//        }
//
//        echo "Cannot process $entry with $function custom function";
//        // false when no function was matched
//        return false;
//    }

    private function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = $dir.DIRECTORY_SEPARATOR.$value;
        $path = str_replace("\\", "/", $path);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            $this->getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;

    }

    private function removeSingleEntry($line)
    {
        $removal = glob($this->path.$line);
        $this->lines = array_diff($this->lines, $removal);
    }

    private function addSingleEntry($line)
    {
        $add = glob($this->path.$line);
        $this->lines = array_unique(array_merge($this->lines, $add));
    }

    private function addSingleDir($dir)
    {
        $this->lines[] = $this->path.$dir;
    }

    private function customFunction($int, string $val)
    {
        echo "custom functions are not supported yet!\r\n";
    }
}

class DirDeploy{
    private $input;
    private $output;
    private $deploymentList;

    function __construct($args)
    {
        for ($i=0; $i<count($args); $i++){
            $a = $args[$i];
            if ($a == "--input"){
                $this->input = $args[++$i];
                continue;
            }

            if ($a == "--output"){
                $this->output = $args[++$i];
                if (substr($this->output, -1) != "/"){
                    $this->output .= "/";
                }
                continue;
            }

            $this->deploymentList = $a;
        }
    }

    public function run()
    {
        $r = new Repository($this->input);
        $lines = file($this->deploymentList);
        foreach ($lines as $line){
            $r->processLine($line);
        }

        $this->outputFiles($r->getResult());
    }

    private function outputFiles($result)
    {
        $copiedFiles = 0;
        $copiedDirs = 0;
        foreach ($result as $entry){
            try {
            $path = $entry;
            if (file_exists($path)){
                if (is_dir($path)){
                    $dirpath = $this->output.str_replace($this->input, "", $entry);
                    if (!file_exists($dirpath))
                    mkdir($dirpath, 0755, true);
                    $copiedDirs++;
                } else {
                    $dir = dirname(str_replace($this->input, "", $entry));
                    if (!file_exists($this->output.$dir)) {
                        mkdir($this->output . $dir, 0755, true);
                        $copiedDirs++;
                    }
                    copy($path, $this->output.str_replace($this->input, "", $entry));
                    $copiedFiles++;
                }
            } else {
                echo "WARNING: $path doesn't exist, cannot copy to output!\r\n";
            }
        } catch (Exception $ex) {
            echo "ERROR: $ex";
            }
        }
        echo "Done, copied $copiedDirs dirs and $copiedFiles files into $this->output \r\n";
    }
}
// mock for running on server
//$argv = array("--input", "../", "--output", "../dd_".date("Y-m-d_h-m-s"), "deployment_list.txt");
//header("Content-Type: text/plain");
// endmock
if (isset($argv)) {
    $dd = new DirDeploy($argv);
    $dd->run();
} else {
    echo "This script supports CLI only!\r\n";
}