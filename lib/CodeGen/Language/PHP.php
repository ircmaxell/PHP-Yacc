<?php
/**
 * Created by PhpStorm.
 * User: ircmaxell
 * Date: 10/10/17
 * Time: 3:44 PM
 */

namespace PhpYacc\CodeGen\Language;

use PhpYacc\CodeGen\Language;

class PHP implements Language
{

    protected $fp;
    protected $hp;

    protected $fileBuffer = '';
    protected $headerBuffer = '';

    public function begin($file, $headerFile)
    {
        $this->fp = $file;
        $this->hp = $headerFile;
        $this->fileBuffer = '';
        $this->headerBuffer = '';
    }

    public function commit()
    {
        fwrite($this->fp, $this->fileBuffer);
        fwrite($this->hp, $this->headerBuffer);
        $this->fp = $this->hp = null;
        $this->fileBuffer = '';
        $this->headerBuffer = '';
    }

    public function inline_comment(string $text)
    {
        $this->fileBuffer .= '/* ' . $text . " */";
    }

    public function comment(string $text)
    {
        $this->fileBuffer .= '//' . $text . "\n";
    }

    public function case_block(string $indent, int $num, string $value)
    {
        $this->fileBuffer .= sprintf("%scase %d: return %s;\n", $indent, $num, var_export($value, true));
    }

    public function write(string $text, bool $includeHeader = false)
    {
        $this->fileBuffer .= $text;
        if ($includeHeader) {
            $this->headerBuffer .= $text;
        }
    }

    public function writeQuoted(string $text)
    {
        $regex = '(\\$(?=[a-zA-Z_])|")';
        $text = preg_replace($regex, "\\\\$0", $text);
        $this->fileBuffer .= $text;
    }
}
