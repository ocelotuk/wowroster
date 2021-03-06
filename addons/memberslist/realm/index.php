<?php
/**
 * WoWRoster.net WoWRoster
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @package    MembersList
*/

if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

include_once ($addon['inc_dir'] . 'memberslist.php');

$memberlist = new memberslist;
$members_list_select = $memberlist->members_list_select;
$members_list_table = $memberlist->members_list_table;
$members_list_where = $memberlist->members_list_where;
$members_list_fields = $memberlist->members_list_fields;

$mainQuery =
	'SELECT '.
	'`members`.`member_id`, '.
	'`members`.`name`, '.
	'`members`.`class`, '.
	'`members`.`classid`, '.
	'`members`.`level`, '.
	'`members`.`zone`, '.
	'`members`.`online`, '.
	'`members`.`last_online`, '.
	"UNIX_TIMESTAMP(`members`.`last_online`) AS 'last_online_stamp', ".
	"DATE_FORMAT(  DATE_ADD(`members`.`last_online`, INTERVAL ".$roster->config['localtimeoffset']." HOUR ), '".$roster->locale->act['timeformat']."' ) AS 'last_online_format', ".
	'`members`.`note`, '.
	'`members`.`guild_title`, '.

	'`alts`.`main_id`, '.
	"$members_list_select".
	'`guild`.`guild_name`, '.
	'`guild`.`guild_id`, '.
	'`guild`.`update_time`, '.
	'`guild`.`factionEn`, '.

	"IF( `members`.`note` IS NULL OR `members`.`note` = '', 1, 0 ) AS 'nisnull', ".
	'`members`.`officer_note`, '.
	"IF( `members`.`officer_note` IS NULL OR `members`.`officer_note` = '', 1, 0 ) AS 'onisnull', ".
	'`members`.`guild_rank`, '.

	'`players`.`server`, '.
	'`players`.`race`, '.
	'`players`.`sex`, '.
	'`players`.`exp`, '.
	'`players`.`clientLocale`, '.

	'`players`.`lifetimeRankName`, '.
	'`players`.`lifetimeHighestRank`, '.
	"IF( `players`.`lifetimeHighestRank` IS NULL OR `players`.`lifetimeHighestRank` = '0', 1, 0 ) AS 'risnull', ".
	'`players`.`hearth`, '.
	"IF( `players`.`hearth` IS NULL OR `players`.`hearth` = '', 1, 0 ) AS 'hisnull', ".
	"UNIX_TIMESTAMP( `players`.`dateupdatedutc`) AS 'last_update_stamp', ".
	"DATE_FORMAT(  DATE_ADD(`players`.`dateupdatedutc`, INTERVAL ".$roster->config['localtimeoffset']." HOUR ), '".$roster->locale->act['timeformat']."' ) AS 'last_update_format', ".
	"IF( `players`.`dateupdatedutc` IS NULL OR `players`.`dateupdatedutc` = '', 1, 0 ) AS 'luisnull', ".

	"GROUP_CONCAT( DISTINCT CONCAT( `proftable`.`skill_name` , '|', `proftable`.`skill_level` ) ORDER BY `proftable`.`skill_order`) as professions, ".
	"GROUP_CONCAT( DISTINCT CONCAT( `talenttable`.`build`, '|', `talenttable`.`tree` , '|', `talenttable`.`pointsspent` , '|', `talenttable`.`background`,'|', `talenttable`.`order` ) ORDER BY `talenttable`.`order`, `talenttable`.`build`) AS 'talents', ".
	"GROUP_CONCAT( DISTINCT CONCAT( `talenttre`.`tree` , '|', `talenttre`.`roles` , '|', `talenttre`.`icon` ) ORDER BY `talenttre`.`tree`) AS 'talents2' ".
	
	'FROM `'.$roster->db->table('members').'` AS members '.
	'LEFT JOIN `'.$roster->db->table('players').'` AS players ON `members`.`member_id` = `players`.`member_id` '.
	'LEFT JOIN `'.$roster->db->table('skills').'` AS proftable ON `members`.`member_id` = `proftable`.`member_id` '.
	'LEFT JOIN `'.$roster->db->table('talenttree').'` AS talenttable ON `members`.`member_id` = `talenttable`.`member_id` '.
	'LEFT JOIN `'.$roster->db->table('talenttree_data').'` AS talenttre ON `members`.`classid` = `talenttre`.`class_id` '.
	'LEFT JOIN `'.$roster->db->table('alts',$addon['basename']).'` AS alts ON `members`.`member_id` = `alts`.`member_id` '.
	'LEFT JOIN `'.$roster->db->table('guild').'` AS guild ON `members`.`guild_id` = `guild`.`guild_id` '.$members_list_table;
	
