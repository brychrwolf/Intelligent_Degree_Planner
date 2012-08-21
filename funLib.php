<?php // advisor function library
// 4. Recursive Depth-Limited-Search for most effiecient path to graduation
/* Similar to the standard DLS algorithm except:
	"limit" is replaced with $termKey which iterates upwards to a limit of 52 terms (4 years of a maxed-out COMBO schedule)
	endCondition is answered with a canGraduate BOOLEAN clause*/
function planDegree($degreePlan, $term, $priorityUnmetCourse, $totalCreditDeficit, $overallResidencyCreditDeficit, $majorResidencyCreditDeficit, $nontraditionalCreditsRemaining, $termKey){
	// If degreePlan leads to graduation, return that degreePlan
	// *later* If (exhausted all combinations) and (degreePlan leads to graduation) and (degreePlan is fastest found), return that plan
	if(count($priorityUnmetCourse)==0 
	/*&& $totalCreditDeficit==0
	&& $overallResidencyCreditDeficit==0 
	&& $majorResidencyCreditDeficit==0*/){
		$degreePlan[] = "GRADUATE";
		return $degreePlan;
	}elseif($termKey >= 52){
		$degreePlan = "CUTOFF";
		return $degreePlan;
	}else{ // Determine the next class to add to $degreePlan
		$cutoff_occurred = FALSE;
				
		//	For each course still unmet, check for a valid class
		foreach($priorityUnmetCourse as $courseId){
			$courseQuery = mysql_query("SELECT * FROM ADV_COURSE WHERE CRS_ID='$courseId'");
			$courseObj = mysql_fetch_object($courseQuery);
			//$day = "M"; $time_start = "08:30"; $time_end = "10:30";
			//$isValid = validRegistration($stuObj->STU_ID, $degreqObj->CRS_ID, $degreePlan, $erm[$termKey], $maxCredits, $day, $time_start, $time_end);
			//echo "$isValid = validRegistration($stuObj->STU_ID, $degreqObj->CRS_ID, $degreePlan, $TERM[$termKey], $maxCredits, $day, $time_start, $time_end) = "; var_dump($isValid);
			if(!TRUE){/*$isValid[0]){
				echo "Class $degreqObj->CRS_ID in ".$TERM[$termKey]." is invalid because";
				switch($isValid[1]){
					case "CLASS_DOES_NOT_EXIST": 
						echo " that course is not offered at that time/day in this term.\n\n";
						break;
					//case "COURSE_ALREADY_MET": break; // Already checked above
					case "MAX_CREDITS_EXCEEDED": 
						echo " the maximum number of credits for this term has been exceeded. Trying again in the next term.\n\n";
						$termKey++;
						break;
					case "PREREQ_NOT_MET": 
						echo " a prerequisite course needs to be taken first.\n\n";
						break;
					default: break;
				}*/
			}else{ // Is a valid course
				// Concider the implications of taking that valid class now, by feeding the new possible degree plan back into planDegree (recursion)
				$possibleAnswer = $degreePlan;
				$possibleAnswer[]->CRS_ID = $courseId;
				$possibleAnswer[count($possibleAnswer)-1]->MET_TERM = $term[$termKey]->TERM_ID;

				array_shift($priorityUnmetCourse);
				$totalCreditDeficit -= $courseObj->CRS_CREDIT;
				$overallResidencyCreditDeficit -= $courseObj->CRS_CREDIT;
				$majorResidencyCreditDeficit -= $courseObj->CRS_CREDIT; // Not calculating correctly! Can't tell between overall and major credits
				$nontraditionalCreditsRemaining	-= 0;//$courseObj->CRS_CR_TYPE=="N" ? $courseObj->CRS_CREDIT : 0;
				
				$degreePlan = planDegree($possibleAnswer, $term, $priorityUnmetCourse, $totalCreditDeficit, $overallResidencyCreditDeficit, $majorResidencyCreditDeficit, $nontraditionalCreditsRemaining, $termKey+1);
				
				//echo "\n\ndegreePlan = planDegree($degreePlan, $term, $priorityUnmetCourse, $totalCreditDeficit, $overallResidencyCreditDeficit, $majorResidencyCreditDeficit, $nontraditionalCreditsRemaining, ".($termKey+1).")";
				if($degreePlan[count($degreePlan)-1] == "CUTOFF") $cutoff_occurred = TRUE;
				elseif($degreePlan[count($degreePlan)-1] != "FAILURE") return $degreePlan;
			}
		}
	}
	if($cutoff_occurred){
		$degreePlan = "CUTOFF";
		return $degreePlan;
	}else{
		$degreePlan = "FAILURE";
		return $degreePlan;
	}
}


?>