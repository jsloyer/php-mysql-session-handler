<?php

/**
* A PHP session handler to keep session data within a MySQL database
*
* @author   Manuel Reinhard <manu@sprain.ch>
* @link     https://github.com/sprain/PHP-MySQL-Session-Handler
*/

class SessionHandler{

    /**
     * a database MySQLi connection resource
     * @var resource
     */
    protected $dbConnection;
    
    
    /**
     * the name of the DB table which handles the sessions
     * @var string
     */
    protected $dbTable;
    


    /**
     * Set db data if no connection is being injected
     * @param   string  $dbHost 
     * @param   string  $dbUser
     * @param   string  $dbPassword
     * @param   string  $dbDatabase
     */ 
    public function setDbDetails($dbHost, $dbUser, $dbPassword, $dbDatabase){

        $this->dbConnection = mysql_connect($dbHost, $dbUser, $dbPassword) or die(mysql_error());
        mysql_select_db($dbDatabase) or die (mysql_error());
            
    }//function
    
    
    /**
     * Inject DB connection from outside
     * @param   object  $dbConnection   expects MySQLi object
     */
    public function setDbTable($dbTable){
    
        $this->dbTable = $dbTable;
        
    }
    

    /**
     * Open the session
     * @return bool
     */
    public function open() {
  
        //delete old session handlers
        $limit = time() - (3600 * 24);
        $sql = sprintf("DELETE FROM %s WHERE timestamp < %s", $this->dbTable, $limit);
        return mysql_query($sql, $this->dbConnection);

    }

    /**
     * Close the session
     * @return bool
     */
    public function close() {

        return mysql_close($this->dbConnection);

    }

    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    public function read($id) {

        $sql = sprintf("SELECT data FROM %s WHERE id = '%s'", $this->dbTable, mysql_real_escape_string($id));
        if ($result = mysql_query($sql, $this->dbConnection)) {
            if (mysql_num_rows($result) && mysql_num_rows($result) > 0) {
                $record = mysql_fetch_array($result);
                return $record['data'];
            } else {
                return false;
            }
        } else {
            return false;
        }
        return true;
        
    }
    

    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    public function write($id, $data) {

        $sql = sprintf("REPLACE INTO %s VALUES('%s', '%s', '%s')",
                       $this->dbTable, 
                       mysql_real_escape_string($id),
                       mysql_real_escape_string($data),
                       time());
        return mysql_query($sql, $this->dbConnection);

    }

    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    public function destroy($id) {
        $sql = sprintf("DELETE FROM %s WHERE `id` = '%s'", $this->dbTable, mysql_real_escape_string($id));
        return mysql_query($sql, $this->dbConnection);

    }
    
    

    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     * @see session.gc_divisor      100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability    1
     * @usage execution rate 1/100
     *        (session.gc_probability/session.gc_divisor)
     */
    public function gc($max) {

        $sql = sprintf("DELETE FROM %s WHERE `timestamp` < '%s'", $this->dbTable, time() - intval($max));
        return mysql_query($sql, $this->dbConnection);

    }

}//class
