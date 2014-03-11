<?php

   /*
   Plugin Name: PHP Mysql Session Handler
   Plugin URI: https://github.com/jsloyer/PHP-MySQL-Session-Handler
   Description: This plugin will store php sessions in the database
   Version: 1.0
   Author: Jeff Sloyer
   Author URI: https://github.com/jsloyer
   License: Apache V2
   */

require_once("SessionHandler.php");

function sessionHandler() {
	$session = new SessionHandler();

	// add db data
	$session->setDbDetails(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// OR alternatively send a MySQLi ressource
	// $session->setDbConnection($mysqli);

	$session->setDbTable("sessions");
	session_set_save_handler(array($session, "open"),
		array($session, "close"),
		array($session, "read"),
		array($session, "write"),
		array($session, "destroy"),
		array($session, "gc")
	);

	// The following prevents unexpected effects when using objects as save handlers.
	register_shutdown_function("session_write_close");

	session_start();
}

function createTableIfDoesNotExist() {
	$query = "SELECT id FROM sessions";
	mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	mysql_select_db(DB_NAME);
	$result = mysql_query($query);

	if(empty($result)) {
        $query = "CREATE TABLE sessions (
          id varchar(255) NOT NULL,
          data mediumtext NOT NULL,
          timestamp int(255) NOT NULL,
          PRIMARY KEY (id)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $result = mysql_query($query);
	}
}

add_filter("authenticate", "sessionHandler", 0);

register_activation_hook( __FILE__, "createTableIfDoesNotExist");