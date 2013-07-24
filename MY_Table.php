<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Table extends CI_Table {

  // --------------------------------------------------------------------

	/**
	 * Prep Args
	 *
	 * Ensures a standard associative array format for all cell data
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	function _prep_args($args)
	{
		// If there is no $args[0], skip this and treat as an associative array
		// This can happen if there is only a single key, for example this is passed to table->generate
		// array(array('foo'=>'bar'))
		if (isset($args[0]) AND (count($args) == 1 && is_array($args[0])))
		{
			if (isset($args[0]['data']))
			{
				$args=$args[0];
			}
			// args sent as indexed array
			elseif ( ! isset($args[0]['data']))
			{
				foreach ($args[0] as $key => $val)
				{
					if (is_array($val) && isset($val['data']))
					{
						$args[$key] = $val;
					}
					else
					{
						$args[$key] = array('data' => $val);
					}
				}
			}
		}
		else
		{
			foreach ($args as $key => $val)
			{
				if ( ! is_array($val))
				{
					$args[$key] = array('data' => $val);
				}
			}
		}

		return $args;
	}



	// --------------------------------------------------------------------

	/**
	 * Generate the table
	 *
	 * @access	public
	 * @param	mixed
	 * @return	string
	 */
	function generate($table_data = NULL)
	{
		// The table data can optionally be passed to this function
		// either as a database result object or an array
		if ( ! is_null($table_data))
		{
			if (is_object($table_data))
			{
				$this->_set_from_object($table_data);
			}
			elseif (is_array($table_data))
			{
				$set_heading = (count($this->heading) == 0 AND $this->auto_heading == FALSE) ? FALSE : TRUE;
				$this->_set_from_array($table_data, $set_heading);
			}
		}

		// Is there anything to display?  No?  Smite them!
		if (count($this->heading) == 0 AND count($this->rows) == 0)
		{
			return 'Undefined table data';
		}

		// Compile and validate the template date
		$this->_compile_template();

		// set a custom cell manipulation function to a locally scoped variable so its callable
		$function = $this->function;

		// Build the table!

		$out = $this->template['table_open'];
		$out .= $this->newline;

		// Add any caption here
		if ($this->caption)
		{
			$out .= $this->newline;
			$out .= '<caption>' . $this->caption . '</caption>';
			$out .= $this->newline;
		}

		// Is there a table heading to display?
		if (count($this->heading) > 0)
		{
			$out .= $this->template['thead_open'];
			$out .= $this->newline;
			$out .= $this->template['heading_row_start'];
			$out .= $this->newline;

			foreach ($this->heading as $heading)
			{
				$temp = $this->template['heading_cell_start'];

				foreach ($heading as $key => $val)
				{
					if ($key != 'data')
					{
						$temp = str_replace('<th', "<th $key='$val'", $temp);
					}
				}

				$out .= $temp;
				$out .= isset($heading['data']) ? $heading['data'] : '';
				$out .= $this->template['heading_cell_end'];
			}

			$out .= $this->template['heading_row_end'];
			$out .= $this->newline;
			$out .= $this->template['thead_close'];
			$out .= $this->newline;
		}

		// Build the table rows
		if (count($this->rows) > 0)
		{
			$out .= $this->template['tbody_open'];
			$out .= $this->newline;

			$i = 1;
			foreach ($this->rows as $row)
			{
				if ( ! is_array($row))
				{
					break;
				}

				// We use modulus to alternate the row colors
				$name = (fmod($i++, 2)) ? '' : 'alt_';

				$temp= $this->template['row_'.$name.'start'];

				//check to see if the row overwrites the default template
				if(isset($row['data']))
				{
					//reassign the row elements to a temporary array
					$row_temporary = $row;

					//assign the data to the row var to work normally
					$row = $row['data'];
					$row = $this->_prep_args($row);

					foreach ($row_temporary as $key => $val)
					{
						if ($key != 'data')
						{
							if(is_array($val))
							{
								$temp = str_replace('<tr', "<tr $key='{$val['data']}'", $temp);
							}
							else
							{
								$temp = str_replace('<tr', "<tr $key='{$val}'", $temp);
							}
						}
					}
				}

				$out .= $temp.$this->newline;

				foreach ($row as $cell)
				{
					$temp = $this->template['cell_'.$name.'start'];

					foreach ($cell as $key => $val)
					{
						if ($key != 'data')
						{
							$temp = str_replace('<td', "<td $key='$val'", $temp);
						}
					}

					$cell = isset($cell['data']) ? $cell['data'] : '';
					$out .= $temp;

					if ($cell === "" OR $cell === NULL)
					{
						$out .= $this->empty_cells;
					}
					else
					{
						if ($function !== FALSE && is_callable($function))
						{
							$out .= call_user_func($function, $cell);
						}
						else
						{
							$out .= $cell;
						}
					}

					$out .= $this->template['cell_'.$name.'end'];
				}

				$out .= $this->template['row_'.$name.'end'];
				$out .= $this->newline;
			}

			$out .= $this->template['tbody_close'];
			$out .= $this->newline;
		}

		$out .= $this->template['table_close'];

		// Clear table class properties before generating the table
		$this->clear();

		return $out;
	}



	function prepareTableRow($theArray=array(), $colHeading=" ", $prefix='', $postfix='', $trueFalse=false)
	{
		$newArray[]=$colHeading;
		foreach($theArray as $value)
		{
			if($trueFalse)
			{
				if($value)
				{
					$value="Yes";
				}
				else
				{
					$value="-";
				}
			}
			if($value==9999999)
			{
				$value="unlimited";
			}

			$newArray[]=$prefix.$value.$postfix;
		}
		return $newArray;
	}
}
