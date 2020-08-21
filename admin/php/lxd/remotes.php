<?php

//Instantiate the GET variables
if (isset($_GET['host']))
  $host = filter_var(urldecode($_GET['host']), FILTER_SANITIZE_STRING);
if (isset($_GET['port']))  
  $port = filter_var(urldecode($_GET['port']), FILTER_SANITIZE_STRING);
if (isset($_GET['alias']))
  $alias = filter_var(urldecode($_GET['alias']), FILTER_SANITIZE_STRING);
if (isset($_GET['id']))
  $id = filter_var(urldecode($_GET['id']), FILTER_SANITIZE_STRING);
if (isset($_GET['action']))
  $action = filter_var(urldecode($_GET['action']), FILTER_SANITIZE_STRING);

//Set the curl variables
$cert = "/root/.config/lxc/client.crt";
$key = "/root/.config/lxc/client.key";

$db = new SQLite3('/var/lxdware/data/sqlite/lxdware.sqlite');

//Run the matching action
switch ($action) {
  case "addRemote":
    if (filter_var($host, FILTER_VALIDATE_IP) || filter_var($host, FILTER_VALIDATE_DOMAIN))
      $valid_domain = true;

    if (filter_var($port, FILTER_VALIDATE_INT))
      $valid_port = true;

    if ($valid_domain && $valid_port){
      $url = "https://" . $host . ":" . $port . "/1.0";
      $results = shell_exec("sudo curl -k -L --cert $cert --key $key -X GET $url");
      $data = json_decode($results, true);

    if ($data['metadata']['auth'] == "trusted"){
      $db->exec('CREATE TABLE IF NOT EXISTS lxd_hosts (id INTEGER PRIMARY KEY AUTOINCREMENT, host TEXT NOT NULL, port INTEGER NOT NULL, alias TEXT, protocol TEXT)');
      $record_added = $db->exec("INSERT INTO lxd_hosts (host, port, alias, protocol) VALUES ('$host', $port, '$alias', 'lxd')");
      if ($record_added)
        echo "Connection Successful, record added";
      else 
        echo "Connection Successful, error adding record";
    } 
    else {
      echo "Connection Problem";
    }
    } 
    else {
      echo "Invalid host or port";
    }
  break;

  case "removeRemote":
    $record_removed = $db->exec("DELETE FROM lxd_hosts WHERE id = $id");
    if ($record_removed)
      echo "Record removed";
    else 
      echo "Error removing record"; 
  break;

}


?>