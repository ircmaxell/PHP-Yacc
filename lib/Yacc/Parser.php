<?php

declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Exception\ParseException;
use RuntimeException;
use PhpYacc\Grammar\Context;
use PhpYacc\Grammar\Symbol;

use function PhpYacc\character_value;

class Parser
{
    /** @var Context */
    protected $context;
    protected $lexer;
    protected $macros;

    /** @var Symbol */
    protected $eofToken;
    /** @var Symbol */
    protected $errorToken;
    protected $startPrime;

    public function __construct(Lexer $lexer, MacroSet $macros)
    {
        $this->lexer = $lexer;
        $this->macros = $macros;
    }

    public function parse(string $code, Context $context)
    {
        $this->context = $context;
        $this->lexer->startLexing($code, $this->context->filename);
        $this->doDeclaration();
        $this->doGrammar();
        $this->context->eofToken = $this->eofToken;
        $this->context->errorToken = $this->errorToken;
        $this->context->startPrime = $this->startPrime;
        $this->context->finish();
        return $this->context;
    }

    protected function copyAction(array $symbols, int $n, $delm, array $attribute): string
    {
        $tokens = [];
        $ct = 0;
        while (($t = $this->lexer->rawGet())->t !== $delm || $ct > 0) {
            switch ($t->t) {
                case EOF:
                    throw ParseException::unexpected($t, Token::decode($delm));
                case '{':
                    $ct++;
                    break;
                case '}':
                    $ct--;
                    break;
            }
            $tokens[] = $t;
        }
        $expanded = $this->macros->apply($this->context, $symbols, $tokens, $n, $attribute);
        return implode('', array_map(function (Token $t) {
            return $t->v;
        }, $expanded));
    }

    protected function doType()
    {
        $type = $this->getType();
        while (true) {
            if (($t = $this->lexer->get())->v === ',') {
                continue;
            }
            if ($t->t !== Token::NAME && $t->t !== "'") {
                break;
            }
            $p = $this->context->internSymbol($t->v, false);
            if ($type !== null) {
                $p->type = $type;
            }
        }
        $this->lexer->unget();
    }

    protected function doGrammar()
    {
        $attribute = [];
        $gbuffer = [null];
        $r = new Production('', 0);
        $r->body = [$this->startPrime];
        $this->context->addGram($r);

        $t = $this->lexer->get();

        while ($t->t !== Token::MARK && $t->t !== EOF) {
            if ($t->t === Token::NAME) {
                if ($this->lexer->peek()->t === '@') {
                    $attribute[0] = $t->v;
                    $this->lexer->get();
                    $t = $this->lexer->get();
                } else {
                    $attribute[0] = null;
                }
                $gbuffer[0] = $this->context->internSymbol($t->v, false);
                $attribute[1] = null;
                if ($gbuffer[0]->isterminal) {
                    throw new RuntimeException("Nonterminal symbol expected: $t");
                } elseif (($tmp = $this->lexer->get())->t !== ':') {
                    throw new RuntimeException("':' expected, $tmp found");
                }
                if ($this->context->startSymbol === null) {
                    $this->context->startSymbol = $gbuffer[0];
                }
            } elseif ($t->t === '|') {
                if (!$gbuffer[0]) {
                    throw new RuntimeException("Syntax Error, unexpected $t");
                }
                $attribute[1] = null;
            } elseif ($t->t === Token::BEGININC) {
                $this->doCopy();
                $t = $this->lexer->get();
                continue;
            } else {
                throw new RuntimeException("Syntax Error Unexpected $t");
            }

            $lastTerm = $this->startPrime;
            $action = '';
            $pos = 0;
            $i = 1;
            while (true) {
                $t = $this->lexer->get();
                if ($t->t === '=') {
                    $pos = $t->ln;
                    if (($t = $this->lexer->get())->t === '{') {
                        $pos = $t->ln;
                        $action = $this->copyAction($gbuffer, $i - 1, '}', $attribute);
                    } else {
                        $this->lexer->unget();
                        $action = $this->copyAction($gbuffer, $i - 1, ';', $attribute);
                    }
                } elseif ($t->t === '{') {
                    $pos = $t->ln;
                    $action = $this->copyAction($gbuffer, $i - 1, '}', $attribute);
                } elseif ($t->t === Token::PRECTOK) {
                    $lastTerm = $this->context->internSymbol($this->lexer->get()->v, false);
                } elseif ($t->t === Token::NAME && $this->lexer->peek()->t === ':') {
                    break;
                } elseif ($t->t === Token::NAME && $this->lexer->peek()->t === '@') {
                    $attribute[$i] = $t->v;
                    $this->lexer->get();
                } elseif ($t->t === Token::NAME || $t->t === "'") {
                    if ($action) {
                        $g = $this->context->genNonTerminal();
                        $r = new Production($action, $pos);
                        $r->body = [$g];
                        $gbuffer[$i++] = $g;
                        $attribute[$i] = null;
                        $r->link = $r->body[0]->value;
                        $g->value = $this->context->addGram($r);
                    }
                    $gbuffer[$i++] = $w = $this->context->internSymbol($t->v, false);
                    $attribute[$i] = null;
                    if ($w->isterminal) {
                        $lastTerm = $w;
                    }
                    $action = '';
                } else {
                    break;
                }
            }
            if (!$action) {
                if ($i > 1 && $gbuffer[0]->type !== null && $gbuffer[0]->type !== $gbuffer[1]->type) {
                    throw new ParseException("Stack types are different");
                }
            }
            $r = new Production($action, $pos);
            $r->body = array_slice($gbuffer, 0, $i);
            $r->precedence = $lastTerm->precedence;
            $r->associativity = $lastTerm->associativity & Symbol::MASK;
            $r->link = $r->body[0]->value;
            $gbuffer[0]->value = $this->context->addGram($r);

            if ($t->t === ';') {
                $t = $this->lexer->get();
            }
        }
        $this->context->gram(0)->body[] = $this->context->startSymbol;
        $this->startPrime->value = null;
        foreach ($this->context->nonterminals as $key => $symbol) {
            if ($symbol === $this->startPrime) {
                continue;
            }
            if (($j = $symbol->value) === null) {
                throw new ParseException("Nonterminal {$symbol->name} used but not defined");
            }
            $k = null;
            while ($j) {
                $w = $j->link;
                $j->link = $k;
                $k = $j;
                $j = $w;
            }
            $symbol->value = $k;
        }
    }

