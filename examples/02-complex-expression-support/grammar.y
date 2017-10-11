
%left '+' T_FOO

%%

expr:
      expr '+' expr   { $$ = $1 + $3; }
    | '1'             { $$ = 1; }
    | T_FOO expr      { $$ = $2; }
;

%%
