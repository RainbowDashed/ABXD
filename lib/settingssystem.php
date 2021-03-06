<?php

/*
	I really don't like the idea of settings.php. Self-modifying code is bad, and 
	it's like that just for historical reasons.
	
	We should now do one settings system that stores everything in the MySQL DB, and works
	for all possible use cases.
	
	This is how the new Settings System should work:
	
	We have to store 4 types of settings:
	- Board global settings
	- Plugin global settings
	- Board per-user settings
	- Plugin per-user settings
	
	Global settings (both types) are stored in the "settings" table.
	"plugin" field is the plugin name, or "main" if it's a board setting.
	"name" field is the name of the setting.
	"value" field is the value of the setting.
	
	TODO: specify how are per-user settings stored and implement them. 
	
	======
	
	Types of settings:
	
	- boolean		(0 or 1, uses a checkbox)
	- integer
	- text			Creates a text field
	- textbox		Creates a text box with no controls
	- textbbcode	Creates a text box with BBCode post help
	- texthtml		Creates a text box with HTML post help (if it's ever implemented)

	- theme			Creates a theme selection drop-down. Stores the theme name.
	- forum			Creates a forum selection drop-down. Stores the FID
	
	Additionally settings can have a default value, a friendly name, and a help text.
	Also there should be some validation of the setting values.
	Specially for per-user settings, which can be modified at will by users.

*/


class Settings
{
	public static $pluginsettings;
	//Loads ALL the settings.

	public static function load()
	{
		self::$pluginsettings = array();
		$rSettings = Query("select * from settings");
		
		while($setting = Fetch($rSettings))
		{
			self::$pluginsettings[$setting["plugin"]][$setting["name"]] = $setting["value"];
		}
	}

	public static function getForPlugin($pluginname)
	{
		global $plugins;
		
		$settings = array();
		
		//Get the setting list.
		if($pluginname == "main")
			include("settingsfile.php");
		else
		{
			@include("./plugins/".$plugins[$pluginname]['dir']."/settingsfile.php");
		}
		return $settings;
	}
	

	public static function checkPlugin($pluginname)
	{
		if(!isset(self::$pluginsettings[$pluginname]))
			self::$pluginsettings[$pluginname] = array();
		
		$changed = false;		

		$settings = self::getForPlugin($pluginname);		
		foreach($settings as $name => $data)
		{
			$type = $data["type"];
			$default = $data["default"];
			
			if(!isset(self::$pluginsettings[$pluginname][$name]))
			{
				self::$pluginsettings[$pluginname][$name] = $default;
				$changed = true;
			}
				
			if(!self::validate(self::$pluginsettings[$pluginname][$name], $type))
			{
				self::$pluginsettings[$pluginname][$name] = $default;
				$changed = true;
			}
		}
		
		self::save($pluginname);
	}

	public static function save($pluginname)
	{
		foreach(self::$pluginsettings[$pluginname] as $name=>$value)
			Query("insert into settings (plugin, name, value) values (".
				"'".justEscape($pluginname)."', ".
				"'".justEscape($name)."', ".
				"'".justEscape($value)."') ".
				"on duplicate key update value=VALUES(value)");
	}
	
	
	public static function validate($value, $type)
	{
		if($type == "integer" || $type == "user" || $type == "forum")
			if($value != (int)$value) //TODO: I'm not sure if it's the best way. is_numeric allows float values too.
				return false;

		return true;
	}
	
	public static function get($name)
	{
		return self::$pluginsettings["main"][$name];
	}
}
?>
