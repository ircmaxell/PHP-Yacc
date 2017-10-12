<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

class Token
{
    const NAME        = 0x0200;
    const NUMBER      = 0x0201;
    const COLON       = ':';
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

    const TOKEN_MAP = [
        self::NAME        => "NAME",
        self::NUMBER      => "NUMBER",
        self::COLON       => 'COLON',
        self::SPACE       => 'SPACE',
        self::NEWLINE     => 'NEWLINE',
        self::MARK        => 'MARK',
        self::BEGININC    => 'BEGININC',
        self::ENDINC      => 'ENDINC',
        self::TOKEN       => 'TOKEN',
        self::LEFT        => 'LEFT',
        self::RIGHT       => 'RIGHT',
        self::NONASSOC    => 'NONASSOC',
        self::PRECTOK     => 'PRECTOK',
        self::TYPE        => 'TYPE',
        self::UNION       => 'UNION',
        self::START       => 'START',
        self::COMMENT     => 'COMMENT',
        self::EXPECT      => 'EXPECT',
        self::PURE_PARSER => 'PURE_PARSER',
    ];

    public $t;
    public $v;
    public $ln;
    public $fn;
    public function __construct($token, string $value, int $lineNumber, string $filename)
    {
        if (!isset(self::TOKEN_MAP[$token]) && !is_string($token)) {
            throw new \RuntimeException("Unknown token found: $token");
        }
        $this->t = $token;
        $this->v = $value;
        $this->ln = $lineNumber;
        $this->fn = $filename;
    }

    public function __toString(): string
    {
        if (!isset(self::TOKEN_MAP[$this->t])) {
            return "[{$this->fn}:{$this->ln}] {$this->t} ({$this->v})";
        }
        return "[{$this->fn}:{$this->ln}] Token::" . self::TOKEN_MAP[$this->t] . " ({$this->v})";
    }
}
