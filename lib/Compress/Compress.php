<?php

declare(strict_types=1);

namespace PhpYacc\Compress;

use PhpYacc\Grammar\Context;
use PhpYacc\Grammar\Symbol;

use function PhpYacc\stable_sort;

require_once __DIR__ . "/functions.php";

class Compress
{
    public const YYUNEXPECTED = 32767;
    public const YYDEFAULT = -32766;
    public const VACANT = -32768;

    /**
     * @var Context $context
     */
    protected $context;
    /**
     * @var CompressResult $result
     */
    protected $result;


    public function compress(Context $context)
    {
        $this->result = new CompressResult();
        $this->context = $context;

        $this->makeup_table2();
        return $this->result;
    }

    protected function compute_preimages()
    {
        $primv = [];

        for ($i = 0; $i < $this->context->nstates; $i++) {
            $primv[$i] = new Preimage($i);
        }

        for ($i = 0; $i < $this->context->nclasses; $i++) {
            for ($j = 0; $j < $this->context->nterminals; $j++) {
                $s = $this->context->class_action[$i][$j];
                if ($s > 0) {
                    $primv[$s]->classes[$primv[$s]->length++] = $i;
                }
            }
        }

        stable_sort($primv, Preimage::class . "::compare");

        $this->context->primof = array_fill(0, $this->context->nstates, 0);
        $this->context->prims = array_fill(0, $this->context->nstates, 0);
        $this->context->nprims = 0;
        for ($i = 0; $i < $this->context->nstates;) {
            $p = $primv[$i];
            $this->context->prims[$this->context->nprims] = $p;
            for (; $i < $this->context->nstates && Preimage::compare($p, $primv[$i]) === 0; $i++) {
                $this->context->primof[$primv[$i]->index] = $p;
            }
            $p->index = $this->context->nprims++;
        }
    }

    protected function encode_shift_reduce(array $t, int $count): array
    {
        for ($i = 0; $i < $count; $i++) {
            if ($t[$i] >= $this->context->nnonleafstates) {
                $t[$i] = $this->context->nnonleafstates + $this->context->default_act[$t[$i]];
            }
        }
        return $t;
    }

    protected function makeup_table2()
    {
        $this->context->term_action = array_fill(0, $this->context->nnonleafstates, 0);
        $this->context->class_action = array_fill(0, $this->context->nnonleafstates * 2, 0);
        $this->context->nonterm_goto = array_fill(0, $this->context->nnonleafstates, 0);
        $this->context->default_act = array_fill(0, $this->context->nstates, 0);
        $this->context->default_goto = array_fill(0, $this->context->nnonterminals, 0);

        $this->resetFrequency();
        $this->context->state_imagesorted = array_fill(0, $this->context->nnonleafstates, 0);
        $this->context->class_of = array_fill(0, $this->context->nstates, 0);

        for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
            $this->context->term_action[$i] = array_fill(0, $this->context->nterminals, self::VACANT);
            $this->context->nonterm_goto[$i] = array_fill(0, $this->context->nnonterminals, self::VACANT);

            foreach ($this->context->states[$i]->shifts as $shift) {
                if ($shift->through->isterminal) {
                    $this->context->term_action[$i][$shift->through->code] = $shift->number;
                } else {
                    $this->context->nonterm_goto[$i][$this->nb($shift->through)] = $shift->number;
                }
            }
            foreach ($this->context->states[$i]->reduce as $reduce) {
                if ($reduce->symbol->isNilSymbol()) {
                    break;
                }
                $this->context->term_action[$i][$reduce->symbol->code] = -$this->encode_rederr($reduce->number);
            }
            $this->context->state_imagesorted[$i] = $i;
        }

        foreach ($this->context->states as $key => $state) {
            foreach ($state->reduce as $r) {
                if ($r->symbol->isNilSymbol()) {
                    break;
                }
            }
            $this->context->default_act[$key] = $this->encode_rederr($r->number);
        }

