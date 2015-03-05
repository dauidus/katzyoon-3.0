<?php
class backupbuddy_api {
	
	/* getLastError() -- NOT YET IMPLEMENTED.
	 *
	 * Retrieve the last error the API encountered. Use if a method returned bool FALSE to get message.
	 *
	 */
	public static function getLastError() {
	}
	
	
	// backupbuddy_api::getOverview()
	public static function getOverview() {
		self::_before();
		return require( dirname(__FILE__) . '/api/_getOverview.php' );
	}
	
	
	// backupbuddy_api::getSchedules()
	public static function getSchedules() {
		self::_before();
		return require( dirname(__FILE__) . '/api/_getSchedules.php' );
	}
	
	
	public static function getPreDeployInfo() {
		self::_before();
		return require( dirname(__FILE__) . '/api/_getPreDeployInfo.php' );
	}
	
	
	public static function getActivePlugins() {
		self::_before();
		return require( dirname(__FILE__) . '/api/_getActivePlugins.php' );
	}
	
	
	private static function _before() {
	}
	
} // end class.