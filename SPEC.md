Libglocal v0.3.0 .lang Syntax Specification
===

## 0. Introduction
Libglocal is a translations framework for PocketMine plugins. The translation process consists of 5 steps:

1. The *plugin developer* first writes the language files that define the message usages (list of messages and the arguments they accept)
1. *Translators* write (non-base) language files in other languages.
1. The base files and the translator files are distributed to *users* (server owners), either by bundling into the plugin or by auto-updating when the server starts.
1. *Users* may customize the messages by creating their local (non-base) language files.
1. The translated messages are displayed to *players*.

To simplify this complicated process, libglocal loads language files like this:

1. libglocal loads all bundled lang files.
1. libglocal checks if these lang files have any updates online.
1. libglocal loads the lang files in the plugin data folder (editable by the user).

libglocal first loads all the base files to define the messages. Then messages are loaded in the order as above; if a message is defined twice, the later-loaded message will override previous ones.

## 1. Common syntax
These formats will be used later.

```bnf
T_EOL ::= "\n" | "\r\n" | <<eof>>
T_WHITESPACE ::= { " " | "\t" }+
T_IDENTIFIER ::= [ { alphabet }+ ":" ]* { alphabet | digit | "-" | "_" }+ { "." { alphabet | digit | "-" | "_" }+ }
```

If `T_IDENTIFIER` contains a `:`, the part before the `:` is a flag applied to the identifier. There must be no space around the `:`.

In the BNF notations in this file, `T_WHITESPACE` is permitted between any two tokens, unless the left token allows a space character inside. It is required between any tokens that does not contain spaces and cannot be delimited otherwise.

## 2. Lines
There are three types of lines: empty lines, block lines and comments lines.

```bnf
<empty line> ::= { " " | "\t" } T_EOL
```

### 2.1. Indentation and blocks
The libglocal syntax is newline-delimited and indent-sensitive. The basic syntax of each line is like this:

```bnf
<block line> ::= T_INDENT L_COMMAND [ T_WHITESPACE <args> ] T_EOL
T_INDENT ::= { " " | "\t" }
L_COMMAND ::= T_IDENTIFIER
```

Blocks can be nested by indentation according to these two basic rules:
- If a line's indent starts with the previous line's indent and has a longer indent, the line is a child of the block representing by the previous line.
- If two lines have the identical indent, and the indents of all lines between them (except empty lines and comment lines) are identical to or start with their indent, they are sibling blocks belonging to the same parent.
- If a line's indent does not start with the previous line but cannot be matched as the sibling of any previous lines, it is an indentation syntax error.

This specification does not restrict the indentation size or whether to use tabs or spaces. Writers MAY even have a mess like `\t \t\t ` as each indent, and have a different indent format per parent block. However, these would result in very unreadable code and lead to syntax errors. Writers SHOULD use a consistent indent, either one tab or a fixed number of spaces, for every indentation step. The conventional RECOMMENDED indent is one tab or two spaces.

### 2.2. Comments
Libglocal only supports full-line comments. 

## 3. Meta blocks
Each lang file must declare meta information to describe themselves.

All meta blocks are not indented. They must be located at the file start.

### 3.1. `lang`
`lang` (`B_META_LANG`) is a required meta block that specifies the language the file is translating.

```bnf
<lang line> ::= "base" "lang" L_LANG_ID <lang name> T_EOL
L_LANG_ID ::= T_IDENTIFIER
```

For example:

```libglocal
base lang en_US English (US)
```

```libglocal
lang zh_TW 繁體中文
```

The `base` flag indicates that this language file is message-defining, i.e. it defines new messages. Non-base files are message-overriding, i.e. it overrides the messages from the base file in the new language.

