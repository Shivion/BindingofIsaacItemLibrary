<?php
	require'header.php';
	
	
	if(isset($_GET['v'] && $_GET['v'] == true)
	{
		$_SESSION['d'] = 'list';
	}
	elseif(isset($_GET['v'] && $_GET['v'] == false)
	{
		$_SESSION['d'] = 'tile';
	}
?>

<!DOCTYPE html>
<html>
<body>
	<div class="container">
        <div id="content">
			
        </div> <!-- END CONTENT -->
    </div> <!-- END container -->
</body>
<script src="search.js"></script>
</html>