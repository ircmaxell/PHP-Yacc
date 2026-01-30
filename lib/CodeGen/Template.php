<?php

declare(strict_types=1);

namespace PhpYacc\CodeGen;

use PhpYacc\Exception\LogicException;
use PhpYacc\Exception\TemplateException;
use PhpYacc\Grammar\Context;
use PhpYacc\Compress\Compress;
use PhpYacc\Compress\CompressResult;
use PhpYacc\Yacc\Macro\DollarExpansion;

use function PhpYacc\is_white;

class Template
{
    protected $metachar = '$';
    protected $template = [];
    protected $lineno = 0;
    protected $copy_header = false;

    /**
     * @var Context
     */
    protected $context;
    /**
     * @var CompressResult
     */
    protected $compress;
    /**
     * @var Language
     */
    protected $language;

    public function __construct(Language $language, string $template, Context $context)
    {
        $this->language = $language;
        $this->context = $context;
        $this->parseTemplate($template);
    }

    public function render(CompressResult $result, $resultFile, $headerFile = null)
    {
        $headerFile = $headerFile ?: fopen('php://memory', 'rw');

        $this->language->begin($resultFile, $headerFile);

        $this->compress = $result;
        $result = '';
        $skipmode = false;
        $linechanged = false;
        $tailcode = false;
        $reducemode = [
            "enabled" => false,
            "m" => -1,
            "n" => 0,
            "mac" => [],
        ];
        $tokenmode = [
            "enabled" => false,
            "mac" => [],
        ];
        $buffer = '';
        $this->print_line();
        foreach ($this->template as $line) {
            $line .= "\n";
            if ($tailcode) {
                $this->language->write($buffer . $line);
                continue;
            }
            if ($skipmode) {
                if ($this->metamatch(ltrim($line), 'endif')) {
                    $skipmode = false;
                }
                continue;
            }
            if ($reducemode['enabled']) {
                if ($this->metamatch(trim($line), 'endreduce')) {
                    $reducemode['enabled'] = false;
                    $this->lineno++;
                    if ($reducemode['m'] < 0) {
                        $reducemode['m'] = $reducemode['n'];
                    }
                    foreach ($this->context->grams as $gram) {
                        if ($gram->action) {
                            for ($j = 0; $j < $reducemode['m']; $j++) {
                                $this->expand_mac($reducemode['mac'][$j], $gram->num, null);
                            }
                        } else {
                            for ($j = $reducemode['m']; $j < $reducemode['n']; $j++) {
                                $this->expand_mac($reducemode['mac'][$j], $gram->num, null);
                            }
                        }
                    }
                    continue;
                } elseif ($this->metamatch(trim($line), 'noact')) {
                    $reducemode['m'] = $reducemode['n'];
                    continue;
                }
                $reducemode['mac'][$reducemode['n']++] = $line;
                continue;
            }
            if ($tokenmode['enabled']) {
                if ($this->metamatch(trim($line), 'endtokenval')) {
                    $tokenmode['enabled'] = false;
                    $this->lineno++;
                    for ($i = 1; $i < $this->context->nterminals; $i++) {
                        $symbol = $this->context->symbol($i);
                        if ($symbol->name[0] != '\'') {
                            $str = $symbol->name;
                            if ($i === 1) {
                                $str = "YYERRTOK";
                            }
                            foreach ($tokenmode['mac'] as $mac) {
                                $this->expand_mac($mac, $symbol->value, $str);
                            }
                        }
                    }
                } else {
                    $tokenmode['mac'][] = $line;
                }
                continue;
            }
            $p = $line;
            $buffer = '';
            for ($i = 0; $i < strlen($line); $i++) {
                $p = substr($line, $i);
                if ($p[0] !== $this->metachar) {
                    $buffer .= $line[$i];
                } elseif ($i + 1 < strlen($line) && $p[1] === $this->metachar) {
                    $i++;
                    $buffer .= $this->metachar;
                } elseif ($this->metamatch($p, '(')) {
                    $start = $i + 2;
                    $val = substr($p, 2);
                    while ($i < strlen($line) && $line[$i] !== ')') {
                        $i++;
                    }
                    if (!isset($line[$i])) {
                        throw new TemplateException('$(: missing ")"');
                    }
                    $length = $i - $start;

                    $buffer .= $this->gen_valueof(substr($val, 0, $length));
                } elseif ($this->metamatch($p, 'TYPEOF(')) {
                    throw new LogicException("TYPEOF is not implemented");
                } else {
                    break;
                }
            }
            if (isset($p[0]) && $p[0] === $this->metachar) {
                if (trim($buffer) !== '') {
                    throw new TemplateException("Non-blank character before \$-keyword");
                }
                if ($this->metamatch($p, 'header')) {
                    $this->copy_header = true;
                } elseif ($this->metamatch($p, 'endheader')) {
                    $this->copy_header = false;
                } elseif ($this->metamatch($p, 'tailcode')) {
                    $this->print_line();
                    $tailcode = true;
                    continue;
                } elseif ($this->metamatch($p, 'verification-table')) {
                    throw new TemplateException("verification-table is not implemented");
                } elseif ($this->metamatch($p, 'union')) {
                    throw new TemplateException("union is not implemented");
                } elseif ($this->metamatch($p, 'tokenval')) {
                    $tokenmode = [
                        "enabled" => true,
                        "mac" => [],
                    ];
                } elseif ($this->metamatch($p, 'reduce')) {
                    $reducemode = [
                        "enabled" => true,
                        "m" => -1,
                        "n" => 0,
                        "mac" => [],
                    ];
                } elseif ($this->metamatch($p, 'switch-for-token-name')) {
                    for ($i = 0; $i < $this->context->nterminals; $i++) {
                        if ($this->context->ctermindex[$i] >= 0) {
                            $symbol = $this->context->symbol($i);
                            $this->language->case_block($buffer, $symbol->value, $symbol->name);
                        }
                    }
                } elseif ($this->metamatch($p, 'production-strings')) {
                    foreach ($this->context->grams as $gram) {
                        $info = array_slice($gram->body, 0);
                        $this->language->write($buffer . "\"");
                        $this->language->writeQuoted($info[0]->name);
                        $this->language->writeQuoted(' :');
                        if (count($info) === 1) {
                            $this->language->writeQuoted(" /* empty */");
                        }
                        for ($i = 1; $i < count($info); $i++) {
                            $this->language->writeQuoted(' ' . $info[$i]->name);
                        }
                        if ($gram->num + 1 === $this->context->ngrams) {
                            $this->language->write("\"\n");
                        } else {
                            $this->language->write("\",\n");
                        }
                    }
                } elseif ($this->metamatch($p, 'listvar')) {
                    $var = trim(substr($p, 9));
                    $this->gen_list_var($buffer, $var);
                } elseif ($this->metamatch($p, 'ifnot')) {
                    $skipmode = $skipmode || $this->evalCond($p);
                } elseif ($this->metamatch($p, 'if')) {
                    $skipmode = $skipmode || !$this->evalCond($p);
                } elseif ($this->metamatch($p, 'endif')) {
                    $skipmode = false;
                } else {
                    throw new TemplateException("Unknown \$: $line");
                }
                $linechanged = true;
            } else {
                if ($linechanged) {
                    $this->print_line();
                    $linechanged = false;
                }
                $this->language->write($buffer, $this->copy_header);
            }
        }

        $this->language->commit();
    }

