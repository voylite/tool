<?php

require_once('config.php');

class process extends config{
	public function __construct($post,$files){
		parent::__construct();
		$this->post = $post;
		$this->files = $files;
		if(isset($this->post['invoke'])){
			$function = (string) $this->post['invoke'];			
			$this->$function();
		}else{
			echo "No Input!!!!!!!!!";
		}
	}

	protected function _clearImport(){
		shell_exec("rm -rf ".$this->_magentoRoot."var/import/image");
		echo "Done!!!!";
	}

	protected function _zipUpload(){
		$permitted = 1;
		if(isset($this->post["submit"]) || (isset($this->post["submit_upload"]) && $this->post["submit_upload"] == 'uploadsubmit')){
			if($this->files["zipfile"]["size"] > 5000000000){
				$permitted = 0;
				echo "File could not be uploaded!!!! File size is too large!!";
				return;
			}
			if($this->files["zipfile"]["type"] == 'application/zip'|| $this->files["zipfile"]["type"] == 'application/x-zip-compressed'){}else{
					$permitted = 0;
					echo "File could not be uploaded!!!! File type should be application/zip or application/x-zip-compressed!!... ",empty($this->files["zipfile"]["type"]) ? "none" : $this->files["zipfile"]["type"]," given!!!";
					return;
			}
		}else{
			$permitted = 0;
		}
		if($permitted){
			shell_exec("rm -rf ".$this->_magentoRoot."var/import/image");
			if(!file_exists($this->_magentoRoot.'var/import/image')){
				mkdir($this->_magentoRoot.'var/import/image',0777,true);
			}
			if(move_uploaded_file($this->files["zipfile"]["tmp_name"], $this->_magentoRoot.'var/import/image/images.zip')){
				$this->_imageExtract();
			}else{
				echo "File could not be uploaded to server!!!!";
			}
		}else{
			echo "File could not be uploaded!!!!";
		}
	}

	private function _imageExtract(){
		$shell = shell_exec("./imageExtract.sh '".$this->_magentoRoot."'");
		echo "Done!!!";
	}

	protected function _downloadColorSize(){
		$permitted = 1;
		if(isset($this->post["submit"]) || (isset($this->post["submit_upload"]) && $this->post["submit_upload"] == 'uploadsubmit')){
			if($this->post["headerline"] < 1){
				$permitted = 0;
				echo "File could not be uploaded!!!! Header line is improper!!";
				return;
			}
			if($this->files["catalogfile"]["size"] > 2048000){
				$permitted = 0;
				echo "File could not be uploaded!!!! File size is too large!!";
				return;
			}
			if($this->files["catalogfile"]["type"] == 'text/csv' || $this->files["catalogfile"]["type"] == 'application/vnd.ms-excel'){}else{
					$permitted = 0;
					echo "File could not be uploaded!!!! File type should be text/csv or application/vnd.ms-excel!!... ",empty($this->files["catalogfile"]["type"]) ? "none" : $this->files["catalogfile"]["type"]," given!!!";
					return;
			}
		}else{
			$permitted = 0;
		}
		if($permitted){
			if(!file_exists('csv')){
				mkdir('csv',0777,true);
			}
			if(move_uploaded_file($this->files["catalogfile"]["tmp_name"], "csv/colorsize.csv")){
				$this->_colorSize();
			}else{
				echo "File could not be uploaded to server!!!!";
			}
		}else{
			echo "File could not be uploaded!!!!";
		}
	}

