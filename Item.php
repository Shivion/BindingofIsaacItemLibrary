<?php
	require_once(__DIR__ . "/Header.php");
	
	// Sanitize $_GET['id'] to ensure it's a number.
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
	
	//Synergy
	if (isset($_GET['t']))
	{
		
		//Make and Execute select
		$singleSynergySelectQuery = "SELECT * FROM synergies WHERE SynergyId = :id";
		$singleSynergySelectStatement = $db->prepare($singleSynergySelectQuery);
		$singleSynergySelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
		$singleSynergySelectStatement->execute();
		
		//get synergyItems
		$singleSynergyItemsSelectQuery = "SELECT * FROM items WHERE ItemId IN (SELECT ItemId FROM synergyItems WHERE SynergyId = :synergyId)";
		$singleSynergyItemsSelectStatement = $db->prepare($singleSynergyItemsSelectQuery);
	}
	//Item
	else
	{
		//Make and Execute select
		$itemSelectQuery = "SELECT * FROM items WHERE ItemId = :id";
		$itemSelectStatement = $db->prepare($itemSelectQuery);
		$itemSelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
		$itemSelectStatement->execute();
		
		//if user is logged in
		if(isset($_SESSION['username']))
		{
			//get all synergies
			$synergySelectQuery = "SELECT * FROM Synergies WHERE SynergyId IN (SELECT SynergyId FROM SynergyItems WHERE ItemId = :id) ORDER BY Approved, NickName";
		} 
		else
		{
			//get only approved synergies
			$synergySelectQuery = "SELECT * FROM Synergies WHERE SynergyId IN (SELECT SynergyId FROM SynergyItems WHERE ItemId = :id) AND Approved = 1 ORDER BY NickName";
		}
		
		//get synergies
		$synergySelectStatement = $db->prepare($synergySelectQuery);
		$synergySelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
		$synergySelectStatement->execute();
		
		
		//get synergyItems
		$synergyItemsSelectQuery = "SELECT * FROM items WHERE ItemId IN (SELECT ItemId FROM synergyItems WHERE SynergyId = :synergyId AND ItemId != :id)";
		$synergyItemsSelectStatement = $db->prepare($synergyItemsSelectQuery);
	}
?>


    <div class="container">
        <div id="content">
			<!-- IF ITS A SYNERGY-->
			<?php if (isset($_GET['t'])): ?>
			
			
				<?php while ($synergyRow = $singleSynergySelectStatement->fetch()): ?>
					<h2><?= '<a class="title" href="Item.php?id='.$synergyRow['SynergyId'].'&amp;t=synergy">'.$synergyRow['NickName'] .'</a>' ?></h2>
					<div class="page-header">	
						<h3>Effect</h3>
					</div>
					<p><?= $synergyRow['Description']?></p>
						<?php
							if($synergyRow['Image'] != null)
							{
								echo '<img class="inGame" src="images/'.$synergyRow['Image'].'"/>';
							}
						?>
					<div class="page-header">
						<h3>Items</h3>
					</div>
					<ul class="list-unstyled">
					<?php
					//Bind and execute statement
					$singleSynergyItemsSelectStatement->bindValue(':synergyId', $synergyRow['SynergyId'], PDO::PARAM_INT);
					$singleSynergyItemsSelectStatement->execute();
					
					while ($singleSynergyItemsRow = $singleSynergyItemsSelectStatement->fetch())
					{
						echo '<li class="Item"><a class="title" alt="'.$singleSynergyItemsRow['Name'].' Effect" href="Item.php?id='.$singleSynergyItemsRow['ItemId'].'">'.$singleSynergyItemsRow['Name'].'<a></li>';
					}
					?>
					</ul class="list-unstyled">
					<?php if(isset($_SESSION['username'])) 
					{
						//edit only shows for admins
						echo '<h5><a class="btn btn-default btn-sm" href="Update.php?id='.$synergyRow['SynergyId'].'&amp;t=synergy">Edit</a></h5>';
					}
					?>
					<?php if ($synergyRow['Approved'] == 0):?>
						<h5>Not Approved</h5>
					<?php endif ?>
				<?php endwhile ?>
				
			<!--ELSE ITS AN ITEM-->
			<?php else:?>
			
			
				<?php while ($itemRow = $itemSelectStatement->fetch()): ?>
					<div class="page-header">
						<h2>
							<?php
								if($itemRow['IconImage'] != null)
								{
									echo '<img class="icon" alt="'.$itemRow['Name'].' Icon" src="images/'.$itemRow['IconImage'].'"/>';
								}
							?>
							<?= $itemRow['Name'] ?>
						</h2>
					</div>
					
					<?php if ($itemRow['Approved'] == 0)
					{
						echo '<h5>Not Approved</h5>';
					}
					?>
					<h6 class="date">Created: <?= date("F,d,Y, g:i a",strtotime(str_replace('-','/',$itemRow['DateCreated']))) ?></h6>
					<h6 class="date">Updated: <?= date("F,d,Y, g:i a",strtotime(str_replace('-','/',$itemRow['DateUpdated']))) ?></h6>
					<div class="page-header"><h3>Effect</h3></div>
					<p><?=$itemRow['Description']?></p>
					<?php
						if($itemRow['InGameImage'] != null)
						{
							echo '<img class="inGame" alt="'.$itemRow['Name'].' Effect" src="images/'.$itemRow['InGameImage'].'"/>';
						}
					?>
					<?php if(isset($_SESSION['username'])) 
					{
						//edit only shows for admins
						echo '<h5><a class="btn btn-default btn-sm" href="Update.php?id='. $itemRow['ItemId']. '&amp;t=item">Edit</a></h5>';
					}
					?>
					<div class="page-header"><h3>Synergies</h3></div>
					<?php while ($synergyRow = $synergySelectStatement->fetch()): ?>
						<div class="Synergy">
							<div class="page-header"><h4><?= '<a class="title" href="Item.php?id='.$synergyRow['SynergyId'].'&amp;t=synergy">'.$synergyRow['NickName'] .'</a>'?></h4></div>
							<p><?= $synergyRow['Description']?></p>
							
							<h5><b>With</b></h5>
							<ul class="list-unstyled">
							<?php
							//Bind and execute statement
							$synergyItemsSelectStatement->bindValue(':id', $id, PDO::PARAM_INT);
							$synergyItemsSelectStatement->bindValue(':synergyId', $synergyRow['SynergyId'], PDO::PARAM_INT);
							$synergyItemsSelectStatement->execute();
							
							while ($synergyItemsRow = $synergyItemsSelectStatement->fetch())
							{
								echo '<li class="Item"><a class="title" href="Item.php?id='.$synergyItemsRow['ItemId'].'">'.$synergyItemsRow['Name'].'</a></li>';
							}
							?>
							</ul>
							<?php if(isset($_SESSION['username'])) 
							{
								//edit only shows for admins
								echo '<h5><a class="btn btn-default btn-sm" href="Update.php?id='.$synergyRow['SynergyId'].'&amp;t=synergy">Edit</a></h5>';
							}
							?>
							<?php if ($synergyRow['Approved'] == 0):?>
								<h5>Not Approved</h5>
							<?php endif ?>
						</div>
					<?php endwhile ?>
					<h5><a class="btn btn-default" href="Create.php?id=<?= $itemRow['ItemId']?>&amp;t=synergy">Create New Synergy</a></h5>
				<?php endwhile ?>
				
				
			<?php endif; ?>
        </div> <!-- END CONTENT -->
    </div> <!-- END container -->
</body>
</html>