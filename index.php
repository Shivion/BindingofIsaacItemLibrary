<?php
	require_once(__DIR__ . "/Header.php");
	
	$sorter = "Name";
	
	if(isset($_GET['s']))
	{
		if($_GET['s'] == "cr")
		{
			$sorter = "DateCreated";
		}
		elseif($_GET['s'] == "up")
		{
			$sorter = "DateUpdated";
		}
		elseif($_GET['s'] == "ap")
		{
			$sorter = "Approved, Name";
		}
	}
	
	if(isset($_SESSION['username']))
	{
		$selectQuery = "SELECT * FROM items ORDER BY $sorter";
	} 
	else
	{
		$selectQuery = "SELECT * FROM items WHERE Approved = 1 ORDER BY $sorter";
	}
	
	
	$selectStatement = $db->prepare($selectQuery);
	$selectStatement->execute();
?>

	<div class="container">
		<div id="content">
			<div class="row">
				<div class="col-md-4" id="search">
					<form class="form-inline">
						<div class="form-group">
							<input class="form-control input-sm" name="search_text" id="search_text" placeholder="Search for Item">
						</div>
						<input class="btn btn-default btn-sm" type="button" name="show_all_button" id="show_all_button" value="Show All Items">
					</form>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<h5 class="inline">Sort By</h5>
					<a class="btn btn-default btn-sm" href="Index.php">Name</a>
					<?php
						if(isset($_SESSION['username']))
						{
							echo '<a class="btn btn-default btn-sm" href="Index.php?s=ap">Approved</a>';
						}
					?>
					<a class="btn btn-default btn-sm" href="Index.php?s=cr">Date Created</a>
					<a class="btn btn-default btn-sm" href="Index.php?s=up">Date Updated</a>
				</div>
				<div class="col-md-6">
					<h5 class="inline">View</h5>
					<a href="#" class="btn btn-default btn-sm" id="tile">Tile</a>
					<a href="#" class="btn btn-default btn-sm" id="list">List</a>
					<br>
				</div>
			</div>
			<div class="page-header">
			<h2 id="ItemsTitle">Items</h2>
			</div>
			<ul>
				<li>This should be hidden</li>
				<?php while ($row = $selectStatement->fetch()): ?>
					<li>
					
					<a href="Item.php?id=<?= $row['ItemId']?>">
					
					<?php
						if($row['IconImage'] != null)
						{
							echo '<img title="'.$row['Name'].'" alt="'.$row['Name'].'" class="icon" src="images/'.$row['IconImage'].'"/>';
						}
					?>
					
					<h3 class="title"><?= $row['Name']?></h3></a>
					
					<?php
						if($row['Approved'] == 0)
						{
							echo'<h6 class="unapproved">Not Approved</h6>';
						}
					?>
					</li>
				<?php endwhile ?>
			</ul>
			<h4><a class="btn btn-default" href="Create.php?t=item">New Item</a></h4>
        </div> <!-- END CONTENT -->
    </div> <!-- END container -->
<script src="search.js"></script>
</body>
</html>