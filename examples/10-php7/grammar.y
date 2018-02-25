%pure_parser
%expect 2

/* We currently rely on the token ID mapping to be the same between PHP 5 and PHP 7 - so the same lexer can be used for
 * both. This is enforced by sharing this token file. */

%left T_INCLUDE T_INCLUDE_ONCE T_EVAL T_REQUIRE T_REQUIRE_ONCE
%left ','
%left T_LOGICAL_OR
%left T_LOGICAL_XOR
%left T_LOGICAL_AND
%right T_PRINT
%right T_YIELD
%right T_DOUBLE_ARROW
%right T_YIELD_FROM
%left '=' T_PLUS_EQUAL T_MINUS_EQUAL T_MUL_EQUAL T_DIV_EQUAL T_CONCAT_EQUAL T_MOD_EQUAL T_AND_EQUAL T_OR_EQUAL T_XOR_EQUAL T_SL_EQUAL T_SR_EQUAL T_POW_EQUAL
%left '?' ':'
%right T_COALESCE
%left T_BOOLEAN_OR
%left T_BOOLEAN_AND
%left '|'
%left '^'
%left '&'
%nonassoc T_IS_EQUAL T_IS_NOT_EQUAL T_IS_IDENTICAL T_IS_NOT_IDENTICAL T_SPACESHIP
%nonassoc '<' T_IS_SMALLER_OR_EQUAL '>' T_IS_GREATER_OR_EQUAL
%left T_SL T_SR
%left '+' '-' '.'
%left '*' '/' '%'
%right '!'
%nonassoc T_INSTANCEOF
%right '~' T_INC T_DEC T_INT_CAST T_DOUBLE_CAST T_STRING_CAST T_ARRAY_CAST T_OBJECT_CAST T_BOOL_CAST T_UNSET_CAST '@'
%right T_POW
%right '['
%nonassoc T_NEW T_CLONE
%token T_EXIT
%token T_IF
%left T_ELSEIF
%left T_ELSE
%left T_ENDIF
%token T_LNUMBER
%token T_DNUMBER
%token T_STRING
%token T_STRING_VARNAME
%token T_VARIABLE
%token T_NUM_STRING
%token T_INLINE_HTML
%token T_CHARACTER
%token T_BAD_CHARACTER
%token T_ENCAPSED_AND_WHITESPACE
%token T_CONSTANT_ENCAPSED_STRING
%token T_ECHO
%token T_DO
%token T_WHILE
%token T_ENDWHILE
%token T_FOR
%token T_ENDFOR
%token T_FOREACH
%token T_ENDFOREACH
%token T_DECLARE
%token T_ENDDECLARE
%token T_AS
%token T_SWITCH
%token T_ENDSWITCH
%token T_CASE
%token T_DEFAULT
%token T_BREAK
%token T_CONTINUE
%token T_GOTO
%token T_FUNCTION
%token T_CONST
%token T_RETURN
%token T_TRY
%token T_CATCH
%token T_FINALLY
%token T_THROW
%token T_USE
%token T_INSTEADOF
%token T_GLOBAL
%right T_STATIC T_ABSTRACT T_FINAL T_PRIVATE T_PROTECTED T_PUBLIC
%token T_VAR
%token T_UNSET
%token T_ISSET
%token T_EMPTY
%token T_HALT_COMPILER
%token T_CLASS
%token T_TRAIT
%token T_INTERFACE
%token T_EXTENDS
%token T_IMPLEMENTS
%token T_OBJECT_OPERATOR
%token T_DOUBLE_ARROW
%token T_LIST
%token T_ARRAY
%token T_CALLABLE
%token T_CLASS_C
%token T_TRAIT_C
%token T_METHOD_C
%token T_FUNC_C
%token T_LINE
%token T_FILE
%token T_COMMENT
%token T_DOC_COMMENT
%token T_OPEN_TAG
%token T_OPEN_TAG_WITH_ECHO
%token T_CLOSE_TAG
%token T_WHITESPACE
%token T_START_HEREDOC
%token T_END_HEREDOC
%token T_DOLLAR_OPEN_CURLY_BRACES
%token T_CURLY_OPEN
%token T_PAAMAYIM_NEKUDOTAYIM
%token T_NAMESPACE
%token T_NS_C
%token T_DIR
%token T_NS_SEPARATOR
%token T_ELLIPSIS


%%

start:
    top_statement_list                                      { $$ = $this->handleNamespaces($this->semStack[$1]); }
;

top_statement_list_ex:
      top_statement_list_ex top_statement                   { if (is_array($this->semStack[$2])) { $$ = array_merge($this->semStack[$1], $this->semStack[$2]); } else { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }; }
    | /* empty */                                           { $$ = array(); }
;

top_statement_list:
      top_statement_list_ex
          { $startAttributes = $this->lookaheadStartAttributes; if (isset($startAttributes['comments'])) { $nop = new Stmt\Nop(['comments' => $startAttributes['comments']]); } else { $nop = null; };
            if ($nop !== null) { $this->semStack[$1][] = $nop; } $$ = $this->semStack[$1]; }
;

reserved_non_modifiers:
      T_INCLUDE | T_INCLUDE_ONCE | T_EVAL | T_REQUIRE | T_REQUIRE_ONCE | T_LOGICAL_OR | T_LOGICAL_XOR | T_LOGICAL_AND
    | T_INSTANCEOF | T_NEW | T_CLONE | T_EXIT | T_IF | T_ELSEIF | T_ELSE | T_ENDIF | T_ECHO | T_DO | T_WHILE
    | T_ENDWHILE | T_FOR | T_ENDFOR | T_FOREACH | T_ENDFOREACH | T_DECLARE | T_ENDDECLARE | T_AS | T_TRY | T_CATCH
    | T_FINALLY | T_THROW | T_USE | T_INSTEADOF | T_GLOBAL | T_VAR | T_UNSET | T_ISSET | T_EMPTY | T_CONTINUE | T_GOTO
    | T_FUNCTION | T_CONST | T_RETURN | T_PRINT | T_YIELD | T_LIST | T_SWITCH | T_ENDSWITCH | T_CASE | T_DEFAULT
    | T_BREAK | T_ARRAY | T_CALLABLE | T_EXTENDS | T_IMPLEMENTS | T_NAMESPACE | T_TRAIT | T_INTERFACE | T_CLASS
    | T_CLASS_C | T_TRAIT_C | T_FUNC_C | T_METHOD_C | T_LINE | T_FILE | T_DIR | T_NS_C | T_HALT_COMPILER
;

semi_reserved:
      reserved_non_modifiers
    | T_STATIC | T_ABSTRACT | T_FINAL | T_PRIVATE | T_PROTECTED | T_PUBLIC
;

identifier:
      T_STRING                                              { $$ = $this->semStack[$1]; }
    | semi_reserved                                         { $$ = $this->semStack[$1]; }
;

