<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use function PhpYacc\is_sym_character;
use function PhpYacc\is_white;
use PhpYacc\Exception\LexingException;
use PhpYacc\Exception\ParseException;

const EOF = "EOF";
const MAXTOKEN = 50000;

class Lexer
{
    const SPACE_TOKENS = [
        Token::SPACE,
        Token::COMMENT,
        Token::NEWLINE,
    ];

    const TAG_MAP = [
        "%%"            => Token::MARK,
        "%{"            => Token::BEGININC,
        "%}"            => Token::ENDINC,
        "%token"        => Token::TOKEN,
        "%term"         => Token::TOKEN,
        "%left"         => Token::LEFT,
        "%right"        => Token::RIGHT,
        "%nonassoc"     => Token::NONASSOC,
        "%prec"         => Token::PRECTOK,
        "%type"         => Token::TYPE,
        "%union"        => Token::UNION,
        "%start"        => Token::START,
        "%expect"       => Token::EXPECT,
        "%pure_parser"  => Token::PURE_PARSER,
    ];
    

    protected $backToken = null;
    protected $token = null;
    protected $prevIsDollar = false;

    protected $filename;
    protected $lineNumber = 0;

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function peek(): Token
    {
        $result = $this->get();
        $this->unget();
        return $result;
    }

    public function get(): Token
    {
        $this->token = $this->rawGet();
        while (in_array($this->token->t, self::SPACE_TOKENS)) {
            $this->token = $this->rawGet();
        }
        return $this->token;
    }

    public function unget()
    {
        if ($this->backToken) {
            throw new LexingException("Too many ungetToken calls");
        }
        $this->backToken = $this->token;
    }

    public function rawGet(): Token
    {
        if ($this->backToken) {
            $this->token = $this->backToken;
            $this->backToken = null;
            return $this->token;
        }
        $c = $this->getc();
        $p = '';
        if (is_white($c)) {
            while (is_white($c)) {
                $p .= $c;
                $c = $this->getc();
            }
            $this->ungetc($c);
            return $this->token(Token::SPACE, $p);
        }
        if ($c === "\n") {
            $this->lineNumber++;
            return $this->token(Token::NEWLINE, $c);
        }
        if ($c === "/") {
            if (($c = $this->getc()) === '*') {
                // skip comments
                $p = "/*";
                while (true) {
                    if (($c = $this->getc()) === '*') {
                        if (($c = $this->getc()) === '/') {
                            break;
                        }
                        $this->ungetc($c);
                    }
                    if ($c === EOF) {
                        throw ParseException::unexpected($this->token(EOF, ''), "*/");
                    }
                    $p .= $c;
                }
                $p .= "*/";
                return $this->token(Token::COMMENT, $p);
            } elseif ($c === '/') {
                // skip // comment
                $p = '//';
                do {
                    $c = $this->getc();
                    if ($c !== EOF) {
                        $p .= $c;
                    }
                } while ($c !== "\n" && $c !== EOF);
                return $this->token(Token::COMMENT, $p);
            }
        }
        if ($c === EOF) {
            return $this->token(EOF, '');
        }

        $tag = $c;
        if ($c === '%') {
            $c = $this->getc();
            if ($c === '%' || $c === '{' | $c === '}' || is_sym_character($c)) {
                $p .= "%";
            } else {
                $this->ungetc($c);
                $c = '%';
            }
        }

        if ($c === '$') {
            if (!$this->prevIsDollar) {
                $p .= '$';
                $c = $this->getc();
                if ($c === '$') {
                    $this->ungetc($c);
                    $this->prevIsDollar = true;
                } elseif (!ctype_digit($c) && is_sym_character($c)) {
                    do {
                        $p .= $c;
                        $c = $this->getc();
                    } while (is_sym_character($c));
                    $this->ungetc($c);
                    $tag = Token::NAME;
                } else {
                    $this->ungetc($c);
                }
            } else {
                $p .= '$';
                $this->prevIsDollar = false;
            }
        } elseif (is_sym_character($c)) {
            do {
                $p .= $c;
                $c = $this->getc();
            } while ($c !== EOF && is_sym_character($c));
            $this->ungetc($c);
            $tag = ctype_digit($p) ? Token::NUMBER : Token::NAME;
        } elseif ($c === '\'' || $c === '"') {
            $p .= $c;
            while (($c = $this->getc()) !== $tag) {
                if ($c === EOF) {
                    throw ParseException::unexpected($this->token("EOF", ''), $tag);
                }
                if ($c === "\n") {
                    throw ParseException::unexpected($this->token(Token::NEWLINE, "\n"), $tag);
                }
                $p .= $c;
                if ($c === '\\') {
                    $c = $this->getc();
                    if ($c === EOF) {
                        break;
                    }
                    if ($c === "\n") {
                        continue;
                    }
                    $p .= $c;
                }
            }
            $p .= $c;
        } else {
            $p .= $c;
        }

        if (isset(self::TAG_MAP[$p])) {
            $tag = self::TAG_MAP[$p];
        }
        return $this->token($tag, $p);
    }

    protected function token($id, $value): Token
    {
        return new Token($id, $value, $this->lineNumber, $this->filename);
    }

    

    protected $buffer = '';
    protected $bufferOffset = 0;
    protected $backChar = null;

    public function startLexing(string $code, string $filename)
    {
        $this->filename = $filename;
        $this->buffer = $code;
        $this->bufferOffset = 0;
        $this->backChar = null;
        $this->backToken = null;
        $this->token = null;
        $this->prevIsDollar = false;
    }

    protected function getc(): string
    {
        if (null !== $this->backChar) {
            $result = $this->backChar;
            $this->backChar = null;
            return $result;
        }
        if ($this->bufferOffset >= strlen($this->buffer)) {
            return EOF;
        }
        return $this->buffer[$this->bufferOffset++];
    }

    protected function ungetc(string $c)
    {
        if ($c === EOF) {
            return;
        }
        if ($this->backChar !== null) {
            throw new LexingException("To many unget calls");
        }
        $this->backChar = $c;
    }
}