$where[] = '`members`.`server` = "'.$roster->db->escape($roster->data['server']).'" ';
	if (!empty($members_list_where))
	{
		foreach ($members_list_where as $piwhere => $str)
		{
			foreach ($str as $e)
			{
				$where[] = $e;
			}
		}
	}
	
$group[] = '`members`.`member_id`';
$order_first[] = 'IF(`members`.`member_id` = `alts`.`member_id`,1,0)';
$order_last[] = '`members`.`level` DESC';
$order_last[] = '`members`.`name` ASC';

$FIELD['name'] = array (
	'lang_field' => 'name',
	'filt_field' => '`members`.`name`',
	'order'      => array( '`members`.`name` ASC' ),
	'order_d'    => array( '`members`.`name` DESC' ),
	'value'      => array($memberlist,'name_value'),
	'display'    => 3,
);

$FIELD['class'] = array (
	'lang_field' => 'class',
	'filt_field' => '`members`.`class`',
	'order'      => array( '`members`.`class` ASC' ),
	'order_d'    => array( '`members`.`class` DESC' ),
	'value'      => array($memberlist,'class_value'),
	'display'    => $addon['config']['member_class'],
);

$FIELD['level'] = array (
	'lang_field' => 'level',
	'filt_field' => '`members`.`level`',
	'order_d'    => array( '`members`.`level` DESC' ),
	'order_d'    => array( '`members`.`level` ASC' ),
	'value'      => array($memberlist,'level_value'),
	'display'    => $addon['config']['member_level'],
);

$FIELD['guild_name'] = array (
	'lang_field' => 'guild',
	'order'      => array( '`guild`.`guild_name` ASC' ),
	'order_d'    => array( '`guild`.`guild_name` DESC' ),
	'value'      => array($memberlist,'guild_name_value'),
	'display'    => 2,
);

$FIELD['guild_title'] = array (
	'lang_field' => 'title',
	'order'      => array( '`members`.`guild_rank` ASC' ),
	'order_d'    => array( '`members`.`guild_rank` DESC' ),
	'display'    => $addon['config']['member_gtitle'],
);

$FIELD['lifetimeRankName'] = array (
	'lang_field' => 'currenthonor',
	'order'      => array( 'risnull', '`players`.`lifetimeHighestRank` DESC' ),
	'order_d'    => array( 'risnull', '`players`.`lifetimeHighestRank` ASC' ),
	'value'      => array($memberlist,'honor_value'),
	'display'    => $addon['config']['member_hrank'],
);

$FIELD['professions'] = array (
	'lang_field' => 'professions',
	'value'      => 'tradeskill_icons',
	'filter'     => 'false',
	'display'    => $addon['config']['member_prof'],
);

$FIELD['hearth'] = array (
	'lang_field' => 'hearthed',
	'order'      => array( 'hisnull', 'hearth ASC' ),
	'order_d'    => array( 'hisnull', 'hearth DESC' ),
	'display'    => $addon['config']['member_hearth'],
);

$FIELD['zone'] = array (
	'lang_field' => 'lastzone',
	'order'      => array( '`members`.`zone` ASC' ),
	'order_d'    => array( '`members`.`zone` DESC' ),
	'display'    => $addon['config']['member_zone'],
);

$FIELD['last_online'] = array (
	'lang_field' => 'lastonline',
	'order'      => array( '`members`.`last_online` DESC' ),
	'order_d'    => array( '`members`.`last_online` ASC' ),
	'value'      => array($memberlist,'last_online_value'),
	'display'    => $addon['config']['member_online'],
);

$FIELD['last_update_format'] = array (
	'lang_field' => 'lastupdate',
	'filt_field' => '`players`.`dateupdatedutc`',
	'order'      => array( 'luisnull','`players`.`dateupdatedutc` DESC' ),
	'order_d'    => array( 'luisnull','`players`.`dateupdatedutc` ASC' ),
	'display'    => $addon['config']['member_update'],
);

$FIELD['note'] = array (
	'lang_field' => 'note',
	'order'      => array( 'nisnull','`members`.`note` ASC' ),
	'order_d'    => array( 'nisnull','`members`.`note` DESC' ),
	'value'      => 'note_value',
	'display'    => $addon['config']['member_note'],
);

