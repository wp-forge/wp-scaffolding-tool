# WP Scaffolding Tool

A zero-configuration* scaffolding tool library built to be included in a WP-CLI package.

## Installation

```shell
composer require wp-forge/wp-scaffolding-tool
```

## Integration

Be sure that the `type` property in your `composer.json` file is set to `wp-cli-package`.

Autoload the file with the code below via Composer:

```php
<?php

use WP_Forge\WP_Scaffolding_Tool\Package;

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

new Package(
	[
		'base_command'             => 'forge',
		'template_config_filename' => 'config.json',
		'project_config_filename'  => '.wp-forge-project.json', 
		'global_config_filename'   => '.wp-forge.json',
		'default_template_repo'    => 'https://github.com/wp-forge/scaffolding-templates.git', 
	]
);
```

The following values are meant to be customized for your specific use case:

- **base_command** - The name of the base WP-CLI command. For example, a value of `forge` would result in available
  commands such as `wp forge init` and `wp forge make`.
- **template_config_filename** - The name of the config file used in scaffolding templates. When scaffolding a new
  entity, this JSON file is read to determine the user prompts and resulting actions.
- **project_config_filename** - The name of the project config file. This is generated when running the `init`
  subcommand.
- **global_config_filename** - The name of the global config file.
- **default_template_repo** - The Git repository URL where the scaffolding templates are located.

## Usage

All commands are self-documented by the tool. Simply type an available command followed by the `--help` flag for more
details.

**Get high-level documentation on available commands:**

```shell
wp <base_command> --help
```

**Get documentation for a specific command:**

```shell
wp <base_command> <subcommand> --help
```

#### Available commands:

