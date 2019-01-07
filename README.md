# libglocal
A virion for localization.

Features:
- Translations of the same [locale](#libglocal-terminology) can be split into different files to favour user-side editing
- Flexible message grouping
- Argument substitution by name No more confusing `{$1}`!
- Argument validation
- Argument defaults and cross-fallbacks
- Advanced argument types, including:
  - `list`: argument accepts an array and implodes according to the delimiter defined by the translation
  - `quantity`: argument accepts a number, and it affects the following words according to the locale's custom plural rules
  - The developer can define custom argument types
- Message substitution (include a message in another message)
- Color stacking (after message substitution, the original color can be restored if changed)
  - Human-readable color/format codes, e.g. `%{error}`, `%{hl1}`, `%{b}`, actual color can be changed
- Optional versioning to ignore outdated translations
- Message ID constant class generator (CLI tool that automatically creates a PHP class/interface declaring constants for message IDs, so you can reference them from PHP code with your IDE checking for typos)
- A PhpStorm plugin for editing libglocal lang files: https://github.com/SOF3/libglocal-idea-plugin

See [en_US.lang](LibglocalExample/resources/lang/en_US.lang) for example syntax.

## Libglocal Terminology
- `lang`/`locale`: human language
- `language`: programming language
- `message`: something that can be translated
- `translation`: the translation of a message in a specific locale

## Libglocal developer guide
Libglocal is a virion. The [Official Poggit Virion Documentation](https://poggit.pmmp.io/virion) contains instructions for including a virion into your plugin.

## Plugin developer (libglocal user) guide
See this document: [Guide for developers, translators and server owners](GUIDE.md)
