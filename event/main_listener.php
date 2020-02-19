<?php
/**
 *
 * phpBB Studio - No Re:. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\nore\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * phpBB Studio - No RE: Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.pm_modify_message_subject'					=> 'nore_pm_modify_message_subject',
			'core.posting_modify_post_subject'					=> 'nore_posting_modify_post_subject',
			'core.viewtopic_modify_quick_reply_template_vars'	=> 'nore_qr_modify_topic_title',
		];
	}

	/**
	 * @event core.pm_modify_message_subject
	 *
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function nore_pm_modify_message_subject($event)
	{
		$event['message_subject'] = preg_replace('/^Re: /', '', $event['message_subject']);
	}

	/**
	 * @event core.posting_modify_post_subject
	 *
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function nore_posting_modify_post_subject($event)
	{
		$event['post_subject'] = preg_replace('/^Re: /', '', $event['post_subject']);
	}

	/**
	 * @event core.viewtopic_modify_quick_reply_template_vars
	 *
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function nore_qr_modify_topic_title($event)
	{
		$event->update_subarray('tpl_ary', 'SUBJECT', censor_text($event['topic_data']['topic_title']));
	}
}
