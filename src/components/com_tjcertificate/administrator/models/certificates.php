<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ListModel;

/**
 * Methods supporting a list of records.
 *
 * @since  1.0.0
 */
class CertificateModelCertificates extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'ci.id',
				'certificate_template_id', 'ci.certificate_template_id',
				'client', 'ci.client',
				'user_id', 'ci.user_id',
				'state', 'ci.state',
				'issued_on', 'ci.issued_on',
				'expired_on', 'ci.expired_on'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.0.0
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select(array('ci.*', 'ct.title', 'users.name as uname'));
		$query->from($db->quoteName('#__tj_certificate_issue', 'ci'));
		$query->join('LEFT', $db->quoteName('#__tj_certificate_templates', 'ct') .
			' ON (' . $db->quoteName('ci.certificate_template_id') . ' = ' . $db->quoteName('ct.id') . ')');
		$query->join('LEFT', $db->quoteName('#__users', 'users') . ' ON (' . $db->quoteName('ci.user_id') . ' = ' . $db->quoteName('users.id') . ')');

		// Filter by certificate id
		$id = $this->getState('filter.id');

		if (!empty($id))
		{
			$query->where($db->quoteName('ci.id') . ' = ' . (int) $id);
		}

		// Filter by certificate template id
		$certificateTemplateId = $this->getState('filter.certificate_template_id');

		if (!empty($certificateTemplateId))
		{
			$query->where($db->quoteName('ci.certificate_template_id') . ' = ' . (int) $certificateTemplateId);
		}

		// Filter by client
		$client = $this->getState('filter.client');

		if (!empty($client))
		{
			$query->where($db->quoteName('ci.client') . ' = ' . $db->quote($client));
		}

		// Filter by user id
		$userId = $this->getState('filter.user_id');

		if (!empty($userId))
		{
			$query->where($db->quoteName('ci.user_id') . ' = ' . (int) $userId);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('ci.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where(' (ct.title LIKE ' . $search . ' OR ci.unique_certificate_id LIKE ' . $search . ' )');
			}
		}

		// Filter by state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('ci.state = ' . (int) $state);
		}
		elseif ($state === '')
		{
			$query->where('(ci.state = 0 OR ci.state = 1)');
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'ci.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}
}
