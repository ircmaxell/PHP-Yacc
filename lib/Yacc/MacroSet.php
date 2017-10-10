<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Grammar\Context;
use ArrayIterator;
use Traversable;

class MacroSet
{

    protected $macros = [];

    public function __construct(MacroAbstract ...$macros)
    {
        $this->addMacro(new Macro\DollarExpansion);
        $this->addMacro(...$macros);
    }

    public function addMacro(MacroAbstract ...$macros)
    {
        foreach ($macros as $macro) {
            $this->macros[] = $macro;
        }
    }

    public function apply(Context $ctx, array $symbols, array $tokens, int $n, array $attribute): array
    {
        $tokens = new ArrayIterator($tokens);
        $macroCount = count($this->macros);
        if ($macroCount === 1) {
            // special case
            return iterator_to_array($this->macros[0]->apply($ctx, $symbols, $tokens, $n, $attribute));
        }
        foreach ($this->macros as $macro) {
            $tokens = $macro->apply($ctx, $symbols, $tokens, $n, $attribute);
        }
        $tokens = self::cache($tokens);

        return iterator_to_array($tokens);
    }

    public static function cache(Traversable $t): Traversable
    {
        return new ArrayIterator(iterator_to_array($t));
    }
}