	private function _colorSize(){
		$data = $this->_readFromCsv("csv/colorsize.csv");

		if($this->post["csvcols"] != count($data[0])){
			exit('!Warning : "CSV columns seems improper."');
		}
		
		if($this->post["csvrows"] != count($data)){
			exit('!Warning : "CSV rows seems improper."');
		}
		$data = array_slice($data,$this->post["headerline"]-1);
		$rawHeader = array_shift($data);
		$header = array_unique($rawHeader);
		
		if(count($header) != count($rawHeader)){
			exit('!Warning : "Header names are repeated or inconsistent.Please recheck the uploaded CSV."');
		}
		foreach($data as $key => $value){
			if(empty($value[1])){
				unset($data[$key]);
			}
		}
		$data = array_column($data, 1,0);
		$data = array_chunk($data, 50,true);
		if($conn = $this->_connectToMagento()){
			$this->data = array(array('sku','color_product'));
			foreach($data as $key2 => $value2){
				$skus = '';
				foreach($value2 as $key3 => $value3){
					$skus .= ','.$value3;
				}
				$skus = str_replace(",", "','", ltrim($skus,','));
				if($tmpdata = $this->_fetchChunkedmaster($conn,$skus)){
					$master = array();
					foreach($tmpdata as $value4){
						$master[$value4['sku']] = $value4;
					}
					foreach($value2 as $sku => $value5){
						$colorpattern = '';
						$relativeSkus = array_flip(explode(',', $value5));
						if(array_intersect_key($master,$relativeSkus)){
							foreach($relativeSkus as $skuRelative => $value6){
								$colorpattern .= $master[$skuRelative]['color'].','.$master[$skuRelative]['size'].','.$master[$skuRelative]['id'].'|';
							}
							$this->data[] = array($sku,rtrim($colorpattern,'|'));
						}
					}
				}else{
					unset($data[$key2]);
					continue;
				}
			}
			mysqli_close($conn);
			$this->_streamCSV();
		}else{
			echo " Mysql Connection Unavailable!!!";
		}
	}

	private function _fetchChunkedmaster($conn,$skus){
		$Query = "select distinct cpe.entity_id as id,cpe.sku,eaov.value as color,eaov2.value as size from catalog_product_entity as cpe left join catalog_product_entity_int as cpei on cpe.entity_id = cpei.entity_id inner join catalog_product_entity_int as cpei2 on cpei.entity_id = cpei2.entity_id inner join eav_attribute_option as eao on cpei.attribute_id = eao.attribute_id inner join eav_attribute_option_value as eaov on eao.option_id = eaov.option_id inner join eav_attribute_option as eao2 on cpei2.attribute_id = eao2.attribute_id inner join eav_attribute_option_value as eaov2 on eao2.option_id = eaov2.option_id where cpei.attribute_id = (select attribute_id from eav_attribute where attribute_code = 'color') and cpei2.attribute_id = (select attribute_id from eav_attribute where attribute_code = 'size') and eaov.option_id = cpei.value and eaov2.option_id = cpei2.value and eaov.store_id = 1 and eaov2.store_id = 1 and cpe.sku IN ('".$skus."')";
		$result = $conn->query($Query);
		while($row = $result->fetch_assoc()){
			$data[] = $row;
        }
		return !empty($data)?$data:false;
	}

	private function _connectToMagento(){
		$cred = include_once($this->_magentoEnv);
		if(array_key_exists('db',$cred) && array_key_exists('connection',$cred['db']) && array_key_exists('default',$cred['db']['connection']) && array_key_exists('host', $cred['db']['connection']['default']) && array_key_exists('dbname', $cred['db']['connection']['default']) && array_key_exists('username', $cred['db']['connection']['default']) && array_key_exists('password', $cred['db']['connection']['default'])){
			$conn = mysqli_connect($cred['db']['connection']['default']['host'],$cred['db']['connection']['default']['username'],$cred['db']['connection']['default']['password'],$cred['db']['connection']['default']['dbname']);
			if (mysqli_connect_errno())
			{
			  echo "Failed to connect to MySQL: " . mysqli_connect_error();
			  return false;
			}else{
				return $conn;
			}
		}else{
			return false;
		}
	}

	protected function _catalogFileUpload(){
		$permitted = 1;
		if(isset($this->post["submit"]) || (isset($this->post["submit_upload"]) && $this->post["submit_upload"] == 'uploadsubmit')){
			if($this->post["headerline"] < 1){
				$permitted = 0;
				echo "File could not be uploaded!!!! Header line is improper!!";
				return;
			}
			if($this->files["catalogfile"]["size"] > 2048000){
				$permitted = 0;
				echo "File could not be uploaded!!!! File size is too large!!";
				return;
			}
			if($this->files["catalogfile"]["type"] == 'text/csv' || $this->files["catalogfile"]["type"] == 'application/vnd.ms-excel'){}else{
					$permitted = 0;
					echo "File could not be uploaded!!!! File type should be text/csv or application/vnd.ms-excel!!... ",empty($this->files["catalogfile"]["type"]) ? "none" : $this->files["catalogfile"]["type"]," given!!!";
					return;
			}
		}else{
			$permitted = 0;
		}
		if($permitted){
			if(!file_exists('csv')){
				mkdir('csv',0777,true);
			}
			if(move_uploaded_file($this->files["catalogfile"]["tmp_name"], "csv/catalogfile.csv")){
				$this->_showHeaders();
			}else{
				echo "File could not be uploaded to server!!!!";
			}
		}else{
			echo "File could not be uploaded!!!!";
		}
	}

