<?php

/**
 * Format class
 *
 * Help convert between various formats such as XML, JSON, CSV, etc.
 *
 * PHP Version 5
 *
 * @category Library
 * @package  Orbital
 * @author   Phil Sturgeon <psturgeon@lincoln.ac.uk>
 * @license  Don't be a Dick Public License 
 * @link     http://philsturgeon.co.uk/code/dbad-license
 */

class Format {

	/**
	 * Array to convert.
	 *
	 * @var array $data 
	 */
	protected $_data = array();

	/**
	 * View filename.
	 *
	 * @var mixed $_from_type 
	 */

	protected $_from_type = NULL;

	/**
	 * Returns an instance of the Format object.
	 *
	 * echo $this->format->factory(array('foo' => 'bar'))->to_xml();
	 *
	 * @param mixed  $data      general date to be converted
	 * @param string $from_type data format the file was provided in
	 *
	 * @return mixed
	 */

	public function factory($data, $from_type = NULL)
	{
		// Stupid stuff to emulate the "new static()" stuff in this libraries PHP 5.3 equivilent
		$class = __CLASS__;
		return new $class($data, $from_type);
	}

	/**
	 * Do not use this directly, call factory()
	 *
	 * @param mixed $data      Array of data
	 * @param mixed $from_type Type of data
	 *
	 * @throws Exception 
	 */

	public function __construct($data = NULL, $from_type = NULL)
	{
		get_instance()->load->helper('inflector');
		
		// If the provided data is already formatted we should probably convert it to an array
		if ($from_type !== NULL)
		{
			if (method_exists($this, '_from_' . $from_type))
			{
				$data = call_user_func(array($this, '_from_' . $from_type), $data);
			}

			else
			{
				throw new Exception('Format class does not support conversion from "' . $from_type . '".');
			}
		}

		$this->_data = $data;
	}

	// FORMATING OUTPUT ---------------------------------------------------------

	/**
	 * to_array
	 *
	 * @param mixed $data Data to convert to array
	 *
	 * @return array
	 */

	public function to_array($data = NULL)
	{
		// If not just null, but nopthing is provided
		if ($data === NULL AND ! func_num_args())
		{
			$data = $this->_data;
		}

		$array = array();

		foreach ((array) $data as $key => $value)
		{
			if (is_object($value) OR is_array($value))
			{
				$array[$key] = $this->to_array($value);
			}

			else
			{
				$array[$key] = $value;
			}
		}

		return $array;
	}
	
	/**
	 * Format XML for output
	 *
	 * @param mixed  $data      input data
	 * @param mixed  $structure structure of data
	 * @param string $basenode  format of data
	 *
	 * @return string 
	 */

	public function to_xml($data = NULL, $structure = NULL, $basenode = 'xml')
	{
		if ($data === NULL AND ! func_num_args())
		{
			$data = $this->_data;
		}

		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') === 1)
		{
			ini_set('zend.ze1_compatibility_mode', 0);
		}

		if ($structure === NULL)
		{
			$structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><{$basenode} />");
		}

		// Force it to be something useful
		if ( ! is_array($data) AND ! is_object($data))
		{
			$data = (array) $data;
		}

		foreach ($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...           
				$key = (singular($basenode) !== $basenode) ? singular($basenode) : 'item';
			}

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_\-0-9]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value) OR is_object($value))
			{
				$node = $structure->addChild($key);

				// recrusive call.
				$this->to_xml($value, $node, $key);
			}

			else
			{
				// add single node.
				$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');

				$structure->addChild($key, $value);
			}
		}

		return $structure->asXML();
	}

	/**
	 * Format HTML for output
	 *
	 * @return string
	 */
	
	public function to_html()
	{
		$data = $this->_data;
		
		// Multi-dimentional array
		if (isset($data[0]))
		{
			$headings = array_keys($data[0]);
		}

		// Single array
		else
		{
			$headings = array_keys($data);
			$data = array($data);
		}

		$ci = get_instance();
		$ci->load->library('table');

		$ci->table->set_heading($headings);

		foreach ($data as &$row)
		{
			$ci->table->add_row($row);
		}

		return $ci->table->generate();
	}

	/**
	 * Format HTML for output
	 *
	 * @return string
	 */

	public function to_csv()
	{
		$data = $this->_data;

		// Multi-dimentional array
		if (isset($data[0]))
		{
			$headings = array_keys($data[0]);
		}

		// Single array
		else
		{
			$headings = array_keys($data);
			$data = array($data);
		}

		$output = implode(',', $headings).PHP_EOL;
		foreach ($data as &$row)
		{
			$output .= '"'.implode('","', $row).'"'.PHP_EOL;
		}

		return $output;
	}

	/**
	 * Encode as JSON
	 *
	 * @return string
	 */

	public function to_json()
	{
		return json_encode($this->_data);
	}

	/**
	 * Encode as Serialized array
	 *
	 * @return array
	 */

	public function to_serialized()
	{
		return serialize($this->_data);
	}

	/**
	 * Output as a string representing the PHP structure
	 *
	 * @return string
	 */

	public function to_php()
	{
		return var_export($this->_data, TRUE);
	}

	/**
	 * Format XML for output
	 *
	 * @param string $string Input string
	 *
	 * @return string
	 */

	protected function _from_xml($string)
	{
		return $string ? (array) simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA) : array();
	}


	/**
	 * Format HTML for output
	 * This function is DODGY! Not perfect CSV support but works with my REST_Controller
	 *
	 * @param string $string Input string
	 *
	 * @return string
	 */

	protected function _from_csv($string)
	{
		$data = array();

		// Splits
		$rows = explode("\n", trim($string));
		$headings = explode(',', array_shift($rows));
		foreach ($rows as $row)
		{
			// The substr removes " from start AND end
			$data_fields = explode('","', trim(substr($row, 1, -1)));

			if (count($data_fields) === count($headings))
			{
				$data[] = array_combine($headings, $data_fields);
			}
		}

		return $data;
	}

	/**
	 * Encode as JSON
	 *
	 * @param string $string Input string
	 *
	 * @return string
	 */

	private function _from_json($string)
	{
		return json_decode(trim($string));
	}

	/**
	 * Encode as Serialized array
	 *
	 * @param string $string Input string
	 *
	 * @return array
	 */

	private function _from_serialize($string)
	{
		return unserialize(trim($string));
	}
}

// End of file format.php //