<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Lexer as CoreLexer;
use PhpYacc\Token;
use RuntimeException;

require_once __DIR__ . '/functions.php';

const EOF = "EOF";
const MAXTOKEN = 50000;

class Lexer implements CoreLexer {

    const SPACE_TOKENS = [
        Tokens::SPACE, 
        Tokens::COMMENT, 
        Tokens::NEWLINE,
    ];

    const TAG_MAP = [
        "%%"            => Tokens::MARK,
        "%{"            => Tokens::BEGININC,
        "%}"            => Tokens::ENDINC,
        "%token"        => Tokens::TOKEN,
        "%term"         => Tokens::TOKEN,
        "%left"         => Tokens::LEFT,
        "%right"        => Tokens::RIGHT,
        "%nonassoc"     => Tokens::NONASSOC,
        "%prec"         => Tokens::PRECTOK,
        "%type"         => Tokens::TYPE,
        "%union"        => Tokens::UNION,
        "%start"        => Tokens::START,
        "%expect"       => Tokens::EXPECT,
        "%pure_parser"  => Tokens::PURE_PARSER,
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
          throw new RuntimeException("Too many ungetToken calls");
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
        if (isWhite($c)) {
            while (isWhite($c)) {
                $p .= $c;
                $c = $this->getc();
            }
            $this->ungetc($c);
            return $this->token(Tokens::SPACE, $p);
        }
        if ($c === "\n") {
            $this->lineNumber++;
            return $this->token(Tokens::NEWLINE, $c);
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
                        throw new RuntimeException("Missing */");
                    }
                    $p .= $c;
                }
                $p .= "*/";
                return $this->token(Tokens::COMMENT, $p);
            } elseif ($c === '/') {
                // skip // comment
                $p = '//';
                do {
                    $c = $this->getc();
                    if ($c !== EOF) { 
                        $p .= $c;
                      }
                } while ($c !== "\n" && $c !== EOF);
                return $this->token(Tokens::COMMENT, $p);
            }
        }
        if ($c === EOF) {
            return $this->token(EOF, '');
        }

        $tag = $c;
        if ($c === '%') {
            $c = $this->getc();
            if ($c === '%' || $c === '{' | $c === '}' || isSymCh($c)) {
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
                } elseif (!ctype_digit($c) && isSymCh($c)) {
                    do {
                        $p .= $c;
                        $c = $this->getc();
                    } while (isSymCh($c));
                    $this->ungetc($c);
                    $tag = Tokens::NAME;
                } else {
                    $this->ungetc($c);
                }
            } else {
                $p .= '$';
                $this->prevIsDollar = false;
            }
        } elseif (isSymCh($c)) {
            do {
                $p .= $c;
                $c = $this->getc();
            } while ($c !== EOF && isSymCh($c));
            $this->ungetc($c);
            $tag = ctype_digit($p) ? Tokens::NUMBER : Tokens::NAME;
        } elseif ($c === '\'' || $c === '"') {
            $p .= $c;
            while (($c = $this->getc()) !== $tag) {
                if ($c === EOF || $c === "\n") {
                    throw new RuntimeException("Missing '");
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
            throw new RuntimeException("To many unget calls");
        }
        $this->backChar = $c;
    }

}
