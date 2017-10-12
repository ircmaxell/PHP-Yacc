<?php

namespace PhpYacc;

use PhpYacc\CodeGen\Template;
use PhpYacc\Yacc\Lexer;
use PhpYacc\Yacc\MacroSet;
use PhpYacc\Yacc\Parser;
use PhpYacc\Grammar\Context;
use PhpYacc\Lalr\Generator as Lalr;
use PhpYacc\Compress\Compress;
use PhpYacc\CodeGen\Language\PHP;

class Generator
{
    protected $parser;
    protected $lalr;
    protected $compressor;

    public function __construct(Parser $parser = null, Lalr $lalr = null, Compress $compressor = null)
    {
        $this->parser = $parser ?: new Parser(new Lexer(), new MacroSet);
        $this->lalr = $lalr ?: new Lalr;
        $this->compressor = $compressor ?: new Compress;
    }

    public function generate(string $grammar, string $grammarFileName, string $template, string $logfile = null): string
    {
        $context = new Context($grammarFileName, is_null($logfile) ? null : fopen($logfile, 'w'));

        $template = new Template(new PHP, $template, $context);

        $this->parser->parse($grammar, $context);

        $this->lalr->compute($context);

        $result = $this->compressor->compress($context);


        return $template->render($result);
    }
}
