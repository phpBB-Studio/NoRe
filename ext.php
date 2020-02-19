<?php
/**
 *
 * phpBB Studio - No Re:. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\nore;

/**
 * phpBB Studio - No Re: Extension base
 */
class ext extends \phpbb\extension\base
{
	/**
	 * @var array An array of installation error messages
	 */
	protected $errors = [];

	/**
	 * Check whether or not the extension can be enabled.
	 *
	 * @return bool|array True if can be enabled. An array of error messages otherwise.
	 */
	public function is_enableable()
	{
		if (phpbb_version_compare(PHPBB_VERSION, '3.3.0-b1', '>='))
		{
			$this->phpbb_requirement();
			$this->php_requirement();

			return count($this->errors) ? $this->enable_failed() : true;
		}
		else
		{
			$is_enableable = true;

			if (!$this->phpbb_requirement())
			{
				list($user, $lang) = $this->get_language();

				$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('ERROR_PHPBB_VERSION');
				$is_enableable = false;

				$user->lang = $lang;
			}

			if (!$this->php_requirement())
			{
				list($user, $lang) = $this->get_language();

				$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('ERROR_PHP_VERSION');
				$is_enableable = false;

				$user->lang = $lang;
			}

			return $is_enableable;
		}
	}

	/**
	 * Generate the best enable failed response for phpBB 3.1 and 3.2.
	 *
	 * @return array
	 */
	protected function get_language()
	{
		$user = $this->container->get('user');
		$lang = $user->lang;

		$user->add_lang_ext('phpbbstudio/nore', 'ext_require');

		return [$user, $lang];
	}

	/**
	 * Check phpBB 3.2.9 minimum requirement.
	 *
	 * @return bool
	 */
	protected function phpbb_requirement()
	{
		if (!(phpbb_version_compare(PHPBB_VERSION, '3.2.9', '>=') && phpbb_version_compare(PHPBB_VERSION, '4.0.0@dev', '<')))
		{
			$this->errors[] = 'ERROR_PHPBB_VERSION';
			return false;
		}
		return true;
	}

	/**
	 * Check PHP 5.4.7 minimum requirement.
	 *
	 * @return bool
	 */
	protected function php_requirement()
	{
		if (phpbb_version_compare(PHP_VERSION, '5.4.7', '<') && phpbb_version_compare(PHPBB_VERSION, '3.3.0-b1', '<'))
		{
			return false;
		}

		if (phpbb_version_compare(PHP_VERSION, '7.1.3', '<') && phpbb_version_compare(PHPBB_VERSION, '3.3.0-b1', '>='))
		{
			$this->errors[] = 'ERROR_PHP_VERSION_OBERON';

			return false;
		}

		return true;
	}

	/**
	 * Generate the best enable failed response for phpBB 3.3 or newer.
	 *
	 * @return array
	 */
	protected function enable_failed()
	{
		$language = $this->container->get('language');
		$language->add_lang('ext_require', 'phpbbstudio/nore');

		return array_map([$language, 'lang'], $this->errors);
	}
}
