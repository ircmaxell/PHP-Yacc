<?php

namespace PhpYacc\Lalr;

use PhpYacc\Lalr\Conflict\ReduceReduce;
use PhpYacc\Lalr\Conflict\ShiftReduce;
use PhpYacc\Lalr\Item;
use PhpYacc\Grammar\Context;
use PhpYacc\Yacc\ParseResult;
use PhpYacc\Grammar\Symbol;
use PhpYacc\Yacc\Production;

require_once __DIR__ . '/functions.php';

class Generator {

    const NON_ASSOC = -32768;

    /** @var ParseResult */
    protected $parseResult;
    /** @var Context */
    protected $context;
    protected $nullable;
    protected $blank;
    /** @var StateList[] */
    protected $statesThrough = [];
    protected $visited = [];
    protected $first;
    protected $follow;
    /** @var State[] $states */
    protected $states;

    protected $nlooks;
    protected $nstates;
    protected $nacts;
    protected $nacts2;
    protected $nnonleafstates;
    protected $nsrerr;
    protected $nrrerr;

    public function compute(ParseResult $parseResult)
    {
        $this->parseResult = $parseResult;
        $this->context = $parseResult->ctx;
        $nSymbols = $this->context->nSymbols();
        $this->nullable = array_fill(0, $nSymbols, false);

        $this->blank = str_repeat("\0", ceil(($nSymbols + NBITS - 1) / NBITS));
        $this->first = array_fill(0, $nSymbols, $this->blank);
        $this->follow = array_fill(0, $nSymbols, $this->blank);
        $this->nlooks = $this->nstates = $this->nacts = $this->nacts2 = 0;
        $this->nnonleafstates = 0;
        $this->nsrerr = $this->nrrerr = 0;
        foreach ($this->context->symbols() as $s) {
            $this->statesThrough[$s->code] = null;
        }

        $this->computeEmpty();
        $this->firstNullablePrecomp();
        $this->computeKernels();
        $this->computeLookaheads();
        $this->fillReduce();
    }

    protected function computeKernels()
    {
        $tmpList = new Lr1(
            $this->parseResult->startPrime, 
            $this->blank, 
            new Item($this->parseResult->gram(0), 1)
        );
        $state = new State(
            $this->context->nilSymbol(),
            $this->makeState($tmpList)
        );
        $this->states = [$state];

        $this->linkState($state, $state->through);
        $tail = $this->states;
        $this->nstates = 1;

        // foreach by ref so that new additions to $this->states are also picked up
        foreach ($this->states as &$p) {
            // Collect direct GOTO's (come from kernel items)

            /** @var Lr1|null $tmpList */
            /** @var Lr1|null $tmpTail */
            $tmpList = $tmpTail = null;

            /** @var Lr1 $x */
            for ($x = $p->items; $x !== null; $x = $x->next) {
                if (!$x->isTailItem()) {
                    $wp = new Lr1($this->parseResult->startPrime, $this->blank, $x->item->slice(1));
                    if ($tmpTail !== null) {
                        $tmpTail->next = $wp;
                    } else {
                        $tmpList = $wp;
                    }
                    $tmpTail = $wp;
                }
            }

            // Collect indirect GOTO's (come from nonkernel items)
            $this->clearVisited();
            for ($tp = $tmpList; $tp != null; $tp = $tp->next) {
                /** @var Symbol $g */
                $g = $tp->item[-1];
                if ($g !== null && !$g->isTerminal() && !$this->visited[$g->code]) {
                    $this->visited[$g->code] = true;
                    /** @var Production $gram */
                    for ($gram = $g->value; $gram != null; $gram = $gram->link) {
                        if (isset($gram->body[1])) {
                            $wp = new Lr1($g, $this->blank, new Item($gram, 2));
                            $tmpTail->next = $wp;
                            $tmpTail = $wp;
                        }
                    }
                }
            }

            $tmpList = $this->sortList($tmpList, function(Lr1 $x, Lr1 $y) {
                $gx = isset($x->item[-1]) ? $x->item[-1]->code : 0;
                $gy = isset($y->item[-1]) ? $y->item[-1]->code : 0;
                if ($gx !== $gy) {
                    return $gx - $gy;
                }
                $px = $x->item->getProduction();
                $py = $y->item->getProduction();
                if ($px !== $py) {
                    return $px->num - $py->num;
                }
                return $x->item->getPos() - $y->item->getPos();
            });

            // Compute next states
            $nextst = [];
            for ($tp = $tmpList; $tp !== null; ) {
                $sp = null;

                $g = $tp->item[-1];
                $sublist = $tp;
                while ($tp != null && $tp->item[-1] === $g) {
                    $sp = $tp;
                    $tp = $tp->next;
                }
                $sp->next = null;

                for ($lp = $this->statesThrough[$g->code]; $lp != null; $lp = $lp->next) {
                    if (isSameSet($lp->state->items, $sublist)) {
                        break;
                    }
                }

                if ($lp !== null) {
                    $q = $lp->state;
                } else {
                    $q = new State($g, $this->makeState($sublist));
                    $this->states[] = $q;
                    $this->linkState($q, $g);
                    $this->nstates++;
                }

                $nextst[] = $q;
            }

            $p->shifts = $nextst;
            $this->nacts += count($nextst);
        }
    }

