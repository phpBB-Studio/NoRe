# phpBB Studio - No Re:

## Installation

Copy the extension to phpBB/ext/phpbbstudio/nore

Go to "ACP" > "Customise" > "Extensions" and enable the "phpBB Studio - No Re:" extension.


The extension removes the prefix "Re : " from posts and PMs in the Reply or Quote process.

The difference with the existing one is that it uses 2 new events

    https://github.com/phpbb/phpbb/pull/5629
    https://github.com/phpbb/phpbb/pull/5630

parses also PMs and carries out its work with only 2 lines of code, not using templates but just PHP.

In addition, we have provided an CLI command with 4 modes which allow you to reparse and remove the prefix from all over the database.

## Command Line Interface

The command: `php bin/phpbbcli.php phpbbstudio:nore`

The “mode” argument must be one of: `forums|topics|posts|pms`

Example: `php bin/phpbbcli.php phpbbstudio:nore posts`
