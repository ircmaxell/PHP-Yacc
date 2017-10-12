<?php

require __DIR__ . "/../vendor/autoload.php";

use PhpYacc\Grammar\Context;

const DEBUG = 1;

$generator = new PhpYacc\Generator;

if (isset($argv[1])) {
    buildFolder(realpath($argv[1]));
} else {
    buildAll(__DIR__);
}


function buildAll(string $dir)
{
    $it = new DirectoryIterator($dir);
    foreach ($it as $file) {
        if (!$file->isDir() || $file->isDot()) {
            continue;
        }
        $dir = $file->getPathname();
        buildFolder($dir);
    }
}


function buildFolder(string $dir) {
    global $generator;
    echo "Building $dir\n";

    $grammar = "$dir/grammar.y";
    $tmpGrammar = "$dir/parser.kmyacc.phpy";
    $skeleton = "$dir/parser.template.php";
    copy($grammar, $tmpGrammar);

    $output = trim(shell_exec("cd $dir && kmyacc -x -t -v -l -m $skeleton -p Parser $tmpGrammar 2>&1"));

    rename("$dir/y.output", "$dir/y.kmyacc.output");

    unlink($tmpGrammar);

    echo "Kmyacc output: \"$output\"\n";

    if (DEBUG) {
        $code = $generator->generate(file_get_contents($grammar), $grammar, file_get_contents($skeleton), "$dir/y.phpyacc.output");
    } else {
        $code = $generator->generate(file_get_contents($grammar), $grammar, file_get_contents($skeleton));
    }

    file_put_contents("$dir/parser.phpyacc.php", $code);

    shell_exec("cd $dir && diff -w parser.kmyacc.php parser.phpyacc.php > parser.diff");

    shell_exec("cd $dir && diff -w y.kmyacc.output y.phpyacc.output > y.diff");


}