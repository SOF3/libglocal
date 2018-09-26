Guide for developers, translators and server owners to libglocal v0.4.0
===

## What is libglocal?
Libglocal is a library for PocketMine plugins to support multi-language display. Here are some highlighted features:
- Message arguments
	- Named argument substitution, e.g. `${foo}`
	- Argument types are checked
		- Supports complex argument: lists (simple arrays, ArrayAccess) and objects (associative arrays, traversable objects)
	- Arguments can be optional and use constant/fallback default values
	- Math rules to check numbers and display conditionally, e.g. `${cows @one={a cow} @={${1} cows}}`
- Message referencing, e.g. `#{message.name}`
	- A message can be included into another message for reuse, just like calling one function from another
- Styled comments
	- Apply stacked and named format spans, e.g. `%{error You are %{b not} an admin!}`
	- Works correctly even if included from another message
- Versioning system to update user customizations as plugin is upgraded
- Module system to make management easier
- Customization
	- Translations can be customized by server owner
	- Individual players and console can choose their own language

## The lifecycle of translations
![libglocal-flow.png](libglocal-flow.png)

In addition, libraries can be included before the plugin's lang files to facilitate the translation process.

To support this lifecycle, libglocal introduced a module system and a versioning scheme:
- Each module represents one plugin or one part of a big plugin, or represents a library
- Each module must have one and exactly one base lang file
- Modules can have dependencies such that one module cannot be loaded without another module. Dependency can be cyclic. Module (base lang file) load order is mostly random and is not affected by the dependency relationship.
- Each auxiliary/custom lang file translation *implements* a message from the base lang file
- Each message from the base lang file has a version (default is `null`, the lowest possible version)
- Each *implementation* targets at a version of the implemented message. If the implemented message has a newer version, the implementation is ignored.
- Auxiliary files are the lang files embedded with the plugin and the lang files downloaded automatically. Custom lang files are the ones found in the `plugin_data` folder.
- Non-base load order: embedded -> downloaded -> custom
- One module may have multiple non-base lang files even in the same language, and each message may be implemented multiple times in the same language. The last-loaded message is used.

## Getting started

### For developers
<details><summary>For developers</summary>

To use libglocal, just add this line to `onEnable()`: `$this->lang = Libglocal::init($this);` with the use statement `use SOFe\Libglocal\Libglocal`.

When you send a message, replace the message with `$this->lang->t($sender, "message.id", $args)`, where `$sender` is the recipient and `"message.id"` is the ID of the message to be translated. `$args` is an optional array that contains the parameters.

