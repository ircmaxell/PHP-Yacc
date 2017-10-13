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

    public function generate(Context $context, string $grammar, string $template, string $resultFile)
    {
        $template = new Template(new PHP, $template, $context);

        $this->parser->parse($grammar, $context);

        $this->lalr->compute($context);

        $result = $this->compressor->compress($context);

        $template->render($result, fopen($resultFile, 'w'));
    }
}