    protected function computeLookaheads() {
        setBit($this->states[0]->items->look, 0);
        do {
            $changed = false;
            foreach ($this->states as $p) {
                $this->computeFollow($p);
                for ($x = $p->items; $x !== null; $x = $x->next) {
                    $g = $x->item[0] ?? null;
                    if (null !== $g) {
                        $s = $x->item->slice(1);
                        $t = null;
                        foreach ($p->shifts as $t) {
                            if ($t->through === $g) {
                                break;
                            }
                        }
                        assert($t->through === $g);
                        for ($y = $t->items; $y !== null; $y = $y->next) {
                            if ($y->item == $s) {
                                break;
                            }
                        }
                        assert($y->item == $s);
                        $changed |= orbits($this->context, $y->look, $x->look);
                    }
                }
                foreach ($p->shifts as $t) {
                    for ($x = $t->items; $x !== null; $x = $x->next) {
                        if ($x->left !== $this->parseResult->startPrime) {
                            $changed |= orbits($this->context, $x->look, $this->follow[$x->left->code]);
                        }
                    }
                }
                for ($x = $p->items; $x !== null; $x = $x->next) {
                    if ($x->isTailItem() && $x->isHeadItem()) {
                        orbits($this->context, $x->look, $this->follow[$x->item[-1]->code]);
                    }
                }
            }
        } while ($changed);

        if (DEBUG) {
            foreach ($this->states as $p) {
                echo "state unknown:\n";
                for ($x = $p->items; $x != null; $x = $x->next) {
                    echo "\t", $x->item, "\n";
                    echo "\t\t[ ";
                    dumpSet($this->context, $x->look);
                    echo "]\n";
                }
            }
        }
    }

