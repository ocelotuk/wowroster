<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Character Bag class, extends item class
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.03
 * @package    CharacterInfo
 * @subpackage Bag
 */

if( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

require_once( ROSTER_LIB . 'item.php');

/**
 * Character Bag class, extends item class
 *
 * @package    CharacterInfo
 * @subpackage Bag
 */
class bag extends item
{
	var $contents;

	function bag( $data )
	{
		$this->item( $data );
		$this->contents = $this->fetchManyItems($this->data['member_id'], $this->data['item_slot'], 'simple');
		if( $this->data['item_quantity'] < count($this->contents) )
		{
			$this->data['item_quantity'] = count($this->contents);

			if( $this->data['item_quantity'] % 2 )
			{
				$this->data['item_quantity']++;
			}
			if( $this->data['item_quantity'] < 4 )
			{
				$this->data['item_quantity'] = 4;
			}
		}
	}

	function out( $send_type = false )
	{
		global $roster, $addon;

		$lang = $this->data['locale'];

		$bag_type = ( strpos($this->data['item_slot'],'Bank') !== false ? 'bank' : 'bag');
		$bag_type = ( $this->data['item_slot'] == 'Bag5' ? 'key' : $bag_type);

		// If send type is true, the tpl array is set with the bag type
		$send_type = ( $send_type ? $bag_type : 'bag');

		$roster->tpl->assign_block_vars($send_type,array(
			'NAME'    => $this->data['item_name'],
			'SLOT'    => $this->data['item_slot'],
			'SLOTL'   => str_replace(" ", "",$this->data['item_slot']),
			'LINK'    => makelink('#' . str_replace(" ", "",$this->data['item_slot'])),
			'QUALITY' => $this->quality,
			'TYPE'    => $bag_type,
			'ICON'    => $this->tpl_get_icon(),
			'TOOLTIP' => $this->tpl_get_tooltip(),
			'LINKTIP' => $this->tpl_get_itemlink(),
			)
		);

		// Select all items for this bag
		for( $slot = 0; $slot < $this->data['item_quantity'] ; $slot++ )
		{
			if( isset($this->contents[$slot+1]) )
			{
				$item = $this->contents[$slot+1];

				$roster->tpl->assign_block_vars($send_type . '.item',array(
					'ICON'     => $item->tpl_get_icon(),
					'TOOLTIP'  => $item->tpl_get_tooltip(),
					'ITEMLINK' => $item->tpl_get_itemlink(),
					'QUALITY'  => $item->quality,
					'QTY'      => $item->quantity
					)
				);
			}
			else
			{
				$roster->tpl->assign_block_vars($send_type . '.item',array(
					'ICON'     => '',
					'TOOLTIP'  => '',
					'ITEMLINK' => '',
					'QUALITY'  => 'none',
					'QTY'      => 0
					)
				);
			}
		}
	}
}

function bag_get( $member_id, $slot )
{
	$item = item::fetchOneItem( $member_id, $slot );
	if( $item )
	{
		return new bag( $item->data );
	}
	else
	{
		return null;
	}
}
