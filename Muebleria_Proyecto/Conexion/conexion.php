<?php
class Database {
    private $host = "localhost";
    private $usuario = "MUEBLERIA";
    private $password = "Admin_2026DB";
    private $conn;
    
    public function __construct() {
        // Para Oracle Cloud 
        $connection_string = "(DESCRIPTION=
            (ADDRESS=(PROTOCOL=tcps)(PORT=1522)(HOST=adb.us-ashburn-1.oraclecloud.com))
            (CONNECT_DATA=(SERVICE_NAME=g77f0cc45aba4ee_proyectomuebleria_high.adb.oraclecloud.com))
            (SECURITY=
                (SSL_SERVER_DN_MATCH=yes)
                (MY_WALLET_DIRECTORY=C:\\oracle\\wallet)
            )
        )";
        
        $this->conn = oci_connect($this->usuario, $this->password, $connection_string, 'AL32UTF8');
        
        if (!$this->conn) {
            $error = oci_error();
            die("Error de conexión: " . $error['message']);
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql, $params = []) {
        $stmt = oci_parse($this->conn, $sql);
        
        foreach ($params as $key => $value) {
            oci_bind_by_name($stmt, $key, $params[$key]);
        }
        
        oci_execute($stmt);
        return $stmt;
    }
    
    public function fetchAll($stmt) {
        $rows = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function fetchOne($stmt) {
        return oci_fetch_assoc($stmt);
    }
    
    public function close() {
        oci_close($this->conn);
    }
}
?>