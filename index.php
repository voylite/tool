<!DOCTYPE html>
<html>
<head>
	<title>csv|upload panel</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
	<div class="outerhead"><span>CSV PANEL</span></div>
	<form id="catalogfileinput" class="form" action="process.php" method="post" enctype="multipart/form-data">
		<div class="form-field"><label class="form-field-name">UPLOAD CSV FILE: </label><input type="file" name="catalogfile" /></div>
		<div class="form-field"><label class="form-field-name">CSV HEADER LINE NO: </label><input type="number" name="headerline" value="1" /></div>
		<div class="form-field"><label class="form-field-name">CSV ROWS: </label><input type="number" name="csvrows" value="" /></div>
		<div class="form-field"><label class="form-field-name">CSV COLUMNS: </label><input type="number" name="csvcols" value="" /></div>
		<div class="form-field"><input type="hidden" name="invoke" value="_catalogFileUpload" /></div>
		<div class="form-field"><input type="submit" class="button" name="submit" value="Next >>" /></div>
	</form>
</body>
</html>