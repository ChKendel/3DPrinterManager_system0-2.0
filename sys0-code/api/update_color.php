<?php
	// Zugriff nur fuer angemeldete Nutzer mit Einstellungs-Recht (role[9]) -
	// vorher war diese Seite komplett ohne Login erreichbar (unauth. SQL-Injection).
	session_start();
	if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"][9]!=="1"){
		header("location: /login/login.php");
		exit;
	}
?>
<!DOCTYPE html>
<html>
<?php

include "../config/config.php";

?>

<script src="/assets/js/load_page.js"></script>
<script>
function load_user()
{
	$(document).ready(function(){
   	$('#content').load("/assets/php/user_page.php");
	});
}
</script>
<?php
	echo "<script type='text/javascript' >load_user()</script>";
?>
<?php
	$color=$_SESSION["color"];
	include "../assets/components.php";
	if(isset($_POST["printer"])){
		$color=htmlspecialchars($_GET["color"]);
		$id=(int)$_POST["printer"]; // (int) verhindert SQL-Injection im numerischen id-Kontext
		$sql="update printer set color='".mysqli_real_escape_string($link, $color)."' where id=$id;"; // id ist int, color escaped
		//echo($sql);
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_execute($stmt);
	}
?>
<div id="content"></div>

<head>
  <title>Filamentfarbe Aktualisieren</title>
  
</head>
<body>
	<div class="container mt-5" style="min-height: 95vh;">
		<div class="row justify-content-center">
	  	<div style="width: 100hh">
	      <h1>Filamentfarbe Aktualisieren</h1>
	      <form class="mt-5" enctype="multipart/form-data" method="POST" action="">
	      <input type="text" value="<?php echo htmlspecialchars($_GET["color"] ?? ''); ?>" name="color" disabled><br><br>
	      <select class="form-control selector" name="printer" required>
		<?php
			//get number of printers
			$num_of_printers=0;
			$sql="select count(*) from printer;";
			$stmt = mysqli_prepare($link, $sql);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);
			mysqli_stmt_bind_result($stmt, $num_of_printers);
			mysqli_stmt_fetch($stmt);
			//echo("test1:".$num_of_printers);
			$last_id=0;
			$printers_av=0;
			while($num_of_printers!=0)
			{
				$id=0;
				$sql="Select id from printer where id>$last_id order by id";
				//echo $sql;
				$color="";
				$stmt = mysqli_prepare($link, $sql);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				mysqli_stmt_bind_result($stmt, $id);
				mysqli_stmt_fetch($stmt);
				if($id!=0 && $id!=$last_id)
				{
					if($id==$_POST["printer"])
						echo("<option printer='$id' value='$id' selected>Drucker $id</option>");
					else
						echo("<option printer='$id' value='$id'>Drucker $id</option>");
				}
				$last_id=$id;
				$num_of_printers--;
			}	
		?>
		</select><br><br>
		<input type="submit" class="btn btn-dark mb-5" value="Farbe aktualisieren" id="button">
		</form>
		<?php
			if(isset($_POST["printer"])){
				echo("<center><div class='alert alert-success' role='alert'>Farbe geändert</div></center>");
			}
		?>
	    </div>
	  </div>
	</div>
	<div id="footer"></div>
</body>

</html>
