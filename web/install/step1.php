<?php
// $Id$

/*
* MRBS Installation - Step 1 (Required)
* Check if the database exists. If not, then create it.
* Drop tables if they exist and then create them fresh.
* Add core data/settings.
*/

require_once '../defaultincludes.inc';
require_once '../mrbs_sql.inc';
?>

<html>
<head>
<title>Install MRBS - Step 1</title>
</head>
<body>
	<h1>Step 1 - Install MRBS Core</h1>

		<p>This part is not automated yet. Please follow the instructions in the documentation to manually create 
		the database and tables. And then edit config.inc.php</p>
		<p><a href="step2.php">Next - Install Permissions System</a></p>
</body>
</html>