    protected function fillReduce() {
        $this->clearVisited();
        foreach ($this->states as $p) {
            /** @var Reduce[] $tmpr */
            $tmpr = [];

            $tdefact = 0;
            foreach ($p->shifts as $t) {
                if ($t->through === $this->parseResult->errorToken) {
                    // shifting error
                    $tdefact = -1;
                }
            }

            // Pick up reduce entries
            for ($x = $p->items; $x !== null; $x = $x->next) {
                if (!$x->isTailItem()) {
                    continue;
                }

                $alook = $x->look; // clone! bitset
                $gram = $x->item->getProduction();

                // find shift/reduce conflict
                foreach ($p->shifts as $m => $t) {
                    $e = $t->through;
                    if (!$e->isTerminal()) {
                        break;
                    }
                    if (testBit($alook, $e->code)) {
                        $rel = $this->comparePrecedence($gram, $e);
                        if ($rel === self::NON_ASSOC) {
                            clearBit($alook, $e->code);
                            unset($p->shifts[$m]);
                            $tmpr[] = new Reduce($e, -1);
                        } elseif ($rel < 0) {
                            // reduce
                            unset($p->shifts[$m]);
                        } elseif ($rel > 0) {
                            // shift
                            clearBit($alook, $e->code);
                        } elseif ($rel == 0) {
                            // conflict
                            clearBit($alook, $e->code);
                            $this->nsrerr++;
                            $p->conflict = new Conflict\ShiftReduce($t, $gram->num, $e, $p->conflict);
                        }
                    }
                }

                foreach ($tmpr as $reduce) {
                    if (testBit($alook, $reduce->symbol->code)) {
                        // reduce/reduce conflict
                        $this->nrrerr++;
                        $p->conflict = new Conflict\ReduceReduce(
                            $reduce->number, $gram->num, $reduce->symbol, $p->conflict);

                        if ($gram->num < $reduce->number) {
                            $reduce->number = $gram->num;
                        }
                        clearBit($alook, $reduce->symbol->code);
                    }
                }

                foreach (forEachMember($this->context, $alook) as $e) {
                    $sym = $this->context->symbols()[$e];
                    $tmpr[] = new Reduce($sym, $gram->num);
                }
            }

            // Decide default action
            if (!$tdefact) {
                $tdefact = -1;

                usort($tmpr, function (Reduce $x, Reduce $y) {
                    if ($x->number != $y->number) {
                        return $y->number - $x->number;
                    }
                    return $x->symbol->code - $y->symbol->code;
                });

                $maxn = 0;
                $nr = count($tmpr);
                for ($j = 0; $j < $nr; ) {
                    for ($k = $j; $j < $nr; $j++) {
                        if ($tmpr[$j]->number != $tmpr[$k]->number) {
                            break;
                        }
                    }
                    if ($j - $k > $maxn && $tmpr[$k]->number > 0) {
                        $maxn = $j - $k;
                        $tdefact = $tmpr[$k]->number;
                    }
                }
            }

            // Squeeze tmpr
            $tmpr = array_filter($tmpr, function(Reduce $reduce) use($tdefact) {
                return $reduce->number !== $tdefact;
            });

            usort($tmpr, function(Reduce $x, Reduce $y) {
                if ($x->symbol !== $y->symbol) {
                    return $x->symbol->code - $y->symbol->code;
                }
                return $x->number - $y->number;
            });
            $tmpr[] = new Reduce($this->context->nilSymbol(), $tdefact);

            // Squeeze shift actions (we deleted some keys)
            $p->shifts = array_values($p->shifts);

            foreach ($tmpr as $reduce) {
                if ($reduce->number >= 0) {
                    $this->visited[$reduce->number] = true;
                }
            }

            // Register tmpr
            $p->reduce = $tmpr;
            $this->nacts2 += count($tmpr);
        }

        $k = 0;
        foreach ($this->parseResult->grams() as $gram) {
            if (!$this->visited[$gram->num]) {
                $k++;
                echo "Never reduced: \n"; // TODO
            }
        }

        if ($k) {
            echo $k, " rule(s) never reduced\n";
        }

        // Sort states in decreasing order of entries
        // do not move initial state
        $initState = array_shift($this->states);
        usort($this->states, function(State $p, State $q) {
            $pt = $pn = 0;
            foreach ($p->shifts as $x) {
                if ($x->through->isTerminal()) {
                    $pt++;
                }
            }
            $numReduces = count($p->reduce) - 1; // -1 for default action
            $pt += $numReduces;
            $pn += $numReduces;

            $qt = $qn = 0;
            foreach ($q->shifts as $x) {
                if ($x->through->isTerminal()) {
                    $qt++;
                }
            }
            $numReduces = count($q->reduce) - 1; // -1 for default action
            $qt += $numReduces;
            $qn += $numReduces;

            if ($pt !== $qt) {
                return $qt - $pt;
            }
            return $qn - $pn;
        });
        array_unshift($this->states, $initState);

        foreach ($this->states as $i => $p) {
            $p->number = $i;
            if (!empty($p->shifts) || $p->reduce[0]->symbol->isNilSymbol()) {
                $this->nnonleafstates = $i;
            }
        }

        foreach ($this->states as $state) {
            $this->printState($state);
        }
    }

