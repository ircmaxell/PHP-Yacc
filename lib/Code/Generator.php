<?php
declare(strict_types=1);

namespace PhpYacc\Code;

use PhpYacc\Grammar\Context;

class Generator
{
    protected $invalidSymbol = 0;
    protected $errorSymbol = 0;

    public function generate(string $className, Context $context): string
    {
        $compress = new Compress;
        $r = $compress->compress($context);




        $template = "<?php\ndeclare(strict_types=1);\n";
        $parts = explode('\\', $className);
        $class = array_pop($parts);
        if (!empty($parts)) {
            $template .= "namespace " . implode('\\', $parts) . ";\n";
        }
        $template .= "use PhpYacc\\Lexer;\n";

        $template .= "class $className {\n";
        $template .= "    const SYMBOL_NONE = -1;\n";
        $template .= "    protected \$unexpectedTokenRule = " . $r::YYUNEXPECTED . ";\n";
        $template .= "    protected \$defaultAction = " . $r::YYDEFAULT . ";\n";
        $template .= $this->handleInvalidSymbol($context);

        $template .= "    protected \$YYNLSTATES = {$context->nnonleafstates};\n";
        $template .= "    protected \$YY2TBLSTATE = " . (count($r->yybase) - $context->nnonleafstates) . ";\n";
        $template .= "    protected \$actionTableSize = " . (count($r->yyaction)) . ";\n";
        $template .= "    protected \$gotoTableSize = " . (count($r->yygoto)) . ";\n";
        $template .= "    protected \$tokenToSymbolMapSize = " . (max(255, max(array_keys($r->yytranslate))) + 1) . ";\n";

        $template .= $this->handleTerminals($context);
        
        foreach ($context->symbols as $symbol) {
            if ($symbol->name === "error") {
                $template .= '    protected $errorSymbol = ' . $symbol->code . ";\n";
                $this->errorSymbol = $symbol->code;
            }
        }

        $template .= '    protected $tokenToSymbol = ' . $this->packYYTranslate($r->yytranslate) . ";\n";
        $template .= '    protected $action = ' . $this->printArray($r->yyaction) . ";\n";
        $template .= '    protected $actionCheck = ' . $this->printArray($r->yycheck) . ";\n";
        $template .= '    protected $actionBase = ' . $this->printArray($r->yybase) . ";\n";
        $template .= '    protected $actionDefault = ' . $this->printArray($r->yydefault, $context->nstates) . ";\n";



        $template .= '    protected $goto = ' . $this->printArray($r->yygoto) . ";\n";

        $template .= '    protected $gotoCheck = ' . $this->printArray($r->yygbase) . ";\n";

        $template .= '    protected $gotoBase = ' . $this->printArray($r->yygbase) . ";\n";

        $template .= '    protected $gotoDefault = ' . $this->printArray($r->yygdefault) . ";\n";
  
        $template .= '    protected $ruleToNonTerminal = ' . $this->printArray($r->yylhs) . ";\n";

        $template .= '    protected $ruleToLength = ' . $this->printArray($r->yylen) . ";\n";


        $template .= $this->handleProductionString($context);


        $template .= $this->buildReduce($context);

        $template .= "}";
        return $template;
    }

    protected function handleInvalidSymbol(Context $context): string
    {
        $max = 0;
        foreach ($context->terminals as $term) {
            $max = max($max, $term->code);
        }
        $this->invalidSymbol = $max + 1;
        return "    protected \$invalidSymbol = {$this->invalidSymbol};\n";
    }

    protected function packYYTranslate(array $translate): string
    {
        $result = array_fill(0, 256, $this->invalidSymbol);
        foreach ($translate as $key => $value) {
            $result[$key] = $value;
        }
        
        return $this->printArray($result);
    }

    protected function printArray(array $array, int $limit = -1): string
    {
        $return = "[";
        if ($limit !== -1) {
            $array = array_slice($array, 0, $limit - 1);
        }
        foreach ($array as $key => $value) {
            if ($key % 10 === 0) {
                $return .= "\n        ";
            }
            $return .= sprintf("%5d,", $value);
        }
        return $return . "\n    ]";
    }

    protected function handleTerminals(Context $context): string
    {
        $result = '';
        foreach ($context->terminals as $term) {
            $result .= "        " . var_export($term->name, true) . ",\n";
        }
        return "    protected \$symbolToName = [\n" . $result . "    ];\n";
    }

    protected function handleProductionString(Context $context): string
    {
        $result = '';

        foreach ($context->grams as $gram) {
            $name = "";
            $sep = '';
            for ($i = 1; $i < count($gram->body); $i++) {
                $name .= $sep . $gram->body[$i]->name;
                $sep = ' ';
            }
            $result .= "        " . var_export("{$gram->body[0]->name} : $name", true) . ",\n";
        }
        return "   protected \$productions = [\n" . $result . "    ];\n";
    }

    protected function buildReduce(Context $context): string
    {
        $result = '    protected function initReduceCallbacks() {' . "\n";
        $result .= '        $this->reduceCallbacks = [' . "\n";
        foreach ($context->grams as $gram) {
            $action = trim($gram->action ?: '$this->semValue = $this->semStack[$stackPos];');
            $result .= "            {$gram->num} => function(\$stackPos) {\n";
            $result .= "                {$action}\n";
            $result .= "            },\n";
        }
        $result .= "        ];\n    }\n";
        return $result;
    }
}
