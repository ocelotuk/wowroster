<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Overall header for Roster
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.8.0
 * @package    WoWRoster
 */

if( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

define('ROSTER_HEADER_INC', true);

/**
 * Detect and set headers
 */
if( $roster->output['http_header'] && !headers_sent() )
{
	$now = gmdate('D, d M Y H:i:s', time()) . ' GMT';

	@header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	@header('Last-Modified: ' . $now);
	@header('Cache-Control: no-store, no-cache, must-revalidate');
	@header('Cache-Control: post-check=0, pre-check=0', false);
	@header('Pragma: no-cache');
	@header('Content-type: text/html; charset=utf-8');
}

switch( $roster->scope )
{
	case 'util':
	case 'user':
	case 'page':
		$roster_title = ' [ ' . $roster->config['default_name'] . ' ] '
			. (isset($roster->output['title']) ? $roster->output['title'] : '');

		$roster->tpl->assign_vars(array(
			'PAGE_INFO'       => (isset($roster->output['title']) ? $roster->output['title'] : ''),
			'ROSTER_TITLE'    => $roster->config['default_name'],
			'ROSTER_SUBTITLE' => isset($roster->config['default_desc']) ? '<br />' . $roster->config['default_desc'] : '',
			'ROSTER_3RDTITLE' => false,
			'LAST_UPDATED'    => false
		));
		break;

	case 'realm':
		$roster_title = ' [ ' . $roster->data['region'] . '-' . $roster->data['server'] . ' ] '
			. (isset($roster->output['title']) ? $roster->output['title'] : '');

		$roster->tpl->assign_vars(array(
			'ROSTER_TITLE'    => $roster->data['region'] . '-' . $roster->data['server'],
			'ROSTER_SUBTITLE' => isset($roster->config['default_desc']) && $roster->config['default_desc'] != '' ? '<br />' . $roster->config['default_desc'] : false,
			'ROSTER_3RDTITLE' => false,
			'LAST_UPDATED'    => false
		));
		break;

	case 'guild':
		$roster_title = ' [ ' . $roster->data['guild_name'] . ' @ ' . $roster->data['region'] . '-'
			. $roster->data['server'] . ' ] ' . (isset($roster->output['title']) ? $roster->output['title'] : '');

		$roster->tpl->assign_vars(array(
			'ROSTER_TITLE'    => $roster->data['guild_name'],
			'ROSTER_SUBTITLE' => '@ ' . $roster->data['region'] . '-' . $roster->data['server'],
			'ROSTER_3RDTITLE' => $roster->data['guild_num_members'],
			'LAST_UPDATED'    => (isset($roster->data['update_time']) ? readbleDate($roster->data['update_time']) . ((!empty($roster->config['timezone'])) ? ' (' . $roster->config['timezone'] . ')' : '') : '')
		));
		break;

	case 'char':
		$roster_title = ' [ ' . $roster->data['guild_name'] . ' @ ' . $roster->data['region'] . '-'
			. $roster->data['server'] . ' ] ' . (isset($roster->output['title']) ? $roster->output['title'] : '');

		$roster->tpl->assign_vars(array(
			'ROSTER_TITLE'    => $roster->data['name'],
			'ROSTER_SUBTITLE' => '@ ' . $roster->data['region'] . '-' . $roster->data['server'],
			'ROSTER_3RDTITLE' => false,
			'LAST_UPDATED'    => (isset($roster->data['update_time']) ? readbleDate($roster->data['update_time']) . ((!empty($roster->config['timezone'])) ? ' (' . $roster->config['timezone'] . ')' : '') : '')
		));
		break;

	default:
		$roster_title = (isset($roster->output['title']) ? $roster->output['title'] : '');

		$roster->tpl->assign_vars(array(
			'ROSTER_TITLE'    => (isset($roster->output['title']) ? $roster->output['title'] : ''),
			'ROSTER_SUBTITLE' => false,
			'ROSTER_3RDTITLE' => false,
			'LAST_UPDATED'   => false
		));
		break;
}

/**
 * Assign template vars
 */
$roster->tpl->assign_vars(array(
	'S_SEO_URL'            => $roster->config['seo_url'],
	'S_HEADER_LOGO'        => (!empty($roster->config['logo']) ? true : false),
	'U_MAKELINK'           => makelink(),
	'ROSTER_URL'           => ROSTER_URL,
	'ROSTER_PATH'          => ROSTER_PATH,
	'WEBSITE_ADDRESS'      => $roster->config['website_address'],
	'HEADER_LOGO'          => $roster->config['logo'],
	'IMG_URL'              => $roster->config['img_url'],
	'INTERFACE_URL'        => $roster->config['interface_url'],
	'IMG_SUFFIX'           => $roster->config['img_suffix'],
	'ROSTER_VERSION'       => $roster->config['version'],
	'ROSTER_CREDITS'       => sprintf($roster->locale->act['roster_credits'], makelink('credits')),
	'XML_LANG'             => substr($roster->config['locale'], 0, 2),

	'ROSTER_SCOPE'         => $roster->scope,
	'PAGE_TITLE'           => $roster_title,
	'ROSTER_HEAD'          => $roster->output['html_head'],
	'ROSTER_HEAD_JS'       => roster_get_js(),
	'ROSTER_HEAD_CSS'      => roster_get_css(),
	'ROSTER_BODY'          => (!empty($roster->config['roster_bg']) ? ' style="background-image:url(' . $roster->config['roster_bg'] . ');Background-attachment:fixed;"' : '') . (!empty($roster->output['body_attr']) ? ' ' . $roster->output['body_attr'] : ''),
	'ROSTER_ONLOAD'        => (!empty($roster->output['body_onload']) ? $roster->output['body_onload'] : ''),
	'ROSTER_TOP'           => $roster->output['top'],

	'L_MENU_LABEL'         => $roster->scope,
	'L_MENU_LABEL_NAME'    => $roster->locale->act[$roster->scope],

	'S_LOCALE_SELECT'      => (bool)$roster->config['header_locale'],
	'S_HEADER_SEARCH'      => (bool)$roster->config['header_search'],
	'S_HEADER_LOGIN'       => (bool)$roster->config['header_login'],
	'S_REALMSTATUS'        => (bool)$roster->config['rs_display'],

	'LOGIN_FORM'           => (is_object($roster->auth) ? $roster->auth->getMenuLoginForm() : ''),
	'REALMSTATUS'          => isset($roster->data['server']) ? makeRealmStatus() : '',

	'FACTION'              => isset($roster->data['factionEn']) ? strtolower($roster->data['factionEn']) : false,

	'U_SEARCH_FORM_ACTION' => makelink('search'),
	'U_MENU_UPDATE_LUA'    => makelink('update')
));

// Make a listing of our current locales
if( $roster->config['header_locale'] )
{
	foreach( $roster->multilanguages as $language )
	{
		$roster->tpl->assign_block_vars('locale_select', array(
			'LOCALE'      => $language,
			'LOCALE_NAME' => $roster->locale->wordings[$language]['langname'],
			'S_SELECTED'  => ($language == $roster->config['locale'] ? true : false)
		));
	}
}

/**
 * Make the data selection list
 */
$menu_select = array();
$roster->tpl->assign_var('S_DATA_SELECT', false);

if( $roster->scope == 'realm' )
{
	// Get the scope select data
	$query = "SELECT DISTINCT `server`, `region`"
		. " FROM `" . $roster->db->table('guild') . "`"
		. " UNION SELECT DISTINCT `server`, `region`"
			. " FROM `" . $roster->db->table('players') . "`"
			. " ORDER BY `server` ASC;";

	$result = $roster->db->query($query);

	if( !$result )
	{
		die_quietly($roster->db->error(), 'Database error', __FILE__, __LINE__, $query);
	}

	$realms = 0;
	while( $data = $roster->db->fetch($result) )
	{
		$menu_select[$data[1]][] = $data[0];
		$realms++;
	}
	$roster->db->free_result($result);

	$roster->tpl->assign_var('S_MENU_SELECT', ($realms > 1 ? true : false));

	// num guilds bs...
	$queryng = "SELECT `guild_name`, CONCAT(`region`,'-',`server`), `guild_id`"
		. " FROM `" . $roster->db->table('guild') . "`"
		. " ORDER BY `region` ASC, `server` ASC, `guild_name` ASC;";

	$resultng = $roster->db->query($queryng);

	$roster->tpl->assign_var('TOTAL_GUILDS', $roster->db->num_rows($resultng));

	if( $realms >= 1 )
	{
		foreach( $menu_select as $region => $realmsArray )
		{
			$roster->tpl->assign_block_vars('menu_select_group', array(
				'U_VALUE' => $region
			));

			foreach( $realmsArray as $name )
			{
				$roster->tpl->assign_block_vars('menu_select_group.menu_select_row', array(
					'TEXT'       => $name,
					'U_VALUE'    => makelink("&amp;a=r:$region-$name", true),
					'S_SELECTED' => ($name == $roster->data['server'] ? true : false)
				));
			}
		}
	}
}
elseif( $roster->scope == 'guild' )
{
	// Get the scope select data
	$query = "SELECT `guild_name`, CONCAT(`region`,'-',`server`), `guild_id`"
		. " FROM `" . $roster->db->table('guild') . "`"
		. " ORDER BY `region` ASC, `server` ASC, `guild_name` ASC;";

	$result = $roster->db->query($query);

	if( !$result )
	{
		die_quietly($roster->db->error(), 'Database error', __FILE__, __LINE__, $query);
	}

	$guilds = 0;
	while( $data = $roster->db->fetch($result, SQL_NUM) )
	{
		$menu_select[$data[1]][$data[2]] = $data[0];
		$guilds++;
	}

	$roster->db->free_result($result);

	$roster->tpl->assign_vars(array(
		'S_DATA_SELECT' => ($guilds > 1 ? true : false),
		'TOTAL_GUILDS' =>  $guilds
		)
	);

	if( count($menu_select) > 0 )
	{
		foreach( $menu_select as $realm => $guild )
		{
			$roster->tpl->assign_block_vars('menu_select_group', array(
				'U_VALUE' => $realm
			));

			foreach( $guild as $id => $name )
			{
				$roster->tpl->assign_block_vars('menu_select_group.menu_select_row', array(
					'TEXT'       => $name,
					'U_VALUE'    => makelink('&amp;a=g:' . $id, true),
					'S_SELECTED' => ($id == $roster->data['guild_id'] ? true : false)
				));
			}
		}
	}
}
elseif( $roster->scope == 'user' )
{
	// Get the scope select data
	$query = "SELECT `guild_name`, CONCAT(`region`,'-',`server`), `guild_id`"
		. " FROM `" . $roster->db->table('guild') . "`"
		. " ORDER BY `region` ASC, `server` ASC, `guild_name` ASC;";

	$result = $roster->db->query($query);

	if( !$result )
	{
		die_quietly($roster->db->error(), 'Database error', __FILE__, __LINE__, $query);
	}

	$guilds = 0;
	while( $data = $roster->db->fetch($result, SQL_NUM) )
	{
		$menu_select[$data[1]][$data[2]] = $data[0];
		$guilds++;
	}

	$roster->db->free_result($result);

	$roster->tpl->assign_vars(array(
		'S_DATA_SELECT' => ($guilds > 1 ? true : false),
		'TOTAL_GUILDS' =>  $guilds
		)
	);

	if( count($menu_select) > 0 )
	{
		foreach( $menu_select as $realm => $guild )
		{
			$roster->tpl->assign_block_vars('menu_select_group', array(
				'U_VALUE' => $realm
			));

			foreach( $guild as $id => $name )
			{
				$roster->tpl->assign_block_vars('menu_select_group.menu_select_row', array(
					'TEXT'       => $name,
					'U_VALUE'    => makelink('&amp;a=g:' . $id, true),
					'S_SELECTED' => ($id == $roster->data['guild_id'] ? true : false)
				));
			}
		}
	}
}
elseif( $roster->scope == 'char' )
{
	// Get the scope select data
	$query = "SELECT `name`, `member_id`"
		. " FROM `" . $roster->db->table('players') . "`"
		. " WHERE `guild_id` = '" . $roster->data['guild_id'] . "'"
		. " ORDER BY `name` ASC;";

	$result = $roster->db->query($query);

	if( !$result )
	{
		die_quietly($roster->db->error(), 'Database error', __FILE__, __LINE__, $query);
	}

	while( $data = $roster->db->fetch($result, SQL_NUM) )
	{
		$menu_select[$data[1]] = $data[0];
	}

	$roster->tpl->assign_var('S_DATA_SELECT', ($roster->db->num_rows() > 1 ? true : false));

	$roster->db->free_result($result);

	if( count($menu_select) > 0 )
	{
		$roster->tpl->assign_block_vars('menu_select_group', array(
			'U_VALUE' => $roster->data['guild_name']
		));

		foreach( $menu_select as $id => $name )
		{
			$roster->tpl->assign_block_vars('menu_select_group.menu_select_row', array(
				'TEXT'       => $name,
				'U_VALUE'    => makelink('&amp;a=c:' . $id, true),
				'S_SELECTED' => ($id == $roster->data['member_id'] ? true : false)
			));
		}
	}
}

// Gather messages and create the messages block
$roster->tpl->assign_var('S_MESSAGES', (bool)$roster->get_messages('', false));

foreach( $roster->get_messages() as $type => $messages )
{
	if( count($messages) > 0 )
	{
		$roster->tpl->assign_block_vars('messages', array(
			'TYPE' => $type
			)
		);

		foreach( $messages as $message )
		{
			$roster->tpl->assign_block_vars('messages.item', array(
				'TITLE' => $message[0],
				'TEXT' => $message[1]
				)
			);
		}
	}
}


// BETA ONLY, COMMENT THIS IN RC OR LATER!
/*
if( file_exists(ROSTER_BASE . 'valid.inc') )
{
	$v_content = '';
	ob_start();
		require (ROSTER_BASE . 'valid.inc');
	$v_content = ob_get_clean();

	$roster->tpl->assign_var('ROSTER_TOP', $v_content);
}
*/
// END BETA ONLY

$roster->tpl->set_handle('roster_header', 'header.html');
$roster->tpl->display('roster_header');

