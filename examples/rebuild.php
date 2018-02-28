<?php

require __DIR__ . "/../vendor/autoload.php";

use PhpYacc\Generator;
use PhpYacc\Grammar\Context;

const DEBUG = true;
const VERBOSE_DEBUG = true;

$options = CliOptions::fromArgv($argv);
$generator = new Generator;

if (isset($options->args[0])) {
    buildFolder($options, $generator, realpath($options->args[0]));
} else {
    buildAll($options, $generator, __DIR__);
}


function buildAll(CliOptions $options, Generator $generator, string $dir)
{
    $it = new DirectoryIterator($dir);
    foreach ($it as $file) {
        if (!$file->isDir() || $file->isDot()) {
            continue;
        }
        $dir = $file->getPathname();
        buildFolder($options, $generator, $dir);
    }
}


function buildFolder(CliOptions $options, Generator $generator, string $dir) {
    chdir($dir);
    echo "Building $dir\n";

    $grammar = "grammar.y";
    $skeleton = "parser.template.php";

    if ($options->runKmyacc) {
        $output = trim(shell_exec("cd $dir && kmyacc -x -t -v -L php -m $skeleton -p Parser $grammar 2>&1"));
        rename("$dir/y.output", "$dir/y.kmyacc.output");
        rename("$dir/grammar.php", "$dir/parser.kmyacc.php");
    }

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

class CliOptions {
    public $runKmyacc = false;
    public $args = [];

    public function fromArgv(array $argv) {
        $options = new self;
        foreach (array_slice($argv, 1) as $arg) {
            if ($arg === '-k') {
                $options->runKmyacc = true;
            } else {
                $options->args[] = $arg;
            }
        }
        return $options;
    }
}
