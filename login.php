<?php
	require'header.php';
	require'phpass/PasswordHash.php';
	
	//get username
	$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	
	//get password
	$pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	
	// Base-2 logarithm of the iteration count used for password stretching
	$hash_cost_log2 = 8;
	
	// Do we require the hashes to be portable to older systems (less secure)?
	$hash_portable = FALSE;
	
?>

<!DOCTYPE html>
<html>
<body>
    <div class="container">
        <div id="content">
			<?php	
				if(isset($_GET["logout"]))
				{
					unset($_SESSION['username']);
					echo '<p>Goodbye!</p>';
					header("Location: index.php");
				}
				else
				{
					//select hashed password for given username
					$selectQuery = "SELECT pass FROM admins WHERE username = :username";
					$selectStatement = $db->prepare($selectQuery);
					$selectStatement->bindValue(':username', $username, PDO::PARAM_INT);
					$selectStatement->execute();
						
					$select = $selectStatement->fetch();
					
					//if a user/pass is found
					if(!empty($select))
					{
	
						//this is an instance of phpass.
						$hasher = new PasswordHash($hash_cost_log2, $hash_portable);
						
						//if passwords match(phpass does the heavy lifting)
						if ($hasher->CheckPassword($pass, $select['pass'])) 
						{
							//store username
							$_SESSION['username'] = $username;
							//go back to index
							header("Location: index.php");
						} 
						else 
						{
							echo '<p>Incorrect password</p>';
						}
					}
					else
					{
						echo '<p>User not found.</p>';
					}
					unset($hasher);
				}
			?>
        </div> <!-- END CONTENT -->
    </div> <!-- END container -->
</body>
</html>