<?php
session_start();
include("../../include_lib/connection.php");
include("funLib.php");

/* Algorithm Table of Contents
1. Retrieve given state and axiom information
	A. The student's vital information
		1) Desired Degree plan
		2) Schedule type enrolled in
		3) Utilize max CLEP credits
		4) Take interim/summer/winter terms
		5) Custom max credits per term
	B. All classes required for graduation, split into groups
		1) Major
		2) General Education (modded if for associates)
	C. Other Degree requirements (Axioms)
		1) Minimum credits for degree
		2) Minimum residency requirements
		3) Maximum credits in any given term
		4) Enumerate term codes for chosen schedule
	D. The current state of the student's record (classes already earned)
		1) Update database with new data from the UI
		2) Get records from the database
		3) Enumerate credit counts by type
2. Discover differences between requirements and what has been earned (modded if for associates)
	A. Discover all major courses still unmet
	B. Discover all unmet prerequisites for all unmet required courses
	C. Minimum total credits deficit
	D. Minimum overall residency credit deficit
	E. Minimum major residency credit deficit
	F. Maximum non-traditional credits remaining
3. Designate Course Priorities
	A. *Maximize CLEPs
	B. **Begin long prereq chains
	C. **Courses that fill multiple requirements
	D. ***Courses that aren't offered often
	E. **Courses that are prereqs to many things 
	F. Degree requirements before GenEd
	G. Take maximum credits every term
4. Recursive Depth-Limited-Search for most effiecient path to graduation
5. Update database with new degree plan
6. Propose the new degree plan to the user
*/