	protected function _showHeaders(){
		$warning = false;
		$message = array();

		$data = $this->_readFromCsv("csv/catalogfile.csv");
		
		if($this->post["csvcols"] != count($data[0])){
			$warning = true;
			$message[] = '!Warning : "CSV columns seems improper."';
		}
		
		if($this->post["csvrows"] != count($data)){
			$warning = true;
			$message[] = '!Warning : "CSV rows seems improper."';
		}
		
		$data = array_slice($data,$this->post["headerline"]-1);
		$this->_writeToCsv($data,'csv/catalogfile.csv');
		$rawHeader = array_shift($data);
		$header = array_unique($rawHeader);
		
		if(count($header) != count($rawHeader)){
			$warning = true;
			$message[] = '!Warning : "Header names are repeated or inconsistent.Please recheck the uploaded CSV."';
		}
		
		$this->_displayHeader($header,$warning,$message);
	}

	protected function _displayHeader($header,$warning,$message){
		include('confirmheaders.php');
	}

	private function _readFromCsv($file){
		$data = array();
		$fp = fopen($file, 'rb');
		while(!feof($fp)) {
		    $data[] = fgetcsv($fp);
		}
		fclose($fp);
		return array_filter($data);
	}

	private function _writeToCsv($data,$file){
		$fp = fopen($file, 'w');
		foreach ($data as $fields) {
			fputcsv($fp, $fields);
		}
		fclose($fp);
	}

	protected function _downloadCsv(){
		if(isset($this->post['submit']) && isset($this->post['checklist'])&& null !== $this->post['checklist']){
			$this->data = $this->_readFromCsv("csv/catalogfile.csv");
			if($this->post['newproduct'] == 1){
				$this->_deleteDescription();
			}else{
				$this->_createDescription();				
			}
			$this->_removeUnwantedKeys();
			$mergeHeader = $this->_getMergeHeader();
			$this->_merge($mergeHeader);
			$this->_unsMergeData($mergeHeader);
			$this->_replaceHeader();
			$this->_altText();
			$this->_rearrangeColumns();
			$this->_streamCSV();
		}else{
			echo "No data provided!!!";
		}
	}

	private function _deleteDescription(){
		if(in_array("Description", $this->post['checklist'])){
			foreach($this->data as $key => $value){
				if($key == 0){
					$headerIndexes = array_flip($value);
				}else{
					$this->data[$key][$headerIndexes['Description']] = '';
				}
			}
		}
	}

	private function _altText(){
		if(in_array("alt_text", $this->post['checklist'])){
			$index = '';
			foreach($this->data[0] as $headerKey => $headerData){
				if($headerData == "alt_text"){
					$index = $headerKey;
					unset($this->data[0][$headerKey]);
					$this->data[0][] = "base_image_label";
					$this->data[0][] = "small_image_label";
					$this->data[0][] = "thumbnail_image_label";
					break;
				}
			}
			foreach($this->data as $headerKey1 => $headerData1){
				if($headerKey1 == 0){
					continue;
				}else{
					$this->data[$headerKey1][] = $headerData1[$index];
					$this->data[$headerKey1][] = $headerData1[$index];
					$this->data[$headerKey1][] = $headerData1[$index];
					unset($this->data[$headerKey1][$index]);
				}
			}
		}
	}

	private function _rearrangeColumns(){
		$preDefined = array();
		$preDefinedCols = array("category","name","description","sku","price","tax_class_id","is_in_stock","image","stock","weight");
		$headers = array_flip($this->data[0]);
		foreach($preDefinedCols as $k1 => $preDefinedCol){
			if(in_array( $preDefinedCol , $this->data[0])){
				$preDefined[] = $headers[$preDefinedCol];
			}
		}
		foreach($this->data as $k => $v){
			$start = array();
			foreach($preDefined as $preKey => $preVal){
				$start[] = $v[$preVal];
				unset($this->data[$k][$preVal]);
			}
			$this->data[$k] = array_values($this->data[$k]);
			$this->data[$k] = array_merge($start, $this->data[$k]);
			if($this->post['newproduct'] != 0 && $k != 0){
				$this->data[$k][0] = '';
			}
		}
	}

