<?php

require __DIR__ . "/../vendor/autoload.php";

use PhpYacc\Generator;
use PhpYacc\Grammar\Context;

const DEBUG = true;
const VERBOSE_DEBUG = true;
const RUN_KMYACC = false;

$generator = new Generator;

if (isset($argv[1])) {
    buildFolder($generator, realpath($argv[1]));
} else {
    buildAll($generator, __DIR__);
}


function buildAll(Generator $generator, string $dir)
{
    $it = new DirectoryIterator($dir);
    foreach ($it as $file) {
        if (!$file->isDir() || $file->isDot()) {
            continue;
        }
        $dir = $file->getPathname();
        buildFolder($generator, $dir);
    }
}


function buildFolder(Generator $generator, string $dir) {
    chdir($dir);
    echo "Building $dir\n";

    $grammar = "grammar.y";
    $tmpGrammar = "parser.kmyacc.phpy";
    $skeleton = "parser.template.php";
    copy($grammar, $tmpGrammar);

    if (RUN_KMYACC) {
        $output = trim(shell_exec("cd $dir && kmyacc -x -t -v -l -m $skeleton -p Parser $tmpGrammar 2>&1"));
        rename("$dir/y.output", "$dir/y.kmyacc.output");
    }

    unlink($tmpGrammar);

    $debugFile = DEBUG ? fopen("$dir/y.phpyacc.output", 'w') : null;
    $generator->generate(
        new Context($grammar, $debugFile, VERBOSE_DEBUG),
        file_get_contents($grammar),
        file_get_contents($skeleton),
        "$dir/parser.phpyacc.php"
    );

    shell_exec("cd $dir && diff -w parser.kmyacc.php parser.phpyacc.php > parser.diff");

    shell_exec("cd $dir && diff -w y.kmyacc.output y.phpyacc.output > y.diff");

}
