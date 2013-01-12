<?php namespace Library;

/**
 * Central library for Plugin logic
 *
 * @author   PyroCMS Dev Team
 * @package  PyroCMS\Core\Libraries
 */
abstract class PluginAbstract
{
	/**
	 * Holds attribute data
	 */
	private $attributes = array();
	
	/**
	 * Holds content between tags
	 */
	private $content = array();

	/**
	 * Set Data for the plugin.
	 *
	 * Avoid doing this in constructor so we do not force logic on developers.
	 *
	 * @param array $content Content of the tags if any
	 * @param array $attributes Attributes passed to the plugin
	 */
	public function set_data($content, $attributes)
	{
		$content AND $this->content = $content;

		if ($attributes)
		{
			// Let's get parse_params first since it
			// dictates how we handle all tags
			if ( ! isset($attributes['parse_params'])) $attributes['parse_params'] = true;
			
			if (str_to_bool($attributes['parse_params']))
			{
				// For each attribute, let's see if we need to parse it.
				foreach ($attributes as $key => $attr)
				{
					$attributes[$key] = $this->parse_parameter($attr);
				}
			}
			
			$this->attributes = $attributes;
		}
	}

	/**
	 * Make the Codeigniter object properties & methods accessible to this class.
	 *
	 * @param string $var The name of the method/property.
	 *
	 * @return mixed
	 */
	public function __get($var)
	{
		if (isset(get_instance()->$var))
		{
			return get_instance()->$var;
		}
	}

	/**
	 * Getter for the content.
	 *
	 * @return string
	 */
	public function content()
	{
		return $this->content;
	}

	/**
	 * Getter for the attributes.
	 *
	 * @return array
	 */
	public function attributes()
	{
		return $this->attributes;
	}

	/**
	 * Get the value of an attribute.
	 *
	 * @param string $param The name of the attribute.
	 * @param mixed $default The default value to return if no value can be found.
	 *
	 * @return mixed The value.
	 */
	public function attribute($param, $default = null)
	{
		return isset($this->attributes[$param]) ? $this->attributes[$param] : $default;
	}

	/**
	 * Parse special variables in an attribute
	 *
	 * @param string $value The value of the attribute.
	 * @param array  $data  Additional data to parse with
	 *
	 * @return string The value.
	 */
	public function parse_parameter($value, $data = array())
	{
		// Parse for variables. Before we do anything crazy,
		// let's check for a bracket.
		if (strpos($value, '[[') !== false)
		{
			// Change our [[ ]] to {{ }}. Sneaky.
			$value = str_replace(array('[[', ']]'), array('{{', '}}'), $value);
			
			$default_data = array(
				'segment_1' => $this->uri->segment(1),
				'segment_2' => $this->uri->segment(2),
				'segment_3' => $this->uri->segment(3),
				'segment_4' => $this->uri->segment(4),
				'segment_5' => $this->uri->segment(5),
				'segment_6' => $this->uri->segment(6),
				'segment_7' => $this->uri->segment(7)
			);
	
			// user info
			if($this->current_user) {
				$default_data['user_id']	= $this->current_user->id;
				$default_data['username']	= $this->current_user->username;
			}

			return $this->parser->parse_string($value, array_merge($default_data, $data), true);
		}

		return $value;
	}

	/**
	 * Render a view located in a module.
	 *
	 * @param string $module The module to load the view from.
	 * @param string $view The name of the view to load.
	 * @param array $vars The array of variables to pass to the view.
	 * @param bool $parse_output Send the output through the LEX parser?
	 *
	 * @return string The rendered view.
	 */
	public function module_view($module, $view, $vars = array(), $parse_output = true)
	{
		if (file_exists($this->template->get_views_path().'modules/'.$module.'/'.$view.(pathinfo($view, PATHINFO_EXTENSION) ? '' : '.php')))
		{
			$path = $this->template->get_views_path().'modules/'.$module.'/';
		}
		else
		{
			list($path, $view) = Modules::find($view, $module, 'views/');
		}

		// save the existing view array so we can restore it
		$save_path = $this->load->get_view_paths();

		// add this view location to the array
		$this->load->set_view_path($path);

		$content = $this->load->_ci_load(array('_ci_view' => $view, '_ci_return' => true));

		// Put the old array back
		$this->load->set_view_path($save_path);
		
		// Parse output with LEX if desired
		if ($parse_output) {
			$content = $this->parser->parse_string($content, ((array)$vars), true);
		}

		return $content;
	}
	
	/**
	 * Render a view located in your theme folder.
	 *
	 * @param string $view The name of the view to load.
	 * @param array $vars The array of variables to pass to the view.
	 * @param bool $parse_output Send the output through the LEX parser?
	 *
	 * @return string The rendered view.
	 */
	public function theme_view($view, $vars = array(), $parse_output = true)
	{
		// default to .html extension like the {{ theme:partial }} plugin
		$view = strpos($view, '.') ? $view : $view . '.html';
		
		// save the existing view array so we can restore it
		$save_path = $this->load->get_view_paths();

		// add this view location to the array
		$this->load->set_view_path($this->load->get_var('template_views'));
		
		$content = $this->load->_ci_load(array('_ci_view' => $view, '_ci_return' => true));
		
		// Put the old array back
		$this->load->set_view_path($save_path);
		
		// Parse output with LEX if desired
		if ($parse_output) {
			$content = $this->parser->parse_string($content, ((array)$vars), true);
		}

		return $content;
	}
}