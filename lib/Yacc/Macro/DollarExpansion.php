<?php

namespace PhpYacc\Yacc\Macro;

use PhpYacc\Token;
use PhpYacc\Yacc\Macro;
use Iterator;
use Generator;
use PhpYacc\Yacc\Tokens;
use PhpYacc\Grammar\Context;
use RuntimeException;

class DollarExpansion extends Macro {
    const SEMVAL_LHS_TYPED   = 1;
    const SEMVAL_LHS_UNTYPED = 2;
    const SEMVAL_RHS_TYPED   = 3;
    const SEMVAL_RHS_UNTYPED = 4;

    protected $macros = [
        self::SEMVAL_LHS_TYPED => '$this->semValue',
        self::SEMVAL_LHS_UNTYPED => '$this->semValue',
        self::SEMVAL_RHS_TYPED => '$stackPos-(%l-%n)',
        self::SEMVAL_RHS_UNTYPED => '$stackPos-(%l-%n)',
    ];

    public function setMacro(int $name, string $value) {
        $this->macros[$name] = $value;
    }

    public function apply(Context $ctx, array $symbols, Iterator $tokens, int $n, array $attribute): Generator
    {
        $type = null;
        for ($tokens->rewind(); $tokens->valid(); $tokens->next()) {
            $t = $tokens->current();
            switch ($t->t) {
                case Tokens::NAME:
                    $type = null;
                    $v = -1;
                    for ($i = 0; $i <= $n; $i++) {
                        if ($symbols[$i]->name === $t->v) {
                            if ($v < 0) {
                                $v = $i;
                            } else {
                                throw new RuntimeException("Ambiguous semantic value reference for $t");
                            }
                        }
                    }
                    if ($v < 0) {
                        for ($i = 0; $i <= $n; $i++) {
                            if ($attribute[$i] === $t->v) {
                                $v = $i;
                                break;
                            }
                        }
                        if ($t->v === $attribute[$n + 1]) {
                            $v = 0;
                        }
                    }
                    if ($v >= 0) {
                        $t = clone $t;
                        $t->t = $v === 0 ? '$' : 0;
                        goto semval;
                    }
                    break;
                case '$':
                    $type = null;
                    $t = self::next($tokens);
                    if ($t->t === '<') {
                        $t = self::next($tokens);
                        if ($t->t !== Tokens::NAME) {
                            throw new RuntimeException("type expected");
                        }
                        $type = $ctx->intern($t->v);
                        if (self::next($tokens)->t !== '>') {
                            throw new RuntimeException("Missing >");
                        }
                        $t = self::next($tokens);
                    }
                    $v = 1;
                    if ($t->t === '$') {
                        $v = 0;
                    } elseif ($t->t === '-') {
                        $t = self::next($tokens);
                        if ($t->t !== Tokens::NUMBER) {
                            throw new RuntimeException("Number expected");
                        }
                        $v = -1 * ((int) $t->v);
                    } else {
                        if ($t->t !== Tokens::NUMBER) {
                            throw new RuntimeException("Number expected");
                        }
                        $v = (int) $t->v;
                        if ($v > $n) {
                            throw new RuntimeException("N is too big");
                        }
                    }
semval:
                    if ($type === null) {
                        $type = $symbols[$v]->type;
                    }
                    if ($type === NULL /** && $ctx->unioned */ && false) {
                        throw new RuntimeException("Type not defined for " . $symbols[$v]->name);
                    }
                    foreach ($this->parseDollar($t, $v, $n, $type ? $type->name : null) as $t) {
                        yield $t;
                    }

                    continue 2;
            }
            yield $t;
        }
    }

    protected function parseDollar(Token $t, int $nth, int $len, string $type = null): array
    {

        if ($t->t === '$') {
            if ($type) {
                $mp = $this->macros[self::SEMVAL_LHS_TYPED];
            } else {
                $mp = $this->macros[self::SEMVAL_LHS_UNTYPED];
            }
        } else {
            if ($type) {
                $mp = $this->macros[self::SEMVAL_RHS_TYPED];
            } else {
                $mp = $this->macros[self::SEMVAL_RHS_UNTYPED];
            }
        }

        $result = '';
        for ($i = 0; $i < strlen($mp); $i++) {
            if ($mp[$i] === '%') {
                $i++;
                switch ($mp[$i]) {
                    case 'n':
                        $result .= sprintf('%d', $nth);
                        break;
                    case 'l':
                        $result .= sprintf('%d', $len);
                        break;
                    case 't':
                        $result .= $type;
                        break;
                    default:
                        $result .= $mp[$i];
                }
            } else {
                $result .= $mp[$i];
            }

        }
        return $this->parse($result, $t->ln, $t->fn);
    }

}