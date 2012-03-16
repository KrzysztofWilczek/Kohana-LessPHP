<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Less files compilation to one or more css files
 * @author Krzysztof Wilczek
 */
class Less_Core
{
	// Default less files extension
	public static $ext = '.less';

	/**
	 * Get the link tag of less paths
	 *
	 * @param   mixed     array of css paths or single path
	 * @param   string    value of media css type
	 * @param   boolean   allow compression
	 * @return  string    link tag pointing to the css paths
	 */
	public static function compile($array = '', $media = 'screen')
	{
		// There was specyfied some path to less file but not in array
		if (is_string($array))
		{
			$array = array($array);
		}
		
		// No any less file to compile
		if (empty($array)) return self::_htmlComment('no less files');

		// Validate less files
		$stylesheets = self::_validateLessFilesList($array);
		
		// Get config
		$config = Kohana::$config->load('less');

		if ($config['bind_to_one_file'])
		{
			// Compile set of less sheets to one css
			return self::_compileToOneFile($config, $stylesheets);
		}
		else
		{
			// Compile one by one less to css file
			return self::_compileFileToFile($config, $stylesheets);
		}
		
	}

	/**
	 * Add commented error
	 * @param String $message
	 * @return String
	 */
	protected static function _htmlComment($message)
	{
		return '<-- '.$message.' //-->';
	}
	
	/**
	 * Check less files 
	 * @param Array $files
	 */
	protected static function _validateLessFilesList($files)
	{
		$stylesheets = array();
		
		// Validate less files
		foreach ($files as $file)
		{
			if (file_exists($file))
			{
				array_push($stylesheets, $file);
			}
			else if (file_exists($file.self::$ext))
			{
				array_push($stylesheets, $file.self::$ext);
			}
			else
			{
				array_push($assets, self::_htmlComment('could not find '.Debug::path($file).self::$ext));
			}
		}
		
		// There is no valid less file
		if ( ! count($stylesheets)) return self::_htmlComment('all less files are invalid');
		
		return $stylesheets;
	}
		
	/**
	 * Get the latest modyfiaction data of one of the files specyfied in list
	 * @param Array $files
	 * @return String
	 */
	protected static function _getLatestModyficationDate($files)
	{
		$last_modified = 0;
		
		foreach ($files as $file)
		{
			$modified = filemtime($file);
			if ($modified !== false and $modified > $last_modified) $last_modified = $modified;
		}
		
		return $last_modified;
		
	}
	
	/**
	 * Create new CSS file and sets chmod 777 
	 * @param String $file_name 
	 */
	protected static function _createCSSFile($file_name = '')
	{
		if (!empty($file_name))
		{
			touch($file_name);
			chmod($file_name, 0777);
		}
	}
	
	/**
	 * Compile set of less files into one CSS file. Return link to CSS local file.
	 * @param Object $config
	 * @param Array $stylesheets
	 * @return String 
	 */
	protected static function _compileToOneFile($config, array $stylesheets)
	{
		$css_file_path = APPPATH .'../'. $config['path'] . $config['css_file_name'];
		$css_ralative_path = $config['path'] . $config['css_file_name'];
		$last_modyfication_of_css_target_file = 0;
		if (file_exists($css_file_path))
		{
			$last_modyfication_of_css_target_file = filemtime($css_file_path);
		}
		else
		{
			//die($css_file_path);
			self::_createCSSFile($css_file_path);
		}
		
		$last_modyfication_of_less_files = self::_getLatestModyficationDate($stylesheets);
		
		// Something was change in less files
		if ($last_modyfication_of_less_files > $last_modyfication_of_css_target_file)
		{
			$css_code = null;
			foreach($stylesheets as $stylesheet)
			{
				$less = new lessc($stylesheet);
				$css_code .= $less->parse();
			}
			
			file_put_contents($css_file_path, $css_code);
		}
		return html::style($css_ralative_path);
	}
	
	/**
	 * Making CSS file name from less file
	 * @param String $less_file_name
	 * @return String
	 */
	protected static function _prepareCSSFileName($less_file_name)
	{
		$file_path_parts = explode('/', $less_file_name);
		$less_file_name = $file_path_parts[count($file_path_parts)-1];
		$less_file_name = str_replace(self::$ext, '', $less_file_name);
		return $less_file_name;
	}
	
	/**
	 * Compile every less file to other css file. Return links to CSS lokal files.
	 * @param Object $config
	 * @param Array $stylesheets
	 * @return String
	 */
	protected static function _compileFileToFile($config, array $stylesheets)
	{
		$css_file_path = APPPATH .'../'. $config['path'];
		$css_ralative_path = $config['path'];
		$response = null;
		foreach ($stylesheets as $stylesheet)
		{
			$css_file_name = self::_prepareCSSFileName($stylesheet);
			$css_file_path = APPPATH .'../'. $config['path'] . $css_file_name; 
			$css_ralative_path = $config['path'] . $css_file_name;

			// Targer CSS file doesn't exists
			if (!file_exists($css_file_path))
			{
				self::_createCSSFile($css_file_path);
				lessc::ccompile($stylesheet, $css_file_path);
			}
			// Target CSS file is older than less file (something was changed)
			else if (filemtime($css_file_path) < filemtime($stylesheet))
			{
				lessc::ccompile($stylesheet, $css_file_path);
			}
			$response .= html::style($css_ralative_path);		
		}
		return $response;
	}
}