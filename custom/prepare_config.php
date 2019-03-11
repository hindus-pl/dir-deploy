<?php

class prepare_config implements CustomFunction {

    public function processPath(&$lines, $path, $input, $output)
    {
        // we'll just copy this as is
        copy($input.$path, $output.$path);
    }
}