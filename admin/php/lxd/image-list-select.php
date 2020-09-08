<?php

$cert = "/var/lxdware/data/lxd/client.crt";
$key = "/var/lxdware/data/lxd/client.key";

if (isset($_GET['remote']))
  $remote = filter_var(urldecode($_GET['remote']), FILTER_SANITIZE_STRING);
if (isset($_GET['project']))
  $project = filter_var(urldecode($_GET['project']), FILTER_SANITIZE_STRING);
if (isset($_GET['image_type']))
  $image_type = filter_var(urldecode($_GET['image_type']), FILTER_SANITIZE_STRING);


$db = new SQLite3('/var/lxdware/data/sqlite/lxdware.sqlite');
$db_statement = $db->prepare('SELECT * FROM lxd_hosts WHERE id = :id LIMIT 1;');
$db_statement->bindValue(':id', $remote);
$db_results = $db_statement->execute();

while($row = $db_results->fetchArray()){
  $url = "https://" . $row['host'] . ":" . $row['port'] . "/1.0/images?project=" . $project;
  $remote_data = shell_exec("sudo curl -k -L --cert $cert --key $key -X GET $url");
  $remote_data = json_decode($remote_data, true);
  $image_urls = $remote_data['metadata'];
  foreach ($image_urls as $image_url){
    $url = "https://" . $row['host'] . ":" . $row['port'] . $image_url . "?project=" . $project;
    $image_data = shell_exec("sudo curl -k -L --cert $cert --key $key -X GET $url");
    $image_data = json_decode($image_data, true);
    $image_data = $image_data['metadata'];

    if ($image_data['fingerprint'] == "" || $image_data['type'] != $image_type)
    continue;

    echo '<option value="' . $image_data['fingerprint'] . '">' . htmlentities($image_data['properties']['description']) . '</option>';


  }
}

?>
