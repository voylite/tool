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