    protected function comparePrecedence(Production $gram, Symbol $x) {
        if ($gram->associativity === Symbol::UNDEF
            || ($x->associativity & Symbol::MASK) === Symbol::UNDEF
        ) {
            return 0;
        }

        $v = $x->precedence - $gram->precedence;
        if ($v !== 0) {
            return $v;
        }

        switch ($gram->associativity) {
            case Symbol::LEFT:
                return -1;
            case Symbol::RIGHT:
                return 1;
            case Symbol::NON:
                return self::NON_ASSOC;
        }
        throw new \Exception('Cannot happen');
    }

    protected function computeFollow(State $st) {
        foreach ($st->shifts as $t) {
            if (!$t->through->isTerminal()) {
                for ($x = $t->items; $x !== null && !$x->isHeadItem(); $x = $x->next) {
                    $this->computeFirst($this->follow[$t->through->code], $x->item);
                }
            }
        }
        for ($x = $st->items; $x !== null; $x = $x->next) {
            /** @var Symbol $g */
            $g = $x->item[0] ?? null;
            if ($g !== null && !$g->isTerminal() && $this->isSeqNullable($x->item->slice(1))) {
                orbits($this->context, $this->follow[$g->code], $x->look);
            }
        }
        do {
            $changed = false;
            foreach ($st->shifts as $t) {
                if (!$t->through->isTerminal()) {
                    $p =& $this->follow[$t->through->code];
                    for ($x = $t->items; $x !== null && !$x->isHeadItem(); $x = $x->next) {
                        if ($this->isSeqNullable($x->item) && $x->left != $this->parseResult->startPrime) {
                            $changed |= orbits($this->context, $p, $this->follow[$x->left->code]);
                        }
                    }
                }
            }
        } while ($changed);
    }

    protected function computeFirst(string &$p, Item $item) {
        /** @var Symbol $g */
        foreach ($item as $g) {
            if ($g->isTerminal()) {
                setBit($p, $g->code);
                return;
            }
            orbits($this->context, $p, $this->first[$g->code]);
            if (!$this->nullable[$g->code]) {
                return;
            }
        }
    }

    protected function isSeqNullable(Item $item) {
        /** @var Symbol $g */
        foreach ($item as $g) {
            if ($g->isTerminal() || !$this->nullable[$g->code]) {
                return false;
            }
        }
        return true;
    }

    protected function linkState(State $state, Symbol $g)
    {
        $this->statesThrough[$g->code] = new StateList(
            $state, 
            $this->statesThrough[$g->code]
        );
    }

    protected function computeEmpty()
    {
        do {
            $changed = false;
            foreach ($this->parseResult->grams() as $gram) {
                $left = $gram->body[0];
                $right = $gram->body[1] ?? null;
                if (($right === null || ($right->associativity & Production::EMPTY)) && !($left->associativity & Production::EMPTY)) {
                    $left->setAssociativityFlag(Production::EMPTY);
                    $changed = true;
                }
            }
        } while ($changed);

        if (DEBUG) {
            echo "EMPTY nonterminals: \n";
            foreach ($this->context->nonTerminals() as $symbol) {
                if ($symbol->associativity & Production::EMPTY) {
                    echo "  " . $symbol->name . "\n";
                }
            }
        }
    }

    protected function firstNullablePrecomp()
    {
        do {
            $changed = false;
            foreach ($this->parseResult->grams() as $gram) {
                $h = $gram->body[0];
                for ($s = 1; $s < count($gram->body); $s++) {
                    $g = $gram->body[$s];
                    if ($g->isTerminal()) {
                        if (!testBit($this->first[$h->code], $g->code)) {
                            $changed = true;
                            setBit($this->first[$h->code], $g->code);
                        }
                        continue 2;
                    }

                    $changed |= orbits(
                        $this->context, 
                        $this->first[$h->code],
                        $this->first[$g->code]
                    );
                    if (!$this->nullable[$g->code]) {
                        continue 2;
                    }
                }

                if (!$this->nullable[$h->code]) {
                    $this->nullable[$h->code] = true;
                    $changed = true;
                }
            }
        } while ($changed);

        if (DEBUG) {
            echo "First:\n";
            foreach ($this->context->nonTerminals() as $symbol) {
                echo "  {$symbol->name}\t[ ";
                dumpSet($this->context, $this->first[$symbol->code]);
                if ($this->nullable[$symbol->code]) {
                    echo "@ ";
                }
                echo "]\n";
            }
        }
    }