    protected function evalCond($spec): bool
    {
        [$dump, $test] = explode(' ', $spec, 2);
        $test = trim($test);
        switch ($test) {
            case '-a':
                return $this->context->aflag;
            case '-t':
                return $this->context->tflag;
            case '-p':
                return !!$this->context->pspref;
            case '%union':
                return !!$this->context->union_body;
            case '%pure_parser':
                return $this->context->pureFlag;
            default:
                throw new TemplateException("$dump: unknown switch: $test");
        }
    }

    protected function expand_mac(string $def, int $value, ?string $str = null)
    {
        $result = '';
        for ($i = 0; $i < strlen($def); $i++) {
            $p = $def[$i];
            if ($p === '%') {
                $p = $def[++$i];
                switch ($p) {
                    case 'n':
                        $result .= sprintf('%d', $value);
                        break;
                    case 's':
                        $result .= $str !== null ? $str : '';
                        break;
                    case 'b':
                        $gram = $this->context->gram($value);
                        $this->print_line($gram->position);
                        $result .= $gram->action;
                        break;
                    default:
                        $result .= $p;
                        break;
                }
            } else {
                $result .= $p;
            }
        }
        $this->language->write($result, $this->copy_header);
    }

    protected function gen_list_var(string $indent, string $var)
    {
        $array = [];
        $size = -1;
        if (isset($this->compress->$var)) {
            $array = $this->compress->$var;
            if (isset($this->compress->{$var . 'size'})) {
                $size = $this->compress->{$var . 'size'};
            } elseif ($var === "yydefault") {
                $size = $this->context->nnonleafstates;
            } elseif (in_array($var, ['yygbase', 'yygdefault'])) {
                $size = $this->context->nnonterminals;
            } elseif (in_array($var, ['yylhs', 'yylen'])) {
                $size = $this->context->ngrams;
            }
            $this->print_array($array, $size < 0 ? count($array) : $size, $indent);
        } elseif ($var === 'terminals') {
            $nl = 0;
            $terminals = iterator_to_array($this->context->terminals);
            usort($terminals, function ($t1, $t2) {
                return $this->context->ctermindex[$t1->code] <=> $this->context->ctermindex[$t2->code];
            });
            foreach ($terminals as $term) {
                $prefix = $nl++ ? ",\n" : "";
                $this->language->write($prefix . $indent . "\"");
                $this->language->writeQuoted($term->name);
                $this->language->write("\"");
            }
            $this->language->write("\n");
        } elseif ($var === 'nonterminals') {
            $nl = 0;
            foreach ($this->context->nonterminals as $nonterm) {
                $prefix = $nl++ ? ",\n" : "";
                $this->language->write($prefix . $indent . "\"");
                $this->language->writeQuoted($nonterm->name);
                $this->language->write("\"");
            }
            $this->language->write("\n");
        } else {
            throw new TemplateException("\$listvar: unknown variable $var");
        }
    }

