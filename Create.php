<?php
	require'header.php';
	
	use class.upload.php;
	
	$t = filter_input(INPUT_GET, 't', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
	$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$desc = filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	
	//define things
	$itemSelectStatement = 0;
	$itemGivenSelectStatement = 0;
	
	if($t=="synergy")
	{	 
		//gets info for not given items
		$itemSelectQuery = "SELECT * FROM Items WHERE ItemId != :id ORDER BY Name";
		$itemSelectStatement = $db->prepare($itemSelectQuery);
		$itemSelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
		$itemSelectStatement->execute();
		
		//gets Name of given item
		//looking for the right one might have been easier, but Idk
		$itemGivenSelectQuery = "SELECT Name FROM Items WHERE ItemId = :id ORDER BY Name";
		$itemGivenSelectStatement = $db->prepare($itemGivenSelectQuery);
		$itemGivenSelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
		$itemGivenSelectStatement->execute();
	}
?>

    <div class="container">
        <div id="content">
			<form class="form-horizontal" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="name">Name</label><br>
					<input class="form-control" type="text" id="name" name="name">
				</div>
				<div class="form-group">
					<label for="desc">Description</label><br>
					<textarea class="form-control" name="desc" id="desc" rows="5" cols="50"></textarea>
				</div>
					<?php 
					if($t=="item")
					{
						echo '<div class="form-group">';
						echo '<label class="image" for="icon">Icon Image</label>';
						echo '<input class="form-control" type="file" id="icon" name="icon">';
						echo '</div>';
					}
					?>
				<div class="form-group">
					<label class="image" for="inGame">In Game Image(Optional)</label>
					<input class="form-control" type="file" id="inGame" name="inGame">
				</div>
				<?php 
				if($t=="synergy")
				{		
					echo '<h3>Items</h3>';
					while ($itemGivenRow = $itemGivenSelectStatement->fetch())
					{	
						echo '<div class="checkbox">';
						echo '<label>';
						echo '<input type="checkbox" name="Items['.$id.']" id="'.$id.'" value="'.$id.'" checked>';
						echo $itemGivenRow["Name"] . '</label></div>';
					}
					while ($itemRow = $itemSelectStatement->fetch())
					{
						echo '<div class="checkbox">';
						echo '<label>';
						echo '<input type="checkbox" name="Items['.$itemRow["ItemId"].']" id="'.$itemRow["ItemId"].'" value="'.$itemRow["ItemId"].'">';
						echo $itemRow["Name"].'</label>';
					}
				}
				if($_POST)
				{
					//get images and set them into Upload instances
					$icon = new Upload($_FILES['icon']);
					$inGame = new Upload($_FILES['inGame']);
					
					if(strlen($name) <= 1 or strlen($desc) <= 1 or ($t == 'synergy' and empty($_POST['Items'])))
					{
						echo "<p>Content is required</p>";
					}
					else
					{
						//check for upload errors
						if(!$icon->uploaded)
						{
							echo'<p>Icon file Error:'.$icon->file_src_error.'</p>';
						}
						if(!$inGame->uploaded )
						{
							echo'<p>In Game file Error:'.$inGame->file_src_error.'</p>';
						}
						//check file type
						if(($icon->uploaded && !$icon->file_is_image) || ($inGame->uploaded && !$inGame->file_is_image))
						{
							echo'<p>Files must be images(png,lpg,gif,bmp)</p>';
						}
						else
						{
							//resize image
							if($icon->uploaded)
							{
								$icon->image_resize = true;
								$icon->image_x = 50;
								$icon->image_ratio_y = true;
							}
							//not resizeing this if its a gif(it breaks them)
							if($inGame->uploaded && $inGame->image_src_type != 'gif' && $inGame->image_src_x > 500)
							{
								$inGame->image_resize = true;
								$inGame->image_x = 500;
								$inGame->image_ratio_y = true;
							}
							
							
							if($t == "item")
							{
								// Build the parameterized SQL query and bind to the above sanitized values.
								$query     = "INSERT INTO Items (Name, description) values (:name, :desc)";
								$statement = $db->prepare($query);
								$statement->bindValue(':name', $name);        
								$statement->bindValue(':desc', $desc);
				 
								// Execute the INSERT.
								$statement->execute();
							
								$insertedId = $db->lastInsertId();
								
								//name and process images as they are uploaded
								if($icon->uploaded)
								{
									//name and process image
									$icon->file_new_name_body = 'item'.$insertedId.'icon';
									$icon->process('images/');
									
									//add image link to db
									$iconQuery     = "UPDATE items SET iconImage = :icon WHERE ItemId = :id";
									$iconStatement = $db->prepare($iconQuery);
									$iconStatement->bindValue(':icon', $icon->file_dst_name);
									$iconStatement->bindValue(':id', $insertedId);
									$iconStatement->execute();
								}
								
								if($inGame->uploaded)
								{
									//name and process image
									$inGame->file_new_name_body = 'item'.$insertedId.'inGame';
									$inGame->process('images/');
									
									//add image link to db
									$inGameQuery     = "UPDATE items SET inGameImage = :inGame WHERE ItemId = :id";
									$inGameStatement = $db->prepare($inGameQuery);
									$inGameStatement->bindValue(':inGame', $inGame->file_dst_name);
									$inGameStatement->bindValue(':id', $insertedId);
									$inGameStatement->execute();
								}
								
								
								
								header("Location: item.php?id=". $insertedId);
							}
							else if($t == "synergy")
							{
								// Build the parameterized SQL query and bind to the above sanitized values.
								$insertQuery     = "INSERT INTO synergies (NickName, Description) values (:name, :desc)";
								$insertStatement = $db->prepare($insertQuery);
								$insertStatement->bindValue(':name', $name);        
								$insertStatement->bindValue(':desc', $desc);
				 
								// Execute the INSERT.
								$insertStatement->execute();
								
								//Get the Primary key of above insert(Google told me this was okay)
								//Also SUPER useful
								$synergyId = $db->lastInsertId();
								
								//process ingame image
								if($inGame->uploaded)
								{
									//name and process image
									$inGame->file_new_name_body = 'synergy'.$synergyId.'inGame';
									$inGame->process('images/');
									
									//add image link to db
									$imageQuery     = "UPDATE synergies SET Image = :inGame WHERE SynergyId = :id";
									$imageStatement = $db->prepare($imageQuery);
									$imageStatement->bindValue(':inGame', $inGame->file_dst_name);        
									$imageStatement->bindValue(':id', $synergyId);
									$imageStatement->execute();
								}
								
								
								//For each item in array of checked items, Create a row in the composite table(SynergyItems)
								//This was tricky, I feel smart
								foreach($_POST['Items'] as $itemId){
									$compositeInsertQuery     = "INSERT INTO synergyItems (ItemId, SynergyId) values (:ItemId, :SynergyId)";
									$compositeInsertStatement = $db->prepare($compositeInsertQuery);
									$compositeInsertStatement->bindValue(':SynergyId', $synergyId);        
									$compositeInsertStatement->bindValue(':ItemId', $itemId);
									
									$compositeInsertStatement->execute();
								}
								
								header("Location: item.php?t=synergy&id=".$synergyId);
							}
							exit();
						}
					}
				}
				?>
				
				<div class="pull-right">
					<input class="btn btn-success" type="submit" value="Create <?= $t?>">
				</div>
			</form>
        </div> <!-- END CONTENT -->
    </div> <!-- END container -->
</body>
</html>