<html>
<head>
	<title>csv|modify panel</title>
	<script type="text/javascript" src="js/min/jquery-3.2.0.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#checkAll").click(function () {
			    jQuery("input[name*='checklist[]']").not(this).prop('checked', this.checked);
			 });
			jQuery("#addmerge").click(function(){
				var counter = jQuery("#addcounter").val();
				jQuery("input[name*='checklist[]']").each(function(){
					if(jQuery(this).is(":checked")){
						if(counter == jQuery("#addcounter").val()){
							jQuery("#addcounter").val(parseInt(counter,10)+1);
						}
						jQuery('<input type="checkbox" name="mergelist'+counter+'[]" value="'+jQuery(this).val()+'">').insertAfter(this);
					}
				});
			jQuery('<div class="form-field">Enter Separator for headers : <input type="text" name="headerseperator[]" value=","></div><div class="form-field">Enter Separator for body : <input type="text" name="bodyseperator[]" value="|"></div>').insertBefore("#submitwrap");
			});
		});
	</script>
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
	<div class="form">
		<form action="process.php" method="post">
			<input type="hidden" name="invoke" value="_downloadCsv" />
			<input type="hidden" id="addcounter" value="1">
			<div><span class="checkall"><input type="checkbox" id="checkAll"/>Check All</span><span><button type="button" class="button" id="addmerge">Add Merge</button></span></div>
			<?php foreach($header as $value){ ?>
			<div class="form-field"><input type="checkbox" name="checklist[]" value="<?php echo $value ?>"><?php echo $value ?>
			<input type="text" name="changelist[<?php echo $value ?>]" value="" placeholder="New Heading"></div>
			<?php } ?>
			<div id="submitwrap" class="center"><input type="submit" class="button" name="submit" value="Download"></div>
		</form>
	</div>
</body>
</html>