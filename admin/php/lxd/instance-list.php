<?php

$cert = "/var/lxdware/data/lxd/client.crt";
$key = "/var/lxdware/data/lxd/client.key";

if (isset($_GET['remote']))
  $remote = filter_var(urldecode($_GET['remote']), FILTER_SANITIZE_STRING);
if (isset($_GET['project']))
  $project = filter_var(urldecode($_GET['project']), FILTER_SANITIZE_STRING);

$db = new SQLite3('/var/lxdware/data/sqlite/lxdware.sqlite');
$db_statement = $db->prepare('SELECT * FROM lxd_hosts WHERE id = :id LIMIT 1;');
$db_statement->bindValue(':id', $remote);
$db_results = $db_statement->execute();

while($row = $db_results->fetchArray()){
  $url = "https://" . $row['host'] . ":" . $row['port'] . "/1.0/instances?recursion=2&project=" . $project;
  $remote_data = shell_exec("sudo curl -k -L --cert $cert --key $key -X GET $url");
  $remote_data = json_decode($remote_data, true);

  $i = 0;
  echo '{ "data": [';

  foreach ($remote_data['metadata'] as $instance_data){
    if ($instance_data['name'] == "")
      continue;

    if ($i > 0){
      echo ",";
    }
    $i++;

    echo "[ ";
    if ($instance_data['status'] == "Running"){
      echo '"';
      echo "<a href='instance.html?instance=".$instance_data['name']."&remote=".$remote."&project=".$project."'><i class='fas fa-cube fa-lg' style='color:#4e73df'></i> </a>";
      echo '",';

      echo '"';
      echo "<a href='instance.html?instance=".$instance_data['name']."&remote=".$remote."&project=".$project."'> ".$instance_data['name']."</a>";
      echo '",';
    }
    else {
      echo '"';
      echo "<a href='instance.html?instance=".$instance_data['name']."&remote=".$remote."&project=".$project."'><i class='fas fa-cube fa-lg' style='color:#ddd'></i> </a>";
      echo '",';

      echo '"';
      echo "<a href='instance.html?instance=".$instance_data['name']."&remote=".$remote."&project=".$project."'> ".$instance_data['name']."</a>";
      echo '",';
    }

    echo '"' . $instance_data['config']['image.description'] . '",';

    $ipv4_address = "";
    $ipv6_address = "";

    if (isset($instance_data['state']['network']['eth0'])) {

      foreach ($instance_data['state']['network']['eth0']['addresses'] as $address){

        if ($address['family'] == 'inet' && $address['scope'] == 'global') {
          $ipv4_address = $address['address'];
        }

        if ($address['family'] == 'inet6' && $address['scope'] == 'global') {
          $ipv6_address = $address['address'];
        }

      }
    }

    $disk_usage = "";

    if (isset($instance_data['state']['disk']['root'])) {

      $disk_usage = $instance_data['state']['disk']['root']['usage']/1024/1024;
      $disk_usage_unit = "MB";

      if ($disk_uage > 1024){
          $disk_usage = $disk_usage / 1024;
          $disk_usage_unit = "GB";
      }

      $disk_usage = number_format($disk_usage,2)." ".$disk_usage_unit;

    }

    echo '"' . $ipv4_address . '",';
    echo '"' . $ipv6_address . '",';
    echo '"' . $disk_usage . '",';
    echo '"' . $instance_data['type'] . '",';
    echo '"' . $instance_data['architecture'] . '",';
    echo '"' . $instance_data['status'] . '",';

    if ($instance_data['status'] == "Running"){
      echo '"';
      echo "<a href='#' onclick=stopInstance('".$instance_data['name']."')> <i class='fas fa-stop fa-lg' style='color:#ddd'></i> </a>";
      echo '"';
    }
    else{
      echo '"';
      echo "<a href='#' onclick=startInstance('".$instance_data['name']."')> <i class='fas fa-play fa-lg' style='color:#ddd'></i> </a>";
      echo '"';
    }

    echo " ]";

  }

  echo " ]}";

}



?>
