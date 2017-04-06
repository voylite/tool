<?php

class process{
	public function __construct($post,$files){
		$this->post = $post;
		$this->files = $files;
		$this->$_POST['invoke']();
	}

	protected function _catalogFileUpload(){
		$permitted = 1;
		if(isset($this->post["submit"])){
			if($this->post["headerline"] < 1){
				$permitted = 0;
				echo "Header line is improper!!";
				return;
			}
			if($this->files["catalogfile"]["size"] > 2048000){
				$permitted = 0;
				echo "File size is too large!!";
				return;
			}
			if($this->files["catalogfile"]["type"] != 'text/csv'){
				$permitted = 0;
				echo "File type should be text/csv!!... ",empty($this->files["catalogfile"]["type"]) ? "none" : $this->files["catalogfile"]["type"]," given!!!";
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
			$this->_createDescription();
			$this->_removeUnwantedKeys();
			$mergeHeader = $this->_getMergeHeader();
			$this->_merge($mergeHeader);
			$this->_unsMergeData($mergeHeader);
			$this->_replaceHeader();
			$this->_streamCSV();
		}else{
			echo "No data provided!!!";
		}
	}

	private function _createDescription(){
		if(in_array("Description", $this->post['checklist'])){
			foreach($this->data as $key => $value){
				if($key == 0){
					$headerIndexes = array_flip($value);
				}else{
					$template = file_get_contents('html/description_template.html');
					$description = $details = $color = $size = $lighting = "";
					
					$description = (isset($value[$headerIndexes['Description']]) && $value[$headerIndexes['Description']] != 'NA' && !empty($value[$headerIndexes['Description']]))?$value[$headerIndexes['Description']]:"";
					$template = str_replace('{{{description}}}', $description, $template);

					$details = (isset($value[$headerIndexes['Suitable For']]) && $value[$headerIndexes['Suitable For']] != 'NA' && !empty($value[$headerIndexes['Suitable For']]))?$value[$headerIndexes['Suitable For']]:"";
					$details .= (isset($value[$headerIndexes['Fixture Material']]) && $value[$headerIndexes['Fixture Material']] != 'NA'  && !empty($value[$headerIndexes['Fixture Material']]))?'<br/>'.$value[$headerIndexes['Fixture Material']]:"";
					$details .= (isset($value[$headerIndexes['Shade Material']]) && $value[$headerIndexes['Shade Material']] != 'NA'  && !empty($value[$headerIndexes['Shade Material']]))?'<br/>'.$value[$headerIndexes['Shade Material']]:"";
					$details .= (isset($value[$headerIndexes['Bulb Used']]) && $value[$headerIndexes['Bulb Used']] != 'NA'  && !empty($value[$headerIndexes['Bulb Used']]))?'<br/>'.$value[$headerIndexes['Bulb Used']]:"";
					$details .= (isset($value[$headerIndexes['Cord Color']]) && $value[$headerIndexes['Cord Color']] != 'NA'  && !empty($value[$headerIndexes['Cord Color']]))?'<br/>'.$value[$headerIndexes['Cord Color']]:"";
					$details .= (isset($value[$headerIndexes['Cord Length - Measuring Unit']]) && $value[$headerIndexes['Cord Color']] != 'NA' && !empty($value[$headerIndexes['Cord Length - Measuring Unit']]))?'<br/>'.$value[$headerIndexes['Cord Length - Measuring Unit']]:"";
					$details .= (isset($value[$headerIndexes['Cord Mesuring Unit']]) && $value[$headerIndexes['Cord Length - Measuring Unit']] != 'NA' && $value[$headerIndexes['Cord Mesuring Unit']] != 'NA' && !empty($value[$headerIndexes['Cord Length - Measuring Unit']]) && !empty($value[$headerIndexes['Cord Mesuring Unit']]))?$value[$headerIndexes['Cord Mesuring Unit']]:"";
					$details = ltrim($details,'<br/>');
					$details = rtrim($details,'<br/>');
					$template = str_replace('{{{details}}}', $details, $template);

					$color = (isset($value[$headerIndexes['Fixture Color']]) && $value[$headerIndexes['Fixture Color']] != 'NA' && !empty($value[$headerIndexes['Fixture Color']]))?$value[$headerIndexes['Fixture Color']]:"";
					$color .= (isset($value[$headerIndexes['Shade Color']]) && $value[$headerIndexes['Shade Color']] != 'NA' && !empty($value[$headerIndexes['Shade Color']]))?','.$value[$headerIndexes['Shade Color']]:"";
					$color = ltrim($color,',');
					$color = rtrim($color,',');
					$template = str_replace('{{{color}}}', $color, $template);

					$size = (isset($value[$headerIndexes['Width']]) && $value[$headerIndexes['Width']] != 'NA' && !empty($value[$headerIndexes['Width']]))?$value[$headerIndexes['Width']]:"";
					$size .= (isset($value[$headerIndexes['Width - Measuring Unit']]) && $value[$headerIndexes['Width']] != 'NA' && !empty($value[$headerIndexes['Width']]) && !empty($value[$headerIndexes['Width - Measuring Unit']]))?$value[$headerIndexes['Width - Measuring Unit']]:"";
					$size .= (isset($value[$headerIndexes['Length']]) && $value[$headerIndexes['Length']] != 'NA' && !empty($value[$headerIndexes['Length']]))?','.$value[$headerIndexes['Length']]:"";
					$size .= (isset($value[$headerIndexes['Length - Measuring Unit']]) && $value[$headerIndexes['Length']] != 'NA' && !empty($value[$headerIndexes['Length']]) && !empty($value[$headerIndexes['Length - Measuring Unit']]))?$value[$headerIndexes['Length - Measuring Unit']]:"";
					$size .= (isset($value[$headerIndexes['Diameter']]) && $value[$headerIndexes['Diameter']] != 'NA' && !empty($value[$headerIndexes['Diameter']]))?','.$value[$headerIndexes['Diameter']]:"";
					$size .= (isset($value[$headerIndexes['Diameter - Measuring Unit']]) && !empty($value[$headerIndexes['Diameter']]) && $value[$headerIndexes['Diameter']] != 'NA' && !empty($value[$headerIndexes['Diameter - Measuring Unit']]))?$value[$headerIndexes['Diameter - Measuring Unit']]:"";
					$size = ltrim($size,',');
					$size = rtrim($size,',');
					$template = str_replace('{{{size}}}', $size, $template);

					$lighting = (isset($value[$headerIndexes['Light Direction']]) && $value[$headerIndexes['Light Direction']] != 'NA' && !empty($value[$headerIndexes['Light Direction']]))?$value[$headerIndexes['Light Direction']]:"";
					$lighting .= (isset($value[$headerIndexes['Switch Type']]) && $value[$headerIndexes['Switch Type']] != 'NA' && !empty($value[$headerIndexes['Switch Type']]))?'<br/>'.$value[$headerIndexes['Switch Type']]:"";
					$lighting .= (isset($value[$headerIndexes['Light Colour']]) && $value[$headerIndexes['Light Colour']] != 'NA' && !empty($value[$headerIndexes['Light Colour']]))?'<br/>'.$value[$headerIndexes['Light Colour']]:"";
					$lighting .= (isset($value[$headerIndexes['Maximum Wattage']]) && $value[$headerIndexes['Maximum Wattage']] != 'NA' && !empty($value[$headerIndexes['Maximum Wattage']]))?'<br/>'.$value[$headerIndexes['Maximum Wattage']]:"";
					$lighting .= (isset($value[$headerIndexes['Maximum Wattage - Measuring Unit']]) && $value[$headerIndexes['Maximum Wattage']] != 'NA' && !empty($value[$headerIndexes['Maximum Wattage']]) && !empty($value[$headerIndexes['Maximum Wattage - Measuring Unit']]))?$value[$headerIndexes['Maximum Wattage - Measuring Unit']]:"";
					$lighting .= (isset($value[$headerIndexes['Filament']]) && $value[$headerIndexes['Filament']] != 'NA' && !empty($value[$headerIndexes['Filament']]))?'<br/>'.$value[$headerIndexes['Filament']]:"";
					$lighting = ltrim($lighting,'<br/>');
					$lighting = rtrim($lighting,'<br/>');
					$template = str_replace('{{{lighting}}}', $lighting, $template);

					$this->data[$key][$headerIndexes['Description']] = $template;
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
		foreach($mergeHeader as $v4){
			$mergeKeys = array();
			foreach($this->data[0] as $key => $header){
				if(in_array($header, $v4)){
					$mergeKeys[] = $key; 
				}
			}
			foreach($this->data as $k5 => $v5){
				$tmp = '';
				foreach($mergeKeys as $v6){
					if($k5 == 0){
						$tmp .= !empty($this->post['changelist'][$v5[$v6]])?$this->post['changelist'][$v5[$v6]].$this->post['headerseperator']:$v5[$v6].$this->post['headerseperator'];
					}else{
						$tmp .= $v5[$v6].$this->post['bodyseperator'];
					}
				}
				if($k5 == 0){
					$tmp = rtrim($tmp,$this->post['headerseperator']);
				}else{
					$tmp = rtrim($tmp,$this->post['bodyseperator']);
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