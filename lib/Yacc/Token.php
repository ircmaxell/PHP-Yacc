<?php

declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Exception\LexingException;

class Token
{
    public const NAME        = 0x0200;
    public const NUMBER      = 0x0201;
    public const COLON       = ':';
    public const SPACE       = ' ';
    public const NEWLINE     = '\n';
    public const MARK        = 0x0100;
    public const BEGININC    = 0x0101;
    public const ENDINC      = 0x0102;
    public const TOKEN       = 0x0103;
    public const LEFT        = 0x0104;
    public const RIGHT       = 0x0105;
    public const NONASSOC    = 0x0106;
    public const PRECTOK     = 0x0107;
    public const TYPE        = 0x0108;
    public const UNION       = 0x0109;
    public const START       = 0x010a;
    public const COMMENT     = 0x010b;

    public const EXPECT      = 0x010c;
    public const PURE_PARSER = 0x010d;

    public const TOKEN_MAP = [
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
            throw new LexingException("Unknown token found: $token");
        }
        $this->t = $token;
        $this->v = $value;
        $this->ln = $lineNumber;
        $this->fn = $filename;
    }

    public static function decode($tag): string
    {
        if (!isset(self::TOKEN_MAP[$tag])) {
            return "$tag";
        }
        return "Token::" . self::TOKEN_MAP[$tag];
    }

    public function __toString(): string
    {
        return "[{$this->fn}:{$this->ln}] " . self::decode($this->t) . " ({$this->v})";
    }
}