    protected function makeState(Lr1 $items): Lr1
    {
        $tail = null;
        for ($p = $items; $p !== null; $p = $p->next) {
            $p->look = null;
            if ($p->left !== $this->parseResult->startPrime) {
                for ($q = $items; $q !== $p; $q = $q->next) {
                    if ($q->left === $p->left) {
                        $p->look = $q->look;
                        break;
                    }
                }
            }
            if ($p->look === null) {
                $p->look = $this->blank;
                $this->nlooks++;
            }
            $tail = $p;
        }
        $this->clearVisited();
        for ($p = $items; $p !== null; $p = $p->next) {
            /** @var Symbol $g */
            $g = $p->item[0] ?? null;
            if ($g !== null && !$g->isTerminal()) {
                $tail = $this->findEmpty($tail, $g);
            }
        }
        return $items;
    }

    protected function clearVisited()
    {
        $nSymbols = $this->context->nSymbols();
        $this->visited = array_fill(0, $nSymbols, false);

    }

    protected function findEmpty(Lr1 $tail, Symbol $x): Lr1
    {
        if (!$this->visited[$x->code] && ($x->associativity & Production::EMPTY)) {
            $this->visited[$x->code] = true;

            /** @var Production $gram */
            for ($gram = $x->value; $gram !== null; $gram = $gram->link) {
                if ($gram->isEmpty()) {
                    $p = new Lr1($this->parseResult->startPrime, $this->blank, new Item($gram, 1));
                    $tail->next = $p;
                    $tail = $p;
                    $this->nlooks++;
                } else if (!$gram->body[1]->isTerminal()) {
                    $tail = $this->findEmpty($tail, $gram->body[1]);
                }
            }
        }
        return $tail;
    }

    protected function sortList(Lr1 $list = null, callable $cmp) {
        $array = [];
        for ($x = $list; $x !== null; $x = $x->next) {
            $array[] = $x;
        }

        usort($array, $cmp);

        $list = null;
        /** @var Lr1 $tail */
        $tail = null;
        foreach ($array as $x) {
            if ($list == null) {
                $list = $x;
            } else {
                $tail->next = $x;
            }
            $tail = $x;
        }
        return $list;
    }

    protected function printState(State $state) {
        echo "state " . $state->number . "\n";
        for ($conf = $state->conflict; $conf !== null; $conf = $conf->next()) {
            if ($conf instanceof ShiftReduce) {
                echo sprintf(
                    "%d: shift/reduce conflict (shift %d, reduce %d) on %s\n",
                    $state->number, $conf->state()->number, $conf->reduce(),
                    $conf->symbol()->name);
            } else if ($conf instanceof ReduceReduce) {
                echo sprintf(
                    "%d: reduce/reduce conflict (reduce %d, reduce %d) on %s\n",
                    $state->number, $conf->reduce1(), $conf->reduce2(),
                    $conf->symbol()->name
                );
            }
        }

        for ($x = $state->items; $x !== null; $x = $x->next) {
            echo "\t" . $x->item . "\n";
        }
        echo "\n";

        $i = $j = 0;
        while (true) {
            $s = $state->shifts[$i] ?? null;
            $r = $state->reduce[$j] ?? null;
            if ($s === null && $r === null) {
                break;
            }

            if ($s !== null && ($r === null || $s->through->code < $r->symbol->code)) {
                $str = $s->through->name;
                echo strlen($str) < 8 ? "\t$str\t\t" : "\t$str\t";
                echo $s->through->isTerminal() ? "shift" : "goto";
                echo " " . $s->number;
                if ($s->isReduceOnly()) {
                    echo " and reduce (" . $s->reduce[0]->number . ")";
                }
                echo "\n";
                $i++;
            } else {
                $str = $r->symbol->isNilSymbol() ? "." : $r->symbol->name;
                echo strlen($str) < 8 ? "\t$str\t\t" : "\t$str\t";
                if ($r->number === 0) {
                    echo "accept\n";
                } else if ($r->number < 0) {
                    echo "error\n";
                } else {
                    echo "reduce ($r->number)\n";
                }
                $j++;
            }
        }
        echo "\n";
    }

}