        for ($j = 0; $j < $this->context->nnonterminals; $j++) {
            $max = 0;
            $maxst = self::VACANT;
            $this->resetFrequency();

            for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                $st = $this->context->nonterm_goto[$i][$j];
                if ($st > 0) {
                    $this->context->frequency[$st]++;
                    if ($this->context->frequency[$st] > $max) {
                        $max = $this->context->frequency[$st];
                        $maxst = $st;
                    }
                }
            }
            $this->context->default_goto[$j] = $maxst;
        }
        # 847

        stable_sort($this->context->state_imagesorted, [$this, 'cmp_states']);

        $j = 0;

        for ($i = 0; $i < $this->context->nnonleafstates;) {
            $k = $this->context->state_imagesorted[$i];
            $this->context->class_action[$j] = $this->context->term_action[$k];
            for (; $i < $this->context->nnonleafstates && $this->cmp_states($this->context->state_imagesorted[$i], $k) === 0; $i++) {
                $this->context->class_of[$this->context->state_imagesorted[$i]] = $j;
            }
            $j++;
        }
        $this->context->nclasses = $j;

        if ($this->context->verboseDebug) {
            $this->context->debug("State=>class:\n");
            for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                if ($i % 10 === 0) {
                    $this->context->debug("\n");
                }
                $this->context->debug(sprintf("%3d=>%-3d ", $i, $this->context->class_of[$i]));
            }
            $this->context->debug("\n");
        }

        $this->compute_preimages();

        if ($this->context->verboseDebug) {
            $this->print_table();
        }

        $this->extract_common();

        $this->authodox_table();
    }

    protected function print_table()
    {
        $this->context->debug("\nTerminal action:\n");
        $this->context->debug(sprintf("%8.8s", "T\\S"));
        for ($i = 0; $i < $this->context->nclasses; $i++) {
            $this->context->debug(sprintf("%4d", $i));
        }
        $this->context->debug("\n");
        for ($j = 0; $j < $this->context->nterminals; $j++) {
            for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                if (self::VACANT !== $this->context->term_action[$i][$j]) {
                    break;
                }
            }
            if ($i < $this->context->nnonleafstates) {
                $this->context->debug(sprintf("%8.8s", $this->context->symbol($j)->name));
                for ($i = 0; $i < $this->context->nclasses; $i++) {
                    $this->context->debug(printact($this->context->class_action[$i][$j]));
                }
                $this->context->debug("\n");
            }
        }

        $this->context->debug("\nNonterminal GOTO table:\n");
        $this->context->debug(sprintf("%8.8s", "T\\S"));
        for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
            $this->context->debug(sprintf("%4d", $i));
        }
        $this->context->debug("\n");
        foreach ($this->context->nonterminals as $symbol) {
            for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                if ($this->context->nonterm_goto[$i][$this->nb($symbol)] > 0) {
                    break;
                }
            }
            if ($i < $this->context->nnonleafstates) {
                $this->context->debug(sprintf("%8.8s", $symbol->name));
                for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                    $this->context->debug(printact($this->context->nonterm_goto[$i][$this->nb($symbol)]));
                }
                $this->context->debug("\n");
            }
        }

        $this->context->debug("\nNonterminal GOTO table:\n");
        $this->context->debug(sprintf("%8.8s default", "T\\S"));
        for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
            $this->context->debug(sprintf("%4d", $i));
        }
        $this->context->debug("\n");
        foreach ($this->context->nonterminals as $symbol) {
            $nb = $this->nb($symbol);
            for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                if ($this->context->nonterm_goto[$i][$nb] > 0) {
                    break;
                }
            }
            if ($i < $this->context->nnonleafstates) {
                $this->context->debug(sprintf("%8.8s", $symbol->name));
                $this->context->debug(sprintf("%8d", $this->context->default_goto[$nb]));
                for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                    if ($this->context->nonterm_goto[$i][$nb] === $this->context->default_goto[$nb]) {
                        $this->context->debug("  = ");
                    } else {
                        $this->context->debug(printact($this->context->nonterm_goto[$i][$nb]));
                    }
                }
                $this->context->debug("\n");
            }
        }
    }

    protected function extract_common()
    {
        $this->context->class2nd = array_fill(0, $this->context->nclasses, -1);

        $alist = null;
        $n = 0;

        for ($i = 0; $i < $this->context->nprims; $i++) {
            $prim = $this->context->prims[$i];
            if ($prim->length < 2) {
                continue;
            }
            $p = new Auxiliary();
            $this->best_covering($p, $prim);
            if ($p->gain < 1) {
                continue;
            }
            $p->preimage = $prim;
            $p->next = $alist;
            $alist = $p;
            $n++;
        }

        if ($this->context->verboseDebug) {
            $this->context->debug("\nCandidates of aux table:\n");
            for ($p = $alist; $p !== null; $p = $p->next) {
                $this->context->debug(sprintf("Aux = (%d) ", $p->gain));
                $f = 0;
                for ($j = 0; $j < $this->context->nterminals; $j++) {
                    if (self::VACANT !== $p->table[$j]) {
                        $this->context->debug(sprintf($f++ ? ",%d" : "%d", $p->table[$j]));
                    }
                }
                $this->context->debug(" * ");
                for ($j = 0; $j < $p->preimage->length; $j++) {
                    $this->context->debug(sprintf($j ? ",%d" : "%d", $p->preimage->classes[$j]));
                }
                $this->context->debug("\n");
            }
            $this->context->debug("Used aux table:\n");
        }
        $this->context->naux = $this->context->nclasses;
        for (;;) {
            $maxgain = 0;
            $maxaux = null;
            $pre = null;
            $maxpre = null;
            for ($p = $alist; $p != null; $p = $p->next) {
                if ($p->gain > $maxgain) {
                    $maxgain = $p->gain;
                    $maxaux = $p;
                    $maxpre = $pre;
                }
                $pre = $p;
            }

            if ($maxaux === null) {
                break;
            }

            if ($maxpre) {
                $maxpre->next = $maxaux->next;
            } else {
                $alist = $maxaux->next;
            }

            $maxaux->index = $this->context->naux;

            for ($j = 0; $j < $maxaux->preimage->length; $j++) {
                $cl = $maxaux->preimage->classes[$j];
                if (eq_row($this->context->class_action[$cl], $maxaux->table, $this->context->nterminals)) {
                    $maxaux->index = $cl;
                }
            }

            if ($maxaux->index >= $this->context->naux) {
                $this->context->class_action[$this->context->naux++] = $maxaux->table;
            }

            for ($j = 0; $j < $maxaux->preimage->length; $j++) {
                $cl = $maxaux->preimage->classes[$j];
                if ($this->context->class2nd[$cl] < 0) {
                    $this->context->class2nd[$cl] = $maxaux->index;
                }
            }

            if ($this->context->verboseDebug) {
                $this->context->debug(sprintf("Selected aux[%d]: (%d) ", $maxaux->index, $maxaux->gain));
                $f = 0;
                for ($j = 0; $j < $this->context->nterminals; $j++) {
                    if (self::VACANT !== $maxaux->table[$j]) {
                        $this->context->debug(sprintf($f++ ? ",%d" : "%d", $maxaux->table[$j]));
                    }
                }
                $this->context->debug(" * ");
                $f = 0;
                for ($j = 0; $j < $maxaux->preimage->length; $j++) {
                    $cl = $maxaux->preimage->classes[$j];
                    if ($this->context->class2nd[$cl] === $maxaux->index) {
                        $this->context->debug(sprintf($f++ ? ",%d" : "%d", $cl));
                    }
                }
                $this->context->debug("\n");
            }

            for ($p = $alist; $p != null; $p = $p->next) {
                $this->best_covering($p, $p->preimage);
            }
        }
        if ($this->context->verboseDebug) {
            for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                if ($this->context->class2nd[$this->context->class_of[$i]] >= 0 && $this->context->class2nd[$this->context->class_of[$i]] !== $this->context->class_of[$i]) {
                    $this->context->debug(sprintf("state %d (class %d): aux[%d]\n", $i, $this->context->class_of[$i], $this->context->class2nd[$this->context->class_of[$i]]));
                } else {
                    $this->context->debug(sprintf("state %d (class %d)\n", $i, $this->context->class_of[$i]));
                }
            }
        }
    }

    protected function best_covering(Auxiliary $aux, Preimage $prim)
    {
        $this->resetFrequency();
        $gain = 0;
        for ($i = 0; $i < $this->context->nterminals; $i++) {
            $max = 0;
            $maxAction = -1;
            $nvacant = 0;
            for ($j = 0; $j < $prim->length; $j++) {
                if ($this->context->class2nd[$prim->classes[$j]] < 0) {
                    $c = $this->context->class_action[$prim->classes[$j]][$i];
                    if ($c > 0 && ++$this->context->frequency[$c] > $max) {
                        $maxAction = $c;
                        $max = $this->context->frequency[$c];
                    } elseif (self::VACANT === $c) {
                        $nvacant++;
                    }
                }
            }
            $n = $max - 1 - $nvacant;
            if ($n > 0) {
                $aux->table[$i] = $maxAction;
                $gain += $n;
            } else {
                $aux->table[$i] = self::VACANT;
            }
        }
        $aux->gain = $gain;
    }

    protected function authodox_table()
    {
        // TODO
        $this->context->ctermindex = array_fill(0, $this->context->nterminals, -1);
        $this->context->otermindex = array_fill(0, $this->context->nterminals, 0);

        $ncterms = 0;
        for ($j = 0; $j < $this->context->nterminals; $j++) {
            if ($j === $this->context->errorToken->code) {
                $this->context->ctermindex[$j] = $ncterms;
                $this->context->otermindex[$ncterms++] = $j;
                continue;
            }
            for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                if ($this->context->term_action[$i][$j] !== self::VACANT) {
                    $this->context->ctermindex[$j] = $ncterms;
                    $this->context->otermindex[$ncterms++] = $j;
                    break;
                }
            }
        }

        // Unlike kmyacc, we preserve unused terminal symbols. This makes things easier if a set
        // tokens is shared between multiple parsers, not all of which might use all tokens.
        // We assign these at the end, to keep numbering of other tokens the same.
        for ($j = 0; $j < $this->context->nterminals; $j++) {
            if ($this->context->ctermindex[$j] === -1) {
                $this->context->ctermindex[$j] = $ncterms;
                $this->context->otermindex[$ncterms++] = $j;
            }
        }

        $cterm_action = array_fill(0, $this->context->naux, array_fill(0, $ncterms, 0));
        for ($i = 0; $i < $this->context->nclasses; $i++) {
            for ($j = 0; $j < $ncterms; $j++) {
                $cterm_action[$i][$j] = $this->context->class_action[$i][$this->context->otermindex[$j]];
            }
        }

        #582

        for ($i = 0; $i < $this->context->nclasses; $i++) {
            if ($this->context->class2nd[$i] >= 0 && $this->context->class2nd[$i] != $i) {
                $table = $this->context->class_action[$this->context->class2nd[$i]];
                for ($j = 0; $j < $ncterms; $j++) {
                    if (self::VACANT !== $table[$this->context->otermindex[$j]]) {
                        if ($cterm_action[$i][$j] === $table[$this->context->otermindex[$j]]) {
                            $cterm_action[$i][$j] = self::VACANT;
                        } elseif ($cterm_action[$i][$j] === self::VACANT) {
                            $cterm_action[$i][$j] = self::YYDEFAULT;
                        }
                    }
                }
            }
        }

        for ($i = $this->context->nclasses; $i < $this->context->naux; $i++) {
            for ($j = 0; $j < $ncterms; $j++) {
                $cterm_action[$i][$j] = $this->context->class_action[$i][$this->context->otermindex[$j]];
            }
        }
        $base = [];
        $this->pack_table($cterm_action, $this->context->naux, $ncterms, false, $this->result->yyaction, $this->result->yycheck, $base);
        $this->result->yydefault = $this->context->default_act;

        $this->result->yybase = array_fill(0, $this->context->nnonleafstates * 2, 0);
        $this->result->yybasesize = $this->context->nnonleafstates;
        for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
            $cl = $this->context->class_of[$i];
            $this->result->yybase[$i] = $base[$cl];
            if ($this->context->class2nd[$cl] >= 0 && $this->context->class2nd[$cl] != $cl) {
                $this->result->yybase[$i + $this->context->nnonleafstates] = $base[$this->context->class2nd[$cl]];
                if ($i + $this->context->nnonleafstates + 1 > $this->result->yybasesize) {
                    $this->result->yybasesize = $i + $this->context->nnonleafstates + 1;
                }
            }
        }

        $this->result->yybase = array_slice($this->result->yybase, 0, $this->result->yybasesize);

        #642
        $nonterm_transposed = array_fill(0, $this->context->nnonterminals, array_fill(0, $this->context->nnonleafstates, 0));
        foreach ($nonterm_transposed as $j => $_dump) {
            for ($i = 0; $i < $this->context->nnonleafstates; $i++) {
                $nonterm_transposed[$j][$i] = $this->context->nonterm_goto[$i][$j];
                if ($this->context->nonterm_goto[$i][$j] === $this->context->default_goto[$j]) {
                    $nonterm_transposed[$j][$i] = self::VACANT;
                }
            }
        }

        $this->pack_table($nonterm_transposed, $this->context->nnonterminals, $this->context->nnonleafstates, true, $this->result->yygoto, $this->result->yygcheck, $this->result->yygbase);

        $this->result->yygdefault = $this->context->default_goto;

        $this->result->yylhs = [];
        $this->result->yylen = [];
        foreach ($this->context->grams as $gram) {
            $this->result->yylhs[] = $this->nb($gram->body[0]);
            $this->result->yylen[] = count($gram->body) - 1;
        }

        $this->result->yytranslatesize = 0;

        foreach ($this->context->terminals as $term) {
            $value = $term->value;
            if ($value + 1 > $this->result->yytranslatesize) {
                $this->result->yytranslatesize = $value + 1;
            }
        }

        $this->result->yytranslate = array_fill(0, $this->result->yytranslatesize, $ncterms);
        $this->result->yyncterms = $ncterms;


        for ($i = 0; $i < $this->context->nterminals; $i++) {
            $symbol = $this->context->symbol($i);
            $this->result->yytranslate[$symbol->value] = $this->context->ctermindex[$i];
        }

        $this->result->yyaction = $this->encode_shift_reduce($this->result->yyaction, count($this->result->yyaction));
        $this->result->yygoto = $this->encode_shift_reduce($this->result->yygoto, count($this->result->yygoto));
        $this->result->yygdefault = $this->encode_shift_reduce($this->result->yygdefault, $this->context->nnonterminals);
    }

    protected function pack_table(array $transit, int $nrows, int $ncols, bool $checkrow, array &$outtable, array &$outcheck, array &$outbase)
    {
        $trow = [];
        for ($i = 0; $i < $nrows; $i++) {
            $trow[] = $p = new TRow($i);
            for ($j = 0; $j < $ncols; $j++) {
                if (self::VACANT !== $transit[$i][$j]) {
                    if ($p->mini < 0) {
                        $p->mini = $j;
                    }
                    $p->maxi = $j + 1;
                    $p->nent++;
                }
            }
            if ($p->mini < 0) {
                $p->mini = 0;
            }
        }

        stable_sort($trow, [TRow::class, 'compare']);

        if ($this->context->verboseDebug) {
            $this->context->debug("Order:\n");
            for ($i = 0; $i < $nrows; $i++) {
                $this->context->debug(sprintf("%d,", $trow[$i]->index));
            }
            $this->context->debug("\n");
        }

        $poolsize = $nrows * $ncols;
        $actpool = array_fill(0, $poolsize, 0);
        $check = array_fill(0, $poolsize, -1);
        $base = array_fill(0, $nrows, 0);
        $handledBases = [];
        $actpoolmax = 0;

        for ($ii = 0; $ii < $nrows; $ii++) {
            $i = $trow[$ii]->index;
            if (vacant_row($transit[$i], $ncols)) {
                $base[$i] = 0;
                goto ok;
            }
            for ($h = 0; $h < $ii; $h++) {
                if (eq_row($transit[$trow[$h]->index], $transit[$i], $ncols)) {
                    $base[$i] = $base[$trow[$h]->index];
                    goto ok;
                }
            }
            for ($j = 0; $j < $poolsize; $j++) {
                $jj = $j;
                $base[$i] = $j - $trow[$ii]->mini;
                if ($base[$i] === 0) {
                    continue;
                }
                if (isset($handledBases[$base[$i]])) {
                    continue;
                }

                for ($k = $trow[$ii]->mini; $k < $trow[$ii]->maxi; $k++) {
                    if (self::VACANT !== $transit[$i][$k]) {
                        if ($jj >= $poolsize) {
                            die("Can't happen");
                        }
                        if ($check[$jj] >= 0) {
                            goto next;
                        }
                    }
                    $jj++;
                }
                break;
                next:;
            }

            $handledBases[$base[$i]] = true;
            $jj = $j;
            for ($k = $trow[$ii]->mini; $k < $trow[$ii]->maxi; $k++) {
                if (self::VACANT !== $transit[$i][$k]) {
                    $actpool[$jj] = $transit[$i][$k];
                    $check[$jj] = $checkrow ? $i : $k;
                }
                $jj++;
            }
            if ($jj >= $actpoolmax) {
                $actpoolmax = $jj;
            }
            ok:;
        }

        $outtable = array_slice($actpool, 0, $actpoolmax);
        $outcheck = array_slice($check, 0, $actpoolmax);
        $outbase = $base;
    }

    public function encode_rederr(int $code): int
    {
        return $code < 0 ? self::YYUNEXPECTED : $code;
    }

    public function resetFrequency()
    {
        $this->context->frequency = array_fill(0, $this->context->nstates, 0);
    }

    public function cmp_states(int $x, int $y): int
    {
        for ($i = 0; $i < $this->context->nterminals; $i++) {
            if ($this->context->term_action[$x][$i] != $this->context->term_action[$y][$i]) {
                return $this->context->term_action[$x][$i] - $this->context->term_action[$y][$i];
            }
        }
        return 0;
    }

    private function nb(Symbol $symbol)
    {
        if ($symbol->isterminal) {
            return $symbol->code;
        } else {
            return $symbol->code - $this->context->nterminals;
        }
    }
}
