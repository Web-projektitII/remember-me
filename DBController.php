<?php
define('DB','db_auth');
include('debuggeri.php');
include('db_functions.php');
include('posti.php');

class DBController {
	private $host = DB_SERVER;
	private $user = DB_USERNAME;
	private $password = DB_PASSWORD;
	private $database = DB;
	private $conn;
    /* Luodaan tietokantayhteys vain kerran */
    private static $keep_conn;
   	
    function __construct() {
        $this->conn = $this->getConn() ?? $this->connectDB();
        self::$keep_conn = $this->conn;
	    }	
	
    function getConn(){
        return self::$keep_conn;
    }

	function connectDB() {
        debuggeri(__METHOD__.",keep:".empty($this->getConn()) ? "asettamaton" : "asetettu");
		$conn = mysqli_connect($this->host,$this->user,$this->password,$this->database);
        return $conn;
	}
	
    function runBaseQuery($query) {
        $result = mysqli_query($this->conn,$query);
        while($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
            }		
        if(!empty($resultset)) return $resultset;
        }
    
    function runQuery($query, $param_type, $param_value_array) {
        $sql = $this->conn->prepare($query);
        $this->bindQueryParams($sql, $param_type, $param_value_array);
        $sql->execute();
        $result = $sql->get_result();
        
        while($row = $result->fetch_assoc()) {
            $resultset[] = $row;
            }
                
        if(!empty($resultset)) return $resultset;
        }
    
    function bindQueryParams($sql, $param_type, $param_value_array) {
        $param_value_reference[] = &$param_type;
        for($i=0; $i<count($param_value_array); $i++) {
            $param_value_reference[] = &$param_value_array[$i];
            }
        debuggeri(__METHOD__.",$param_type,".var_export($param_value_array,true));
        call_user_func_array(array($sql,'bind_param'),$param_value_reference);
        }
    
    function insert($query, $param_type, $param_value_array) {
        $sql = $this->conn->prepare($query);
        //debuggeri(__METHOD__.",$query,$param_type,".var_export($param_value_array,true));
        $this->bindQueryParams($sql, $param_type, $param_value_array);
        $sql->execute();
    }
    
    function update($query, $param_type, $param_value_array) {
        $sql = $this->conn->prepare($query);
        //debuggeri(__METHOD__.",$query");
        $this->bindQueryParams($sql, $param_type, $param_value_array);
        $sql->execute();
        }
}
?>