namespace_name_parts:
      T_STRING                                              { $$ = array($this->semStack[$1]); }
    | namespace_name_parts T_NS_SEPARATOR T_STRING          { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

namespace_name:
      namespace_name_parts                                  { $$ = new Name($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
;

semi:
      ';'                                                   { /* nothing */ }
    | error                                                 { /* nothing */ }
;

no_comma:
      /* empty */ { /* nothing */ }
    | ',' { $this->emitError(new Error('A trailing comma is not allowed here', $this->startAttributeStack[$1] + $this->endAttributes)); }
;

optional_comma:
      /* empty */
    | ','

top_statement:
      statement                                             { $$ = $this->semStack[$1]; }
    | function_declaration_statement                        { $$ = $this->semStack[$1]; }
    | class_declaration_statement                           { $$ = $this->semStack[$1]; }
    | T_HALT_COMPILER
          { $$ = new Stmt\HaltCompiler($this->lexer->handleHaltCompiler(), $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_NAMESPACE namespace_name semi
          { $$ = new Stmt\Namespace_($this->semStack[$2], null, $this->startAttributeStack[$1] + $this->endAttributes);
            $$->setAttribute('kind', Stmt\Namespace_::KIND_SEMICOLON);
            $this->checkNamespace($$); }
    | T_NAMESPACE namespace_name '{' top_statement_list '}'
          { $$ = new Stmt\Namespace_($this->semStack[$2], $this->semStack[$4], $this->startAttributeStack[$1] + $this->endAttributes);
            $$->setAttribute('kind', Stmt\Namespace_::KIND_BRACED);
            $this->checkNamespace($$); }
    | T_NAMESPACE '{' top_statement_list '}'
          { $$ = new Stmt\Namespace_(null, $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes);
            $$->setAttribute('kind', Stmt\Namespace_::KIND_BRACED);
            $this->checkNamespace($$); }
    | T_USE use_declarations semi                           { $$ = new Stmt\Use_($this->semStack[$2], Stmt\Use_::TYPE_NORMAL, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_USE use_type use_declarations semi                  { $$ = new Stmt\Use_($this->semStack[$3], $this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | group_use_declaration semi                            { $$ = $this->semStack[$1]; }
    | T_CONST constant_declaration_list semi                { $$ = new Stmt\Const_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
;

use_type:
      T_FUNCTION                                            { $$ = Stmt\Use_::TYPE_FUNCTION; }
    | T_CONST                                               { $$ = Stmt\Use_::TYPE_CONSTANT; }
;

/* Using namespace_name_parts here to avoid s/r conflict on T_NS_SEPARATOR */
group_use_declaration:
      T_USE use_type namespace_name_parts T_NS_SEPARATOR '{' unprefixed_use_declarations '}'
          { $$ = new Stmt\GroupUse(new Name($this->semStack[$3], $this->startAttributeStack[$3] + $this->endAttributeStack[$3]), $this->semStack[$6], $this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_USE use_type T_NS_SEPARATOR namespace_name_parts T_NS_SEPARATOR '{' unprefixed_use_declarations '}'
          { $$ = new Stmt\GroupUse(new Name($this->semStack[$4], $this->startAttributeStack[$4] + $this->endAttributeStack[$4]), $this->semStack[$7], $this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_USE namespace_name_parts T_NS_SEPARATOR '{' inline_use_declarations '}'
          { $$ = new Stmt\GroupUse(new Name($this->semStack[$2], $this->startAttributeStack[$2] + $this->endAttributeStack[$2]), $this->semStack[$5], Stmt\Use_::TYPE_UNKNOWN, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_USE T_NS_SEPARATOR namespace_name_parts T_NS_SEPARATOR '{' inline_use_declarations '}'
          { $$ = new Stmt\GroupUse(new Name($this->semStack[$3], $this->startAttributeStack[$3] + $this->endAttributeStack[$3]), $this->semStack[$6], Stmt\Use_::TYPE_UNKNOWN, $this->startAttributeStack[$1] + $this->endAttributes); }
;

unprefixed_use_declarations:
      non_empty_unprefixed_use_declarations optional_comma  { $$ = $this->semStack[$1]; }
;

non_empty_unprefixed_use_declarations:
      non_empty_unprefixed_use_declarations ',' unprefixed_use_declaration
          { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | unprefixed_use_declaration                            { $$ = array($this->semStack[$1]); }
;

use_declarations:
      non_empty_use_declarations no_comma                   { $$ = $this->semStack[$1]; }
;

non_empty_use_declarations:
      non_empty_use_declarations ',' use_declaration        { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | use_declaration                                       { $$ = array($this->semStack[$1]); }
;

inline_use_declarations:
      non_empty_inline_use_declarations optional_comma      { $$ = $this->semStack[$1]; }
;

non_empty_inline_use_declarations:
      non_empty_inline_use_declarations ',' inline_use_declaration
          { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | inline_use_declaration                                { $$ = array($this->semStack[$1]); }
;

unprefixed_use_declaration:
      namespace_name
          { $$ = new Stmt\UseUse($this->semStack[$1], null, Stmt\Use_::TYPE_UNKNOWN, $this->startAttributeStack[$1] + $this->endAttributes); $this->checkUseUse($$, $1); }
    | namespace_name T_AS T_STRING
          { $$ = new Stmt\UseUse($this->semStack[$1], $this->semStack[$3], Stmt\Use_::TYPE_UNKNOWN, $this->startAttributeStack[$1] + $this->endAttributes); $this->checkUseUse($$, $3); }
;

use_declaration:
      unprefixed_use_declaration                            { $$ = $this->semStack[$1]; }
    | T_NS_SEPARATOR unprefixed_use_declaration             { $$ = $this->semStack[$2]; }
;

inline_use_declaration:
      unprefixed_use_declaration                            { $$ = $this->semStack[$1]; $$->type = Stmt\Use_::TYPE_NORMAL; }
    | use_type unprefixed_use_declaration                   { $$ = $this->semStack[$2]; $$->type = $this->semStack[$1]; }
;

constant_declaration_list:
      non_empty_constant_declaration_list no_comma          { $$ = $this->semStack[$1]; }
;

non_empty_constant_declaration_list:
      non_empty_constant_declaration_list ',' constant_declaration
          { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | constant_declaration                                  { $$ = array($this->semStack[$1]); }
;

constant_declaration:
    T_STRING '=' expr                                       { $$ = new Node\Const_($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

class_const_list:
      non_empty_class_const_list no_comma                   { $$ = $this->semStack[$1]; }
;

non_empty_class_const_list:
      non_empty_class_const_list ',' class_const            { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | class_const                                           { $$ = array($this->semStack[$1]); }
;

class_const:
    identifier '=' expr                                     { $$ = new Node\Const_($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

inner_statement_list_ex:
      inner_statement_list_ex inner_statement               { if (is_array($this->semStack[$2])) { $$ = array_merge($this->semStack[$1], $this->semStack[$2]); } else { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }; }
    | /* empty */                                           { $$ = array(); }
;

inner_statement_list:
      inner_statement_list_ex
          { $startAttributes = $this->lookaheadStartAttributes; if (isset($startAttributes['comments'])) { $nop = new Stmt\Nop(['comments' => $startAttributes['comments']]); } else { $nop = null; };
            if ($nop !== null) { $this->semStack[$1][] = $nop; } $$ = $this->semStack[$1]; }
;

inner_statement:
      statement                                             { $$ = $this->semStack[$1]; }
    | function_declaration_statement                        { $$ = $this->semStack[$1]; }
    | class_declaration_statement                           { $$ = $this->semStack[$1]; }
    | T_HALT_COMPILER
          { throw new Error('__HALT_COMPILER() can only be used from the outermost scope', $this->startAttributeStack[$1] + $this->endAttributes); }
;

non_empty_statement:
      '{' inner_statement_list '}'
    {
        if ($this->semStack[$2]) {
            $$ = $this->semStack[$2]; $attrs = $this->startAttributeStack[$1]; $stmts = $$; if (!empty($attrs['comments'])) {$stmts[0]->setAttribute('comments', array_merge($attrs['comments'], $stmts[0]->getAttribute('comments', []))); };
        } else {
            $startAttributes = $this->startAttributeStack[$1]; if (isset($startAttributes['comments'])) { $$ = new Stmt\Nop(['comments' => $startAttributes['comments']]); } else { $$ = null; };
            if (null === $$) { $$ = array(); }
        }
    }
    | T_IF '(' expr ')' statement elseif_list else_single
          { $$ = new Stmt\If_($this->semStack[$3], ['stmts' => is_array($this->semStack[$5]) ? $this->semStack[$5] : array($this->semStack[$5]), 'elseifs' => $this->semStack[$6], 'else' => $this->semStack[$7]], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_IF '(' expr ')' ':' inner_statement_list new_elseif_list new_else_single T_ENDIF ';'
          { $$ = new Stmt\If_($this->semStack[$3], ['stmts' => $this->semStack[$6], 'elseifs' => $this->semStack[$7], 'else' => $this->semStack[$8]], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_WHILE '(' expr ')' while_statement                  { $$ = new Stmt\While_($this->semStack[$3], $this->semStack[$5], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DO statement T_WHILE '(' expr ')' ';'               { $$ = new Stmt\Do_($this->semStack[$5], is_array($this->semStack[$2]) ? $this->semStack[$2] : array($this->semStack[$2]), $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_FOR '(' for_expr ';'  for_expr ';' for_expr ')' for_statement
          { $$ = new Stmt\For_(['init' => $this->semStack[$3], 'cond' => $this->semStack[$5], 'loop' => $this->semStack[$7], 'stmts' => $this->semStack[$9]], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_SWITCH '(' expr ')' switch_case_list                { $$ = new Stmt\Switch_($this->semStack[$3], $this->semStack[$5], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_BREAK optional_expr semi                            { $$ = new Stmt\Break_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_CONTINUE optional_expr semi                         { $$ = new Stmt\Continue_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_RETURN optional_expr semi                           { $$ = new Stmt\Return_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_GLOBAL global_var_list semi                         { $$ = new Stmt\Global_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_STATIC static_var_list semi                         { $$ = new Stmt\Static_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_ECHO expr_list semi                                 { $$ = new Stmt\Echo_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_INLINE_HTML                                         { $$ = new Stmt\InlineHTML($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr semi                                             { $$ = $this->semStack[$1]; }
    | T_UNSET '(' variables_list ')' semi                   { $$ = new Stmt\Unset_($this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_FOREACH '(' expr T_AS foreach_variable ')' foreach_statement
          { $$ = new Stmt\Foreach_($this->semStack[$3], $this->semStack[$5][0], ['keyVar' => null, 'byRef' => $this->semStack[$5][1], 'stmts' => $this->semStack[$7]], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_FOREACH '(' expr T_AS variable T_DOUBLE_ARROW foreach_variable ')' foreach_statement
          { $$ = new Stmt\Foreach_($this->semStack[$3], $this->semStack[$7][0], ['keyVar' => $this->semStack[$5], 'byRef' => $this->semStack[$7][1], 'stmts' => $this->semStack[$9]], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DECLARE '(' declare_list ')' declare_statement      { $$ = new Stmt\Declare_($this->semStack[$3], $this->semStack[$5], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_TRY '{' inner_statement_list '}' catches optional_finally
          { $$ = new Stmt\TryCatch($this->semStack[$3], $this->semStack[$5], $this->semStack[$6], $this->startAttributeStack[$1] + $this->endAttributes); $this->checkTryCatch($$); }
    | T_THROW expr semi                                     { $$ = new Stmt\Throw_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_GOTO T_STRING semi                                  { $$ = new Stmt\Goto_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_STRING ':'                                          { $$ = new Stmt\Label($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | error                                                 { $$ = array(); /* means: no statement */ }
;

statement:
      non_empty_statement                                   { $$ = $this->semStack[$1]; }
    | ';'
          { $startAttributes = $this->startAttributeStack[$1]; if (isset($startAttributes['comments'])) { $$ = new Stmt\Nop(['comments' => $startAttributes['comments']]); } else { $$ = null; };
            if ($$ === null) $$ = array(); /* means: no statement */ }
;

catches:
      /* empty */                                           { $$ = array(); }
    | catches catch                                         { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }
;

name_union:
      name                                                  { $$ = array($this->semStack[$1]); }
    | name_union '|' name                                   { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

catch:
    T_CATCH '(' name_union T_VARIABLE ')' '{' inner_statement_list '}'
        { $$ = new Stmt\Catch_($this->semStack[$3], substr($this->semStack[$4], 1), $this->semStack[$7], $this->startAttributeStack[$1] + $this->endAttributes); }
;

optional_finally:
      /* empty */                                           { $$ = null; }
    | T_FINALLY '{' inner_statement_list '}'                { $$ = new Stmt\Finally_($this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

variables_list:
      non_empty_variables_list no_comma                     { $$ = $this->semStack[$1]; }
;

non_empty_variables_list:
      variable                                              { $$ = array($this->semStack[$1]); }
    | non_empty_variables_list ',' variable                 { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

optional_ref:
      /* empty */                                           { $$ = false; }
    | '&'                                                   { $$ = true; }
;

optional_ellipsis:
      /* empty */                                           { $$ = false; }
    | T_ELLIPSIS                                            { $$ = true; }
;

function_declaration_statement:
    T_FUNCTION optional_ref T_STRING '(' parameter_list ')' optional_return_type '{' inner_statement_list '}'
        { $$ = new Stmt\Function_($this->semStack[$3], ['byRef' => $this->semStack[$2], 'params' => $this->semStack[$5], 'returnType' => $this->semStack[$7], 'stmts' => $this->semStack[$9]], $this->startAttributeStack[$1] + $this->endAttributes); }
;

class_declaration_statement:
      class_entry_type T_STRING extends_from implements_list '{' class_statement_list '}'
          { $$ = new Stmt\Class_($this->semStack[$2], ['type' => $this->semStack[$1], 'extends' => $this->semStack[$3], 'implements' => $this->semStack[$4], 'stmts' => $this->semStack[$6]], $this->startAttributeStack[$1] + $this->endAttributes);
            $this->checkClass($$, $2); }
    | T_INTERFACE T_STRING interface_extends_list '{' class_statement_list '}'
          { $$ = new Stmt\Interface_($this->semStack[$2], ['extends' => $this->semStack[$3], 'stmts' => $this->semStack[$5]], $this->startAttributeStack[$1] + $this->endAttributes);
            $this->checkInterface($$, $2); }
    | T_TRAIT T_STRING '{' class_statement_list '}'
          { $$ = new Stmt\Trait_($this->semStack[$2], ['stmts' => $this->semStack[$4]], $this->startAttributeStack[$1] + $this->endAttributes); }
;

class_entry_type:
      T_CLASS                                               { $$ = 0; }
    | T_ABSTRACT T_CLASS                                    { $$ = Stmt\Class_::MODIFIER_ABSTRACT; }
    | T_FINAL T_CLASS                                       { $$ = Stmt\Class_::MODIFIER_FINAL; }
;

extends_from:
      /* empty */                                           { $$ = null; }
    | T_EXTENDS class_name                                  { $$ = $this->semStack[$2]; }
;

interface_extends_list:
      /* empty */                                           { $$ = array(); }
    | T_EXTENDS class_name_list                             { $$ = $this->semStack[$2]; }
;

implements_list:
      /* empty */                                           { $$ = array(); }
    | T_IMPLEMENTS class_name_list                          { $$ = $this->semStack[$2]; }
;

class_name_list:
      non_empty_class_name_list no_comma                    { $$ = $this->semStack[$1]; }
;

non_empty_class_name_list:
      class_name                                            { $$ = array($this->semStack[$1]); }
    | non_empty_class_name_list ',' class_name              { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

for_statement:
      statement                                             { $$ = is_array($this->semStack[$1]) ? $this->semStack[$1] : array($this->semStack[$1]); }
    | ':' inner_statement_list T_ENDFOR ';'                 { $$ = $this->semStack[$2]; }
;

foreach_statement:
      statement                                             { $$ = is_array($this->semStack[$1]) ? $this->semStack[$1] : array($this->semStack[$1]); }
    | ':' inner_statement_list T_ENDFOREACH ';'             { $$ = $this->semStack[$2]; }
;

declare_statement:
      non_empty_statement                                   { $$ = is_array($this->semStack[$1]) ? $this->semStack[$1] : array($this->semStack[$1]); }
    | ';'                                                   { $$ = null; }
    | ':' inner_statement_list T_ENDDECLARE ';'             { $$ = $this->semStack[$2]; }
;

declare_list:
      non_empty_declare_list no_comma                       { $$ = $this->semStack[$1]; }
;

non_empty_declare_list:
      declare_list_element                                  { $$ = array($this->semStack[$1]); }
    | non_empty_declare_list ',' declare_list_element       { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

declare_list_element:
      T_STRING '=' expr                                     { $$ = new Stmt\DeclareDeclare($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

switch_case_list:
      '{' case_list '}'                                     { $$ = $this->semStack[$2]; }
    | '{' ';' case_list '}'                                 { $$ = $this->semStack[$3]; }
    | ':' case_list T_ENDSWITCH ';'                         { $$ = $this->semStack[$2]; }
    | ':' ';' case_list T_ENDSWITCH ';'                     { $$ = $this->semStack[$3]; }
;

case_list:
      /* empty */                                           { $$ = array(); }
    | case_list case                                        { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }
;

case:
      T_CASE expr case_separator inner_statement_list       { $$ = new Stmt\Case_($this->semStack[$2], $this->semStack[$4], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DEFAULT case_separator inner_statement_list         { $$ = new Stmt\Case_(null, $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

case_separator:
      ':'
    | ';'
;

while_statement:
      statement                                             { $$ = is_array($this->semStack[$1]) ? $this->semStack[$1] : array($this->semStack[$1]); }
    | ':' inner_statement_list T_ENDWHILE ';'               { $$ = $this->semStack[$2]; }
;

elseif_list:
      /* empty */                                           { $$ = array(); }
    | elseif_list elseif                                    { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }
;

elseif:
      T_ELSEIF '(' expr ')' statement                       { $$ = new Stmt\ElseIf_($this->semStack[$3], is_array($this->semStack[$5]) ? $this->semStack[$5] : array($this->semStack[$5]), $this->startAttributeStack[$1] + $this->endAttributes); }
;

new_elseif_list:
      /* empty */                                           { $$ = array(); }
    | new_elseif_list new_elseif                            { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }
;

new_elseif:
     T_ELSEIF '(' expr ')' ':' inner_statement_list         { $$ = new Stmt\ElseIf_($this->semStack[$3], $this->semStack[$6], $this->startAttributeStack[$1] + $this->endAttributes); }
;

else_single:
      /* empty */                                           { $$ = null; }
    | T_ELSE statement                                      { $$ = new Stmt\Else_(is_array($this->semStack[$2]) ? $this->semStack[$2] : array($this->semStack[$2]), $this->startAttributeStack[$1] + $this->endAttributes); }
;

new_else_single:
      /* empty */                                           { $$ = null; }
    | T_ELSE ':' inner_statement_list                       { $$ = new Stmt\Else_($this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

foreach_variable:
      variable                                              { $$ = array($this->semStack[$1], false); }
    | '&' variable                                          { $$ = array($this->semStack[$2], true); }
    | list_expr                                             { $$ = array($this->semStack[$1], false); }
    | array_short_syntax                                    { $$ = array($this->semStack[$1], false); }
;

parameter_list:
      non_empty_parameter_list no_comma                     { $$ = $this->semStack[$1]; }
    | /* empty */                                           { $$ = array(); }
;

non_empty_parameter_list:
      parameter                                             { $$ = array($this->semStack[$1]); }
    | non_empty_parameter_list ',' parameter                { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

parameter:
      optional_param_type optional_ref optional_ellipsis T_VARIABLE
          { $$ = new Node\Param(substr($this->semStack[$4], 1), null, $this->semStack[$1], $this->semStack[$2], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); $this->checkParam($$); }
    | optional_param_type optional_ref optional_ellipsis T_VARIABLE '=' expr
          { $$ = new Node\Param(substr($this->semStack[$4], 1), $this->semStack[$6], $this->semStack[$1], $this->semStack[$2], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); $this->checkParam($$); }
;

type_expr:
      type                                                  { $$ = $this->semStack[$1]; }
    | '?' type                                              { $$ = new Node\NullableType($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
;

type:
      name                                                  { $$ = $this->handleBuiltinTypes($this->semStack[$1]); }
    | T_ARRAY                                               { $$ = 'array'; }
    | T_CALLABLE                                            { $$ = 'callable'; }
;

optional_param_type:
      /* empty */                                           { $$ = null; }
    | type_expr                                             { $$ = $this->semStack[$1]; }
;

optional_return_type:
      /* empty */                                           { $$ = null; }
    | ':' type_expr                                         { $$ = $this->semStack[$2]; }
;

argument_list:
      '(' ')'                                               { $$ = array(); }
    | '(' non_empty_argument_list no_comma ')'              { $$ = $this->semStack[$2]; }
;

non_empty_argument_list:
      argument                                              { $$ = array($this->semStack[$1]); }
    | non_empty_argument_list ',' argument                  { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

argument:
      expr                                                  { $$ = new Node\Arg($this->semStack[$1], false, false, $this->startAttributeStack[$1] + $this->endAttributes); }
    | '&' variable                                          { $$ = new Node\Arg($this->semStack[$2], true, false, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_ELLIPSIS expr                                       { $$ = new Node\Arg($this->semStack[$2], false, true, $this->startAttributeStack[$1] + $this->endAttributes); }
;

global_var_list:
      non_empty_global_var_list no_comma                    { $$ = $this->semStack[$1]; }
;

non_empty_global_var_list:
      non_empty_global_var_list ',' global_var              { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | global_var                                            { $$ = array($this->semStack[$1]); }
;

global_var:
      simple_variable                                       { $$ = new Expr\Variable($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
;

static_var_list:
      non_empty_static_var_list no_comma                    { $$ = $this->semStack[$1]; }
;

non_empty_static_var_list:
      non_empty_static_var_list ',' static_var              { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | static_var                                            { $$ = array($this->semStack[$1]); }
;

static_var:
      T_VARIABLE                                            { $$ = new Stmt\StaticVar(substr($this->semStack[$1], 1), null, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_VARIABLE '=' expr                                   { $$ = new Stmt\StaticVar(substr($this->semStack[$1], 1), $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

class_statement_list:
      class_statement_list class_statement                  { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }
    | /* empty */                                           { $$ = array(); }
;

class_statement:
      variable_modifiers property_declaration_list ';'
          { $$ = new Stmt\Property($this->semStack[$1], $this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); $this->checkProperty($$, $1); }
    | method_modifiers T_CONST class_const_list ';'
          { $$ = new Stmt\ClassConst($this->semStack[$3], $this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); $this->checkClassConst($$, $1); }
    | method_modifiers T_FUNCTION optional_ref identifier '(' parameter_list ')' optional_return_type method_body
          { $$ = new Stmt\ClassMethod($this->semStack[$4], ['type' => $this->semStack[$1], 'byRef' => $this->semStack[$3], 'params' => $this->semStack[$6], 'returnType' => $this->semStack[$8], 'stmts' => $this->semStack[$9]], $this->startAttributeStack[$1] + $this->endAttributes);
            $this->checkClassMethod($$, $1); }
    | T_USE class_name_list trait_adaptations               { $$ = new Stmt\TraitUse($this->semStack[$2], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

trait_adaptations:
      ';'                                                   { $$ = array(); }
    | '{' trait_adaptation_list '}'                         { $$ = $this->semStack[$2]; }
;

trait_adaptation_list:
      /* empty */                                           { $$ = array(); }
    | trait_adaptation_list trait_adaptation                { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }
;

trait_adaptation:
      trait_method_reference_fully_qualified T_INSTEADOF class_name_list ';'
          { $$ = new Stmt\TraitUseAdaptation\Precedence($this->semStack[$1][0], $this->semStack[$1][1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | trait_method_reference T_AS member_modifier identifier ';'
          { $$ = new Stmt\TraitUseAdaptation\Alias($this->semStack[$1][0], $this->semStack[$1][1], $this->semStack[$3], $this->semStack[$4], $this->startAttributeStack[$1] + $this->endAttributes); }
    | trait_method_reference T_AS member_modifier ';'
          { $$ = new Stmt\TraitUseAdaptation\Alias($this->semStack[$1][0], $this->semStack[$1][1], $this->semStack[$3], null, $this->startAttributeStack[$1] + $this->endAttributes); }
    | trait_method_reference T_AS T_STRING ';'
          { $$ = new Stmt\TraitUseAdaptation\Alias($this->semStack[$1][0], $this->semStack[$1][1], null, $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | trait_method_reference T_AS reserved_non_modifiers ';'
          { $$ = new Stmt\TraitUseAdaptation\Alias($this->semStack[$1][0], $this->semStack[$1][1], null, $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

trait_method_reference_fully_qualified:
      name T_PAAMAYIM_NEKUDOTAYIM identifier                { $$ = array($this->semStack[$1], $this->semStack[$3]); }
;
trait_method_reference:
      trait_method_reference_fully_qualified                { $$ = $this->semStack[$1]; }
    | identifier                                            { $$ = array(null, $this->semStack[$1]); }
;

method_body:
      ';' /* abstract method */                             { $$ = null; }
    | '{' inner_statement_list '}'                          { $$ = $this->semStack[$2]; }
;

variable_modifiers:
      non_empty_member_modifiers                            { $$ = $this->semStack[$1]; }
    | T_VAR                                                 { $$ = 0; }
;

method_modifiers:
      /* empty */                                           { $$ = 0; }
    | non_empty_member_modifiers                            { $$ = $this->semStack[$1]; }
;

non_empty_member_modifiers:
      member_modifier                                       { $$ = $this->semStack[$1]; }
    | non_empty_member_modifiers member_modifier            { $this->checkModifier($this->semStack[$1], $this->semStack[$2], $2); $$ = $this->semStack[$1] | $this->semStack[$2]; }
;

member_modifier:
      T_PUBLIC                                              { $$ = Stmt\Class_::MODIFIER_PUBLIC; }
    | T_PROTECTED                                           { $$ = Stmt\Class_::MODIFIER_PROTECTED; }
    | T_PRIVATE                                             { $$ = Stmt\Class_::MODIFIER_PRIVATE; }
    | T_STATIC                                              { $$ = Stmt\Class_::MODIFIER_STATIC; }
    | T_ABSTRACT                                            { $$ = Stmt\Class_::MODIFIER_ABSTRACT; }
    | T_FINAL                                               { $$ = Stmt\Class_::MODIFIER_FINAL; }
;

property_declaration_list:
      non_empty_property_declaration_list no_comma          { $$ = $this->semStack[$1]; }
;

non_empty_property_declaration_list:
      property_declaration                                  { $$ = array($this->semStack[$1]); }
    | non_empty_property_declaration_list ',' property_declaration
          { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

property_declaration:
      T_VARIABLE                                            { $$ = new Stmt\PropertyProperty(substr($this->semStack[$1], 1), null, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_VARIABLE '=' expr                                   { $$ = new Stmt\PropertyProperty(substr($this->semStack[$1], 1), $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

expr_list:
      non_empty_expr_list no_comma                          { $$ = $this->semStack[$1]; }
;

non_empty_expr_list:
      non_empty_expr_list ',' expr                          { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | expr                                                  { $$ = array($this->semStack[$1]); }
;

for_expr:
      /* empty */                                           { $$ = array(); }
    | expr_list                                             { $$ = $this->semStack[$1]; }
;

expr:
      variable                                              { $$ = $this->semStack[$1]; }
    | list_expr '=' expr                                    { $$ = new Expr\Assign($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | array_short_syntax '=' expr                           { $$ = new Expr\Assign($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable '=' expr                                     { $$ = new Expr\Assign($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable '=' '&' variable                             { $$ = new Expr\AssignRef($this->semStack[$1], $this->semStack[$4], $this->startAttributeStack[$1] + $this->endAttributes); }
    | new_expr                                              { $$ = $this->semStack[$1]; }
    | T_CLONE expr                                          { $$ = new Expr\Clone_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_PLUS_EQUAL expr                            { $$ = new Expr\AssignOp\Plus($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_MINUS_EQUAL expr                           { $$ = new Expr\AssignOp\Minus($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_MUL_EQUAL expr                             { $$ = new Expr\AssignOp\Mul($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_DIV_EQUAL expr                             { $$ = new Expr\AssignOp\Div($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_CONCAT_EQUAL expr                          { $$ = new Expr\AssignOp\Concat($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_MOD_EQUAL expr                             { $$ = new Expr\AssignOp\Mod($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_AND_EQUAL expr                             { $$ = new Expr\AssignOp\BitwiseAnd($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_OR_EQUAL expr                              { $$ = new Expr\AssignOp\BitwiseOr($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_XOR_EQUAL expr                             { $$ = new Expr\AssignOp\BitwiseXor($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_SL_EQUAL expr                              { $$ = new Expr\AssignOp\ShiftLeft($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_SR_EQUAL expr                              { $$ = new Expr\AssignOp\ShiftRight($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_POW_EQUAL expr                             { $$ = new Expr\AssignOp\Pow($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_INC                                        { $$ = new Expr\PostInc($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_INC variable                                        { $$ = new Expr\PreInc($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | variable T_DEC                                        { $$ = new Expr\PostDec($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DEC variable                                        { $$ = new Expr\PreDec($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_BOOLEAN_OR expr                                { $$ = new Expr\BinaryOp\BooleanOr($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_BOOLEAN_AND expr                               { $$ = new Expr\BinaryOp\BooleanAnd($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_LOGICAL_OR expr                                { $$ = new Expr\BinaryOp\LogicalOr($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_LOGICAL_AND expr                               { $$ = new Expr\BinaryOp\LogicalAnd($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_LOGICAL_XOR expr                               { $$ = new Expr\BinaryOp\LogicalXor($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '|' expr                                         { $$ = new Expr\BinaryOp\BitwiseOr($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '&' expr                                         { $$ = new Expr\BinaryOp\BitwiseAnd($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '^' expr                                         { $$ = new Expr\BinaryOp\BitwiseXor($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '.' expr                                         { $$ = new Expr\BinaryOp\Concat($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '+' expr                                         { $$ = new Expr\BinaryOp\Plus($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '-' expr                                         { $$ = new Expr\BinaryOp\Minus($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '*' expr                                         { $$ = new Expr\BinaryOp\Mul($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '/' expr                                         { $$ = new Expr\BinaryOp\Div($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '%' expr                                         { $$ = new Expr\BinaryOp\Mod($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_SL expr                                        { $$ = new Expr\BinaryOp\ShiftLeft($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_SR expr                                        { $$ = new Expr\BinaryOp\ShiftRight($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_POW expr                                       { $$ = new Expr\BinaryOp\Pow($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | '+' expr %prec T_INC                                  { $$ = new Expr\UnaryPlus($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | '-' expr %prec T_INC                                  { $$ = new Expr\UnaryMinus($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | '!' expr                                              { $$ = new Expr\BooleanNot($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | '~' expr                                              { $$ = new Expr\BitwiseNot($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_IS_IDENTICAL expr                              { $$ = new Expr\BinaryOp\Identical($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_IS_NOT_IDENTICAL expr                          { $$ = new Expr\BinaryOp\NotIdentical($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_IS_EQUAL expr                                  { $$ = new Expr\BinaryOp\Equal($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_IS_NOT_EQUAL expr                              { $$ = new Expr\BinaryOp\NotEqual($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_SPACESHIP expr                                 { $$ = new Expr\BinaryOp\Spaceship($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '<' expr                                         { $$ = new Expr\BinaryOp\Smaller($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_IS_SMALLER_OR_EQUAL expr                       { $$ = new Expr\BinaryOp\SmallerOrEqual($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '>' expr                                         { $$ = new Expr\BinaryOp\Greater($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_IS_GREATER_OR_EQUAL expr                       { $$ = new Expr\BinaryOp\GreaterOrEqual($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_INSTANCEOF class_name_reference                { $$ = new Expr\Instanceof_($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | '(' expr ')'                                          { $$ = $this->semStack[$2]; }
    | expr '?' expr ':' expr                                { $$ = new Expr\Ternary($this->semStack[$1], $this->semStack[$3], $this->semStack[$5], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr '?' ':' expr                                     { $$ = new Expr\Ternary($this->semStack[$1], null, $this->semStack[$4], $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_COALESCE expr                                  { $$ = new Expr\BinaryOp\Coalesce($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_ISSET '(' variables_list ')'                        { $$ = new Expr\Isset_($this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_EMPTY '(' expr ')'                                  { $$ = new Expr\Empty_($this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_INCLUDE expr                                        { $$ = new Expr\Include_($this->semStack[$2], Expr\Include_::TYPE_INCLUDE, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_INCLUDE_ONCE expr                                   { $$ = new Expr\Include_($this->semStack[$2], Expr\Include_::TYPE_INCLUDE_ONCE, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_EVAL '(' expr ')'                                   { $$ = new Expr\Eval_($this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_REQUIRE expr                                        { $$ = new Expr\Include_($this->semStack[$2], Expr\Include_::TYPE_REQUIRE, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_REQUIRE_ONCE expr                                   { $$ = new Expr\Include_($this->semStack[$2], Expr\Include_::TYPE_REQUIRE_ONCE, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_INT_CAST expr                                       { $$ = new Expr\Cast\Int_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DOUBLE_CAST expr                                    { $$ = new Expr\Cast\Double($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_STRING_CAST expr                                    { $$ = new Expr\Cast\String_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_ARRAY_CAST expr                                     { $$ = new Expr\Cast\Array_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_OBJECT_CAST expr                                    { $$ = new Expr\Cast\Object_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_BOOL_CAST expr                                      { $$ = new Expr\Cast\Bool_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_UNSET_CAST expr                                     { $$ = new Expr\Cast\Unset_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_EXIT exit_expr
          { $attrs = $this->startAttributeStack[$1] + $this->endAttributes;
            $attrs['kind'] = strtolower($this->semStack[$1]) === 'exit' ? Expr\Exit_::KIND_EXIT : Expr\Exit_::KIND_DIE;
            $$ = new Expr\Exit_($this->semStack[$2], $attrs); }
    | '@' expr                                              { $$ = new Expr\ErrorSuppress($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | scalar                                                { $$ = $this->semStack[$1]; }
    | '`' backticks_expr '`'                                { $$ = new Expr\ShellExec($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_PRINT expr                                          { $$ = new Expr\Print_($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_YIELD                                               { $$ = new Expr\Yield_(null, null, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_YIELD expr                                          { $$ = new Expr\Yield_($this->semStack[$2], null, $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_YIELD expr T_DOUBLE_ARROW expr                      { $$ = new Expr\Yield_($this->semStack[$4], $this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_YIELD_FROM expr                                     { $$ = new Expr\YieldFrom($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_FUNCTION optional_ref '(' parameter_list ')' lexical_vars optional_return_type
      '{' inner_statement_list '}'
          { $$ = new Expr\Closure(['static' => false, 'byRef' => $this->semStack[$2], 'params' => $this->semStack[$4], 'uses' => $this->semStack[$6], 'returnType' => $this->semStack[$7], 'stmts' => $this->semStack[$9]], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_STATIC T_FUNCTION optional_ref '(' parameter_list ')' lexical_vars optional_return_type
      '{' inner_statement_list '}'
          { $$ = new Expr\Closure(['static' => true, 'byRef' => $this->semStack[$3], 'params' => $this->semStack[$5], 'uses' => $this->semStack[$7], 'returnType' => $this->semStack[$8], 'stmts' => $this->semStack[$10]], $this->startAttributeStack[$1] + $this->endAttributes); }
;

anonymous_class:
      T_CLASS ctor_arguments extends_from implements_list '{' class_statement_list '}'
          { $$ = array(new Stmt\Class_(null, ['type' => 0, 'extends' => $this->semStack[$3], 'implements' => $this->semStack[$4], 'stmts' => $this->semStack[$6]], $this->startAttributeStack[$1] + $this->endAttributes), $this->semStack[$2]);
            $this->checkClass($$[0], -1); }

new_expr:
      T_NEW class_name_reference ctor_arguments             { $$ = new Expr\New_($this->semStack[$2], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_NEW anonymous_class
          { list($class, $ctorArgs) = $this->semStack[$2]; $$ = new Expr\New_($class, $ctorArgs, $this->startAttributeStack[$1] + $this->endAttributes); }
;

lexical_vars:
      /* empty */                                           { $$ = array(); }
    | T_USE '(' lexical_var_list ')'                        { $$ = $this->semStack[$3]; }
;

lexical_var_list:
      non_empty_lexical_var_list no_comma                   { $$ = $this->semStack[$1]; }
;

non_empty_lexical_var_list:
      lexical_var                                           { $$ = array($this->semStack[$1]); }
    | non_empty_lexical_var_list ',' lexical_var            { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
;

lexical_var:
      optional_ref T_VARIABLE                               { $$ = new Expr\ClosureUse(substr($this->semStack[$2], 1), $this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
;

function_call:
      name argument_list                                    { $$ = new Expr\FuncCall($this->semStack[$1], $this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | callable_expr argument_list                           { $$ = new Expr\FuncCall($this->semStack[$1], $this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | class_name_or_var T_PAAMAYIM_NEKUDOTAYIM member_name argument_list
          { $$ = new Expr\StaticCall($this->semStack[$1], $this->semStack[$3], $this->semStack[$4], $this->startAttributeStack[$1] + $this->endAttributes); }
;

class_name:
      T_STATIC                                              { $$ = new Name($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | name                                                  { $$ = $this->semStack[$1]; }
;

name:
      namespace_name_parts                                  { $$ = new Name($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_NS_SEPARATOR namespace_name_parts                   { $$ = new Name\FullyQualified($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_NAMESPACE T_NS_SEPARATOR namespace_name_parts       { $$ = new Name\Relative($this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

class_name_reference:
      class_name                                            { $$ = $this->semStack[$1]; }
    | new_variable                                          { $$ = $this->semStack[$1]; }
    | error                                                 { $$ = new Expr\Error($this->startAttributeStack[$1] + $this->endAttributes); $this->errorState = 2; }
;

class_name_or_var:
      class_name                                            { $$ = $this->semStack[$1]; }
    | dereferencable                                        { $$ = $this->semStack[$1]; }
;

exit_expr:
      /* empty */                                           { $$ = null; }
    | '(' optional_expr ')'                                 { $$ = $this->semStack[$2]; }
;

backticks_expr:
      /* empty */                                           { $$ = array(); }
    | T_ENCAPSED_AND_WHITESPACE
          { $$ = array(new Scalar\EncapsedStringPart(Scalar\String_::parseEscapeSequences($this->semStack[$1], '`'), $this->startAttributeStack[$1] + $this->endAttributes)); }
    | encaps_list                                           { foreach ($this->semStack[$1] as $s) { if ($s instanceof Node\Scalar\EncapsedStringPart) { $s->value = Node\Scalar\String_::parseEscapeSequences($s->value, '`', true); } }; $$ = $this->semStack[$1]; }
;

ctor_arguments:
      /* empty */                                           { $$ = array(); }
    | argument_list                                         { $$ = $this->semStack[$1]; }
;

constant:
      name                                                  { $$ = new Expr\ConstFetch($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | class_name_or_var T_PAAMAYIM_NEKUDOTAYIM identifier
          { $$ = new Expr\ClassConstFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    /* We interpret and isolated FOO:: as an unfinished class constant fetch. It could also be
       an unfinished static property fetch or unfinished scoped call. */
    | class_name_or_var T_PAAMAYIM_NEKUDOTAYIM error
          { $$ = new Expr\ClassConstFetch($this->semStack[$1], new Expr\Error($this->startAttributeStack[$3] + $this->endAttributeStack[$3]), $this->startAttributeStack[$1] + $this->endAttributes); $this->errorState = 2; }
;

array_short_syntax:
      '[' array_pair_list ']'
          { $attrs = $this->startAttributeStack[$1] + $this->endAttributes; $attrs['kind'] = Expr\Array_::KIND_SHORT;
            $$ = new Expr\Array_($this->semStack[$2], $attrs); }
;

dereferencable_scalar:
      T_ARRAY '(' array_pair_list ')'
          { $attrs = $this->startAttributeStack[$1] + $this->endAttributes; $attrs['kind'] = Expr\Array_::KIND_LONG;
            $$ = new Expr\Array_($this->semStack[$3], $attrs); }
    | array_short_syntax                                    { $$ = $this->semStack[$1]; }
    | T_CONSTANT_ENCAPSED_STRING
          { $attrs = $this->startAttributeStack[$1] + $this->endAttributes; $attrs['kind'] = ($this->semStack[$1][0] === "'" || ($this->semStack[$1][1] === "'" && ($this->semStack[$1][0] === 'b' || $this->semStack[$1][0] === 'B')) ? Scalar\String_::KIND_SINGLE_QUOTED : Scalar\String_::KIND_DOUBLE_QUOTED);
            $$ = new Scalar\String_(Scalar\String_::parse($this->semStack[$1]), $attrs); }
;

scalar:
      T_LNUMBER                                             { $$ = $this->parseLNumber($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DNUMBER                                             { $$ = new Scalar\DNumber(Scalar\DNumber::parse($this->semStack[$1]), $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_LINE                                                { $$ = new Scalar\MagicConst\Line($this->startAttributeStack[$1] + $this->endAttributes); }
    | T_FILE                                                { $$ = new Scalar\MagicConst\File($this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DIR                                                 { $$ = new Scalar\MagicConst\Dir($this->startAttributeStack[$1] + $this->endAttributes); }
    | T_CLASS_C                                             { $$ = new Scalar\MagicConst\Class_($this->startAttributeStack[$1] + $this->endAttributes); }
    | T_TRAIT_C                                             { $$ = new Scalar\MagicConst\Trait_($this->startAttributeStack[$1] + $this->endAttributes); }
    | T_METHOD_C                                            { $$ = new Scalar\MagicConst\Method($this->startAttributeStack[$1] + $this->endAttributes); }
    | T_FUNC_C                                              { $$ = new Scalar\MagicConst\Function_($this->startAttributeStack[$1] + $this->endAttributes); }
    | T_NS_C                                                { $$ = new Scalar\MagicConst\Namespace_($this->startAttributeStack[$1] + $this->endAttributes); }
    | dereferencable_scalar                                 { $$ = $this->semStack[$1]; }
    | constant                                              { $$ = $this->semStack[$1]; }
    | T_START_HEREDOC T_ENCAPSED_AND_WHITESPACE T_END_HEREDOC
          { $attrs = $this->startAttributeStack[$1] + $this->endAttributes; $attrs['kind'] = strpos($this->semStack[$1], "'") === false ? Scalar\String_::KIND_HEREDOC : Scalar\String_::KIND_NOWDOC; preg_match('/\A[bB]?<<<[ \t]*[\'"]?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'"]?(?:\r\n|\n|\r)\z/', $this->semStack[$1], $matches); $attrs['docLabel'] = $matches[1];;
            $$ = new Scalar\String_(Scalar\String_::parseDocString($this->semStack[$1], $this->semStack[$2]), $attrs); }
    | T_START_HEREDOC T_END_HEREDOC
          { $attrs = $this->startAttributeStack[$1] + $this->endAttributes; $attrs['kind'] = strpos($this->semStack[$1], "'") === false ? Scalar\String_::KIND_HEREDOC : Scalar\String_::KIND_NOWDOC; preg_match('/\A[bB]?<<<[ \t]*[\'"]?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'"]?(?:\r\n|\n|\r)\z/', $this->semStack[$1], $matches); $attrs['docLabel'] = $matches[1];;
            $$ = new Scalar\String_('', $attrs); }
    | '"' encaps_list '"'
          { $attrs = $this->startAttributeStack[$1] + $this->endAttributes; $attrs['kind'] = Scalar\String_::KIND_DOUBLE_QUOTED;
            foreach ($this->semStack[$2] as $s) { if ($s instanceof Node\Scalar\EncapsedStringPart) { $s->value = Node\Scalar\String_::parseEscapeSequences($s->value, '"', true); } }; $$ = new Scalar\Encapsed($this->semStack[$2], $attrs); }
    | T_START_HEREDOC encaps_list T_END_HEREDOC
          { $attrs = $this->startAttributeStack[$1] + $this->endAttributes; $attrs['kind'] = strpos($this->semStack[$1], "'") === false ? Scalar\String_::KIND_HEREDOC : Scalar\String_::KIND_NOWDOC; preg_match('/\A[bB]?<<<[ \t]*[\'"]?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'"]?(?:\r\n|\n|\r)\z/', $this->semStack[$1], $matches); $attrs['docLabel'] = $matches[1];;
            foreach ($this->semStack[$2] as $s) { if ($s instanceof Node\Scalar\EncapsedStringPart) { $s->value = Node\Scalar\String_::parseEscapeSequences($s->value, null, true); } } $s->value = preg_replace('~(\r\n|\n|\r)\z~', '', $s->value); if ('' === $s->value) array_pop($this->semStack[$2]);; $$ = new Scalar\Encapsed($this->semStack[$2], $attrs); }
;

optional_expr:
      /* empty */                                           { $$ = null; }
    | expr                                                  { $$ = $this->semStack[$1]; }
;

dereferencable:
      variable                                              { $$ = $this->semStack[$1]; }
    | '(' expr ')'                                          { $$ = $this->semStack[$2]; }
    | dereferencable_scalar                                 { $$ = $this->semStack[$1]; }
;

callable_expr:
      callable_variable                                     { $$ = $this->semStack[$1]; }
    | '(' expr ')'                                          { $$ = $this->semStack[$2]; }
    | dereferencable_scalar                                 { $$ = $this->semStack[$1]; }
;

callable_variable:
      simple_variable                                       { $$ = new Expr\Variable($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | dereferencable '[' optional_expr ']'                  { $$ = new Expr\ArrayDimFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | constant '[' optional_expr ']'                        { $$ = new Expr\ArrayDimFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | dereferencable '{' expr '}'                           { $$ = new Expr\ArrayDimFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | function_call                                         { $$ = $this->semStack[$1]; }
    | dereferencable T_OBJECT_OPERATOR property_name argument_list
          { $$ = new Expr\MethodCall($this->semStack[$1], $this->semStack[$3], $this->semStack[$4], $this->startAttributeStack[$1] + $this->endAttributes); }
;

variable:
      callable_variable                                     { $$ = $this->semStack[$1]; }
    | static_member                                         { $$ = $this->semStack[$1]; }
    | dereferencable T_OBJECT_OPERATOR property_name        { $$ = new Expr\PropertyFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

simple_variable:
      T_VARIABLE                                            { $$ = substr($this->semStack[$1], 1); }
    | '$' '{' expr '}'                                      { $$ = $this->semStack[$3]; }
    | '$' simple_variable                                   { $$ = new Expr\Variable($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | '$' error                                             { $$ = new Expr\Error($this->startAttributeStack[$1] + $this->endAttributes); $this->errorState = 2; }
;

static_member:
      class_name_or_var T_PAAMAYIM_NEKUDOTAYIM simple_variable
          { $$ = new Expr\StaticPropertyFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

new_variable:
      simple_variable                                       { $$ = new Expr\Variable($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | new_variable '[' optional_expr ']'                    { $$ = new Expr\ArrayDimFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | new_variable '{' expr '}'                             { $$ = new Expr\ArrayDimFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | new_variable T_OBJECT_OPERATOR property_name          { $$ = new Expr\PropertyFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | class_name T_PAAMAYIM_NEKUDOTAYIM simple_variable     { $$ = new Expr\StaticPropertyFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | new_variable T_PAAMAYIM_NEKUDOTAYIM simple_variable   { $$ = new Expr\StaticPropertyFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

member_name:
      identifier                                            { $$ = $this->semStack[$1]; }
    | '{' expr '}'	                                        { $$ = $this->semStack[$2]; }
    | simple_variable	                                    { $$ = new Expr\Variable($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
;

property_name:
      T_STRING                                              { $$ = $this->semStack[$1]; }
    | '{' expr '}'	                                        { $$ = $this->semStack[$2]; }
    | simple_variable	                                    { $$ = new Expr\Variable($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | error                                                 { $$ = new Expr\Error($this->startAttributeStack[$1] + $this->endAttributes); $this->errorState = 2; }
;

list_expr:
      T_LIST '(' list_expr_elements ')'                     { $$ = new Expr\List_($this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
;

list_expr_elements:
      list_expr_elements ',' list_expr_element              { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | list_expr_element                                     { $$ = array($this->semStack[$1]); }
;

list_expr_element:
      variable                                              { $$ = new Expr\ArrayItem($this->semStack[$1], null, false, $this->startAttributeStack[$1] + $this->endAttributes); }
    | list_expr                                             { $$ = new Expr\ArrayItem($this->semStack[$1], null, false, $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_DOUBLE_ARROW variable                          { $$ = new Expr\ArrayItem($this->semStack[$3], $this->semStack[$1], false, $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_DOUBLE_ARROW list_expr                         { $$ = new Expr\ArrayItem($this->semStack[$3], $this->semStack[$1], false, $this->startAttributeStack[$1] + $this->endAttributes); }
    | /* empty */                                           { $$ = null; }
;

array_pair_list:
      inner_array_pair_list
          { $$ = $this->semStack[$1]; $end = count($$)-1; if ($$[$end] === null) unset($$[$end]); }
;

inner_array_pair_list:
      inner_array_pair_list ',' array_pair                  { $this->semStack[$1][] = $this->semStack[$3]; $$ = $this->semStack[$1]; }
    | array_pair                                            { $$ = array($this->semStack[$1]); }
;

array_pair:
      expr T_DOUBLE_ARROW expr                              { $$ = new Expr\ArrayItem($this->semStack[$3], $this->semStack[$1], false, $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr                                                  { $$ = new Expr\ArrayItem($this->semStack[$1], null, false, $this->startAttributeStack[$1] + $this->endAttributes); }
    | expr T_DOUBLE_ARROW '&' variable                      { $$ = new Expr\ArrayItem($this->semStack[$4], $this->semStack[$1], true, $this->startAttributeStack[$1] + $this->endAttributes); }
    | '&' variable                                          { $$ = new Expr\ArrayItem($this->semStack[$2], null, true, $this->startAttributeStack[$1] + $this->endAttributes); }
    | /* empty */                                           { $$ = null; }
;

encaps_list:
      encaps_list encaps_var                                { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }
    | encaps_list encaps_string_part                        { $this->semStack[$1][] = $this->semStack[$2]; $$ = $this->semStack[$1]; }
    | encaps_var                                            { $$ = array($this->semStack[$1]); }
    | encaps_string_part encaps_var                         { $$ = array($this->semStack[$1], $this->semStack[$2]); }
;

encaps_string_part:
      T_ENCAPSED_AND_WHITESPACE                             { $$ = new Scalar\EncapsedStringPart($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
;

encaps_base_var:
      T_VARIABLE                                            { $$ = new Expr\Variable(substr($this->semStack[$1], 1), $this->startAttributeStack[$1] + $this->endAttributes); }
;

encaps_var:
      encaps_base_var                                       { $$ = $this->semStack[$1]; }
    | encaps_base_var '[' encaps_var_offset ']'             { $$ = new Expr\ArrayDimFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | encaps_base_var T_OBJECT_OPERATOR T_STRING            { $$ = new Expr\PropertyFetch($this->semStack[$1], $this->semStack[$3], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DOLLAR_OPEN_CURLY_BRACES expr '}'                   { $$ = new Expr\Variable($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DOLLAR_OPEN_CURLY_BRACES T_STRING_VARNAME '}'       { $$ = new Expr\Variable($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_DOLLAR_OPEN_CURLY_BRACES T_STRING_VARNAME '[' expr ']' '}'
          { $$ = new Expr\ArrayDimFetch(new Expr\Variable($this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes), $this->semStack[$4], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_CURLY_OPEN variable '}'                             { $$ = $this->semStack[$2]; }
;

encaps_var_offset:
      T_STRING                                              { $$ = new Scalar\String_($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_NUM_STRING                                          { $$ = $this->parseNumString($this->semStack[$1], $this->startAttributeStack[$1] + $this->endAttributes); }
    | '-' T_NUM_STRING                                      { $$ = $this->parseNumString('-' . $this->semStack[$2], $this->startAttributeStack[$1] + $this->endAttributes); }
    | T_VARIABLE                                            { $$ = new Expr\Variable(substr($this->semStack[$1], 1), $this->startAttributeStack[$1] + $this->endAttributes); }
;

%%
