<?php  
header("Content-type: text/html; charset=utf-8");    
$msdb=mssql_connect("*.*.*.*:2900","sa","maxwin80743314");  
if (!$msdb) {  
    echo "connect sqlserver error";  
    exit;  
    }  
mssql_select_db("maxbus",$msdb);  
$result = mssql_query("SELECT top 5 * FROM logicroute", $msdb);  
while($row = mssql_fetch_array($result)) {  
 print_r($row);  
}  
mssql_free_result($result);  
?>