// 1. Retrieve given state and axiom information
	// A. The student's vital information
		// 1) Desired Degree plan
		// 2) Schedule type enrolled in
		// 3) Utilize max CLEP credits
		// 4) Take interim/summer/winter terms
		// 5) Custom max credits per term
		$forcedStuId = 1; // Force STU_ID=1 until the UI is completed
		$stuQuery = mysql_query("SELECT * FROM ADV_STUDENT WHERE STU_ID=$forcedStuId");
		$student = mysql_fetch_object($stuQuery);

	// B. All classes required for graduation, split into groups
		// 1) Major
			$degreq = NULL;
			$degreqQuery = mysql_query("SELECT D.CRS_ID FROM ADV_DEGREQ D WHERE D.DEG_ID='$student->DEG_ID'");
			while($degreqObj = mysql_fetch_object($degreqQuery)){ 
				$degreq[] = $degreqObj->CRS_ID;
			}
		
		// 2) General Education (modded if for associates)
			$gedreq = NULL;
			$gedreqQueryText = "SELECT GR.* FROM ADV_GEDREQ GR";
			// For associate degrees, filter from ADV_GEDREQ_ASSOC
			if(substr($student->DEG_ID, 0, 1) == "A")
				$gedreqQueryText .= " INNER JOIN ADV_GEDREQ_ASSOC GRA ON (GRA.GED_ID=GR.GED_ID AND GRA.GED_CAT=GR.GED_CAT) WHERE GRA.DEG_ID='$student->DEG_ID'";
			$gedreqQueryText .= " ORDER BY GED_ID, GED_CAT, CRS_ID";
			$gedreqQuery = mysql_query($gedreqQueryText);
			while($gedreqObj = mysql_fetch_object($gedreqQuery)){
				$gedreq[] = $gedreqObj;
			}
			
	// C. Other Degree requirements (Axioms)
		// 1) Minimum credits for degree		
		// 2) Minimum residency requirements
		// 3) Maximum credits in any given term
			$axiQuery = mysql_query("SELECT * FROM ADV_AXIOM");
			while($axiObj = mysql_fetch_object($axiQuery)){
				$AXI_ID = $axiObj->AXI_ID;
				$AXIOM->$AXI_ID->VALUE = $axiObj->AXI_VALUE;
				$AXIOM->$AXI_ID->DESC = $axiObj->AXI_DESC;
			}

		// 4) Enumerate term codes for chosen schedule
			$termQueryText = "SELECT * FROM ADV_TERM WHERE TERM_SCHED=";
			switch($student->STU_SCHED){
				case "MAIN": $termQueryText .= "'MAIN'".($student->STU_SCHED_OPT?" OR TERM_SCHED='MAIN_OPT'":""); break;
				case "MCP": $termQueryText .= "'MCP'".($student->STU_SCHED_OPT?" OR TERM_SCHED='MCP_OPT'":""); break;
				case "COMBO": $termQueryText .= "'MCP' OR TERM_SCHED='MAIN'".($student->STU_SCHED_OPT?" OR TERM_SCHED='MAIN_OPT' OR TERM_SCHED='MCP_OPT'":""); break;
			}// Add up to 5 years of terms...
			$term = NULL;
			$firstTermYear = (int) floor($student->STU_START_TERM / 100);
			for($nextTermYear = $firstTermYear; $nextTermYear < $firstTermYear + 5; $nextTermYear++){
				$termQuery = mysql_query($termQueryText);
				while($termObj = mysql_fetch_object($termQuery)){
					// ...that come after the first term
					$nextTerm = $nextTermYear * 100 + $termObj->TERM_ADDEND;
					if($nextTerm > $student->STU_START_TERM){
						$term[] = $termObj;
						$term[count($term)-1]->TERM_ID = $nextTerm;
					}	
				}
			}
		
	// D. The current state of the student's record (classes already earned)
		// 1) Update database with new data from the UI
			// For all COURSEs MET with POST data, INSERT a new record but "ON DUPLICATE KEY UPDATE"
			$insertQueryText = "INSERT INTO ADV_MET (STU_ID, CRS_ID, MET_TERM, MET_CR_TYPE) VALUES";
			foreach($_POST as $key=>$value){
				if(substr($key, 0, 3) == "MET"){
					$insertQueryText .= (substr($insertQueryText, -1)=="S"?"":",")." ('$student->STU_ID', '".$_POST["CRS_ID-".substr($key, 4)]."', '".$_POST["TERM-".substr($key, 4)]."', '".$_POST["CR_TYPE-".substr($key, 4)]."')";
				}
			}$insertQueryText .= " ON DUPLICATE KEY UPDATE MET_TERM=VALUES(MET_TERM), MET_CR_TYPE=VALUES(MET_CR_TYPE)";
			mysql_query($insertQueryText);
		
		// 2) Get records from the database
			$earned = NULL;
			$earnedQuery = mysql_query("SELECT * FROM ADV_EARNED E WHERE STU_ID=$student->STU_ID");
			while($earnedObj = mysql_fetch_object($earnedQuery)){
				$earned[] = $earnedObj;
			}
				
		// 3) Enumerate credit counts by type
			$creditCount = NULL;
			$creditCountQuery = mysql_query("SELECT ERN_CR_TYPE AS TYPE, SUM(ERN_CREDITS) AS SUM FROM ADV_EARNED WHERE STU_ID=1 GROUP BY ERN_CR_TYPE");
			while($creditCountObj = mysql_fetch_object($creditCountQuery)){
				$creditCount[$creditCountObj->TYPE] = $creditCountObj->SUM;
			}

