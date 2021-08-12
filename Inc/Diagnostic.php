<?php
namespace Inc;
	class Diagnostic{
		public $link;
		protected $illness = [];
		protected  $columns = [];

		public function __construct($columns){
			session_start();
			$this->columns = $columns;
			$this->link = mysqli_connect('diagnostic', 'root', '', 'diagnostic');
		}

		public function formTest(){
					$part = "<form method=\"POST\">";
					foreach($this->columns as $title=>$column){
						$data = $this->getData('illness', $column);
						$part .= "<h3>$title</h3>
								<select name=\"$column\">";
						foreach($data as $elem){
							$part .= "<option>$elem[$column]</option>";
						}
						$part .= "</select>";	
					}
					$part .= "<input type=\"submit\" value=\"Диагноз\" name=\"send\">
						     <form>";	
					return $part;
		}

		public function getIllness(){
			$part = [];
			foreach($this->columns as $title=>$column){
				$illness = $this->getData('illness', 'name', "WHERE $column='$_POST[$column]'");
				$part[] = $illness['name'];
			}
			 $this->illness = array_unique($part);
			 $_SESSION['illness']  = $this->illness;
			 return $this->result($this->illness);
		}


		public function decide(){
			if(isset($_POST['send'])){
				$test = $this->getIllness();
			}elseif(!isset($_POST['send']) AND !isset($_POST['final']) and !isset($_POST['again'])){
				$test = $this->formTest();
			}elseif(isset($_POST['final'])){
				$test = "Имеющаяся болезнь:"."<br><p class=\"diag\">$_POST[sick]</p><br>
					  		 <a href=\"/\">Пройти тест заново</a>";
				$_SESSION['illness'] = null;
			}elseif(isset($_POST['again'])){
				$test = $this->formResultTest($_SESSION['illness']);
			}
			return $test;
		}
			
		private function result($illness){
				if(count($illness) == 1){
					  $part = "Имеющаяся болезнь:"."<br><p class=\"diag\">$illness[0]</p><br>
					  		 <a href=\"/\">Пройти тест заново</a>";
				}elseif(count($illness) > 1){
					$part = $this->formResultTest($illness);
				}
				return $part;
		}

		private function formResultTest($illness){
			$part = "Возможные болезни:".implode(',', $illness)."
					<form method=\"POST\">";
			foreach($illness as $sick){
					$query = "SELECT other_symptoms.text as `text`
						 FROM illness
						 LEFT JOIN rel ON rel.sick_id=illness.id
						 LEFT JOIN other_symptoms ON rel.symptom_id=other_symptoms.id WHERE illness.name='$sick' ORDER BY RAND() LIMIT 1;
						";
							$result = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
							$data = mysqli_fetch_assoc($result);
						$part .= "<label for=\"$sick\"><input type=\"radio\" value=\"$sick\" name=\"sick\" id=\"$sick\"> $data[text]</label><br>";
			}
			$part .= "<input type=\"submit\" name=\"final\" value=\"Диагноз\">
					</form>
					<form method=\"POST\">
						<input type=\"submit\" name=\"again\" value=\"Ни один из симптомов не подходит\">
					</form>
					";
			return $part;
		}		
		

		private function getData($table, $column = '*', $where = ''){
			$query = "SELECT $column FROM $table $where";
			$result = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
			if($where == null){
				for($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);
			}else{
				$data = mysqli_fetch_assoc($result);
			}
			return $data;
		}
	}