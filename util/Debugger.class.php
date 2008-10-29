<?php

if (!class_exists('Debugger', false))
{

	class Debugger
	{

		// +--------------------------------------------------------------------+
		// | PRIVATE VARIABLES													|
		// +--------------------------------------------------------------------+

		private static $enabled = true;

		// +--------------------------------------------------------------------+
		// | PUBLIC METHODS														|
		// +--------------------------------------------------------------------+

		public static function enabled ($v)
		{
			self::$enabled = $v;
		}

		// ----------------------------------------------------------------------

		public static function varDump ()
		{

			if (!self::$enabled) return;

			for ($i=0; $i<func_num_args(); $i++)
			{
				print('<pre>');
				var_dump(func_get_arg($i));
				print('</pre>');
			}

		}

		// ----------------------------------------------------------------------

		public static function printTrace ($message, $file, $line)
		{
			if (!self::$enabled) return;

			printf('<pre>%s(%s): %s...</pre>', $file, $line, $message);

		}

		// ----------------------------------------------------------------------

	}

}

?>