$FIELD['officer_note'] = array (
	'lang_field' => 'onote',
	'order'      => array( 'onisnull','`members`.`note` ASC' ),
	'order_d'    => array( 'onisnull','`members`.`note` DESC' ),
	'value'      => 'note_value',
	'display'    => $addon['config']['member_onote'],
);
foreach ($members_list_fields as $pifield => $data)
{
	foreach ($data as $name => $d)
	{
		$FIELD[$name] = $d;
	}
}

$memberlist->prepareData($mainQuery, $where, $group, $order_first, $order_last, $FIELD, 'memberslist');

// Start output
echo $memberlist->makeMembersList('syellow');


/**
 * Controls Output of the Tradeskill Icons Column
 *
 * @param array $row - of character data
 * @return string - Formatted output
 */
function tradeskill_icons ( $row )
{
	global $roster, $addon;

	$cell_value ='';

	// Don't proceed for characters without data
	if ($row['clientLocale'] == '')
	{
		return '<div>&nbsp;</div>';
	}

	$lang = $row['clientLocale'];

	$profs = explode(',',$row['professions']);
	foreach ( $profs as $prof )
	{
		$r_prof = explode('|',$prof);
		$value = isset( $r_prof[1] ) ? explode( ':', $r_prof[1] ) : array( 1, 1 );
		$curr = $value[0];
		$max = isset( $value[1] ) ? $value[1] : floor($skill_curr/75)*75;
		$toolTip = $curr . "/" . $max;
		$toolTiph = $r_prof[0];

		if( $r_prof[0] == $roster->locale->wordings[$lang]['riding'] )
		{
			// Flying
			if( $curr > 150 )
			{
				// Class-specific flying mount
				if( isset( $roster->locale->wordings[$lang]['ts_flyingIcon'][$row['class']] ) )
				{
					$icon = $roster->locale->wordings[$lang]['ts_flyingIcon'][$row['class']];
				}
				// Standard faction flying mount
				else
				{
					$icon = $roster->locale->wordings[$lang]['ts_flyingIcon'][$row['factionEn']];
				}
			}
			// Riding
			else
			{
				// Class-specific riding mount
				if( isset( $roster->locale->wordings[$lang]['ts_ridingIcon'][$row['class']] ) )
				{
					$icon = $roster->locale->wordings[$lang]['ts_ridingIcon'][$row['class']];
				}
				// Standard racial riding mount
				else
				{
					$icon = $roster->locale->wordings[$lang]['ts_ridingIcon'][$row['race']];
				}
			}
		}
		else
		{
			$icon = isset($roster->locale->wordings[$lang]['ts_iconArray'][$r_prof[0]])?$roster->locale->wordings[$lang]['ts_iconArray'][$r_prof[0]]:'';
		}

		// Don't add professions we don't have an icon for. This keeps other skills out.
		if ($icon != '')
		{
			$icon = '<div class="item-sm" '.makeOverlib($toolTip,$toolTiph,'',2,'',',WRAP').'><img src="'.$roster->config['interface_url'].'Interface/Icons/'.$icon.'.'.$roster->config['img_suffix'].'" alt="" /><div class="mask"></div></div>';

			if( active_addon('info') )
			{
				$cell_value .= '<a href="' . makelink('char-info-recipes&amp;a=c:' . $row['member_id'] . '#' . strtolower(str_replace(' ','',$r_prof[0]))) . '">' . $icon . '</a>';
			}
			else
			{
				$cell_value .= $icon;
			}
		}
	}
	return $cell_value;
}

/**
 * Controls Output of a Note Column
 *
 * @param array $row - of character data
 * @return string - Formatted output
 */
function note_value ( $row, $field )
{
	global $roster, $addon;

	if( !empty($row[$field]) )
	{
		$note = htmlspecialchars(nl2br($row[$field]));

		if( $addon['config']['compress_note'] )
		{
			$value = '<img src="'.$roster->config['theme_path'].'/images/note.gif" style="cursor:help;" '.makeOverlib($note,$roster->locale->act['note'],'',1,'',',WRAP').' alt="[]" />';
		}
		else
		{
			$value = $note;
		}
	}
	else
	{
		$note = '&nbsp;';
		if( $addon['config']['compress_note'] )
		{
			$value = '<img src="'.$roster->config['theme_path'].'/images/no_note.gif" alt="[]" />';
		}
		else
		{
			$value = $note;
		}
	}

	return '<div style="display:none;">'.$note.'</div>'.$value;
}
