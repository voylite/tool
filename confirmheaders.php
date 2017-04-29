<html>
<head>
	<title>csv|modify panel</title>
	<script type="text/javascript" src="js/min/jquery-3.2.0.min.js"></script>
	<script type="text/javascript" src="js/confirmheaders.js"></script>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
	<?php 
		if($warning){ 
			foreach($message as $msg){ ?>
				<div class="warning"><?php echo $msg ?></div>
			<?php }
		}
	?>
	<div>
		<div class="goto"><a href="color-size.php" target="_blank">Go to Color-Size CSV PANEL</a></div>
		<div class="goto"><a href="import.php" target="_blank">Go to image zip import PANEL</a></div>
		<form>
			<input type="hidden" name="invoke" value="_catalogFileUpload" />
			<input type="hidden" name="submit_upload" value="uploadsubmit" />
			<input type="file" name="catalogfile" />
			CSV HEADER LINE NO : <input type="number" name="headerline" value="1" />
			CSV ROWS : <input type="number" name="csvrows" value="1" />
			CSV COLUMNS : <input type="number" name="csvcols" value="1" />
			<input type="button" value="upload" 
			        onClick="fileUpload(this.form,'process.php','upload'); return false;" >
			<div id="upload"></div>
		</form>
	</div>
	<div class="form top-margin">
		<form action="process.php" method="post">
			<input type="hidden" name="invoke" value="_downloadCsv" />
			<input type="hidden" id="addcounter" value="1">
			<input type="hidden" id="newproduct" name="newproduct" value="0">
			<input type="hidden" id="isimagecsv" name="isimagecsv" value="0">
			<div><span class="checkall"><input type="checkbox" id="checkAll"/>Check All</span><span><button type="button" class="button" id="addmerge">Add Merge</button></span><span class="checkall"><input type="submit" class="button" name="submit" value="Download"></span><span><button type="button" class="button" id="newpro">New Product Default</button></span><span class="checkall"><button type="button" class="button" id="genupdate">General Update</button></span><span><button type="button" class="button" id="imagecsv">Image CSV</button></span></div>
			<?php foreach($header as $value){ ?>
			<div class="form-field"><input type="checkbox" name="checklist[]" value="<?php echo $value ?>"><?php echo $value ?>
			<?php if($value != 'alt_text'){ ?>
			<input type="text" step="<?php echo $value ?>" name="changelist[<?php echo $value ?>]" value="" placeholder="New Heading">
			<?php } ?>
			</div>
			<?php } ?>
			<div id="submitwrap" class="center"><input type="submit" class="button" name="submit" value="Download"></div>
		</form>
	</div>
</body>
</html>