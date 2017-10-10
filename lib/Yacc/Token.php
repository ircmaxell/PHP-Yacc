<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

class Token
{
    const NAME        = 0x0200;
    const NUMBER      = 0x0201;
    const SPACE       = ' ';
    const NEWLINE     = '\n';
    const MARK        = 0x0100;
    const BEGININC    = 0x0101;
    const ENDINC      = 0x0102;
    const TOKEN       = 0x0103;
    const LEFT        = 0x0104;
    const RIGHT       = 0x0105;
    const NONASSOC    = 0x0106;
    const PRECTOK     = 0x0107;
    const TYPE        = 0x0108;
    const UNION       = 0x0109;
    const START       = 0x010a;
    const COMMENT     = 0x010b;

    const EXPECT      = 0x010c;
    const PURE_PARSER = 0x010d;

    public $t;
    public $v;
    public $ln;
    public $fn;
    public function __construct($token, string $value, int $lineNumber, string $filename)
    {
        $this->t = $token;
        $this->v = $value;
        $this->ln = $lineNumber;
        $this->fn = $filename;
    }

    public function __toString(): string
    {
        return "[{$this->fn}:{$this->ln}] {$this->t} ({$this->v})";
    }
}
