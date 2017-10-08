<?php

require __DIR__ . "/../vendor/autoload.php";

const DEBUG = 1;

$it = new DirectoryIterator(__DIR__);

$lexer = new PhpYacc\Yacc\Lexer();
$macroset = new PhpYacc\Yacc\MacroSet;

$parser = new PhpYacc\Yacc\Parser($lexer, $macroset);


$generator = new PhpYacc\Lalr\Generator;


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

    $output = trim(shell_exec("cd $dir && kmyacc -t -v -l -m $skeleton -p Parser $tmpGrammar 2>&1"));

    rename("$dir/y.output", "$dir/y.kmyacc.output");

    unlink($tmpGrammar);

    echo "Kmyacc output: \"$output\"\n";



    $parseResult = $parser->parse(file_get_contents($grammar), $grammar);

    $lalrResult = $generator->compute($parseResult, $grammar);

    file_put_contents("$dir/y.phpyacc.output", $lalrResult->output);







}