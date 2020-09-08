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
  $url = "https://" . $row['host'] . ":" . $row['port'] . "/1.0/certificates?project=" . $project;
  $remote_data = shell_exec("sudo curl -k -L --cert $cert --key $key -X GET $url");
  $remote_data = json_decode($remote_data, true);
  $certificate_urls = $remote_data['metadata'];

  $i = 0;
  echo '{ "data": [';

  foreach ($certificate_urls as $certificate_url){
    $url = "https://" . $row['host'] . ":" . $row['port'] . $certificate_url . "?project=" . $project;
    $certificate_data = shell_exec("sudo curl -k -L --cert $cert --key $key -X GET $url");
    $certificate_data = json_decode($certificate_data, true);
    $certificate_data = $certificate_data['metadata'];
    
if ($i > 0){
      echo ",";
    }
    $i++;

    echo "[ ";
    echo '"';
    echo "<a href='#' onclick=loadCertificateJson('".$certificate_data['fingerprint']."')> <i class='fas fa-wallet fa-lg' style='color:#4e73df'></i> </a>";    
    echo '",';

    echo '"';
    echo "<a href='#' onclick=loadCertificateJson('".$certificate_data['fingerprint']."')>".htmlentities($certificate_data['name'])."</a>";
    echo '",';


    echo '"' . htmlentities($certificate_data['type']) . '",';
    echo '"' . htmlentities($certificate_data['fingerprint']) . '",';


    echo '"';
      echo "<div class='dropdown no-arrow'>";
      echo "<a class='dropdown-toggle' href='#' role='button' id='dropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
      echo "<i class='fas fa-ellipsis-v fa-lg fa-fw text-gray-400'></i>";
      echo "</a>";
      echo "<div class='dropdown-menu dropdown-menu-right shadow animated--fade-in' aria-labelledby='dropdownMenuLink'>";
      echo "<div class='dropdown-header'>Options:</div>";
      echo "<a class='dropdown-item' href='#' onclick=deleteCertificate('".$certificate_data['fingerprint']."')>Delete</a>";
      echo "</div>";
      echo "</div>";
    echo '"';

    echo " ]";

  }

  echo " ]}";
  
}

?>