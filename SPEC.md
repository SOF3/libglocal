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
`require` (`B_META_REQUIRE` is an optional meta block that indicates the dependencies required by

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
L_ARG_REF ::= "${" T_IDENTIFIER "}"
```

The `T_IDENTIFIER` is the reference to the argument. If the argument is an object, the `.`s in the identifier will follow the path into the object fields just like the object field resolution syntax in C, Java, etc. If the argument is a list of object, a list of list of objects, etc., the conversion is performed per object and the list structure is preserved.

#### 4.3.3. Message reference
A message reference is replaced by resolving the referenced message. It has this syntax:

```bnf
L_MESSAGE_REF ::= "#{" ["$"] T_IDENTIFIER L_MESSAGE_ARG_LIST "}"
L_MESSAGE_ARG_LIST ::= (T_IDENTIFIER "=" L_ARG_VALUE [","])*
```

Message references can be constant or dynamic. Without the `$`, the first `T_IDENTIFIER` is the constant name of another message. With the `$`, it is the name of an argument that dynamically points to a message. `.`s in the name are resolved as object fields by the same rule as mentioned above. The final resolved data type must be a string.

The recurrences in `L_MESSAGE_ARG_LIST` are arguments to be passed to the message, where the `T_IDENTIFIER` is the argument name and the `L_ARG_VALUE` is the argument value. The argument value may be a string literal, a number or an argument reference.

##### 4.3.3.1. Number value literal
For numbers, negative and decimal numbers are supported. A `-` prefix (but not `+`) is allowed to indicate that the number is negative. Decimal places are placed behind a `.` separator. There is no exponential notation or non-decimal-base notation.

##### 4.3.3.2. String value literal
For strings literals, they must be quoted in `{}` braces. They are parsed with the same rules as a literal text. Nested argument/message references are permitted. However, recursion would always result in an error, even if it is for some reason finite.

Just like a message literal, if the value is empty, it must contain a `\0`.

##### 4.3.3.3. Argument reference
Arguments from the current message can be referenced by putting the name of the argument directly. `.`s in the name are resolved as object fields by the same rule as mentioned above.

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

### 4.6. Doc modifiers

### 4.7. Version modifiers
