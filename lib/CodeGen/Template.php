<?php
declare(strict_types=1);

namespace PhpYacc\CodeGen;

use PhpYacc\Grammar\Context;
use PhpYacc\Compress\Compress;
use PhpYacc\Compress\CompressResult;
use PhpYacc\Yacc\Macro\DollarExpansion;
use RuntimeException;
use PhpYacc\Yacc\Production;

class Template
{
    protected $metachar = '$';
    protected $template = [];
    protected $lineno = 0;
    protected $copy_header = false;

    protected $fp;
    protected $hp;
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

    public function getHeader(): string
    {
        //todo
    }

    public function render(CompressResult $result)
    {
        $this->fp = fopen('php://memory', 'rw');
        $this->hp = fopen('php://memory', 'rw');

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
        $buffer = '';
        $this->print_line($this->lineno, $this->context->filename);
        foreach ($this->template as $line) {
            $line .= "\n";
            if ($tailcode) {
                echo $buffer . $line;
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
                                $this->expand_mac($reducemode['mac'][$j], $gram, null);
                            }
                        } else {
                            for ($j = $reducemode['m']; $j < $reducemode['n']; $j++) {
                                $this->expand_mac($reducemode['mac'][$j], $gram, null);
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
                        throw new \LogicException('$(: missing ")"');
                    }
                    $length = $i - $start;

                    $buffer .= $this->gen_valueof(substr($val, 0, $length));
                } elseif ($this->metamatch($p, 'TYPEOF(')) {
                    throw new \LogicException("TYPEOF is not implemented");
                } else {
                    break;
                }
            }
            if (isset($p[0]) && $p[0] === $this->metachar) {
                if (trim($buffer) !== '') {
                    throw new RuntimeException("Non-blank character before \$-keyword");
                }
                if ($this->metamatch($p, 'header')) {
                    // Skip
                } elseif ($this->metamatch($p, 'endheader')) {
                    $this->copy_header = false;
                } elseif ($this->metamatch($p, 'tailcode')) {
                    $this->print_line();
                    $tailcode = true;
                    continue;
                } elseif ($this->metamatch($p, 'verification-table')) {
                    throw new \LogicException("verification-table is not implemented");
                } elseif ($this->metamatch($p, 'union')) {
                    throw new \LogicException("union is not implemented");
                } elseif ($this->metamatch($p, 'tokenval')) {
                    $this->gen_tokenval();
                } elseif ($this->metamatch($p, 'reduce')) {
                    $reducemode = [
                        "enabled" => true,
                        "m" => -1,
                        "n" => 0,
                        "mac" => [],
                    ];
                } elseif ($this->metamatch($p, 'switch-for-token-name')) {
                    for ($i = 0; $i < $this->context->nterminals; $i++) {
                        if ($this->context->ctermindex[$i] > 0) {
                            $symbol = $this->context->symbol($i);
                            fprintf($this->fp, '%s%s', $buffer, $this->lanugage->case_block($symbol->value, $symbol->name));
                        }
                    }
                } elseif ($this->metamatch($p, 'production-strings')) {
                    foreach ($this->context->grams as $gram) {
                        $info = array_slice($gram->body, 0);
                        fprintf($this->fp, "%s\"%s :", $buffer, addcslashes($info[0]->name, '$"'));
                        if (count($info) === 1) {
                            fprintf($this->fp, " /* empty */");
                        }
                        for ($i = 1; $i < count($info); $i++) {
                            fprintf($this->fp, " %s", $info[$i]->name);
                        }
                        if ($gram->num + 1 === $this->context->ngrams) {
                            fwrite($this->fp, "\"\n");
                        } else {
                            fwrite($this->fp, "\",\n");
                        }
                    }
                } elseif ($this->metamatch($p, 'listvar')) {
                    $var = trim(substr($p, 9));
                    $this->gen_list_var($buffer, $var);
                } elseif ($this->metamatch($p, 'ifnot')) {
                    $skipmode = $skipmode || !$this->skipif($p);
                } elseif ($this->metamatch($p, 'if')) {
                    $skipmode = $skipmode || $this->skipif($p);
                } elseif ($this->metamatch($p, 'endif')) {
                    $skipmode = false;
                } else {
                    throw new RuntimeException("Unknown \$: $line");
                }
                $linechanged = true;
            } else {
                if ($linechanged) {
                    $this->print_line();
                    $linechanged = false;
                }
                fwrite($this->fp, $buffer);
                if ($this->copy_header) {
                    fwrite($this->hp, $buffer);
                }
            }
        }

        fseek($this->fp, 0);
        return stream_get_contents($this->fp);
    }

    protected function skipif($spec): bool
    {
        list($dump, $test) = explode(' ', $spec, 2);
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
                throw new RuntimeException("$dump: unknown switch: $test");
        }
    }

    protected function expand_mac(string $def, Production $gram, string $str = null)
    {
        $result = '';
        for ($i = 0; $i < strlen($def); $i++) {
            $p = $def[$i];
            if ($p === '%') {
                $p = $def[++$i];
                switch ($p) {
                    case 'n':
                        $result .= sprintf('%d', $gram->num);
                        break;
                    case 's':
                        $result .= $str !== null ? $str : '';
                        break;
                    case 'b':
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
        fwrite($this->fp, $result);
        if ($this->copy_header) {
            fwrite($this->hp, $result);
        }
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
            foreach ($this->context->terminals as $term) {
                if ($this->context->ctermindex[$term->code] >= 0) {
                    if ($nl++) {
                        fprintf($this->fp, ",\n");
                    }
                    fprintf($this->fp, "%s\"%s\"", $indent, addcslashes($term->name, '$"'));
                }
            }
            fprintf($this->fp, "\n");
        } elseif ($var === 'nonterminals') {
            $nl = 0;
            foreach ($this->context->nonterminals as $nonterm) {
                if ($nl++) {
                    fprintf($this->fp, ",\n");
                }
                fprintf($this->fp, "%s\"%s\"", $indent, addcslashes($nonterm->name, '$"'));
            }
            fprintf($this->fp, "\n");
        } else {
            throw new RuntimeException("\$listvar: unknown variable $var");
        }
    }

    protected function print_array(array $array, int $limit, string $indent)
    {
        $col = 0;
        for ($i = 0; $i < $limit; $i++) {
            if ($col === 0) {
                fwrite($this->fp, $indent);
            }
            fprintf($this->fp, $i + 1 === $limit ? "%5d" : "%5d,", $array[$i]);
            if (++$col === 10) {
                fwrite($this->fp, "\n");
                $col = 0;
            }
        }
        if ($col !== 0) {
            fwrite($this->fp, "\n");
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

            default:
                throw new \LogicException("Unknown variable: \$($var)");
        }
    }

    protected function printArray(array $array, int $limit = -1): string
    {
        $return = "[";
        if ($limit !== -1) {
            $array = array_slice($array, 0, $limit - 1);
        }
        foreach ($array as $key => $value) {
            if ($key % 10 === 0) {
                $return .= "\n        ";
            }
            $return .= sprintf("%5d,", $value);
        }
        return $return . "\n    ]";
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
            while (strlen($p) > 0 && isWhite($p[0])) {
                $p = substr($p, 1);
            }
            $this->lineno++;
            if ($this->metamatch($p, "include")) {
                $skip = true;
            } elseif ($this->metamatch($p, "meta")) {
                if (!isset($p[6]) || isWhite(($p[6]))) {
                    var_dump($p);
                    throw new \LogicException("\$meta: missing character in definition: $p");
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
            throw new \RuntimeException("\$semval: bad format $macro");
        }
    }

    protected function print_line(int $line = -1, string $filename = null)
    {
        if ($line === -1) {
            $line = $this->lineno;
        }
        if ($filename === null) {
            $filename = $this->context->filename;
        }
        //fprintf($this->fp, $this->language->comment("line %d \"%s\""), $line, $filename);
    }
}

function isWhite(string $c): bool
{
    return $c === ' ' || $c === "\t" || $c === "\r" || $c === "\x0b" || $c === "\x0c";
}