	private function _createDescription(){
		if(in_array("Description", $this->post['checklist']) || in_array("Product Knowledge & Care Instruction", $this->post['checklist'])){
			foreach($this->data as $key => $value){
				if($key == 0){
					$headerIndexes = array_flip($value);
				}else{
					if(in_array("Description", $this->post['checklist'])){
						$template = file_get_contents('html/description_template.html');
						$description = $details = $color = $dimensions = $lightSpecifications = $suitableFor = $packageContents = "";
						
						if(array_key_exists('Description', $headerIndexes)){
							$description = (isset($value[$headerIndexes['Description']]) && $value[$headerIndexes['Description']] != 'NA' && !empty($value[$headerIndexes['Description']]))?' <br/> '.$value[$headerIndexes['Description']]:"";
							$template = str_replace('{{{description}}}', $description, $template);
						}
						
						if(array_key_exists('Fixture Material', $headerIndexes)){
							$details .= (isset($value[$headerIndexes['Fixture Material']]) && $value[$headerIndexes['Fixture Material']] != 'NA'  && !empty($value[$headerIndexes['Fixture Material']]))?'<br/>Fixture Material: '.$value[$headerIndexes['Fixture Material']]:"";
						}
						
						if(array_key_exists('Shade Material', $headerIndexes)){
							$details .= (isset($value[$headerIndexes['Shade Material']]) && $value[$headerIndexes['Shade Material']] != 'NA'  && !empty($value[$headerIndexes['Shade Material']]))?'<br/>Shade Material: '.$value[$headerIndexes['Shade Material']]:"";
						}

						if(array_key_exists('Adjustable', $headerIndexes)){
							$details .= (isset($value[$headerIndexes['Adjustable']]) && $value[$headerIndexes['Adjustable']] != 'NA'  && !empty($value[$headerIndexes['Adjustable']]))?'<br/>Adjustable: '.$value[$headerIndexes['Adjustable']]:"";
						}

						if(array_key_exists('Assembly Required', $headerIndexes)){
							$details .= (isset($value[$headerIndexes['Assembly Required']]) && $value[$headerIndexes['Assembly Required']] != 'NA'  && !empty($value[$headerIndexes['Assembly Required']]))?'<br/>Assembly Required: '.$value[$headerIndexes['Assembly Required']]:"";
						}
						
						$template = str_replace('{{{details}}}', $details, $template);

						if(array_key_exists('Fixture Color', $headerIndexes)){
							$color = (isset($value[$headerIndexes['Fixture Color']]) && $value[$headerIndexes['Fixture Color']] != 'NA' && !empty($value[$headerIndexes['Fixture Color']]))?' <br/> Fixture Color: '.$value[$headerIndexes['Fixture Color']]:"";
						}
						if(array_key_exists('Shade Color', $headerIndexes)){
							$color .= (isset($value[$headerIndexes['Shade Color']]) && $value[$headerIndexes['Shade Color']] != 'NA' && !empty($value[$headerIndexes['Shade Color']]))?' <br/> Shade Color: '.$value[$headerIndexes['Shade Color']]:"";
						}
						if(array_key_exists('Cord Colour', $headerIndexes)){
							$color .= (isset($value[$headerIndexes['Cord Colour']]) && $value[$headerIndexes['Cord Colour']] != 'NA'  && !empty($value[$headerIndexes['Cord Colour']]))?'<br/> Cord Color: '.$value[$headerIndexes['Cord Colour']]:"";
						}
						
						$template = str_replace('{{{color}}}', $color, $template);

						if(array_key_exists('Light Direction', $headerIndexes)){
							$lightSpecifications = (isset($value[$headerIndexes['Light Direction']]) && $value[$headerIndexes['Light Direction']] != 'NA' && !empty($value[$headerIndexes['Light Direction']]))?'<br/> Light Direction: '.$value[$headerIndexes['Light Direction']]:"";
						}
						
						if(array_key_exists('Switch Type', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Switch Type']]) && $value[$headerIndexes['Switch Type']] != 'NA' && !empty($value[$headerIndexes['Switch Type']]))?'<br/> Switch Type: '.$value[$headerIndexes['Switch Type']]:"";
						}

						if(array_key_exists('Socket Type', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Socket Type']]) && $value[$headerIndexes['Socket Type']] != 'NA' && !empty($value[$headerIndexes['Socket Type']]))?'<br/> Socket Type: '.$value[$headerIndexes['Socket Type']]:"";
						}

						if(array_key_exists('Bulb Base', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Bulb Base']]) && $value[$headerIndexes['Bulb Base']] != 'NA' && !empty($value[$headerIndexes['Bulb Base']]))?'<br/> Bulb Base: '.$value[$headerIndexes['Bulb Base']]:"";
						}

						if(array_key_exists('Recommended Bulb', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Recommended Bulb']]) && $value[$headerIndexes['Recommended Bulb']] != 'NA' && !empty($value[$headerIndexes['Recommended Bulb']]))?'<br/> Recommended Bulb: '.$value[$headerIndexes['Recommended Bulb']]:"";
						}

						if(array_key_exists('No. of bulbs', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['No. of bulbs']]) && $value[$headerIndexes['No. of bulbs']] != 'NA' && !empty($value[$headerIndexes['No. of bulbs']]))?'<br/> Number of Bulbs: '.(int) $value[$headerIndexes['No. of bulbs']]:"";
						}

						if(array_key_exists('Bulb Used', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Bulb Used']]) && $value[$headerIndexes['Bulb Used']] != 'NA' && !empty($value[$headerIndexes['Bulb Used']]))?'<br/> Bulb Used: '.$value[$headerIndexes['Bulb Used']]:"";
						}
						
						if(array_key_exists('Light Colour', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Light Colour']]) && $value[$headerIndexes['Light Colour']] != 'NA' && !empty($value[$headerIndexes['Light Colour']]))?'<br/> Light Colour: '.$value[$headerIndexes['Light Colour']]:"";
						}
						
						if(array_key_exists('Maximum Wattage', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Maximum Wattage']]) && $value[$headerIndexes['Maximum Wattage']] != 'NA' && !empty($value[$headerIndexes['Maximum Wattage']]))?'<br/> Max Wattage: '.$value[$headerIndexes['Maximum Wattage']]:"";
							if(array_key_exists('Maximum Wattage - Measuring Unit', $headerIndexes)){
								$lightSpecifications .= (isset($value[$headerIndexes['Maximum Wattage - Measuring Unit']]) && $value[$headerIndexes['Maximum Wattage']] != 'NA' && !empty($value[$headerIndexes['Maximum Wattage']]) && !empty($value[$headerIndexes['Maximum Wattage - Measuring Unit']]))?' '.$value[$headerIndexes['Maximum Wattage - Measuring Unit']]:"";
							}
						}
						
						if(array_key_exists('Cord Length', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Cord Length']]) && $value[$headerIndexes['Cord Length']] != 'NA' && !empty($value[$headerIndexes['Cord Length']]))?'<br/>Cord Length: '.$value[$headerIndexes['Cord Length']]:"";
							if(array_key_exists('Cord Length - Measuring Unit', $headerIndexes)){
								$lightSpecifications .= (isset($value[$headerIndexes['Cord Length - Measuring Unit']]) && $value[$headerIndexes['Cord Length - Measuring Unit']] != 'NA' && !empty($value[$headerIndexes['Cord Length - Measuring Unit']]) && !empty($value[$headerIndexes['Cord Length - Measuring Unit']]))?' '.$value[$headerIndexes['Cord Length - Measuring Unit']]:"";
							}
						}

						if(array_key_exists('Cord Material', $headerIndexes)){
							$lightSpecifications .= (isset($value[$headerIndexes['Cord Material']]) && $value[$headerIndexes['Cord Material']] != 'NA' && !empty($value[$headerIndexes['Cord Material']]))?'<br/> Cord Material: '.$value[$headerIndexes['Cord Material']]:"";
						}

						$template = str_replace('{{{light_specifications}}}', $lightSpecifications, $template);

						if(array_key_exists('Width', $headerIndexes)){
							$dimensions = (isset($value[$headerIndexes['Width']]) && $value[$headerIndexes['Width']] != 'NA' && !empty($value[$headerIndexes['Width']]))?' <br/> Width: '.$value[$headerIndexes['Width']]:"";
							if(array_key_exists('Width - Measuring Unit', $headerIndexes)){
								$dimensions .= (isset($value[$headerIndexes['Width - Measuring Unit']]) && $value[$headerIndexes['Width']] != 'NA' && !empty($value[$headerIndexes['Width']]) && !empty($value[$headerIndexes['Width - Measuring Unit']]))?' '.$value[$headerIndexes['Width - Measuring Unit']]:"";
							}
						}
						
						if(array_key_exists('Length', $headerIndexes)){
							$dimensions .= (isset($value[$headerIndexes['Length']]) && $value[$headerIndexes['Length']] != 'NA' && !empty($value[$headerIndexes['Length']]))?' <br/> Length: '.$value[$headerIndexes['Length']]:"";
							if(array_key_exists('Length - Measuring Unit', $headerIndexes)){
								$dimensions .= (isset($value[$headerIndexes['Length - Measuring Unit']]) && $value[$headerIndexes['Length']] != 'NA' && !empty($value[$headerIndexes['Length']]) && !empty($value[$headerIndexes['Length - Measuring Unit']]))?' '.$value[$headerIndexes['Length - Measuring Unit']]:"";
							}
						}
						
						if(array_key_exists('Diameter', $headerIndexes)){
							$dimensions .= (isset($value[$headerIndexes['Diameter']]) && $value[$headerIndexes['Diameter']] != 'NA' && !empty($value[$headerIndexes['Diameter']]))?' <br/> Diameter: '.$value[$headerIndexes['Diameter']]:"";
							if(array_key_exists('Diameter - Measuring Unit', $headerIndexes)){
								$dimensions .= (isset($value[$headerIndexes['Diameter - Measuring Unit']]) && !empty($value[$headerIndexes['Diameter']]) && $value[$headerIndexes['Diameter']] != 'NA' && !empty($value[$headerIndexes['Diameter - Measuring Unit']]))?' '.$value[$headerIndexes['Diameter - Measuring Unit']]:"";
							}
						}

						if(array_key_exists('Height', $headerIndexes)){
							$dimensions .= (isset($value[$headerIndexes['Height']]) && $value[$headerIndexes['Height']] != 'NA' && !empty($value[$headerIndexes['Height']]))?' <br/> Height: '.$value[$headerIndexes['Height']]:"";
							if(array_key_exists('Height - Measuring Unit', $headerIndexes)){
								$dimensions .= (isset($value[$headerIndexes['Height - Measuring Unit']]) && !empty($value[$headerIndexes['Height']]) && $value[$headerIndexes['Height']] != 'NA' && !empty($value[$headerIndexes['Height - Measuring Unit']]))?' '.$value[$headerIndexes['Height - Measuring Unit']]:"";
							}
						}

						if(array_key_exists('Weight', $headerIndexes)){
							$dimensions .= (isset($value[$headerIndexes['Weight']]) && $value[$headerIndexes['Weight']] != 'NA' && !empty($value[$headerIndexes['Weight']]))?' <br/> Weight: '.$value[$headerIndexes['Weight']]:"";
							if(array_key_exists('Weight - Measuring Unit', $headerIndexes)){
								$dimensions .= (isset($value[$headerIndexes['Weight - Measuring Unit']]) && !empty($value[$headerIndexes['Weight']]) && $value[$headerIndexes['Weight']] != 'NA' && !empty($value[$headerIndexes['Weight - Measuring Unit']]))?' '.$value[$headerIndexes['Weight - Measuring Unit']]:"";
							}
						}
						
						$template = str_replace('{{{dimensions}}}', $dimensions, $template);

						if(array_key_exists('Suitable For', $headerIndexes)){
							$suitableFor = (isset($value[$headerIndexes['Suitable For']]) && $value[$headerIndexes['Suitable For']] != 'NA' && !empty($value[$headerIndexes['Suitable For']]))?' <p> <b> Suitable For </b> <br/>'.$value[$headerIndexes['Suitable For']].' </p> ':"";
						}
						$template = str_replace('{{{suitable_for}}}', $suitableFor, $template);

						if(array_key_exists('Package Contents', $headerIndexes)){
							$packageContents = (isset($value[$headerIndexes['Package Contents']]) && $value[$headerIndexes['Package Contents']] != 'NA' && !empty($value[$headerIndexes['Package Contents']]))?' <p> <b> Package Contents </b> <br/>'.$value[$headerIndexes['Package Contents']].' </p> ':"";
						}
						$template = str_replace('{{{package_contents}}}', $packageContents, $template);

						$this->data[$key][$headerIndexes['Description']] = $template;
					}
					if(in_array("Product Knowledge & Care Instruction", $this->post['checklist'])){
						$template = file_get_contents('html/instruction_template.html');
						$instruction = "";
						
						$instruction = (isset($value[$headerIndexes['Product Knowledge & Care Instruction']]) && $value[$headerIndexes['Product Knowledge & Care Instruction']] != 'NA' && !empty($value[$headerIndexes['Product Knowledge & Care Instruction']]))?$value[$headerIndexes['Product Knowledge & Care Instruction']]:"";
						$template = str_replace('{{{instruction}}}', $instruction, $template);
						$this->data[$key][$headerIndexes['Product Knowledge & Care Instruction']] = $template;	
					}
				}
			}
		}
	}

	private function _replaceHeader(){
		$newNames = array_filter($this->post['changelist']);
		foreach($newNames as $oldHeader => $newHeader){
			foreach($this->data[0] as $key => $header){
				if($oldHeader == $header){
					$this->data[0][$key] = $newHeader;
				}
			}
		}
	}

	private function _streamCSV(){
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.csv');
		$output = fopen('php://output', 'w');
		foreach ($this->data as $fields) {
			fputcsv($output, $fields);
		}
		fclose($output);
	}

	private function _removeUnwantedKeys(){
		$requiredHeader = $this->post['checklist'];
		$unwantedkeys = array();
		foreach($this->data[0] as $key => $header){
			if(!in_array($header, $requiredHeader)){
				$unwantedkeys[] = $key; 
			}
		}
		if(isset($unwantedkeys)){
			foreach($this->data as $k => $v){
				foreach($unwantedkeys as $v2){
					unset($this->data[$k][$v2]);
				}
				$this->data[$k] = array_values($this->data[$k]);
			}
		}
	}

	private function _getMergeHeader(){
		$mergeHeader = array();
		foreach($this->post as $k3 => $v3){
			if(preg_match('/mergelist\d/',$k3)){
			  	$mergeHeader[] = $v3;
			}
		}
		return $mergeHeader;
	}

	private function _merge($mergeHeader){
		foreach($mergeHeader as $k4 => $v4){
			$mergeKeys = array();
			foreach($this->data[0] as $key => $header){
				if(in_array($header, $v4)){
					$mergeKeys[] = $key; 
				}
			}
			$measuringUnitKeys = array();
			foreach($this->data as $k5 => $v5){
				if($this->post['isimagecsv'] == 1){
					if($k5 == 0){
						$this->data[$k5][] = 'small_image';
						$this->data[$k5][] = 'thumbnail_image';
					}else{
						$this->data[$k5][] = $this->data[$k5][2];
						$this->data[$k5][] = $this->data[$k5][2];
					}
				}
				$tmp = '';
				foreach($mergeKeys as $k6 => $v6){
					if($k5 == 0){
						if(strpos($v5[$v6], '- Measuring Unit')){
							$tmp = rtrim($tmp,$this->post['headerseperator'][$k4]);
							$measuringUnitKeys[] = $v6;
						}
						if($this->post['changelist'][$v5[$v6]] == ' '){
							$tmp .= $this->post['headerseperator'][$k4];
						}else{
							$tmp .= !empty($this->post['changelist'][$v5[$v6]])?$this->post['changelist'][$v5[$v6]].$this->post['headerseperator'][$k4]:$v5[$v6].$this->post['headerseperator'][$k4];
						}
					}else{
						if(in_array($v6, $measuringUnitKeys)){
							$tmp = rtrim($tmp,$this->post['bodyseperator'][$k4]);
							if(!empty($tmp)){
								$tmp .= ' '.$v5[$v6].$this->post['bodyseperator'][$k4];
							}
						}else{
							$tmp .= $v5[$v6].$this->post['bodyseperator'][$k4];
						}
					}
				}
				if($k5 == 0){
					$tmp = rtrim($tmp,$this->post['headerseperator'][$k4]);
				}else{
					$tmp = rtrim($tmp,$this->post['bodyseperator'][$k4]);
				}
				$this->data[$k5][] = $tmp;
			}
		}
	}

	private function _unsMergeData($mergeHeader){
		foreach($mergeHeader as $v4){
			$mergeKeys = array();
			foreach($this->data[0] as $key => $header){
				if(in_array($header, $v4)){
					$mergeKeys[] = $key; 
				}
			}
			foreach($this->data as $k5 => $v5){
				foreach($mergeKeys as $v6){
					unset($this->data[$k5][$v6]);
				}
				$this->data[$k5] = array_values($this->data[$k5]);
			}
		}
	}
}

$obj = new process($_POST,$_FILES);

?>