<?php

namespace PhpYacc\Lalr;

use PhpYacc\Core\ArrayObject;
use PhpYacc\Grammar\Context;
use PhpYacc\Yacc\ParseResult;
use PhpYacc\Grammar\Symbol;
use PhpYacc\Yacc\Production;

require_once __DIR__ . '/functions.php';

class Generator {

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
    /** @var State $states */
    protected $states;
    protected $nlooks;
    protected $nstates;
    protected $nacts;

    public function compute(ParseResult $parseResult)
    {
        $this->parseResult = $parseResult;
        $this->context = $parseResult->ctx;
        $nSymbols = $this->context->nSymbols();
        $this->nullable = array_fill(0, $nSymbols, false);

        $this->blank = str_repeat("\0", ceil(($nSymbols + NBITS - 1) / NBITS));
        $this->first = array_fill(0, $nSymbols, $this->blank);
        $this->follow = array_fill(0, $nSymbols, $this->blank);
        $this->nlooks = 0;
        $this->nstates = 0;
        $this->nacts = 0;
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
            $this->parseResult->gram(0)->body->slice(2)
        );
        $this->states = new State();
        $this->states->through = $this->context->nilSymbol();
        $this->states->items = $this->makeState($tmpList);

        $this->linkState($this->states, $this->states->through);
        $tail = $this->states;
        $this->nstates = 1;

        for ($p = $this->states; $p !== null; $p = $p->next) {
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
                        if ($gram->body[2] !== null) {
                            $wp = new Lr1($g, $this->blank, $gram->body->slice(3));
                            $tmpTail->next = $wp;
                            $tmpTail = $wp;
                        }
                    }
                }
            }

            // This is NOT the same comparison function as in the original code
            // It uses comparisons between unrelated pointers as far as I can see :/
            $tmpList = $this->sortList($tmpList, function(Lr1 $x, Lr1 $y) {
                $i = -1;
                do {
                    $gx = $x->item[$i] !== null ? $x->item[$i]->code : 0;
                    $gy = $y->item[$i] !== null ? $y->item[$i]->code : 0;
                    if ($gx !== $gy) {
                        return $gx - $gy;
                    }
                    $i++;
                } while ($x->item[$i] !== null || $y->item[$i] !== null);
                return 0;
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
                    $q = new State();
                    $tail->next = $q;
                    $tail = $q;
                    $q->items = $this->makeState($sublist);
                    $q->through = $g;
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
        setBit($this->states->items->look, 0);
        do {
            $changed = false;
            for ($p = $this->states; $p !== null; $p = $p->next) {
                $this->computeFollow($p);
                for ($x = $p->items; $x !== null; $x = $x->next) {
                    $g = $x->item[0];
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
            for ($p = $this->states; $p != null; $p = $p->next) {
                echo "state unknown:\n";
                for ($x = $p->items; $x != null; $x = $x->next) {
                    echo "\t", $x->item, "\n"; // TODO match output
                    echo "\t\t[ ";
                    dumpSet($this->context, $x->look);
                    echo "]\n";
                }
            }
        }
    }

    protected function fillReduce() {
        $this->clearVisited();
        for ($p = $this->states; $p != null; $p = $p->next) {
            $tdefact = 0;
            foreach ($p->shifts as $t) {
                if ($t->through === $this->parseResult->errorToken) {
                    // shifting error
                    $tdefact = -1;
                }
            }

            // Pick up reduce entries
            $nr = 0;
            for ($x = $p->items; $x !== null; $x = $x->next) {
                if (!$x->isTailItem()) {
                    continue;
                }

                $alook = $x->look; // clone! bitset
                // TODO $pnum;
                foreach ($p->shifts as $t) {
                    // TODO if (t == RUBOUT) continue;
                    $e = $t->through;
                    if (!$e->isTerminal()) {
                        break;
                    }
                    if (testBit($alook, $e->code)) {
                        //
                    }
                }
            }

            // TODO
        }
        // TODO
        $k = 0;
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
            $g = $x->item[0];
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

    protected function computeFirst(string &$p, ArrayObject $item) {
        $i = 0;
        /** @var Symbol $g */
        while (null !== $g = $item[$i++]) {
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

    protected function isSeqNullable(ArrayObject $item) {
        $i = 0;
        /** @var Symbol $g */
        while (null !== $g = $item[$i++]) {
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
                $left = $gram->body[1];
                $right = $gram->body[2];
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
                $h = $gram->body[1];
                for ($s = 2; $s < count($gram->body) + 1; $s++) {
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
            $g = $p->item[0];
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
                if ($gram->body[2] === null) {
                    $p = new Lr1($this->parseResult->startPrime, $this->blank, $gram->body->slice(2));
                    $tail->next = $p;
                    $tail = $p;
                    $this->nlooks++;
                } else if (!$gram->body[2]->isTerminal()) {
                    $tail = $this->findEmpty($tail, $gram->body[2]);
                }
            }
        }
        return $tail;
    }

    function sortList(Lr1 $list = null, callable $cmp) {
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

}