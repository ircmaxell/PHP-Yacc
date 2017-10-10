

%token NUMBER
%left '+' '-'
%left '*' '/'
%right '^'
%left NEG

%%

statement
    : /* empty */ { exit(0); }
    | expression { printf("= %f\n", $1); }
    ;

expression
    : factor { $$ = $1; }
    | expression '*' expression { $$ = $1 * $3; }
    | expression '/' expression { $$ = $1 / $3; }
    | expression '+' expression { $$ = $1 + $3; }
    | expression '-' expression { $$ = $1 - $3; }
    | expression '^' expression { $$ = pow($1, $3); }
    | '-' expression %prec NEG { $$ = -$2; }
    ;

factor
    : NUMBER { $$ = $1; }
    | '(' expression ')' { $$ = $2; }
    ;

%%
