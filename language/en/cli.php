<?php
/**
 *
 * phpBB Studio - No Re:. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/*
 * Some characters you may want to copy&paste:
 * ’ » “ ” …
 */
$lang = array_merge($lang, [
	'CLI_NORE'			=> 'phpBB Studio - No Re: console command',

	'CLI_NORE_HELLO'	=> '
 phpBB Studio: Hello user ;-)',

	'CLI_NORE_HELP'		=> 'phpBB Studio - No Re:
Finds and removes all “Re: ” from (last) post subjects.',

	'CLI_NORE_INVALID'	=> 'The “%1$s” argument must be one of: %2$s.',

	'CLI_NORE_START'	=> '
 phpBB Studio - No Re: starting job...',

	'CLI_NORE_CONFIRM'	=> '
 phpBB Studio - Confirmation requested.

 The following operation can be reverted only via a database backup.

 Are you sure you want to remove “Re: ” from all ‘%s’ [y]/[n]',

	'CLI_NORE_DONE'		=> [
		0	=> 'phpBB Studio - Done, no “Re: ” needed to be cleaned from %2$s.',
		1	=> 'phpBB Studio - Done, %d “Re: ” was cleaned from %2$s.',
		2	=> 'phpBB Studio - Done, %d “Re: ” were cleaned from %2$s.',
	],
]);
