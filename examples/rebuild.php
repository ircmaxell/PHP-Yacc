<?php

require __DIR__ . "/../vendor/autoload.php";

use PhpYacc\Grammar\Context;

const DEBUG = 1;

$it = new DirectoryIterator(__DIR__);

$generator = new PhpYacc\Generator;


foreach ($it as $file) {
    if (!$file->isDir() || $file->isDot()) {
        continue;
    }
    $dir = $file->getPathname();
    echo "Building $dir\n";

    $grammar = "$dir/grammar.y";
    $tmpGrammar = "$dir/parser.kmyacc.phpy";
    $skeleton = "$dir/parser.template.php";
    copy($grammar, $tmpGrammar);

    $output = trim(shell_exec("cd $dir && kmyacc -x -t -v -l -m $skeleton -p Parser $tmpGrammar 2>&1"));

    rename("$dir/y.output", "$dir/y.kmyacc.output");

    unlink($tmpGrammar);

    echo "Kmyacc output: \"$output\"\n";


    $code = $generator->generate(file_get_contents($grammar), $grammar, file_get_contents($skeleton), "$dir/y.phpyacc.output");

    file_put_contents("$dir/parser.phpyacc.php", $code);




}