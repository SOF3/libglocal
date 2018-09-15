Guide for developers, translators and server owners
===
> Updated for libglocal version 0.3.0

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
base lang en_US English (US)
author AuthorName
version 0.1.0

messages PluginName
	my-first-message= Hello world!
```
</details>

Try calling `$player->sendMessage($this->lang->t($player, "PluginName.my-first-message"));` from your plugin. It should send "Hello world!" to the player.

Remember to replace `PluginName`, `AuthorName` with the plugin's name and author. `0.1.0` is the version for the base file, but it should resemble the plugin version, because it should be bumped every time messages in the plugin are changed publicly.

### For translators
<details><summary>For translators</summary>

First, find the language code of the language you want to translate into. It should match the Minecraft client language codes. [Minecraft Wiki](https://minecraft.gamepedia.com/Language) has a table for this. In this part, I assume your language code is `zh_TW`.

Under the lang folder, create a file like this:

```libglocal
lang zh_TW 繁體中文
author AuthorName
version 0.1.0

messages PluginName
```
</details>

Replace `PluginName`, `AuthorName` and `0.1.0` with the plugin's name, author and version.

### For server owners
<details><summary>For server owners></summary>

After downloading the plugin that uses libglocal, restart the server. Libglocal will generate the template lang files under the plugin's data folder. You may find one or multiple `.lang` files. Open them with [Notepad++](https://notepad-plus-plus.org) or any plain text editor you like. If you want to edit a message, delete the `//` at the start of the line you changed and the surrounding lines separated by empty lines.

For example, if the generated template is like this:

<details><summary>Original template</summary>

```libglocal
lang en_US English (US)
version 0.5.0

messages ExamplePlugin

//  lorem.ipsum = Dolor sit amet, consectetur adipiscing elit.
//    for 1.0

//  lorem.sed = Do eiusmod tempor incididunt ut labore et dolore magna aliqua.

//  lorem.ut = enim ad minim veniam, quis nostrud exercitation ullamco laboris.
//    for 1.0

//  nisi.in = Voluptate velit esse cillum dolore eu fugiat nulla pariatur.
//    | Excepteur sint non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
//    for 1.0

//  nisi.ut = Aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit.
//    for 1.0
```
</details>

To change `Excepteur sint` to `Occaecat cupidatat`, this section becomes like this:

<details><summary>Edited file</summary>

```libglocal
lang en_US English (US)
version 0.5.0

messages ExamplePlugin

//  lorem.ipsum = Dolor sit amet, consectetur adipiscing elit.
//    for 1.0

//  lorem.sed = Do eiusmod tempor incididunt ut labore et dolore magna aliqua.

//  lorem.ut = enim ad minim veniam, quis nostrud exercitation ullamco laboris.
//    for 1.0

  nisi.in = Voluptate velit esse cillum dolore eu fugiat nulla pariatur.
    | Occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
    for 1.0

//  nisi.ut = Aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit.
//    for 1.0
```
</details>
