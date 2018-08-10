##Hunspell PHP wrapper
This is a hunspell php wrapper.

This fork changes the shell commands with windows and bash style shells. This will no longer work on the system the original author wrote it on. I'm not sure what type of shell they were using but the manner in which they were setting the environment variable does not work on windows or centos/bash. This fork addresses that problem by using the windows "set" command via powershell which supports piping. The command for non windows machines was changed to use "export" for setting the environment.

An additional change was made to the parsing of the return value as the `PHP_EOL` value used in the original source was not working on my machine. This was changed to "\n" and it works great.


Example
===================
```php
$hunspell = new \HunspellPHP\Hunspell();

var_dump($hunspell->find('otwórz'));
```