    protected function doDeclaration()
    {
        $this->eofToken = $this->context->internSymbol("EOF", true);
        $this->eofToken->value = 0;
        $this->errorToken = $this->context->internSymbol("error", true);
        $this->startPrime = $this->context->internSymbol("\$start", false);

        while (($t = $this->lexer->get())->t !== Token::MARK) {
            switch ($t->t) {
                case Token::TOKEN:
                case Token::RIGHT:
                case Token::LEFT:
                case Token::NONASSOC:
                    $this->doToken($t);
                    break;
                case Token::BEGININC:
                    $this->doCopy();
                    break;
                case Token::UNION:
                    $this->doUnion();
                    $this->context->unioned = true;
                    break;
                case Token::TYPE:
                    $this->doType();
                    break;
                case Token::EXPECT:
                    $t = $this->lexer->get();
                    if ($t->t === Token::NUMBER) {
                        $this->context->expected = (int) $t->v;
                    } else {
                        throw ParseException::unexpected($t, Token::NUMBER);
                    }
                    break;
                case Token::START:
                    $t = $this->lexer->get();
                    $this->context->startSymbol = $this->context->internSymbol($t->v, false);
                    break;
                case Token::PURE_PARSER:
                    $this->context->pureFlag = true;
                    break;
                case EOF:
                    throw new ParseException("No grammar given");
                default:
                    throw new ParseException("Syntax error, unexpected {$t->v}");
            }
        }
        $base = 256;
        foreach ($this->context->terminals as $terminal) {
            if ($terminal === $this->context->eofToken) {
                continue;
            }
            if ($terminal->value < 0) {
                $terminal->value = $base++;
            }
        }
    }

    protected $currentPrecedence = 0;

    protected function doToken(Token $tag)
    {
        $preIncr = 0;
        $type = $this->getType();
        $t = $this->lexer->get();

        while ($t->t === Token::NAME || $t->t === "'") {
            $p = $this->context->internSymbol($t->v, true);
            if ($p->name[0] === "'") {
                $p->value = character_value(substr($p->name, 1, -1));
            }

            if ($type) {
                $p->type = $type;
            }
            switch ($tag->t) {
                case Token::LEFT:
                    $p->associativity |= Symbol::LEFT;
                    break;
                case Token::RIGHT:
                    $p->associativity |= Symbol::RIGHT;
                    break;
                case Token::NONASSOC:
                    $p->associativity |= Symbol::NON;
                    break;
            }
            if ($tag->t !== Token::TOKEN) {
                $p->precedence = $this->currentPrecedence;
                $preIncr = 1;
            }
            $t = $this->lexer->get();
            if ($t->t === Token::NUMBER) {
                if ($p->value === null) {
                    $p->value = (int) $t->v;
                } else {
                    throw new ParseException("Unexpected Token::NUMBER as {$p->name} already has a value");
                }
                $t = $this->lexer->get();
            }
            if ($t->t === ',') {
                $t = $this->lexer->get();
            }
        }
        $this->lexer->unget();
        $this->currentPrecedence += $preIncr;
    }

    protected function getType()
    {
        $t = $this->lexer->get();
        if ($t->t !== '<') {
            $this->lexer->unget();
            return null;
        }
        $ct = 1;
        $p = '';
        $t = $this->lexer->get();
        while (true) {
            switch ($t->t) {
                case "\n":
                case EOF:
                    throw ParseException::unexpected($t, ">");

                case '<':
                    $ct++;
                    break;
                case '>':
                    $ct--;
                    break;
            }
            if ($ct === 0) {
                break;
            }
            $p .= $t->v;
            $t = $this->lexer->rawGet();
        }
        $this->context->unioned = true;
        return $this->context->intern($p);
    }
}
