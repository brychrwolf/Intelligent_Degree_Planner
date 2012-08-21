<?php
include("../../include_lib/connection.php");
$axiQuery = mysql_query("SELECT * FROM ADV_AXIOM");
while($axiObj = mysql_fetch_object($axiQuery)){
	$AXI_ID = $axiObj->AXI_ID;
	$AXIOM->$AXI_ID->VALUE = $axiObj->AXI_VALUE;
	$AXIOM->$AXI_ID->DESC = $axiObj->AXI_DESC;
}var_dump($AXIOM);

// Other notes moved from recalculate.php
/*/ Start location varible to pass info via GET so that permalinks can be easily created
$location = "location: index.php?"
			."optDegId=".$_POST["optDegId"]
			."&optSchedule=".$_POST["optSchedule"]
			."&optMaxCredPerPeriod=".$_POST["optMaxCredPerPeriod"]
			."&optMaxNTCred=".$_POST["optMaxNTCred"]
			."&optSummerAndWinter=".$_POST["optSummerAndWinter"]
			."&optInterim=".$_POST["optInterim"];*/

//var_dump($_POST);
/*array(23) {
  ["STU_ID"]=>  string(1) "1"
  ["optDegId"]=>  string(4) "ASCS"
  ["optSchedule"]=>  string(3) "MCP"
  ["optMaxNTCred"]=>  string(2) "on"
  ["optSummerAndWinter"]=>  string(2) "on"
  ["optInterim"]=>  string(2) "on"
  ["CRS_ID-GEDREQ-CS-A"]=>  string(7) "WRI1150"
  ["TERM-GEDREQ-CS-A"]=>  string(6) "201020"
  ["CR_TYPE-GEDREQ-CS-A"]=>  string(1) "N"
  ["MET-GEDREQ-CS-A"]=>  string(2) "on"
  ["CRS_ID-GEDREQ-GS-A"]=>  string(8) "PHYS1000"
  ["TERM-GEDREQ-GS-A"]=>  string(0) ""
  ["CR_TYPE-GEDREQ-GS-A"]=>  string(0) ""
  ["MET-GEDREQ-RE-A"]=>  string(2) "on"
  ["CRS_ID-GEDREQ-RE-A"]=>  string(7) "WRI1200"
  ["TERM-GEDREQ-RE-A"]=>  string(6) "201130"
  ["CR_TYPE-GEDREQ-RE-A"]=>  string(1) "H"
  ["CRS_ID-GEDREQ-VC-B"]=>  string(8) "THEA1000"
  ["TERM-GEDREQ-VC-B"]=>  string(0) ""
  ["CR_TYPE-GEDREQ-VC-B"]=>  string(0) ""
  ["CRS_ID-GEDREQ-WC-B"]=>  string(8) "STSS2601"
  ["TERM-GEDREQ-WC-B"]=>  string(0) ""
  ["CR_TYPE-GEDREQ-WC-B"]=>  string(0) ""
}*/

/*
		for($year = date("Y"); $year < date("Y")+4; $year++){
		foreach($AXIOM as $key=>$value){
			// Need to change condition of combo/both
			if(substr($key, 0, 3) == substr($stuObj->STU_SCHED, 0, 3)){
				$term = $year * 100 + $value->VALUE;
				$gedreqQueryText = "SELECT GR.GED_ID, GR.GED_CAT, C.* FROM ADV_GEDREQ GR";
				// For associate degrees, filter from ADV_GEDREQ_ASSOC
				if(substr($stuObj->DEG_ID, 0, 1) == "A")
					$gedreqQueryText .= " INNER JOIN ADV_GEDREQ_ASSOC GRA ON (GRA.GED_ID=GR.GED_ID AND GRA.GED_CAT=GR.GED_CAT) JOIN ADV_COURSE C ON GR.CRS_ID=C.CRS_ID WHERE GRA.DEG_ID='$stuObj->DEG_ID'";
				$gedreqQuery = mysql_query($gedreqQueryText);
				while($gedreqObj = mysql_fetch_object($gedreqQuery)){
					echo "if(validRegistration($stuObj->STU_ID, $gedreqObj->CRS_ID, $term, 9, 'M', '', ''')){<BR>\n";
					//if(validRegistration($stuObj->STU_ID, $gedreqObj->CRS_ID, $term, $day, $time_start, $time_end){
					//need to add stuobj->maxcredits, coursestart and end time databse entries
				}
				
				$degreqQuery = mysql_query("SELECT C.* FROM ADV_DEGREQ D, ADV_COURSE C WHERE D.DEG_ID='ASCS' AND D.CRS_ID=C.CRS_ID");
				while($degreqObj = mysql_fetch_object($degreqQuery)){
				}
			}	
		}	*/
?>