Then create the folder `lang` under `resources`, and create a file `en_US.lang` inside. (You can change `en_US` to another base language you like, but `en_US` is recommended for _base_ language, because that's usually the language most translators understand)

Then copy this into `en_US.lang`:

<details><summary>Template base lang file</summary>

```libglocal
base lang en_US = English (US)
author= AuthorName
version 0.1.0

module PluginName
my-first-message= Hello world!
```
</details>

Try calling `$player->sendMessage($this->lang->t($player, "PluginName.my-first-message"));` from your plugin. It should send "Hello world!" to the player.

Remember to replace `PluginName`, `AuthorName` with the plugin's name and author. `0.1.0` is the version for the base file, but it should resemble the plugin version, because it should be bumped every time messages in the plugin are changed publicly.
</details>

To translate a message, just write it like a base lang file. However, you don't need to (and should not) declare the arguments and docs again, because you are not defining the message.

### For translators
<details><summary>For translators</summary>

First, find the language code of the language you want to translate into. It should match the Minecraft client language codes. [Minecraft Wiki](https://minecraft.gamepedia.com/Language) has a table for this. In this part, I assume your language code is `zh_TW`.

Under the lang folder, create a file like this:

```libglocal
lang zh_TW = 繁體中文
author= AuthorName

module ModuleName
```
</details>

Replace `AuthorName` with your (translator's) name. `ModuleName` is the name found behind the `messages` line of the base file, which is typically the plugin name. `0.1.0` should be replaced with the base lang version are translating.

### For server owners
<details><summary>For server owners</summary>

After downloading the plugin that uses libglocal, restart the server. Libglocal will generate the template lang files under the plugin's data folder. You may find one or multiple `.lang` files. Open them with [Notepad++](https://notepad-plus-plus.org) or any plain text editor you like. If you want to edit a message, delete the `//` at the start of the line you changed and the surrounding lines separated by empty lines.

For example, if the generated template is like this:

<details><summary>Original template</summary>

```libglocal
lang en_US = English (US)
version 0.5.0

module ExamplePlugin

//lorem.ipsum = Dolor sit amet, consectetur adipiscing elit.
//  ~1.0

//lorem.sed = Do eiusmod tempor incididunt ut labore et dolore magna aliqua.

//lorem.ut = enim ad minim veniam, quis nostrud exercitation ullamco laboris.
//  ~1.0

//nisi.in = Voluptate velit esse cillum dolore eu fugiat nulla pariatur.
//  | Excepteur sint non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
//  ~1.0

//nisi.ut = Aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit.
//  ~1.0
```
</details>

To change `Excepteur sint` to `Occaecat cupidatat`, this section becomes like this:

<details><summary>Edited file</summary>

```libglocal
lang en_US = English (US)
version 0.5.0

module ExamplePlugin

//lorem.ipsum = Dolor sit amet, consectetur adipiscing elit.
//  ~1.0

//lorem.sed = Do eiusmod tempor incididunt ut labore et dolore magna aliqua.

//lorem.ut = enim ad minim veniam, quis nostrud exercitation ullamco laboris.
//  ~1.0

nisi.in = Voluptate velit esse cillum dolore eu fugiat nulla pariatur.
  | Occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
  ~1.0

//nisi.ut = Aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit.
//  ~1.0
```
</details>

## Libglocal file format
### Lexing rules
<details>
<summary>Technical details</summary>

The generic rules for libglocal file syntax:
- **EMPTY**: If the line only consists of spaces and tabs (_whitespace characters_), the line is called an "empty line".
- **COMMENT**: If the line starts with `//` (excluding leading spaces), it is a comment line
- **INDENT**: The leading _whitespace characters_ of the line are called the _indent_. They control the line groupings.
- **INDENT_CHILD**: If the _indent_ of a line starts with that of the previous line and is longer, the line is a _child_ of the previous line.
- **INDENT_SIBLING**: If two lines have the same _indent_ and all lines between them (if any) are _descendents_ (_children_ or recursive _children_) of the first line, the two lines are _siblings_.
- **INDENT_ERROR**: If a line is not the first line and cannot be identified as a _child_ of the previous line or as the _sibling_ of any lines before it, it has an indentation error.
- **IDENTIFIER**: An _identifier_ is a consecutive string containing only one or more latin alphabets `A-Z` `a-z`, digits `0-9`, hyphens `-`, underscores `_` and dots `.`.
- **IDENTIFIER_LIST**: A line consists of one or multiple _identifiers_, separated by _whitespace characters_.
- **IDENTIFIER_SYMBOL**: `~` is parsed as an _identifier_ only if placed at the start of a line. It can be followed by normal _identifiers_ without _whitespace characters_ in between.
- **IDENTIFIER_FLAG**: An _identifier_ (except the last one) can be followed by a `:` character, which means the _identifier_ is a _flag_ on the _identifier_ following it. There must be no _whitespace characters_ around the colon. There may be multiple _flags_ on the same _identifier_.
- **IDENTIFIER_DELIM**: The last _identifier_ can be followed by a `=` character. The part behind the `=`, separated by zero or more _whitespace characters_, is a _literal_.
- **IDENTIFIER_ARG**: `$` is parsed as an _identifier_ only if placed at the start of a line, but behind the `=` of that line should be an _attribute value_ not a _literal_.
- **LITERAL_SYMBOL**: `*` functions the same way to the lexer as a `=` if placed at the start of a line, so it can be followed by a _literal_ after zero or more _whitespace characters_.
- **LITERAL**: A _literal_ consists of zero or multiple of the following components:
  - Simple static components: *LITERAL_SIMPLE*, *LITERAL_ESCAPE*, *LITERAL_CONT*
  - Complex static components: *LITERAL_SPAN*
  - Complex dynamic components: *LITERAL_REF*
- **LITERAL_SIMPLE**: A **LITERAL_SIMPLE** component consists of one or multiple consecutive UTF-8 characters except `#` `$` `%` `\` `}`  and _newline_ (Unix-style LF `\n` or Windows-style CRLF `\r\n`).
- **LITERAL_ESCAPE**: A **LITERAL_ESCAPE** component consists of a `\` followed by one ASCII character. (Only certain characters are allowed behind the `\`, but the restriction is not part of the lexing rules)
- **LITERAL_CONT**: A **LITERAL_CONT** component consists of a _newline_ followed by zero or multiple _whitespace characters_, then one of `!`, `|` or `\`. The _whitespace characters_ in between are NOT parsed using **INDENT** rules.
- **LITERAL_SPAN**: A **LITERAL_SPAN** component has the format (`%{`, _identifier_, _whitespace characters_, _literal_, `}`).
- **LITERAL_REF**: A **LITERAL_REF** component starts with `#{`, `${` or `#{$`, followed by an _identifier_, finally a `}`. There can be an _attribute list_ between the _identifier_ and the `}`.
- **ATTRIBUTE_LIST**: An _attribute list_ is a recurring sequence of (_attribute name_, `=`, _attribute value_), delimited by a `}`. _Attribute name_ is an _identifier_ with an optional `@` in front of it. _Attribute value_ is either a _number_ (an unsigned or negative-signed integer or decimal number), or an _identifier_, or a pair of `{}` with a _literal_ inside.
- **MATH_SYMBOL**: `@` placed at the start of a line indicates that the line is a math rule. It must be followed by one or more `@`, or an _identifier_, or both, then _whitespace characters_. After that, _numbers_, modulus sign `@` and _comparator_ (one of `=` `!=` `<>` `<=` `>=` `<` `>`) and _whitespace characters_ are allowed in the line.
</details>

### Metadata part
The file consists of two parts. The first part is the metadata part, which can contain
- a required `lang`/`base lang` statement
- zero or multiple `author` statements
- an optional `version` statement
- zero or multiple `require` statements
- zero or multiple `use` statements

The `lang`/`base lang` statement indicates the language of the file. It should be in the format `base lang lang_id = Language Name` or `lang lang_id = Language Name`, where `lang_id` is the [language code](https://minecraft.gamepedia.com/Language) and `Lauguage Name` is the local name of the language.

`author` statements list the author names. All translators should be included in the author list. The format is `author = AuthorName`.

> Note: Only the `author` and `lang` statements require a `=`. A convenient way to memorize: If the last parameter may contain a space, it must follow a `=`.

### Messages part
The second part contains the messages. First it should have a line `module ModuleName`, where `ModuleName` is the name of the file's module (explained in the [Getting started](#getting-started) section). Then the following lines contain messages. Each message has a simple format:

```
message-id = Message content
```

where `message-id` is the message ID, and `Message content` is the message text.

## Messages
A message is something to be translated. It can be a sentence, a big passage, or just one word. Each message must be defined in and only in one base file (except `local` messages, which are defined in the first file loaded with it). Then it can be overridden in auxiliary and custom lang files. The last-loaded files will override the rest of files.

## Message groups
If two messages have the same part before `.` in the ID, they are grouped together, and groups can also be grouped in parent groups. For example, the message `a.b.c` is in the group `a.b`, which is in the group `a`. Message groups can also be written in blocked format. For example, the following codes are equivalent:

<details>

```
lorem.ipsum = Dolor sit amet.
lorem.ut.enim = Ad minim veniam.
```

```
lorem
  ipsum = Dolor sit amet.
  ut.enim = Ad minim veniam.
```

```
lorem
  ipsum = Dolor sit amet.
  ut
    enim = Ad minim veniam.
```

</details>

## Escape sequences
The characters `#$%}\` may have special meaning in libglocal. The characters `}` and `\` must be escaped by using `\}` and `\\` instead.

Normally, `#`, `$` and `%` do not need to be escaped, but if they are followed by a `{`, they must be escaped by using `\#`, `\$` and `\%`  instead.

The leading and trailing spaces and tabs before and after each message (i.e. after the `=` sign or at the end of line) will be ignored. If you really want an actual space there, use `\s` to replace the space. It is also possible to use `\0`, which gets converted into nothing (not even a space), if it is placed at the beginning/end, the spaces after/before it will not get ignored.

In addition, messages are only written on one line. To break the message into two lines, use a `\n` sequence. Alternatively, _continuation sequences_ can be used.

## Continuation sequences
Sometimes lines are too long and it might be useful to break them into multiple lines. This is possible using continuation sequences.

There are three types of continuation sequences: space continuation (`|`), newline continuation (`!`) and concat continuation (`\`). Simply put part of the message on the second line with the continuation character (`|!\`) at the beginning of the second line. There can be any number of spaces and tabs around the continuation character, which will be ignored (without applying indent block rules). For example, the following two codes are equivalent:

<details>

```
lorem.ipsum = Dolor sit amet.\nAd minim veniam.
```

```
lorem.ipsum = Dolor sit
    | amet.
  ! Ad minim
    | ven
    \iam.
```

</details>

`|` is most useful in space-separated languages like English and Spanish, while `\` is most useful in non-space-separated languages like Chinese and Japanese. Conventionally, `!` continuations are further indented by one level, while `|` and `\` are further indented by two levels. However, this is not mandatory.

## Spans

## Arguments
Arguments must be declared in the message definition in this syntax:

```
lorem.ipsum = Dolor ${sit} ${amet}.
  $sit
  $amet
```

This snippet declares two variables `sit` and `amet` and uses them in the message.

Arguments can be optional and take default values using the `= value` syntax. The value is in the _attribute value_ format. See the [Attribute format](#attribute-value-format) section for details. For example:

```
lorem = Dolor ${sit} ${amet}.
  $sit = {ipsum}
  $amet = sit
```

The default value of `${sit}` is "ipsum", and that of `${amet}` is equal to the value taken by `${sit}`, i.e. the value passed for `${sit}` if any, or "ipsum" if `${sit}` is not passed from the plugin (or the [message reference](#message-references) using).

## Argument types
There are 4 simple types and 2 complex types: `string`, `int`, `float`, `bool`, `list`, `object`.

Different argument types may accept constraints and attributes. Constraints are child blocks of the argument declaration, so they can only be used in the base lang file. Attributes are used in an [attribute list](#attribute-list) behind the argument name, so they are different in every implementation.

### `string` type

#### PHP value
#### Default value
#### Constraints
#### Attributes

### `int` type

#### PHP value
#### Default value
#### Constraints
#### Attributes

### `float` type

#### PHP value
#### Default value
#### Constraints
#### Attributes

### `bool` type

#### PHP value
#### Default value
#### Constraints
#### Attributes

### `list` type

#### PHP value
#### Default value
#### Constraints
#### Attributes

### `object` type

#### PHP value
#### Default value
#### Constraints
#### Attributes

## Attribute list
An attribute list is a sequence of attribute names and attribute values. Each attribute has the format `name = value`, where `name` is an identifier and `value` follows the [attribute value format](#attribute-value-format). The `=` is optional and can be replaced by whitespaces, but this makes the syntax confusing and is not encouraged.

The attribute name can alternative be a reference to a math rule in the format `@`+identifier, or just a bare `@`.

### Attribute value format
There are 4 types of attribute formats: string literal, number literal, message ref and arg ref.

A string literal is simply a literal (like message values) enclosed by a pair of `{}`.

A number literal is an integer or a decimal number (in the format `xxx.xxx`), optionally signed negatively (positive sign is not allowed).

A message ref is a `#` sign followed by an identifier, which points to the message ID indicated in the identifier. The referenced message must not take any arguments. If arguments are to be passed, the string literal format with a [message reference](#message-references) inside should be used, like this: `{#{lorem.ipsum arg1={value}}}`

An arg ref is simply an identifier (without a leading `$`), which points to the argument indicated in the identifier. This format does not allow using attributes on the referenced argument. To use attributes, the string literal format with an argument reference inside should be used, like this: `{${$arg attribute={value}}}`

## Message references
Messages can include other messages using the message reference format:

```
lorem.ipsum = dolor ${sit} amet
  $sit

ut.enim = Ad minim #{lorem.ipsum sit=veniam}.
  $veniam
```

In this example, the value of `ut.enim` is equivalent to `Ad minim dolor ${veniam} amet.`. `sit=veniam` means that `${veniam}` is used as the value for the argument `${sit}` in `lorem.ipsum`. This argument list is in the [attribute list format](#attribute-list).

The message ID may be fully-qualified or relative. Relative IDs start with a `.`, which means "the parent group of the current message". Each additional `.` means "the parent group of the referenced group", so `..` means the grandparent group, `...` means the great-grandparent group, vice versa. The ID may also be aliased using the `use` statements in the metadata part of the file.

The message ID can also be dynamic using the `$` prefix. Consider this example:

```
lorem.ipsum = Dolor #{$sit} amet.
  $sit
```

The argument `$sit` here should accept a fully-qualified message ID. (Relative IDs are not allowed)

## Message visibility
Some messages may want to be referenced only from other messages. This can be achieved by message visibility.

There are 4 message visibilities: `public`, `lib`, `module` and `local`. This table explains the visibilities:

| Visibility | Can be used from plugin | Can be used/overridden from other languages | Can be used/overridden from other modules | Can be used/overridden from the same language and module | Can be declared in non-base lang files |
| :---: | :---: | :---: | :---: | :---: | :---: |
| `public` | Yes | Yes | Yes | Yes | No |
| `lib` | No | Yes | Yes | Yes | No |
| `module` | No | Yes | No | Yes | No |
| `local` | No | No | Yes | Yes | Yes |

The visibility can be declared as a flag on the message ID of the declaration, e.g. `lib:lorem.ipsum = Dolor sit amet.`. Note that the visibility should only be declared in the definition (i.e. the base lang file for `public`, `lib` and `module`).

`public:` is the default visibility and needs not be declared.

## Modules

## Versioning
