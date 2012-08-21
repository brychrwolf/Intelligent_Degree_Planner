<?php
// Check if a CLASS registration is valid for a COURSE/TERM/DAY/TIME-RANGE request
// Can be used as a stand alone tool for the UI later!
function validRegistration($stuId, $courseId, $degreePlan, $term, $maxCredits, $day, $time_start, $time_end){
	// Assume Registration is valid, then try to prove invalid
	$valid[0] = TRUE;

	// Break SQL into many statements to facilate returning specific errors
	// Return FALSE if CLASS record does not exist; We are assuming all course are taught in every term/day/time for now
	if(FALSE){
		$valid[0] = FALSE;
		$valid[1] = "CLASS_DOES_NOT_EXIST";
		return $valid;
	}// MET clause removed because it is concidered already in the recursive calls to planDegree
	// Else false if COURSE has already been MET
	//elseif(mysql_query("SELECT * FROM ADV_MET WHERE STU_ID=$stuId AND CRS_ID='$courseId'")){
	//	$valid[0] = FALSE;
	//	$valid[1] = "COURSE_ALREADY_MET";
	//}
	// Return FALSE if $maxCredits will be exceeded
	//$sumQuery = mysql_query("SELECT SUM(CRS_CREDIT) AS SUM FROM ADV_MET M RIGHT JOIN ADV_COURSE C ON C.CRS_ID=M.CRS_ID WHERE (M.STU_ID=$stuId AND M.MET_TERM=$term) OR C.CRS_ID='$courseId'");
	//$creditsThisTerm = mysql_fetch_object($sumQuery);
	//if($maxCredits < $creditsThisTerm->SUM){
	// Max Credit check needs to be on the current degree plan, not the live database
	$creditsThisTerm = 0;
	foreach($degreePlan as $metCourse){ 
		if($metCourse->MET_TERM == $term){
			$creditQuery = mysql_query("SELECT CRS_CREDIT FROM ADV_COURSE WHERE CRS_ID='$metCourse->CRS_ID' OR CRS_ID='$courseId'");
			$creditObj = mysql_fetch_object($creditQuery);
			$creditsThisTerm += $creditObj->CRS_CREDIT;
			if($maxCredits < $creditsThisTerm){
				$valid[0] = FALSE;
				$valid[1] = "MAX_CREDITS_EXCEEDED";
				return $valid;
			}
		}
	}// Else false if any PREREQ has been MET
	$metQuery = mysql_query("SELECT P.*, M.MET_TERM, M.MET_CR_TYPE FROM ADV_PREREQ P LEFT JOIN ADV_MET M ON P.PRQ_ID=M.CRS_ID AND M.STU_ID=$stuId WHERE P.CRS_ID='$courseId'");
	while($metObj = mysql_fetch_object($metQuery)){
		if($metObj->MET_TERM != NULL){
			$valid[0] = FALSE;
			// Can add which prereqs caused the error later, or leave it up to the function caller to decide
			$valid[1] = "PREREQ_NOT_MET";
			return $valid;
		}
	}
	// Return the array $valid[boolean][string]
	return $valid;
}
?>