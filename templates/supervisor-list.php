<!-- page to display supervisor information -->
<?php include('../includes/header.php');

//---- CONNECT TO DATABASE ---//
$db_server = "localhost";
$db_root = "root";
$db_pwd = "";
$db_name = "rses_db";

$connect_db = new mysqli($db_server, $db_root, $db_pwd, $db_name);

if($connect_db->connect_error){
    die("Sorry, could not connect to the Database.". $connect_db->connect_error);
} 

//---- GET SQL RECORDS ---//
$sql_stmt = "SELECT supervisors.supervisor_name, supervisors.affiliation, 
users.email, supervisors.no_of_publications, supervisors.expertise 
FROM supervisors JOIN users ON supervisors.user_id = users.user_id
WHERE users.role_level = 'supervisor'";

$supervisor_info_tb = $connect_db->query($sql_stmt); // supervisor information table.

if (!$supervisor_info_tb) {
    die("Query failed: " . $connect_db->error);
}

?>
<!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/rses/assets/css/style.css"/>
    <title>Supervisor List</title>
 </head>
 <body>
    <section class="supervisor-list-section">
        <h2>SUPERVISORS</h2>
     <div class="sup-list-container">
      <?php 
      if($supervisor_info_tb->num_rows > 0){ // if there's data in the record.
         while($row = $supervisor_info_tb->fetch_assoc()){       
      ?>
          <div class="supervisor-list-div">
            <img src="/rses/assets/images/supervisor-profile-img.png" alt="supervisor profile"/>
            <div class="supervisor-details-div">
               <div><strong>Name: </strong><?= htmlspecialchars($row['supervisor_name']) ?></div>
               <div><strong>Affiliation: </strong><?= htmlspecialchars($row['affiliation']) ?></div>
               <div><strong>Email: </strong><?= htmlspecialchars($row['email']) ?></div>
               <div><strong>Publications: </strong><?= htmlspecialchars($row['no_of_publications']) ?></div>
               <div><strong>Expertise: </strong><?= htmlspecialchars($row['expertise']) ?></div>
            </div>
          </div>

       <?php  }// end of while loop.
       } else 
       {
        echo "<p style='text-align:center; color:aliceblue; font-size:18px;'>No supervisors registered yet.</p>";
       } ?>
      </div>
    </section>
     
 </body>
</html>