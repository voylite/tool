<!DOCTYPE html>
<html>
<head>
	<title>csv|upload panel</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
	<div class="goto"><a href="/" target="_blank">Go to CSV PANEL</a></div>
	<div class="goto"><a href="import.php" target="_blank">Go to image zip import PANEL</a></div>
	<div class="outerhead2"><span>Color-Size CSV PANEL</span></div>
	<form id="catalogfileinput" class="form2" action="process.php" method="post" enctype="multipart/form-data">
		<div class="form-field"><label class="form-field-name">UPLOAD CSV FILE: </label><input type="file" name="catalogfile" /></div>
		<div class="form-field"><label class="form-field-name">CSV HEADER LINE NO: </label><input type="number" name="headerline" value="1" /></div>
		<div class="form-field"><label class="form-field-name">CSV ROWS: </label><input type="number" name="csvrows" value="" /></div>
		<div class="form-field"><label class="form-field-name">CSV COLUMNS: </label><input type="number" name="csvcols" value="2" /></div>
		<div class="form-field"><input type="hidden" name="invoke" value="_downloadColorSize" /></div>
		<div class="form-field"><input type="submit" class="button" name="submit" value="Download" /></div>
	</form>
</body>
</html>