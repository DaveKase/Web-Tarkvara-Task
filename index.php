<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="site.css"/>
		
		<title>
			Taavi's test task
		</title>
	</head>
	
	<body>
		<?php
			$servername = "SERVER_NAME";
			$username = "USERNAME";
			$password = "PASSWORD";
			$dbname = "DATABASE_NAME";
			$post_id = 0;
			$keyword_id = 0;
			
			$nameErr = $emailErr = $keywordErr = $titleErr = $contentErr = "";
			$name = $email = $keywords = $title = $content = "";
			$age = 0;
			$valid_name = $valid_email = $valid_age = $valid_keywords = $valid_title = $valid_content = false;

			$conn = mysqli_connect($servername, $username, $password, $dbname);
			
			if (!$conn) {
				die("Connection failed: " . mysqli_connect_error());
			}
			
			$sql = "SELECT * FROM posts";
			$result = $conn->query($sql);
			
			if (mysqli_num_rows($result) > 0) {
				echo '<div id="previous_posts" class="posts_form">';
				while($post_row = mysqli_fetch_assoc($result)) {
					$post_id = $post_row["id"];
					$post_name = $post_row["name"];
					$post_email = $post_row["email"];
					$post_age = $post_row["age"];
					$post_title = $post_row["title"];
					$post_contents = $post_row["contents"];
					$post_keywords = "";
					$counter = 0;
					
					$keyword_sql = "SELECT * FROM keywords WHERE post_id = " . $post_id;
					$keyword_result = $conn->query($keyword_sql);
					$numResults = mysqli_num_rows($keyword_result);
					
					if ($numResults > 0) {
						while($keyword_row = mysqli_fetch_assoc($keyword_result)) {
							if (++$counter == $numResults) {
								$post_keywords = $post_keywords . $keyword_row["keyword"];
								$keyword_id = $keyword_row["id"];
							} else {
								$post_keywords = $post_keywords . $keyword_row["keyword"] . ", ";
							}
						}
					}
					
					$poster_info = $post_name . ', ' . $post_email;
					
					if ($post_age > 0) {
						$poster_info = $poster_info . ', ' . $post_age;
					}
					
					echo '<div class="posts_row">';
					echo '<div class="poster_info">' . $poster_info  . '</div>';
					echo '<div class="post_keywords">' . $post_keywords . '</div>';
					echo '<div class="post_title">' . $post_title . '</div>';
					echo '<div class="post_content">' . $post_contents . '</div>';
					echo '</div>';
				}
				
				echo '</div>';
			}
			
			mysqli_close($conn);
			
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if (empty($_POST["name"])) {
					$nameErr = "Sisesta nimi";
				} else {
					$name = test_input($_POST["name"]);
					
					if (!preg_match("/^[a-zA-Z öÖäÄõÕüÜ]*$/",$name)) {
						$nameErr = "Ainult tähed ja tühikud on lubatud"; 
					} else {
						$valid_name = true;
					}
				}

				if (empty($_POST["email"])) {
					$emailErr = "Sisesta e-mail";
				} else {
					$email = test_input($_POST["email"]);
					
					if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$emailErr = "Sisestatud e-mail on vale"; 
					} else {
						$valid_email = true;
					}
				}

				if (empty($_POST["age"])) {
					$age = 0;
					$valid_age = true;
				} else {
					$age = test_input($_POST["age"]);
					$valid_age = true;
				}
				
				if (empty($_POST["keywords"])) {
					$keywords = "Märksõnad puuduvad";
					$valid_keywords = true;
				} else {
					$keywords = test_input($_POST["keywords"]);
					
					if (!preg_match("/^[a-zA-Z ,öÖäÄõÕüÜ]*$/",$keywords)) {
						$keywordErr = "Ainult tähed, tühikud ja komad on lubatud"; 
					} else {
						$valid_keywords = true;
					}
				}

				if (empty($_POST["title"])) {
					$titleErr = "Sisesta pealkiri";
				} else {
					$title = test_input($_POST["title"]);
					
					if (!preg_match("/^[a-zA-Z öÖäÄõÕüÜ]*$/",$title)) {
						$titleErr = "Ainult tähed ja tühikud on lubatud"; 
					} else {
						$valid_title = true;
					}
				}

				if (empty($_POST["content"])) {
					$contentErr = "Sisesta postituse sisu";
				} else {
					$content = test_input($_POST["content"]);
					
					if (!preg_match("/^[a-zA-Z .,!?öÖäÄõÕüÜ]*$/",$content)) {
						$contentErr = "Ainult tähed, tühikud, komad ja punktid on lubatud"; 
					} else {
						$valid_content = true;
					}
				}
				if ($valid_name && $valid_email && $valid_age && $valid_keywords && $valid_title && $valid_content) {
					$conn = mysqli_connect($servername, $username, $password, $dbname);
		
					if (!$conn) {
						die("Connection failed: " . mysqli_connect_error());
					}
					
					$id = $post_id + 1;
				
					$post_sql = "INSERT INTO posts (id, name, email, age, title, contents) VALUES ("
						. $id . ", '" . $name . "', '" . $email . "', " . $age . ", '" . $title . "', '" . $content . "');";
					
					mysqli_query($conn, $post_sql);
					mysqli_close($conn);
					
					if (strpos($keywords, ',') !== FALSE)
					{
						$splitted_keywords = explode(",", $keywords);
						
						foreach ($splitted_keywords as $keyword) {
							$key_id = $keyword_id + 1;
							$keyword_sql = "INSERT INTO keywords (id, post_id, keyword) VALUES (" . $key_id . ", " . $id . ", '" . $keyword . "');";
							
							$conn = mysqli_connect($servername, $username, $password, $dbname);
							
							if (!$conn) {
								die ("Connection failed: " . mysqli_connect_error());
							}
							mysqli_query($conn, $keyword_sql);
							
							$keyword_id = $key_id;
							mysqli_close($conn);
						}
					} else {
						$key_id = $keyword_id + 1;
						$keyword_sql = "INSERT INTO keywords (id, post_id, keyword) VALUES (" . $key_id . ", " . $id . ", '" . $keywords . "');";
						
						$conn = mysqli_connect($servername, $username, $password, $dbname);
							
							if (!$conn) {
								die("Connection failed: " . mysqli_connect_error());
							}
							
							mysqli_query($conn, $keyword_sql);
							
							$keyword_id = $key_id;
							mysqli_close($conn);
					}
					
					$nameErr = $emailErr = $keywordErr = $titleErr = $contentErr = "";
					$name = $email = $keywords = $title = $content = "";
					
					echo '<script>var myNode = document.getElementById("previous_posts"); . 
						myNode.innerHTML = "";</script>';
					
					$conn = mysqli_connect($servername, $username, $password, $dbname);
					
					if (!$conn) {
						die ("Connection failed: " . mysqli_connect_error());
					}
					
					$sql = "SELECT * FROM posts";
					$result = $conn->query($sql);
					
					if (mysqli_num_rows($result) > 0) {
						echo '<div id="previous_posts" class="posts_form">';
						while($post_row = mysqli_fetch_assoc($result)) {
							$post_id = $post_row["id"];
							$post_name = $post_row["name"];
							$post_email = $post_row["email"];
							$post_age = $post_row["age"];
							$post_title = $post_row["title"];
							$post_contents = $post_row["contents"];
							$post_keywords = "";
							$counter = 0;
							
							$keyword_sql = "SELECT * FROM keywords WHERE post_id = " . $post_id;
							$keyword_result = $conn->query($keyword_sql);
							$numResults = mysqli_num_rows($keyword_result);
							
							if ($numResults > 0) {
								while($keyword_row = mysqli_fetch_assoc($keyword_result)) {
									if (++$counter == $numResults) {
										$post_keywords = $post_keywords . $keyword_row["keyword"];
										$keyword_id = $keyword_row["id"];
									} else {
										$post_keywords = $post_keywords . $keyword_row["keyword"] . ", ";
									}
								}
							}
							
							$poster_info = $post_name . ', ' . $post_email;
				
							if ($post_age > 0) {
								$poster_info = $poster_info . ', ' . $post_age;
							}
							
							echo '<div class="posts_row">';
							echo '<div class="poster_info">' . $poster_info . '</div>';
							echo '<div class="post_keywords">' . $post_keywords . '</div>';
							echo '<div class="post_title">' . $post_title . '</div>';
							echo '<div class="post_content">' . $post_contents . '</div>';
							echo '</div>';
						}
						
						echo '</div>';
					}
				}
				
				$valid_name = $valid_email = $valid_age = $valid_keywords = $valid_title = $valid_content = false;
				$name = $email = $keywords = $title = $content = "";
				$age = 0;
			}

			function test_input($data) {
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
			}
		?>
		
		<div class="insert_post_form">
			<form method="post" novalidate>
				<span class="error">Tärniga tähistatud väljad on kohustuslikud!</span><br/>
				Nimi:<span class="error"> * <?php echo $nameErr;?></span><br/>
				<input type="text" name="name"><br/>
				E-mail:<span class="error"> * <?php echo $emailErr;?></span><br/>
				<input type="email" name="email"><br/>
				Vanus:<br/>
				<input type="number" name="age" min=0 value="0"><br/>
				Märksõnad:<span class="error"> <?php echo $keywordErr;?></span><br/>
				<input type="text" name="keywords"><br/>
				Pealkiri:<span class="error"> * <?php echo $titleErr;?></span><br/>
				<input type="text" name="title"><br/>
				Sisu:<span class="error"> * <?php echo $contentErr;?></span><br/>
				<textarea name="content" rows="10" cols="30"></textarea><br/><br/>
				
				<input type="submit" value="Postita">
			</form>
		</div>
	</body>
</html>
