# PHP-Yacc

This is a port of [`kmyacc`](https://github.com/moriyoshi/kmyacc-forked) into PHP. It is a parser-generator, meaning it takes a YACC grammar file and generates a parser file.

## A Direct Port (For Now)

Right now, this is a direct port. Meaning that it works exactly like `kmyacc`. Looking in the examples, you can see that this means that you must supply a "parser template" in addition to the grammar.

Longer term, we want to add simplifying functionality. We will always support providing a template, but we will offer a series of default templates for common use-cases.

## What can I do with this?

You can parse most structured and unstructured grammars. There are some gotchas to [LALR(1) parsers](https://en.wikipedia.org/wiki/LALR_parser) that you need to be aware of (for example, Shift/Shift conflicts and Shift/Reduce conflicts). But those are beyond this simple intro.

## How does it work?

I don't know. I just ported the code until it worked correctly. 

## YACC Grammar

That's way beyond the scope of this documentation, but checkout [The YACC page here](http://dinosaur.compilertools.net/yacc/) for some info.

Over time we will document the grammar more...

## How do I use it?

For now, check out the examples folder. The current state of the CLI tool will change, so any usage today should please provide feedback and use-cases so that we can better design the tooling support.

## Why did you do this?

Many projects have the need for parsers (and therefore parser-generators). Nikita's [PHP-Parser](https://github.com/nikic/PHP-Parser) is one tool that uses kmyacc to generate its parser. There are many other projects out there that either use hand-written parsers, or use kmyacc or another parser-generator.

Unfortunately, not many parser-generators exist for PHP. And those that do exist I have found to be rigid or not powerful enough to parse PHP itself.

This project is an aim to resolve that.

## Performance

There's a TON of performance optimizations possible here. The original code was a direct port, so some structures are definitely sub-optimal. Over time we will improve the performance.

However, this will always be at least a slightly-slow process. Generating a parser requires a lot of resources, so should never happen inside of a web request. 

Using the generated parser however should be quite fast (the generated parser is fairly well optimized already). 

## What's left to do?

A bunch of things. Here's the wishlist:

 * Refactor to make conventions consistent (some parts currently use camel-case, some parts use snakeCase, etc).
 * Performance tuning
 * Unit test as much as possible
 * Document as much as possible (It's a complicated series of algorithms with no source documentation in either project).
 * Redesign the CLI binary and how it operates
 * Decide whether multi-language support is worth while, or if we should just move to only PHP codegen support.
 * Add default templates and parser implementations
    * At least one of which generates an "AST" by default, similar to Ruby's [Treetop library](https://github.com/nathansobo/treetop)
 * Build a reasonably performant lexer-generator (very likely as a separate project)
 * A lot of debugging (though we don't know of any bugs, they are there)
 * Building out of features we didn't need for the initial go (for example, support for `%union`, etc).

And a lot more.

## Contributing