`LANG_ID` defines the language ID. It should match the internal language IDs from the Minecraft client, as [documented on Minecraft wiki](https://minecraft.gamepedia.com/Language#Available_languages). `<lang name>` is the language name as displayed to the user in that language, e.g. `zh_tw` is `繁體中文`.

### 3.2. `version`
`version` (`B_META_VERSION`) is a required meta block that specifies the version of this translation. The plugin version should match the `IDENTIFIER` format, and is compared using `version_compare`.

```bnf
<version> ::= "version" T_IDENTIFIER
```

For base language files, the `version` meta block does not change any semantics; it is just for reference.

For non-base language files, it indicates the base version the file is targetted against. It can be overridden per message using the `version` message modifier.

If the version of a message in the translated file is older than the `version` modifier in its base declaration, the message will be ignored and the base language one will be used because it is outdated.

### 3.3. `author`
`author` (`B_META_AUTHOR`) is an optional meta block that indicates the authors of the the language file. It is only used for displaying. There can be multiple `author` meta blocks.

```bnf
B_META_AUTHOR ::= "author" L_AUTHOR_NAME
L_AUTHOR_NAME ::= T_IDENTIFIER
```

`AUTHOR_NAME` can be a string of any characters except control characters.

### 3.4. `require`
`require` (`B_META_REQUIRE`) is an optional meta block that indicates the dependencies required by this module. It can be used multiple times.

### 3.5. `use`
`use` (`B_META_USE`) is an optional meta block that declares an alias for a message.

```bnf
B_META_USE ::= "use" L_TARGET [L_ALIAS]
L_TARGET ::= T_IDENTIFIER
L_ALIAS ::= T_IDENTIFIER
```

If `L_ALIAS` is left out, the last part of the target message ID will be used, just like class aliases in PHP `use` statements.

### 3.6. Math rules
Math rules start with a `@`. They define predicate functions for testing numbers.

```bnf
B_MATH_RULE ::= "@" T_IDENTIFIER {L_MATH_PREDICATE}+
L_MATH_PREDICATE ::= [ "%" T_NUMBER ] T_MATH_COMPARATOR T_NUMBER
```

`L_MATH_PREDICATE`s are joined with the **logical AND**, i.e. the argument should satisfy all `L_MATH_PREDICATE`s.

`T_MATH_COMPARATOR` is one of the following:
- `=`: equals
- `<>`: not equals
- `<`: less than
- `<=`: less than or equal to
- `>`: greater than
- `>=`: greater than or equal to

If the `"%" T_NUMBER` section is present, the argument is reduced to the lowest non-negative equivalent at mod `T_NUMBER`. In other words, the remainder of the argument divided by `T_NUMBER` is used to compare instead. Unlike the modulus behaviour in many programming languages, the `%` here always produces a non-negative result even if any of the operands are negative.

## 4. Messages
The `messages` block (`L_MESSAGES`) contains all messages declared by the file. Under the `messages` block, the following parent-child block relations are allowed:

```
            +----------<---------+
            v                    ^
B_MESSAGES -+-> B_MESSAGE_GROUP -+-> B_MESSAGE ---> B_MODIFIER ---> B_CONSTRAINT
            v                    ^
            +---------->---------+
```

### 4.1. `messages` block
Syntax:

```bnf
B_MESSAGES ::= "messages" T_IDENTIFIER
```

The `T_IDENTIFIER` can contain the module name of the file. It is used in `require` of other files. It is also prepended to message IDs just like a message group.

Libglocal requires all lang files to declare a module to prevent clashing message IDs.

If the file is the only translation file in the plugin, the module name should be the plugin name. If the plugin has multiple lang files, they should have different module names, e.g. `xialotecon.bank`, `xialotecon.shop`, etc.

### 4.2. Basic message declarations
#### 4.2.1. Syntax
A message is the message name followed by the literal message value, separated one or more spaces or tabs, or a mix of both.

```bnf
B_MESSAGE ::= L_MESSAGE_ID L_LITERAL
L_MESSAGE_ID ::= T_IDENTIFIER
```

#### 4.2.2. Message visibility
Message visibility can be changed by prepending a visibility flag:
- `local:`: it can only be referenced by other messages in the same language
- `lib:`: it is a library message, so it can only be referenced by lang files, but cannot be used from the plugin directly.

### 4.3. Literal text format
Libglocal performs trimming on all literal text sections.

```bnf
L_LITERAL ::= { T_LITERAL_STRING | T_LITERAL_ESCAPE | L_ARG_REF | L_MESSAGE_REF | L_SPAN | ( T_EOL {" " | "\t"}+ ("|" | "\\" | "!") ) }+
```

`T_LITERAL_STRING` is any normal text without `\`, `#`, `$`, `%`, `}`, `\r`, `\n`. A `\` character indicates the start of a `T_LITERAL_ESCAPE` token.

`\r`, `\n` or EOF indicates the termination of the `L_LITERAL`. If the terminator is `\r`, the next byte will be skipped (even if it is not a `\n` byte).

`${` indicates the start of an `L_ARG_REF` element. `#{` indicates the start of an `L_MESSAGE_REF` element. `%{` indicates the start of an `L_SPAN` token. `}` in `L_LITERAL` indicates the end of an `L_SPAN` token. If `#`, `$` and `%` are not followed by `{`, it does not need to be escaped, but will be identified as a separate `T_LITERAL_STRING` token themselves.

`}` closes a `%{` `L_SPAN` (also closes `L_ARG_REF` and `L_MESSAGE_REF`, but they do not have `L_LITERAL` inside and is not relevant here). Even if the `L_LITERAL` is not inside a `L_SPAN`, `}` MUST still be escaped for the sake of maintainability.

#### 4.3.1. Escape sequences
Literal text will perform the following conversions

- `\\` -> `\` (MUST be escaped)
- `\#` -> `#` (MUST be escaped if followed by `{`, MAY be escaped otherwise)
- `\$` -> `$` (MUST be escaped if followed by `{`, MAY be escaped otherwise)
- `\%` -> `%` (MUST be escaped if followed by `{`, MAY be escaped otherwise)
- `\}` -> `}` (MUST be escaped)
- a literal newline, followed by any number of spaces or tabs, then a `|` character -> one whitespace. Trailing spaces/tabs on the first line and spaces/tabs following the `|` character are deleted.
- a literal newline, followed by any number of spaces or tabs, then a `\` character -> nothing. Trailing spaces/tabs on the first line and spaces/tabs following the `\` character are deleted.
- a literal newline, followed by any number of spaces or tabs, then a `!` character -> one line feed character. Trailing spaces/tabs on the first line and spaces/tabs following the `!` character are deleted.
- `\n` -> a line feed character
- `\s` -> a space character (useful for the leading and trailing spaces, because libglocal trims the leading and trailing spaces and tabs for each line)
- `\0` or `\.` -> nothing (not even a NUL byte), useful for creating empty messages (without this, empty messages are parsed as message groups instead, and the message will not be created)

If an unescaped `\` character does not match any of these sequences, a syntax error is raised.

#### 4.3.2. Argument reference
An argument reference is replaced by its value when resolved. It has this syntax:

```bnf
L_ARG_REF ::= "${" T_IDENTIFIER [","] L_ARG_ATTRIBUTES "}"
L_ARG_ATTRIBUTES ::= (["@"] T_IDENTIFIER "=" [","])*
```

`L_ARG_ATTRIBUTES` provides a set of attributes to change the behaviour of the argument depending on the argument type. See the "Argument types" section for details.

#### 4.3.3. Message reference
A message reference is replaced by resolving the referenced message. It has this syntax:

```bnf
L_MESSAGE_REF ::= "#{" ["$"] T_IDENTIFIER [","] L_MESSAGE_ARG_LIST "}"
L_MESSAGE_ARG_LIST ::= (T_IDENTIFIER "=" L_ARG_VALUE [","])*
```

Message references can be constant or dynamic. Without the `$`, the first `T_IDENTIFIER` is the constant name of another message. If the identifier starts with `.`, the message name is relative to the parent group of the current message; each additional leading `.` approaches the higher-level group just like `../` in a filesystem. With the `$`, it is the name of an argument that dynamically points to a message. `.`s in the name are resolved as object fields by the same rule as mentioned above. The final resolved data type must be a string.

The recurrences in `L_MESSAGE_ARG_LIST` are arguments to be passed to the message, where the `T_IDENTIFIER` is the argument name and the `L_ARG_VALUE` is the argument value. The argument value may be a string literal, a number or an argument reference.

##### 4.3.3.1. Number value literal
For numbers, negative and decimal numbers are supported. A `-` prefix (but not `+`) is allowed to indicate that the number is negative. Decimal places are placed behind a `.` separator. There is no exponential notation or non-decimal-base notation.

##### 4.3.3.2. String value literal
For strings literals, they must be quoted in `{}` braces. They are parsed with the same rules as a literal text. Nested argument/message references are permitted. However, recursion would always result in an error, even if it is for some reason finite.

Just like a message literal, if the value is empty, it must contain a `\0`.

##### 4.3.3.3. Argument reference
Arguments from the current message can be referenced by putting the name of the argument directly. `.`s in the name are resolved as object fields by the same rule as mentioned above. 

##### 4.3.3.4. Inner message reference
If the value should be a message that does not require any 

#### 4.3.4. Span
A span is a part of the message that gets decorated. Libglocal will calculate the appropriate color/format codes such that the message displays correctly. It has this syntax:

```bnf
L_SPAN ::= "%{" T_SPAN_NAME L_LITERAL "}"
T_SPAN_NAME ::= T_IDENTIFIER
```

`T_SPAN_NAME` indicates the format to be applied. Only the following values are allowed:

| `T_SPAN_NAME` | Description |
| :----: | :----: |
| **Styles** | Used for coloring whole sentences by type |
| `info` | Style for normal information, default white |
| `success` | Style for messages indicating success, default gray |
| `notice` | Style for less significant warnings, default aqua |
| `warn` | Style for more significant warnings, default yellow |
| `error` | Style for error messages, default red |
| **Highlight** | Used for emphasizing important words by color |
| `hl1` | default green |
| `hl2` | default light purple |
| `hl3` | default gold (yellow -> aqua) (aqua -> red) |
| `hl4` | default aqua (aqua -> red) (red -> yellow) |
| **Decoration** | Used to apply text decorations |
| `b` | bold |
| `i` | italic |
| `u` | underline |
| `s` | strikethrough |

The color codes can be customized by the plugin when initializing libglocal.

Due to the limited number of readable colors supported by clients, `hl3` and `hl4` use colors similar to other formats. If they have the same color as the words surrounding them (including parent span and child span), the fallback colors will be applied. This fallback behaviour can also be customized.

### 4.4. Message groups
In addition to the module name, messages in the same file can also be sub-grouped. A message group is declared by a block that contains only the message group name and has no text. The IDs of the messages inside the group will be prepended with the group ID, which is, recursively, the group name prepended with its parent group ID, or the module name if it is directly under `messages`.

### 4.5. Argument modifiers
Argument modifiers are allowed in overrides/implementations, only if they are used to declare math rules (or math rules for object fields). Argument types are not allowed in the argument modifiers of overrides/implementations, even though the type is not the default (`string`).

### 4.6. Doc modifiers

### 4.7. Version modifiers

## 5. Argument types
This section explains the argument types allowed.

### 5.1. `string`
String is the default data type.

#### 5.1.1. Input PHP values
PHP strings are used as-is. Objects that implement `SOFe\Libglocal\Stringable` or have a stringable mapping in configuration are converted to the `express()` value. Other values are not accepted.

#### 5.1.2. Constraints
`enum`/`pattern` can be declared multiple times for the same argument. If any `enum` or `pattern` constraints are declared, the accepted string value must be equal to one of the `enum` constraints or match one of the `pattern` regular expressions. The value is a literal, so the regex still needs to be escaped using literal rules. The pattern should be Perl-compatible. See https://php.net/pcre for documentation.

#### 5.1.3 Attributes
`case`: `lower`, `upper` or `ucfirst`, converting string case. If not set, string case would not be converted.

### 5.2. `int`/`float`
Int refers to the `int` PHP type, and float refers to the `float` PHP type.

#### 5.2.1. Input PHP values
Both `int` and `float` argument types accept PHP ints. The `float` argument type also accepts PHP floats. All other values are not accepted.

#### 5.2.2. Math rules
Math rules can be declared per int/float argument, in a similar structure with constraints. They are only visible to this argument in this message in this file, i.e. they are not visible to overriding messages and implementations in other languages. Unlike constraints, they can be declared in implementations, and they can 

In addition, if math rules are declared for the argument, the global math rules are ignored. Only the rules in this argument are used to classify the number.

If the rule name is empty, values matching the rule are classified into the "fallback rule", just as the numbers that do not match any rules.

#### 5.2.3. Constraints
Lines starting with `@@` are "restriction math rules". Unlike the math rules mentioned above, restriction math rules are real constraints used to restrict the argument value.

#### 5.2.3. Attributes
Although arithmetic operations might be useful, they are currently not being added into libglocal for the sake of simplicity. Since libglocal is designed to be a translations library, it would be unreasonable to express the same message with different values. If there are indeed such needs, developers should consider introducing an extra int/float parameter.

`INF`, `-INF` and `NAN` will fail all restriction math rules.

##### 5.2.3.1. Math rule replacement
`@{math rule}`: If the math rule named `{math rule}` is satisfied, the argument will be replaced with the string value of this attribute. A single `@` without the math rule name is the "fallback rule", which is used when none of the other math rules are matched. with the special argument `${1}` as the argument (the original name can still be used). Consider this example from libglocal stdlib, for ordinal numbers in English:

```
ordinal ${ord @one={${1}st} @two={${1}nd} @three={${1}rd} @={${1}th}}
	$ord int
		@one %10=1 %100<>11
		@two %10=2 %100<>12
		@three %10=3 %100<>13
```

A more complex example for ordinal numbers in Georgian:

```
ordinal ${ord @one={${1}-ლი} @many={მე-${1}} @={${1}-ე}
	$ord int
		@one %10=1
		@many =0
		@ %100=0
		@many %100>=2 %100<=20
		@many %20=0
```

A more common example would be to use it for quantities. For example, to express the number of players online:

```
online There are ${players @one={${1} player} @={${1} players}} online.
	$players int
```

Here, the global en_US rule `@one =1` declared in stdlib is used to match the integer.

Another possible usage is to generalize numbers, saying "There are many players online" or "There are few players online" if there are more than 30 players:

```
online There are ${players @many={many} @={few}} players online.
	$players int
		@many >30
```

##### 5.2.3.2. Float precision
If the `precision` attribute is present, the float is rounded to the nearest multiple of `10^(-n)`, where `n` is the value of the attribute. Alternatively, if the `sig` attribute, the float is displayed in `n` significant figures.

The `lpad` and `rpad` attributes would fill zeroes on the left and right of the decimal point to meet the length as specified by the attribute value. It would not perform truncation.

All of the above operations do not express the number in scientific notation.

The `INF`, `-INF` and `NAN` float values are expressed using the `stdlib.float.pinf`, `stdlib.float.ninf` and `stdlib.float.nan` messages. The expression can be changed using the `pinf`, `ninf` and `nan` attributes.

### 5.3. `bool`
Bool is the true/false type.

#### 5.3.1. Input PHP values
Only PHP values of the `bool` data type are accepted. Other values are unacceptable.

#### 5.3.2. Attributes
Although logical operators might be useful, they are currently not being added into libglocal for the sake of simplicity. Since libglocal is designed to be a translations library, it would be unreasonable to express the same message in multiple ways. If there are indeed such needs, developers should consider introducing an extra bool parameter.

The boolean value is expressed using the `stdlib.bool.true` and `stdlib.bool.false` messages respectively. To change the expression, the attributes `true` and `false` can be used instead, where the value should a string to be used. It is also possible to use this syntax, although not beautifully, for conditional messages: 

```
usage You ${is-op true={are} false={are not}} op. You may use /kill to suicide. ${is-op true={You may use /kill <player> to kill a player.} false={}}
	$is-op bool
```

### 5.4. `object`
Objects are complex arguments with a fixed structure.

#### 5.4.1. Input PHP values
Arrays and objects are accepted. For arrays and `ArrayAccess` objects, their values are accessed using the `$value[$field]` syntax. For other objects, their values are accessed using `$value->{$field}` (so they must be public or implement `__get()`). Other values are unacceptable.

Note that `isset()` calls are used before accessing the value. Therefore, `ArrayAccess` objects must ensure the proper functioning of both `offsetExists` and `offsetGet`, while objects implementing `__get()` must also have a valid `__isset()` function.

#### 5.4.2. Constraints
Object arguments must have at least one field constraint. The syntax of a field constraint is the same as the syntax of an argument modifier.

#### 5.4.3. Expression
Objects cannot be displayed directly. To use objects in arguments, the field reference operator `.` must be used just like `->` in PHP. For example, to access the field `bar` of an object argument `foo`, the syntax should be `${foo.bar}`.

### 5.5. `list:`
A list is a linear, ordered collection of values.

#### 5.5.1. Input PHP values
If an array is passed, its `array_values()` will be used. (Warning: if an array `[1 => "a", 0 => "b"]` is passed, it would be used as `["a", "b"]` rather than `["b", "a"]`, because `array_values()` ignores the keys and only cares about the array entry order.

Objects that implement Iterator are also acceptable. `Iterator->rewind()` is always called beforehand.

Other values are unacceptable.

#### 5.5.2. Constraints
`min` and `max` restrict the range of the size of the list.

#### 5.5.3. Attributes
The `delim` attribute can be used to indicate the separator between two elements of the list. The default value is a reference to the message `stdlib.list.delimiter`.

The `map` attribute can be used to indicate the format used in each element of the list. The mapped value can be accessed using the special parameter `${1}`. The key in the list can be accessed using the special argument `${0}`, although it is not recommended. (This may be deprecated in the future if there is to be an addition of a `mapping` type)