// 2. Discover differences between requirements and what has been earned (modded if for associates)
	// A. Discover all major courses still unmet
		$unmetCourse = NULL;
		foreach($degreq as $req){
			$courseWasMet = FALSE;
			foreach($earned as $met){
				if($req == $met->CRS_ID) 
					$courseWasMet = TRUE;
			}if(!$courseWasMet)
				$unmetCourse[] = $req;
		}
		
	// B. Discover all unmet prerequisites for all unmet required courses
		// **concider making this recursive, to discover prereq chains?
		foreach($unmetCourse as $courseId){
			$prereqQuery = mysql_query("SELECT P.* FROM ADV_PREREQ P LEFT JOIN ADV_EARNED E ON P.PRQ_ID=E.CRS_ID AND E.STU_ID=$student->STU_ID WHERE P.CRS_ID='$courseId'");
			while($prereqObj = mysql_fetch_object($prereqQuery)){
				if(!isset($prereqObj->ERN_TERM))
					$unmetPrereq[] = $prereqObj;
			}
		}
		
	// C. Minimum total credit deficit
		$rule = (substr($student->DEG_ID, 0, 1) == "A") ? $AXIOM->MIN_ASSOCIATE_CREDITS->VALUE : $AXIOM->MIN_BACHELOR_CREDITS->VALUE;
		$totalCreditDeficit = $rule - ($creditCount["H"] + $creditCount["T"] + $creditCount["N"]);
		
	// D. Minimum overall residency credit deficit
		$rule = (substr($student->DEG_ID, 0, 1) == "A") ? $AXIOM->MIN_ASSOCIATE_RESIDENCY_OVERALL_CREDITS->VALUE : $AXIOM->MIN_BACHELOR_RESIDENCY_UPPER_CREDITS->VALUE;
		$overallResidencyCreditDeficit = $rule - ($creditCount["H"]);
		
	// E. Minimum major residency credit deficit
		// Needs a way to discren major courses, versus others other HPU
		$rule = (substr($student->DEG_ID, 0, 1) == "A") ? $AXIOM->MIN_ASSOCIATE_RESIDENCY_MAJOR_CREDITS->VALUE : $AXIOM->MIN_BACHELOR_RESIDENCY_MAJOR_CREDITS->VALUE;
		$majorResidencyCreditDeficit = $rule - ($creditCount["H"]);
		
	// F. Maximum non-traditional credits remaining
		// Needs a way to discren major courses, versus others other HPU
		$rule = $AXIOM->MAX_NON_TRADITIONAL_CREDITS->VALUE;
		$nontraditionalCreditsRemaining = $rule - ($creditCount["N"]);

// 3. Designate Course Priorities
	// A. *Maximize CLEPs
	// B. **Begin long prereq chains
	// C. **Courses that fill multiple requirements
	// D. ***Courses that aren't offered often
	// E. **Courses that are prereqs to many things 
	// F. Degree requirements before GenEd
	// G. Take maximum credits every term
	$priorityUnmetCourse = $unmetCourse;
	
// 4. Recursive Depth-Limited-Search for most effiecient path to graduation
	$degreePlan = planDegree(NULL, $term, $priorityUnmetCourse, $totalCreditDeficit, $overallResidencyCreditDeficit, $majorResidencyCreditDeficit, $nontraditionalCreditsRemaining, 0);
		
// 5. Update database with new degree plan

// 6. Propose the new degree plan to the user
	// header($location);

echo "student\n"
	."degreq\n"
	."gedreq\n"
	."AXIOM\n"
	."term\n"
	."earned\n"
	."creditCount\n"
	."unmetCourse\n"
	."unmetPrereq\n"
	."totalCreditDeficit\n"
	."overallResidencyCreditDeficit\n"
	."majorResidencyCreditDeficit\n"
	."nontraditionalCreditsRemaining\n"
	."degreePlan\n";

echo "\n\nstudent = "; var_dump($student);
echo "\n\ndegreq = "; var_dump($degreq);
echo "\n\ngedreq = "; var_dump($gedreq);
echo "\n\nAXIOM = "; var_dump($AXIOM);
echo "\n\nterm = "; var_dump($term);
echo "\n\nearned = "; var_dump($earned);
echo "\n\ncreditCount = "; var_dump($creditCount);
echo "\n\nunmetCourse = "; var_dump($unmetCourse);
echo "\n\nunmetPrereq = "; var_dump($unmetPrereq);
echo "\n\ntotalCreditDeficit = "; var_dump($totalCreditDeficit);
echo "\n\noverallResidencyCreditDeficit = "; var_dump($overallResidencyCreditDeficit);
echo "\n\nmajorResidencyCreditDeficit = "; var_dump($majorResidencyCreditDeficit);
echo "\n\nnontraditionalCreditsRemaining = "; var_dump($nontraditionalCreditsRemaining);
echo "\n\npriorityUnmetCourse = "; var_dump($priorityUnmetCourse);
echo "\n\ndegreePlan = "; var_dump($degreePlan);
?>