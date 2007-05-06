<?php
/**
 * WoWRoster.net WoWRoster
 *
 * MySQL Interface
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: pvp3.php 897 2007-05-06 00:35:11Z Zanix $
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.8.0
*/

if( eregi(basename(__FILE__),$_SERVER['PHP_SELF']) )
{
	die("You can't access this file directly!");
}

/**
 * SQL_DB class, MySQL version
 * Abstracts MySQL database functions
 */
class roster_db
{
	var $link_id     = 0;                   // Connection link ID       @var link_id
	var $query_id    = 0;                   // Query ID                 @var query_id
	var $record      = array();             // Record                   @var record
	var $record_set  = array();             // Record set               @var record_set
	var $query_count = 0;                   // Query count              @var query_count
	var $queries     = array();             // Queries                  @var queries
	var $error_die   = false;               // Die on errors?           @var error_die

	var $prefix      = '';
	/**
	 * Constructor
	 *
	 * Connects to a MySQL database
	 *
	 * @param $dbhost Database server
	 * @param $dbname Database name
	 * @param $dbuser Database username
	 * @param $dbpass Database password
	 * @param $pconnect Use persistent connection
	 * @return mixed Link ID / false
	 */
	function roster_db( $dbhost, $dbname, $dbuser, $dbpass, $prefix)
	{
		$this->prefix = $prefix;

		if( empty($dbpass) )
		{
			$this->link_id = @mysql_connect($dbhost, $dbuser);
		}
		else
		{
			$this->link_id = @mysql_connect($dbhost, $dbuser, $dbpass);
		}

		if( (is_resource($this->link_id)) && (!is_null($this->link_id)) && ($dbname != '') )
		{
			if( !@mysql_select_db($dbname, $this->link_id) )
			{
				@mysql_close($this->link_id);
				$this->link_id = false;
			}
			return $this->link_id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Closes MySQL connection
	 *
	 * @return bool
	 */
	function close_db( )
	{
		if( $this->link_id )
		{
			if( $this->query_id )
			{
				@mysql_free_result($this->query_id);
			}
			return @mysql_close($this->link_id);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get last SQL error
	 *
	 * @return string last SQL error
	 */
	function error()
	{
		$result = @mysql_errno($this->link_id).': '.mysql_error($this->link_id);
		return $result;
	}

	/**
	 * Get last SQL errno
	 *
	 * @return string last SQL errno
	 */
	function errno()
	{
		$result = @mysql_errno($this->link_id);
		return $result;
	}

	/**
	 * Get connection error
	 */
	function connect_error()
	{
		return @mysql_errno().': '.mysql_error();
	}

	/**
	 * Basic query function
	 *
	 * @param $query Query string
	 * @return mixed Query ID / Error string / Bool
	 */
	function query( $query )
	{
		global $roster;

		// Remove pre-existing query resources
		unset($this->query_id);

		//$query = preg_replace('/;.*$/', '', $query);

		if( $query != '' )
		{
			$this->query_count++;
			$this->query_id = @mysql_query($query, $this->link_id);
		}
		if( !empty($this->query_id) )
		{
			$this->queries[$this->query_count] = $query;

			unset($this->record[$this->query_id]);
			unset($this->record_set[$this->query_id]);
			return $this->query_id;
		}
		elseif( isset($roster) && $roster->config['debug_mode'] || $this->error_die )
		{
			die_quietly($this->error(), 'Database Error',basename(__LINE__),__FILE__,$query);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Return the first record (single column) in a query result
	 *
	 * @param $query Query string
	 */
	function query_first( $query )
	{
		$this->query($query);
		$record = $this->fetch($this->query_id);
		$this->free_result($this->query_id);

		return $record[0];
	}

	/**
	 * Build query
	 *
	 * @param $query
	 * @param $array Array of field => value pairs
	 */
	function build_query( $query , $array = false )
	{
		if( !is_array($array) )
		{
			return false;
		}

		$fields = array();
		$values = array();

		if( $query == 'INSERT' )
		{
			foreach( $array as $field => $value )
			{
				$fields[] = $field;

				if( is_null($value) )
				{
					$values[] = 'NULL';
				}
				elseif( is_string($value) )
				{
					$values[] = "'" . $this->escape($value) . "'";
				}
				else
				{
					$values[] = ( is_bool($value) ) ? intval($value) : $value;
				}
			}

			$query = ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
		}
		elseif( $query == 'UPDATE' )
		{
			foreach( $array as $field => $value )
			{
				if( is_null($value) )
				{
					$values[] = "$field = NULL";
				}
				elseif( is_string($value) )
				{
					$values[] = "$field = '" . $this->escape($value) . "'";
				}
				else
				{
					$values[] = ( is_bool($value) ) ? "$field = " . intval($value) : "$field = $value";
				}
			}

			$query = implode(', ', $values);
		}

		return $query;
	}

	/**
	 * Fetch one record
	 *
	 * @param $query_id Query ID
	 * @param $result_type MYSQL_ASSOC, MYSQL_NUM, or MYSQL_BOTH
	 * @return mixed Record / false
	 */
	function fetch( $query_id = 0, $result_type = MYSQL_BOTH)
	{
		if( !$query_id )
		{
			$query_id = $this->query_id;
		}

		if( $query_id )
		{
			$this->record[$query_id] = @mysql_fetch_array($query_id, $result_type);
			return $this->record[$query_id];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Fetch all records
	 *
	 * @param $query_id Query ID
	 * @return mixed Record Set / false
	 */
	function fetch_all( $query_id = 0, $result_type = MYSQL_BOTH )
	{
		if( !$query_id )
		{
			$query_id = $this->query_id;
		}
		if( $query_id )
		{
			unset($this->record_set[$query_id]);
			unset($this->record[$query_id]);
			while( $this->record_set[$query_id] = @mysql_fetch_array($query_id, $result_type) )
			{
				// empty loop
			}
			return $this->record_set[$query_id];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Find the number of returned rows
	 *
	 * @param $query_id Query ID
	 * @return mixed Number of rows / false
	 */
	function num_rows( $query_id = 0 )
	{
		if( !$query_id )
		{
			$query_id = $this->query_id;
		}

		if( $query_id )
		{
			$result = @mysql_num_rows($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Finds out the number of rows affected by a query
	 *
	 * @return mixed Affected Rows / false
	 */
	function affected_rows( )
	{
		return ( $this->link_id ) ? @mysql_affected_rows($this->link_id) : false;
	}

	/**
	 * Find the ID of the row that was just inserted
	 *
	 * @return mixed Last ID / false
	 */
	function insert_id( )
	{
		if( $this->link_id )
		{
			$result = @mysql_insert_id($this->link_id);
			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Free result data
	 *
	 * @param $query_id Query ID
	 * @return bool
	 */
	function free_result( $query_id = 0 )
	{
		if( !$query_id )
		{
			$query_id = $this->query_id;
		}

		if( $query_id )
		{
			unset($this->record[$query_id]);
			unset($this->record_set[$query_id]);

			@mysql_free_result($query_id);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Remove quote escape
	 *
	 * @param $string
	 * @return string
	 */
	function escape( $string )
	{
		if( version_compare( phpversion(), '4.3.0', '>' ) )
			return mysql_real_escape_string( $string );
		else
			return mysql_escape_string( $string );
	}

	/**
	 * Set the error_die var
	 *
	 * @param $setting
	 */
	function error_die( $setting = true )
	{
		$this->error_die = $setting;
	}

	/**
	 * Expand base table name to a full table name
	 *
	 * @param string $table the base table name
	 * @param string $addon the name of the addon, empty for a base roster table
	 * @return string tablename as fit for MySQL queries
	 */
	function table($table, $addon='')
	{
		if( $addon)
		{
			return $this->prefix.'addons_'.$addon.($table != '' ? '_'.$table : '');
		}
		else
		{
			return $this->prefix.$table;
		}
	}

	/**
	 * Get the queries for the footer
	 */
	function getQueries()
	{
		$output = "<ul>\n";
		foreach( $this->queries as $query )
		{
			$output .= "  <li>".nl2br(htmlentities($query))."</li>\n";
		}
		$output .= "</ul>\n";
		return $output;
	}
}
