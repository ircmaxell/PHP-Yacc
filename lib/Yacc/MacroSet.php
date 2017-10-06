<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\MacroSet as CoreMacroSet;

class MacroSet extends CoreMacroSet {


    public function __construct(Macro ...$macros)
    {
        $this->addMacro(new Macro\DollarExpansion);
        parent::__construct(...$macros);
        
    }

}