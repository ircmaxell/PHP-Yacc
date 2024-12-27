<?php

namespace PhpYacc\Yacc\Macro;

use PhpYacc\Exception\ParseException;
use PhpYacc\Yacc\MacroAbstract;
use Iterator;
use Generator;
use PhpYacc\Yacc\Token;
use PhpYacc\Grammar\Context;
use RuntimeException;

class DollarExpansion extends MacroAbstract
{
    public const SEMVAL_LHS_TYPED   = 1;
    public const SEMVAL_LHS_UNTYPED = 2;
    public const SEMVAL_RHS_TYPED   = 3;
    public const SEMVAL_RHS_UNTYPED = 4;


    public function apply(Context $ctx, array $symbols, Iterator $tokens, int $n, array $attribute): Generator
    {
        $type = null;
        for ($tokens->rewind(); $tokens->valid(); $tokens->next()) {
            $t = $tokens->current();
            switch ($t->t) {
                case Token::NAME:
                    if (!$ctx->allowSemanticValueReferenceByName) {
                        break;
                    }
                    $type = null;
                    $v = -1;
                    for ($i = 0; $i <= $n; $i++) {
                        if ($symbols[$i]->name === $t->v) {
                            if ($v < 0) {
                                $v = $i;
                            } else {
                                throw new ParseException("Ambiguous semantic value reference for $t");
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
                        if ($t->t !== Token::NAME) {
                            throw ParseException::unexpected($t, Token::NAME);
                        }
                        $type = $ctx->intern($t->v);
                        $dump = self::next($tokens);
                        if ($dump->t !== '>') {
                            throw ParseException::unexpected($dump, '>');
                        }
                        $t = self::next($tokens);
                    }
                    $v = 1;
                    if ($t->t === '$') {
                        $v = 0;
                    } elseif ($t->t === '-') {
                        $t = self::next($tokens);
                        if ($t->t !== Token::NUMBER) {
                            throw ParseException::unexpected($t, Token::NUMBER);
                        }
                        $v = -1 * ((int) $t->v);
                    } else {
                        if ($t->t !== Token::NUMBER) {
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
                    if ($type === null /** && $ctx->unioned */ && false) {
                        throw new ParseException("Type not defined for " . $symbols[$v]->name);
                    }
                    foreach ($this->parseDollar($ctx, $t, $v, $n, $type ? $type->name : null) as $t) {
                        yield $t;
                    }

                    continue 2;
            }
            yield $t;
        }
    }

    protected function parseDollar(Context $ctx, Token $t, int $nth, int $len, string $type = null): array
    {
        if ($t->t === '$') {
            if ($type) {
                $mp = $ctx->macros[self::SEMVAL_LHS_TYPED];
            } else {
                $mp = $ctx->macros[self::SEMVAL_LHS_UNTYPED];
            }
        } else {
            if ($type) {
                $mp = $ctx->macros[self::SEMVAL_RHS_TYPED];
            } else {
                $mp = $ctx->macros[self::SEMVAL_RHS_UNTYPED];
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
