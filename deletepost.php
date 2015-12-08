<?php
	//This Script deletes posts based on given id
	require_once(__DIR__ . "/connect.php");
	require_once(__DIR__ . "/Header.php");
	
	// Sanitize inputs to ensure it's a number.
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $t = filter_input(INPUT_POST, 't', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	
	
	
?>

<!DOCTYPE html>
<html>
	<body>
		<div class="container">
			<div id="content">
				<p>
				<?php
				if(!isset($_SESSION['username']))
				{
					print"Only Admins may delete posts, please log in";
				}
				else
				{
					//Delete given object
					if($t == "synergy")
					{
						$query = "DELETE FROM synergies WHERE synergyId = :id";
						
						$imageQuery = "SELECT Image FROM synergies WHERE synergyId = :id";
						$imageStatement = $db->prepare($imageQuery);
						$imageStatement->bindValue(':id', $id, PDO::PARAM_INT);
						$imageStatement->execute();
						
						$row = $statement->fetch();
		
						if(file_exists('images/'.$row['Image']))
						{
							unlink('images/'.$row['Image']);
						}
					}
					else if($t == "item")
					{
						$query = "DELETE FROM items WHERE itemId = :id";
						
						$imageQuery = "SELECT IconImage,InGameImage FROM items WHERE itemId = :id";
						$imageStatement = $db->prepare($imageQuery);
						$imageStatement->bindValue(':id', $id, PDO::PARAM_INT);
						$imageStatement->execute();
						
						$row = $imageStatement->fetch();
		
						if(file_exists('images/'.$row['IconImage']))
						{
							unlink('images/'.$row['IconImage']);
						}
						
						if(file_exists('images/'.$row['InGameImage']))
						{
							unlink('images/'.$row['InGameImage']);
						}
					}
					$statement = $db->prepare($query);
					$statement->bindValue(':id', $id, PDO::PARAM_INT);
					$statement->execute();
					
					//redirect to index
					header("Location:index.php");
					
					exit();
				}
				?>
				</p>
			</div> <!-- END CONTENT -->
		</div>
	</body>
</html>