    protected function print_array(array $array, int $limit, string $indent)
    {
        $col = 0;
        for ($i = 0; $i < $limit; $i++) {
            if ($col === 0) {
                $this->language->write($indent);
            }
            $this->language->write(sprintf($i + 1 === $limit ? "%5d" : "%5d,", $array[$i]));
            if (++$col === 10) {
                $this->language->write("\n");
                $col = 0;
            }
        }
        if ($col !== 0) {
            $this->language->write("\n");
        }
    }

    protected function gen_valueof(string $var): string
    {
        switch ($var) {
            case 'YYSTATES':
                return sprintf('%d', $this->context->nstates);
            case 'YYNLSTATES':
                return sprintf('%d', $this->context->nnonleafstates);
            case 'YYINTERRTOK':
                return sprintf('%d', $this->compress->yytranslate[$this->context->errorToken->value]);
            case 'YYUNEXPECTED':
                return sprintf('%d', Compress::YYUNEXPECTED);
            case 'YYDEFAULT':
                return sprintf('%d', Compress::YYDEFAULT);
            case 'YYMAXLEX':
                return sprintf('%d', count($this->compress->yytranslate));
            case 'YYLAST':
                return sprintf('%d', count($this->compress->yyaction));
            case 'YYGLAST':
                return sprintf('%d', count($this->compress->yygoto));
            case 'YYTERMS':
            case 'YYBADCH':
                return sprintf('%d', $this->compress->yyncterms);
            case 'YYNONTERMS':
                return sprintf('%d', $this->context->nnonterminals);
            case 'YY2TBLSTATE':
                return sprintf("%d", $this->compress->yybasesize - $this->context->nnonleafstates);
            case 'CLASSNAME':
            case '-p':
                return $this->context->pspref ?: 'yy';
            default:
                throw new TemplateException("Unknown variable: \$($var)");
        }
    }

    protected function parseTemplate(string $template)
    {
        $template = preg_replace("(\r\n|\r)", "\n", $template);
        $lines = explode("\n", $template);
        $this->lineno = 1;
        $skip = false;
        foreach ($lines as $line) {
            $p = $line;
            if ($skip) {
                $this->template[] = $line;
                continue;
            }
            while (strlen($p) > 0 && is_white($p[0])) {
                $p = substr($p, 1);
            }
            $this->lineno++;
            if ($this->metamatch($p, "include")) {
                $skip = true;
            } elseif ($this->metamatch($p, "meta")) {
                if (!isset($p[6]) || is_white(($p[6]))) {
                    throw new TemplateException("\$meta: missing character in definition: $p");
                }
                $this->metachar = $p[6];
            } elseif ($this->metamatch($p, "semval")) {
                $this->def_semval_macro(substr($p, 7));
            } else {
                $this->template[] = $line;
            }
        }
    }

    protected function metamatch(string $text, string $keyword): bool
    {
        return isset($text[0]) && $text[0] === $this->metachar && substr($text, 1, strlen($keyword)) === $keyword;
    }

    protected function def_semval_macro(string $macro)
    {
        if (strpos($macro, '($)') !== false) {
            $this->context->macros[DollarExpansion::SEMVAL_LHS_UNTYPED] = ltrim(substr($macro, 3));
        } elseif (strpos($macro, '($,%t)') !== false) {
            $this->context->macros[DollarExpansion::SEMVAL_LHS_TYPED] = ltrim(substr($macro, 6));
        } elseif (strpos($macro, '(%n)') !== false) {
            $this->context->macros[DollarExpansion::SEMVAL_RHS_UNTYPED] = ltrim(substr($macro, 4));
        } elseif (strpos($macro, '(%n,%t)') !== false) {
            $this->context->macros[DollarExpansion::SEMVAL_RHS_TYPED] = ltrim(substr($macro, 7));
        } else {
            throw new TemplateException("\$semval: bad format $macro");
        }
    }

    protected function print_line(int $line = -1, ?string $filename = null)
    {
        if ($line === -1) {
            $line = $this->lineno;
        }
        if ($filename === null) {
            $filename = $this->context->filename;
        }
        //$this->language->inline_comment("{$filename}:$line");
    }
}
