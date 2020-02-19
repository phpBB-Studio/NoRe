<?php
/**
 *
 * phpBB Studio - No Re:. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\nore\console\command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * phpBB Studio - No Re: console command.
 */
class nore extends \phpbb\console\command\command
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var string Forums table */
	protected $forums_table;

	/** @var string Topics table */
	protected $topics_table;

	/** @var string Posts table */
	protected $posts_table;

	/** @var string Private Message table */
	protected $pms_table;

	/** @var ProgressBar */
	protected $progress;

	/** @var string */
	protected $re = 'Re: ';

	/** @var array */
	protected $modes = [
		'forums'	=> 'forum_last_post_subject',
		'topics'	=> 'topic_last_post_subject',
		'posts'		=> 'post_subject',
		'pms'		=> 'message_subject',
	];

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\db\driver\driver_interface	$db				Database object
	 * @param  \phpbb\language\language				$language		Language object
	 * @param  \phpbb\log\log						$log			Log object
	 * @param  \phpbb\user							$user			User object
	 * @param  string								$forums_table	Forums table
	 * @param  string								$topics_table	Topics table
	 * @param  string								$posts_table	Posts table
	 * @param  string								$pms_table		PM table
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\user $user,
		$forums_table,
		$topics_table,
		$posts_table,
		$pms_table
	)
	{
		$this->db			= $db;
		$this->language		= $language;
		$this->log			= $log;

		$this->forums_table	= $forums_table;
		$this->topics_table	= $topics_table;
		$this->posts_table	= $posts_table;
		$this->pms_table	= $pms_table;

		parent::__construct($user);
	}

	/**
	 * Configure the "phpbbstudio:nore" command.
	 *
	 * @status protected
	 * @return void
	 */
	protected function configure()
	{
		$this->language->add_lang('cli', 'phpbbstudio/nore');

		$this
			->setName('phpbbstudio:nore')
			->setDescription($this->language->lang('CLI_NORE'))
			->setHelp($this->language->lang('CLI_NORE_HELP'))
			->addArgument('mode', InputArgument::REQUIRED, implode('|', array_keys($this->modes)));
	}

	/**
	 * Interact with the user.
	 *
	 * Confirm they really want to carry out this operation!
	 *
	 * @param  InputInterface	$input		An InputInterface instance
	 * @param  OutputInterface	$output		An OutputInterface instance
	 * @access protected
	 * @return void
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		/* Validate the user input */
		$input->validate();

		/* Request the "mode" argument */
		$mode = $input->getArgument('mode');

		/* Make sure it is a valid mode */
		if (!in_array($mode, array_keys($this->modes)))
		{
			throw new RuntimeException($this->language->lang('CLI_NORE_INVALID', 'mode', implode('|', array_keys($this->modes))));

		}

		$helper = $this->getHelper('question');

		$question = new ConfirmationQuestion(
			$this->language->lang('CLI_NORE_CONFIRM', $this->get_mode_lang($mode)),
			false
		);

		if (!$helper->ask($input, $output, $question))
		{
			/* Confirm box was answered with "N" */
			$input->setArgument('mode', false);
		}
	}

	/**
	 * Execute the "phpbbstudio:nore" command.
	 *
	 * @param  InputInterface	$input		An InputInterface instance
	 * @param  OutputInterface	$output		An OutputInterface instance
	 * @return void
	 * @access protected
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/* Request the "mode" argument */
		$mode = $input->getArgument('mode');

		/* Make sure it's not false (confirm box answered with "N") */
		if ($mode)
		{
			/* Set up base values */
			$column	= $this->modes[$mode];
			$table	= $this->{$mode . '_table'};
			$where	= $this->get_sql_where($column);

			$output->writeln($this->language->lang('CLI_NORE_HELLO'));

			$io = new SymfonyStyle($input, $output);

			$count = 0;
			$limit = 100;
			$total = $this->get_count($column, $table, $where);

			if ($total !== 0)
			{
				$io->section($this->language->lang('CLI_NORE_START'));

				$this->progress = $this->create_progress_bar($total, $io, $output);

				$this->progress->setMessage($this->language->lang('CLI_NORE_START'));

				$this->progress->start();

				while ($count < $total)
				{
					$count += $this->clean_table($column, $table, $where, $limit);

					$this->progress->advance();
				}

				$this->progress->finish();
			}

			$this->log->add('admin', ANONYMOUS, '', 'LOG_NORE_' . utf8_strtoupper($mode) . '_DONE', false, array($count));

			$io->newLine(2);

			$io->success($this->language->lang('CLI_NORE_DONE', $count, $this->get_mode_lang($mode)));
		}
	}

	/**
	 * Clean a table, removing all "Re: " from a post subject.
	 *
	 * @param  string	$column		The column name
	 * @param  string	$table		The table name
	 * @param  string	$where		The SQL WHERE statement
	 * @param  int		$limit		The maximum of rows to updates
	 * @return int					The amount of rows affected by the SQL query.
	 * @access protected
	 */
	protected function clean_table($column, $table, $where, $limit)
	{
		$length = $this->get_sql_length();
		$strlen = strlen($this->re);

		/**
		 * Update a column which starts with "Re: ".
		 *
		 * 	UPDATE phpbb_posts
		 * 	SET post_subject = SUBSTRING(post_subject, 4, LENGTH(post_subject))
		 * 	WHERE post_subject LIKE 'Re: %'
		 */

		$sql = "UPDATE {$table} 
			SET {$column} = SUBSTRING({$column}, {$strlen}, {$length}({$column}))
			WHERE {$where}";
		$this->db->sql_query_limit($sql, $limit);

		return (int) $this->db->sql_affectedrows();
	}

	/**
	 * Get the amount of rows that need to be cleaned.
	 *
	 * @param  string	$column		The column name
	 * @param  string	$table		The table name
	 * @param  string	$where		The SQL WHERE statement
	 * @return int					The amount of rows that need to be cleaned
	 * @access protected
	 */
	protected function get_count($column, $table, $where)
	{
		$sql = "SELECT COUNT({$column}) as count
			FROM {$table} 
			WHERE {$where}";
		$result = $this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('count');
		$this->db->sql_freeresult($result);

		return (int) $count;
	}

	/**
	 * Get the SQL WHERE statement for finding subjects starting with "Re: ".
	 *
	 * @param  string	$column		The column name
	 * @return string
	 * @access protected
	 */
	protected function get_sql_where($column)
	{
		$like = $this->db->sql_like_expression($this->re . $this->db->get_any_char());

		return "{$column} {$like}";
	}

	/**
	 * Get the SQL LENGTH function for the current SQL layer.
	 *
	 * @return string				The LENGTH function
	 * @access protected
	 */
	protected function get_sql_length()
	{
		switch ($this->db->get_sql_layer())
		{
			case 'mssql_odbc':
			case 'mssqlnative':
				return 'LEN';

			default:
				return 'LENGTH';
		}
	}

	/**
	 * Get a localised language string for a specific mode.
	 *
	 * @param  string	$mode		The mode (forums|topics|posts|pms)
	 * @return string				The localised language
	 * @access protected
	 */
	protected function get_mode_lang($mode)
	{
		return $mode === 'pms' ? $this->language->lang('PRIVATE_MESSAGES') : $this->language->lang(utf8_strtoupper($mode));
	}
}
