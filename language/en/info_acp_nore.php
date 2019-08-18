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
	'LOG_NORE_PMS_DONE'		=> '<strong>phpBB Studio</strong> - <em>No Re</em>: PMs parsed and cleaned via CLI<br>» Total: %s',
	'LOG_NORE_POSTS_DONE'	=> '<strong>phpBB Studio</strong> - <em>No Re</em>: Posts parsed and cleaned via CLI<br>» Total: %s',
	'LOG_NORE_TOPICS_DONE'	=> '<strong>phpBB Studio</strong> - <em>No Re</em>: Topics parsed and cleaned via CLI<br>» Total: %s',
	'LOG_NORE_FORUMS_DONE'	=> '<strong>phpBB Studio</strong> - <em>No Re</em>: Forums parsed and cleaned via CLI<br>» Total: %s',
]);
