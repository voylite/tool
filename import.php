<!DOCTYPE html>
	<head>
		<title>Image|Uploader</title>
		<script type="text/javascript" src="js/min/jquery-3.2.0.min.js"></script>
		<script type="text/javascript" src="js/confirmheaders.js"></script>
		<link rel="stylesheet" type="text/css" href="css/style.css">
	</head>
	<body>
	<div class="goto"><a href="color-size.php" target="_blank">Go to Color-Size CSV PANEL</a></div>
	<div class="goto"><a href="/" target="_blank">Go to CSV PANEL</a></div>
	<div class="outerhead2"><span>image zip import PANEL</span></div>
	<div class="zipOuter">
		<div>
			<form>
				<input type="hidden" name="invoke" value="_zipUpload" />
				<input type="hidden" name="submit_upload" value="uploadsubmit" />
				<input type="file" name="zipfile" />
				<input type="button" class="button" value="upload" 
				        onClick="fileUpload(this.form,'process.php','upload'); return false;" >
				<div id="upload"></div>
			</form>
		</div>
		<div class="zipInner">
			<span class="zipInnerbuttonSpan"> Clear import if you uploaded wrong zip or after your import has been done!!! </span>
			<form action="process.php" method="post">
				<input type="hidden" name="invoke" value="_clearImport" />
				<input type="submit" class="button zipInnerbutton" name="submit" value="ClearImport">
			</form>
		</div>
	</div>
	</body>
</html>