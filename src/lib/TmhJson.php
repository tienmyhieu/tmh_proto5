<?php

namespace lib;

class TmhJson
{
    public function load($path, $file)
    {
        $contents = '[]';
        if ($this->exists($path . $file . '.json')) {
//            echo "<pre>" . 'reading ' . $path . $file . PHP_EOL . "</pre>";
            $contents = file_get_contents($path . $file . '.json');
        } else {
//            echo "<pre>" . 'not reading ' . $path . $file . PHP_EOL . "</pre>";
        }
        return json_decode($contents, true);
    }

    private function exists($url): bool
    {
        return (false !== @file_get_contents($url, 0, null, 0, 1));
    }
}