- [repo](#the-repo-command)
- [make](#the-make-command)
- [init](#the-init-command)
- [template](#the-template-command)
- [config](#the-config-command)

### The `repo` Command

#### List

List all available template repositories:

```shell
wp <base_command> repo list
```

#### Clone

Clone a Git repository containing [scaffolding templates](#scaffolding-templates) so they will be locally available to
the tool.

```shell
wp <base_command> repo clone <repository_url>
```

When cloning a repository, you can optionally provide a name. This allows you to utilize multiple repositories
containing scaffolding templates from various sources. If you do not set a name, the system will use the name "default"
automatically.

```shell
wp <base_command> repo clone <repository_url> --as=<name>
```

#### Link

The `link` command registers a symlink to a local scaffolding template directory.

To create a new link, run:

```shell
wp <base_command> repo link ./path/to/templates --as=<name>
```

#### Update

To update a repository that has already been cloned, run the following command where the `name` is the registered name
for the scaffolding repository (the default value is `default`):

```shell
wp <base_command> repo update <name>
```

Where `name` is the name used when running the `repo clone` subcommand.

#### Delete

To delete a locally cloned repository, run:

```shell
wp <base_command> repo delete <name>
```

Where `name` is the name used when running the `repo clone` subcommand.

### The `make` Command

To scaffold using a template from a named repository, just prefix the entity name with your custom namespace.

For example, if you set the name to be `company`, and you wanted to scaffold a `wp-plugin`, then you would run this
command:

```shell
wp <base_command> make company:wp-plugin
```

This will ensure that the repository containing the `company` templates will be checked for the `wp-plugin` scaffolding.
In the event that you have multiple template sources configured and the requested template cannot be found under the
requested namespace, the tool will ask you if you want to check the other template sources for that template.

You can also use a path to leverage templates found nested in other folders.

```shell
wp <base_command> make company:github-actions/lint-php
```

The above command would look in the `~/.wp-cli/templates/company` folder for the template in
the `github-actions/lint-php` directory.

### The `init` Command

The `init` command will ask a series of questions and generate a project configuration file.

```shell
wp <base_command> init
```

Data found in the project configuration file will be automatically loaded when a command is run within the project. If a
scaffolding template requests a specific piece of information and it can be found in the project config file, then the
user won't see a prompt requesting that information.

### The `template` Command

#### List

To list all available scaffolding templates, run:

```shell
wp <base_command> template list
```

#### Create

To create a new scaffolding template in the current directory, run:

```shell
wp <base_command> template create
```

**Note:** *This feature is experimental and is still in active development.*

### The `config` Command

All subcommands will accept the `--global` flag. When used, all commands will apply to the global config file.
Otherwise, all commands will apply to the project config file.

#### Create

Create a new config file:

```shell
wp <base_command> config create [--global]
```

When used with the `--global` flag, an empty global config file will be created in the user's home directory.

When used without the `--global` flag, this will trigger the `init` command to create a project config.

#### Edit

Launch the system file editor to edit the config file:

```shell
wp <base_command> config edit [--global]
```

#### Has

Check if the config file has a specific value:

```shell
wp <base_command> config has <key> [--global]
```

Where `key` is the name of the JSON property. Dot notation can be used to reference nested properties.

#### Get

Get a value from a config file:

```shell
wp <base_command> config get <key> [--global]
```

Where `key` is the name of the JSON property. Dot notation can be used to reference nested properties.

#### Set

Set a value in a config file:

```shell
wp <base_command> config set <key> <value> [--global]
```

Where `key` is the name of the JSON property. Dot notation can be used to reference nested properties.

The `value` is the value to be set.

#### Delete

Delete a value in a config file:

```shell
wp <base_command> config delete <key> [--global]
```

Where `key` is the name of the JSON property. Dot notation can be used to reference nested properties.

#### List

List the settings from a config file:

```shell
wp <base_command> config list [--global]
```

#### Path

Get the path to a config file:

```shell
wp <base_command> config path [--global]
```

## Scaffolding Templates

In order to use this tool, you must first have a Git repository where you will host your scaffolding templates.

**Let's get started!**

> **Step 1:** Create a [new Git repository](https://github.com/new).

> **Step 2:** Create a folder in the repository for each thing you will want to scaffold. The name of the folder is the name you will use with the `make` command.

Examples of things you might want to scaffold:

- WordPress plugins
- WordPress themes
- WordPress sites
- Custom post types
- GitHub actions
- Other custom code you use frequently

> **Step 3:** Make sure you have a template config file (e.g. `config.json`) file in the template folder. This will 
> tell the CLI what to do with your template.

### Config Examples

A simple `config.json` file might look like this:

```json
{
  "directives": [
    {
      "action": "copy",
      "from": "lint-php.yml",
      "to": ".github/workflows/lint-php.yml",
      "relativeTo": "projectRoot"
    }
  ]
}
```

This would copy the `lint-php.yml` file from the template folder to the `.github/workflows/lint-php.yml` file relative
to the project root. You can provide multiple copy directives to copy not only files, but also entire directories. If
you want the path to be relative to the current directory where the CLI tool is being run, then just leave off
the `relativeTo` property or set its value to `workingDir`.

It is very common that you will want to replace placeholders in your templates. To facilitate this, you must first
collect the required information from the user.

You can add a `prompts` section to trigger these data requests in the CLI:

```json
{
  "prompts": [
    {
      "message": "What is your first name?",
      "name": "first_name",
      "type": "input"
    },
    {
      "message": "What country are you in?",
      "name": "country",
      "type": "input",
      "default": "United States"
    },
    {
      "message": "What is your favorite ice cream?",
      "name": "ice_cream",
      "type": "radio",
      "options": [
        "Chocolate",
        "Vanilla",
        "Strawberry"
      ]
    },
    {
      "message": "Select one or more taxonomies",
      "name": "taxonomies",
      "type": "checkboxes",
      "options": [
        "Categories",
        "Tags"
      ]
    }
  ]
}
```

With these prompts defined, you can now use the `name` field as a [Mustache](https://mustache.github.io/) placeholder in
any template file. You can also reference the name of any property from the project configuration file in your templates
without needing to prompt the user.

You can have a template leverage other templates by using the `runCommand` directive and calling the `make` command:

```json
{
  "directives": [
    {
      "action": "runCommand",
      "command": "wp forge make github-actions/lint-js"
    },
    {
      "action": "runCommand",
      "command": "wp forge make github-actions/lint-php"
    },
    {
      "action": "runCommand",
      "command": "wp forge make github-actions/lint-yml"
    }
  ]
}
```
