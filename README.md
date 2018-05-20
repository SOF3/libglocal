# libglocal
A virion for localization.

Features:
- Translations of the same [locale](#libglocal-terminology) can be split into different files to favour user-side editing
- Flexiblw message grouping
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

See [en_US.lang][en_US example] for example syntax.

## Libglocal Terminology
- `lang`/`locale`: human language
- `language`: programming language
- `message`: something that can be translated
- `translation`: the translation of a message in a specific locale

## Developer guide
Libglocal is a virion. The [Official Poggit Virion Documentation][virion doc] contains instructions for including a virion into your plugin.

### Base lang file
First you should choose a base lang. This will define all possible messages you will use in the plugin, and you will edit them while coding. Choose a locale that's easy to type while coding, e.g. `en_US`. (Locale IDs should match the [Minecraft locale codes][gamepedia locales list])

In the resources folder of the plugin, create a folder (use any folder name you like, default `lang/`). Create a `.lang` file for the base locale, e.g. `en_US.lang`, containing these two lines:

```
base lang en_US English (US)

messages
```

`en_US` is the locale code, and `English (US)` is the translated locale name. The translated locale name may be used in language selection.

All messages will be defined below the `messages` line.

#### Adding a message
Messages are identified by message IDs, which can contain `A-Z` `a-z` `0-9` `_` `-` `.`. The syntax for declaring a message is very simple:

```
  message-id Message content.
```

`message-id` is the message ID, and after one or multiple spaces/tabs, `Message content.` is the translated message in this locale.

Note the two leading spaces. This is an indent, which indicates the message is in the group `messages`, similar to YAML. Any number of spaces/tabs can be used for indentation, but it must be consistent per column. You can use 4 spaces for the first indent level but 2 tabs for the second indent level (very bad idea though!), and as long as you always use 4 spaces for the first indent level, the syntax is sitll correct. This is possible in libglocal because you can only increase the indent by one level each line (although you can dedent many levels). However, since this usually causes bugs, you are strongly recommended to use a consistent indentation each time.

In the translation content, the `\` character can be used to escape the following special characters:
- `\\`: `\` (all `\` must be escaped)
- `\#`: `#` (used to escape message interpolation) (if `#` isn't followed by a `{`, it doesn't need to be escaped)
- `\$`: `$` (used to escape argument reference) (if `$` isn't followed by a `{`, it doesn't need to be escaped)
- `\%`: `%` (used to escape spans) (if `%` isn't followed by a `{`, it doesn't need to be escaped)
- `\/`: `/` (used to escape comments) (if `/` isn't followed by another `/`, it doesn't need to be escaped)
- `\}`: `}` (used to escape span closing) (if `}` is not inside a [stack span](#stack-spans), it doesn't need to be escaped, but to prevent future bugs, you are encouraged to)
- `\n`: line break (LF)
- `\s`: normal whitespace character (useful for adding leading/trailing spaces, because all messages are trimmed when parsed)
- `\0`: **no** characters (nothing at all, not a NUL byte!) (useful for adding leading/trailing spaces, because all messages are trimmed when parsed)

#### Grouping messages
Messages can be grouped with an indent format similar to YAML and converted to a canonical ID like the `Config->getNested()` key.

```
base lang en_US English (US)

messages
  example
    lorem.ipsum
      message-id Message content.
```

This defines a message with ID `messages.exmaple.lorem.ipsum.message-id`, and the `en_US` translation is `Message content.`.

#### Arguments
Arguments is declared in the message scope, i.e. all translations of the same message must have the same set of arguments.

To declare an argument, add an indented line below the message with this syntax: `arg <ARG_NAME> [<ARG_TYPE>] [= <DEFAULT_VALUE>]`

`<ARG_NAME>` can contain `A-Z` `a-z` `0-9` `_` `-` `.` (same requirements as message IDs). It can be referenced from the message value in the format `${<ARG_NAME>}`. For example:

```
  message-id Message content with ${argName} here!
    arg argName
```

##### Argument types
The default argument type is `string`, which means the input argument is converted to a string using [PHP `(string)` rules][string-cast rules]. However, if an array or a resource is passed, an `InvalidArgumentException` will be thrown. Other types include:

###### Numbers
There are two types for numbers: `int`/`integer` and `float`/`double`/`real`/`number`.

For the former type, the argument must be a PHP int. For the latter, the argument must be a PHP int or PHP float. Passing in other types would result in an `InvalidArgumentException`.

Additionally, the range(s) of the number can be specified in the [numeric constraints format](#numeric-constraints) on an indented line. If there are multiple ranges, they are combined with a logic "or". For example:

```
  message-id The year ${year} is a leap number, and the number ${n} is positive but less than or equal to 100.
    arg m int
      range %400=0
      range %4=0 %100!=0
    arg n number
      range >0 <=100
```

(Leap year algorithm based on [Microsoft's formula][microsoft leap year formula]: `OR(MOD(A1,400)=0,AND(MOD(A1,4)=0,MOD(A1,100)<>0))`)

###### `quantity`
The `quantity` argument type is a number that is expressed differently when the number changes. A common usage is to handle plural forms. The format should be like this:

```
  message-id You have ${friends}.
    arg friends quantity
      default %d friend
      when >1: %d friends
```

There may be multiple `when` constraints. The part before the `:` is a [numeric constraint](#numeric-constraints). The part after the `:` is evaluated according to the [`sprintf` format][sprintf], with the number as the sole argument.

If none of the `when` constraints is matched, the `default` constraint would be used. The `default` line can be omitted if all acceptable numbers must match some of the `when` constriants.

`quantity` is an extension of `number`, i.e. it can also use the `range` constraints from `number`.

###### Custom argument types
The plugin can provide custom argument types. Argument type names must be in the identifier format (`A-Z` `a-z` `0-9` `_` `-` `.`).

###### The `list:` modifier
The `list:` modifier can be added in front of **any** argument types. The argument value must be an array where each element is applicable to the argument type behind `list:`. For example, `list:int` requires an int array. The keys are ignored, and the result in the translation is the array imploded with the delimiter `, ` (comma + space).

The delimiter can be overridden both for the whole locale and only for this message. To override the delimiter for the whole locale, override the message [`lib.list.delimiter`][en_US lib]. To override the delimiter for a single message, add an indented line indicating the `delimiter`, for example:

```
  message-id You can choose ${color-options} wool.
    arg color-options list:string
      delimiter: /
```

#### Numeric constraints
Numeric constraints are used in int/float range and quantifiers to validate/filter a **single** value (can't be used to compare against other arguments). There are 6 basic constraint types: equal to (`=`), not equal to (`!=`/`<>`), less than (`<`), greater than (`>`), less than or equal to (`<=`), greater than or equal to (`>=`), followed by a number.

In addition, the modulus operator `%` can be applied before the 6 constraints, also followed by a number (the modulus, can be any non-zero real number), e.g. `%2=0` is a constraint for even numbers.

Multiple constraints can be combined for a logic "or" by separating them by spaces. On the other hand, there must not be spaces within the same constraint.

  [en_US example]: LibglocalExample/resources/lang/en_US.lang
  [en_US lib]: default-lib/en_US.lang
  [gamepedia locale list]: https://minecraft.gamepedia.com/Language#Available_languages
  [microsoft leap year formula]: https://support.microsoft.com/en-us/help/214019/method-to-determine-whether-a-year-is-a-leap-year
  [sprintf]: https://php.net/sprintf
  [string-cast rules]: http://php.net/language.types.string#language.types.string.casting
  [virion doc]: https://poggit.pmmp.io/virion
