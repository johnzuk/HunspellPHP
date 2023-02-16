# Hunspell PHP wrapper
Forked from [johnzuk/HunspellPHP](https://github.com/johnzuk/HunspellPHP)

### Version 2.0.0
Version 2.0.0 and above requires PHP ^8.0.0 and includes an important fix to the result matcher regex. If you need this for an older version of PHP I recommend that you fork 1.2 and update the regex matcher property of the Hunspell class to what is set in the current version of the code.

[View Changelog](CHANGELOG.md)

### The reason for this fork
This project was initially forked because the shell commands used were for a non-bash shell. This fork's main purpose was to convert the shell commands to a BASH compatible syntax and add support for Windows powershell. As such this fork will not work correctly outside of a bash or powershell environment.

An additional change was made to the parsing of the return value as the `PHP_EOL` value used in the original source was not working in my testing. This was changed to "\n" which resolved the issue.

Example
===================
```php
$hunspell = new \HunspellPHP\Hunspell();
var_dump($hunspell->find('otwórz'));
```
