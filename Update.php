<?php
	require_once(__DIR__ . "/header.php");
	
	use class.upload.php;
	
	//get type of object? to update
	$t = filter_input(INPUT_GET, 't', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    //get id of object
	$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
	
	$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$desc = filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$approved = filter_input(INPUT_POST, 'approved', FILTER_SANITIZE_NUMBER_INT);
	
	$oldName = "";
	
	$oldDesc = "";
	
	if($t=="synergy")
	{
		//select given synergy
		$synergySelectQuery = "SELECT * FROM synergies WHERE SynergyId = :id";
		$synergySelectStatement = $db->prepare($synergySelectQuery);
		$synergySelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
		$synergySelectStatement->execute();
		
		$selectRow = $synergySelectStatement->fetch();
		
		$oldName = $selectRow['NickName'];
		
		$oldDesc = $selectRow['Description'];
		
		$oldApproved = $selectRow['Approved'];
		
		$oldImage = $selectRow['Image'];
		
		//select items in given synergy
		$synergyItemsSelectQuery = "SELECT * FROM items WHERE ItemId IN (SELECT ItemId FROM synergyItems WHERE SynergyId = :id) ORDER BY NAME";
		$synergyItemsSelectStatement = $db->prepare($synergyItemsSelectQuery);
		
		//select items NOT in given synergy
		$itemSelectQuery = "SELECT * FROM items WHERE ItemId NOT IN (SELECT ItemId FROM SynergyItems WHERE SynergyId = :id) ORDER BY Name";
		$itemSelectStatement = $db->prepare($itemSelectQuery);
	}
	else if($t=="item")
	{	
		//select item info
		$selectQuery = "SELECT * FROM items WHERE ItemId = :id";
		$selectStatement = $db->prepare($selectQuery);
		$selectStatement->bindValue(':id', $id, PDO::PARAM_INT);
		$selectStatement->execute();
		
		$selectRow = $selectStatement->fetch();
		
		$oldName = $selectRow['Name'];
		
		$oldDesc = $selectRow['Description'];
		
		$oldApproved = $selectRow['Approved'];
		
		$oldIconImage = $selectRow['IconImage'];
		
		$oldInGameImage = $selectRow['InGameImage'];
	}
?>


    <div class="container">
        <div id="content">
			<?php if(isset($_SESSION['username'])): ?>
				<form class="form-horizontal" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="name">Name</label>
						<input class="form-control" type="text" id="name" name="name" value="<?= $oldName?>">
					</div>
					<div class="form-group">
						<label for="desc">Description</label>
						<textarea class="form-control" name="desc" id="desc" rows="5" cols="50"><?= $oldDesc?></textarea><br>
					</div>
					<div class="checkbox">
						<label>
							<input type="hidden" name="approved" value="0">
							<input type="checkbox" name="approved" id="approved" value="1" <?php if($oldApproved == 1){echo 'checked';}?>>
						Approved</label>
					</div>
					<div class="form-group">
					<?php
					if($t=="item")
					{
							echo '<label class="image" for="icon">Icon Image</label><br>';
							if($oldIconImage != null)
							{
								echo '<img class="icon" alt="'.$oldName.' Icon" src="images/'.$oldIconImage.'"/><br>';
								echo '<div class="checkbox"><label><input type="checkbox" id="deleteIcon" name="deleteIcon" value="true">';
								echo 'Delete</label></div>';
							}
							echo '<input class="form-control" type="file" id="icon" name="icon">';
							echo '<label class="image" for="inGame">In Game Image(Optional)</label><br>';
							if($oldInGameImage != null)
							{
								echo '<img class="inGame" alt="'.$oldName.' Effect" src="images/'.$oldInGameImage.'"/><br>';
								echo '<div class="checkbox"><label><input type="checkbox" id="deleteInGame" name="deleteInGame" value="true">';
								echo 'Delete</label></div>';
							}
							echo '<input class="form-control" type="file" id="inGame" name="inGame">';
						
					}
					elseif($t=="synergy")
					{	
						echo '<label class="image" for="inGame">In Game Image(Optional)</label><br>';
						if($oldImage != null)
						{
							echo '<img class="inGame" alt="'.$oldName.' Effect" src="images/'.$oldImage.'"/><br>';
							echo '<div class="checkbox"><label><input type="checkbox" id="deleteImage" name="deleteImage" value="true">';
							echo 'Delete</label></div>';
						}
						echo '<input type="file" class="form-control" id="inGame" name="inGame">';
						
						$synergyItemsSelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
						$synergyItemsSelectStatement->execute();
						
						$itemSelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
						$itemSelectStatement->execute();
						
						echo '<h3>Items</h3>';
						
						while ($synergyItemsSelectRow = $synergyItemsSelectStatement->fetch())
						{
							echo '<div class="checkbox"><label><input type="hidden" name="previousItems['.$synergyItemsSelectRow["ItemId"].']" value="'.$synergyItemsSelectRow["ItemId"].'">';
							echo '<input type="checkbox" name="previousItems['.$synergyItemsSelectRow["ItemId"].']" id="<'.$synergyItemsSelectRow["ItemId"].'" value="null" checked>';
							echo $synergyItemsSelectRow["Name"].'</label></div>';
						}
						while ($itemSelectRow = $itemSelectStatement->fetch())
						{
							echo '<div class="checkbox"><label><input type="checkbox" name="Items['.$itemSelectRow["ItemId"].']" id="'.$itemSelectRow["ItemId"].'" value="'.$itemSelectRow["ItemId"].'">';
							echo $itemSelectRow["Name"].'</label></div>';
						}
					}
					?>
					<br>
					<input class="btn btn-success" type="submit" value="Update">
					</div>
				</form>
				<div class="pull-right" id="delete">
					<form action="deletepost.php" method="post">
						<input type="hidden" name="id" <?= 'value="'.$id.'"' ?>>
						<input type="hidden" name="t" <?= 'value="'.$t.'"' ?>>
						<input class="btn btn-danger" type="submit" value="Delete" id="del">
					</form>
				</div>
			<?php else: ?>
				<h1> Only admins may edit posts, please log in.<h1>
			<?php 
			endif;
				if ($_POST)
				{
					//get images and set them into Upload instances
					$icon = new Upload($_FILES['icon']);
					$inGame = new Upload($_FILES['inGame']);
					
					if(strlen($name) <= 1 or strlen($desc) <= 1 or ($t == "synergy" and (empty($_POST['Items']) and empty($_POST['previousItems']))))
					{
						print"Content is required";
					}
					else if(!isset($_SESSION['username']))
					{
						//Its probably unneeded, but oh well
						print"Only Admins may edit posts, please log in";
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
								$itemUpdateQuery     = "UPDATE items SET name = :name, description = :desc, approved = :approved WHERE ItemId = :id";
								$itemUpdateStatement = $db->prepare($itemUpdateQuery);
								$itemUpdateStatement->bindValue(':name', $name);        
								$itemUpdateStatement->bindValue(':desc', $desc);
								$itemUpdateStatement->bindValue(':id', $id);
								$itemUpdateStatement->bindValue(':approved', $approved);
				 
								// Execute the INSERT.
								$itemUpdateStatement->execute();
								
								//name and process images as they are uploaded
								if($icon->uploaded)
								{
									if(file_exists('images/'.$oldIconImage))
									{
										unlink('images/'.$oldIconImage);
									}
									
									//name and process image
									$icon->file_new_name_body = 'item'.$id.'icon';
									$icon->file_overwrite = true;
									$icon->process('images/');
									
									//add image link to db
									$iconQuery     = "UPDATE items SET iconImage = :icon WHERE ItemId = :id";
									$iconStatement = $db->prepare($iconQuery);
									$iconStatement->bindValue(':icon', $icon->file_dst_name);
									$iconStatement->bindValue(':id', $id);
									$iconStatement->execute();
								}
								//delete old item
								elseif(isset($_POST[deleteIcon]))
								{
									if(file_exists('images/'.$oldIcon))
									{
										unlink('images/'.$oldIcon);
									}
									
									//nullify
									$iconQuery     = "UPDATE items SET iconImage = NULL WHERE ItemId = :id";
									$iconStatement = $db->prepare($iconQuery);
									$iconStatement->bindValue(':id', $id);
									$iconStatement->execute();
								}
								
								if($inGame->uploaded)
								{
									if(file_exists('images/'.$oldInGameImage))
									{
										unlink('images/'.$oldInGameImage);
									}
									
									//name and process image
									$icon->file_new_name_body = 'item'.$id.'inGame';
									$inGame->file_overwrite = true;
									$inGame->process('images/');
									
									//add image link to db
									$inGameQuery     = "UPDATE items SET inGameImage = :inGame WHERE ItemId = :id";
									$inGameStatement = $db->prepare($inGameQuery);
									$inGameStatement->bindValue(':inGame', $inGame->file_dst_name);
									$inGameStatement->bindValue(':id', $id);
									$inGameStatement->execute();
								}
								elseif(isset($_POST[deleteInGame]))
								{
									if(file_exists('images/'.$oldInGameImage))
									{
										unlink('images/'.$oldInGameImage);
									}
									
									//add image link to db
									$inGameQuery     = "UPDATE items SET inGameImage = NULL WHERE ItemId = :id";
									$inGameStatement = $db->prepare($inGameQuery);
									$inGameStatement->bindValue(':id', $id);
									$inGameStatement->execute();
								}
								
								header("Location: item.php?id=".$id);
								
								
								
							}
							else if($t == "synergy")
							{
								// Build the parameterized SQL query and bind to the above sanitized values.
								$insertQuery     = "UPDATE synergies SET NickName = :name, Description = :desc, approved = :approved WHERE synergyId = :id";
								$insertStatement = $db->prepare($insertQuery);
								$insertStatement->bindValue(':name', $name);        
								$insertStatement->bindValue(':desc', $desc);        
								$insertStatement->bindValue(':id', $id); 
								$insertStatement->bindValue(':approved', $approved);
				 
								// Execute the INSERT.
								$insertStatement->execute();
								
								//process ingame image
								if($inGame->uploaded)
								{
									if(file_exists('images/'.$oldImage))
									{
										unlink('images/'.$oldImage);
									}
									
									//name and process image
									$inGame->file_new_name_body = 'synergy'.$id.'inGame';
									$inGame->file_overwrite = true;
									$inGame->process('images/');
									
									//add image link to db
									$imageQuery     = "UPDATE synergies SET Image = :inGame WHERE SynergyId = :id";
									$imageStatement = $db->prepare($imageQuery);
									$imageStatement->bindValue(':inGame', $inGame->file_dst_name);        
									$imageStatement->bindValue(':id', $id);
									$imageStatement->execute();
								}
								elseif(isset($_POST[deleteImage]))
								{
									if(file_exists('images/'.$oldImage))
									{
										unlink('images/'.$oldImage);
									}
									
									//add image link to db
									$inGameQuery     = "UPDATE synergies SET Image = NULL WHERE SynergyId = :id";
									$inGameStatement = $db->prepare($inGameQuery);
									$inGameStatement->bindValue(':id', $id);
									$inGameStatement->execute();
								}
								
								//For each item in array of unchecked previous(ly used)items, Delete its row in the composite table(SynergyItems)
								//this was really cool
								foreach($_POST['previousItems'] as $itemId)
								{
									if($itemId != "null")
									{
										$compositeDeleteQuery     = "DELETE FROM synergyItems WHERE SynergyId = :id AND ItemId = :itemId";
										$compositeDeleteStatement = $db->prepare($compositeDeleteQuery);
										$compositeDeleteStatement->bindValue(':id', $id);        
										$compositeDeleteStatement->bindValue(':itemId', $itemId);
										$compositeDeleteStatement->execute();
							
									}
								}
								
								//Add row in the composite table(SynergyItems) for new items
								if(isset($_POST['Items']))
								{
									foreach($_POST['Items'] as $itemId)
									{
										$compositeInsertQuery     = "INSERT INTO synergyItems (ItemId, SynergyId) values (:ItemId, :SynergyId)";
										$compositeInsertStatement = $db->prepare($compositeInsertQuery);
										$compositeInsertStatement->bindValue(':SynergyId', $id);        
										$compositeInsertStatement->bindValue(':ItemId', $itemId);   
										$compositeInsertStatement->execute();
									}
								}
							
								header("Location: item.php?t=synergy&amp;id=".$id);
							}
							else
							{
								print"Invalid Type";
							}
						}
					}
				}
				?>
        </div> <!-- END CONTENT -->
    </div> <!-- END container -->
</body>
</html>