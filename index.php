<?php session_start();
//error_reporting(0);
error_reporting(E_ALL);
include("../../include_lib/connection.php");
include("../../include_lib/designVariables.php");
?>
<HTML>
<HEAD>
	<META HTTP-EQUIV="Content-Language" CONTENT="en-us">
	<META HTTP-EQUIV="Content-Type" CONTENT="text/php; charset=windows-1252">
	<META NAME="keywords" CONTENT="<?php echo $design_variables->homeKeywords ?>">
	<META NAME="description" CONTENT="<?php echo $design_variables->homeDescription ?>">
	<META NAME="author" CONTENT="Bryan Wolfford">
	<TITLE>An Intelligent Advisor</TITLE>
	<LINK REL="icon" HREF="../../images/favicon.gif" TYPE="image/gif">
	<LINK REL=stylesheet HREF="../../include_lib/default.css" TYPE="text/css">
</HEAD>

<?php include("../../include_lib/templateTop.php");
	// Remove the sidebar but keep midFullDiv
	//include("../../include_lib/templateLeft.php");
	echo "<DIV ID=\"midFullDiv\">\n";
	echo "<DIV ID=\"liveTwitterDiv\" STYLE=\"display:none\"></DIV>\n";

	// Get student's details forced to STU_ID=1 for now
	$forcedStuId = 1;
	$stuQuery = mysql_query("SELECT * FROM ADV_STUDENT WHERE STU_ID=$forcedStuId");
	$stuObj = mysql_fetch_object($stuQuery);
?>
	<DIV CLASS="commonTitle">An Intelligent Advisor</DIV>
	<FORM ID="advForm" ACTION="recalculate.php" METHOD="POST">
	<!-- Send STU_ID -->
	<INPUT TYPE="hidden" NAME="STU_ID" VALUE="<?php echo $forcedStuId ?>"/>
	<TABLE ID="advOptionTable">
		<TR><TD CLASS="advTableTitle"><?php echo "$stuObj->STU_FNAME $stuObj->STU_LNAME's"?> Calculation Options</TD></TR>
		<TR><TD CLASS="commonCenter">
			<!-- Making the degree plan require a submit button because of the anticipated resource demand by the search algorithm.-->
			<SELECT CLASS="commonInput" NAME="optDegId">
				<OPTION VALUE="">-- Please Select a Degree Plan --</OPTION>
				<?php // Creat new option for all available degrees
					$degQuery = mysql_query("SELECT * FROM ADV_DEGREE");
					while($degObj = mysql_fetch_object($degQuery)) echo "<OPTION VALUE=\"$degObj->DEG_ID\"".($degObj->DEG_ID == $stuObj->DEG_ID ? "SELECTED" : "").">$degObj->DEG_ID - $degObj->DEG_NAME</OPTION>\n";
				?>
			</SELECT>
		</TD></TR>
		<TR><TD CLASS="commonCenter">
			<!-- Making the degree plan require a submit button because of the anticipated resource demand by the search algorithm.-->
			<SELECT CLASS="commonInput" NAME="optSchedule">
				<OPTION VALUE="">-- Please Select a Schedule --</OPTION>
				<OPTION VALUE="MAIN" <?php echo $stuObj->STU_SCHED == "MAIN" ? "SELECTED" : "" ?>>Downtown/Hawai'i Loa</OPTION>
				<OPTION VALUE="MCP" <?php echo $stuObj->STU_SCHED == "MCP" ? "SELECTED" : "" ?>>Military Campus Terms</OPTION>
				<OPTION VALUE="COMBO" <?php echo $stuObj->STU_SCHED == "COMBO" ? "SELECTED" : "" ?>>Both/All Schedules</OPTION>
			</SELECT>
		</TD></TR>
		<TR><TD CLASS="commonLeft"><INPUT TYPE="checkbox" CLASS="advCheckbox" NAME="optMaxNTCred" CHECKED/> Maximize Non-Traditional Credits like CLEP/AP?</TD></TR>
		<!-- Later toggle extra sessions based on "optSchedule" choice -->
		<TR><TD CLASS="commonLeft"><INPUT TYPE="checkbox" CLASS="advCheckbox" NAME="optSummerAndWinter" <?php echo $stuObj->STU_SCHED_OPT ? "CHECKED" : "" ?>/> Utilize the Downtown Summer/Winter Terms?</TD></TR>
		<TR><TD CLASS="commonLeft"><INPUT TYPE="checkbox" CLASS="advCheckbox" NAME="optInterim" <?php echo $stuObj->STU_SCHED_OPT ? "CHECKED" : "" ?>/> Utilize the MCP Interim Periods?</TD></TR>
		<TR><TD CLASS="commonRight"><INPUT CLASS="commonInput" TYPE="SUBMIT" VALUE="Recalculate"></TD></TR>
	</TABLE>
	
	<TABLE ID="advGenreqTable">
		<TR><TD CLASS="advTableTitle" COLSPAN="5">General Education Requirements</TD></TR>
		<TR>
			<TD CLASS="advColHead">Required Theme</TD>
			<TD CLASS="advColHead">Course Id</TD>
			<TD CLASS="advColHead">Term Credited</TD>
			<TD CLASS="advColHead">Credit Type</TD>
		</TR>
<?php // Get the General Eduaction requirements (filtered)
	$gedreqQueryText = "SELECT GR.* FROM ADV_GEDREQ GR";
	// For associate degrees, filter from ADV_GEDREQ_ASSOC
	if(substr($stuObj->DEG_ID, 0, 1) == "A")
		$gedreqQueryText .= " INNER JOIN ADV_GEDREQ_ASSOC GRA ON (GRA.GED_ID=GR.GED_ID AND GRA.GED_CAT=GR.GED_CAT) WHERE GRA.DEG_ID='$stuObj->DEG_ID'";
	$gedreqQueryText .= " ORDER BY GED_ID, GED_CAT, CRS_ID";
	$gedreqQuery = mysql_query($gedreqQueryText);
	//echo "\n<div class=\"commonDebug\">$gedreqQueryText</div>\n";
	// Only print GEDREQ rows for unique GED_ID/CAT pairs
	while($gedreqObj = mysql_fetch_object($gedreqQuery)){
		$GED_ID = $gedreqObj->GED_ID;
		$GED_CAT = $gedreqObj->GED_CAT;
		$gedTheme->$GED_ID=$GED_CAT;// = $gedreqObj->CRS_ID;
	}//echo "\n<div class=\"commonDebug\">";var_dump($gedTheme);echo "</div>\n";
	foreach($gedTheme as $themeId=>$themeCat){
		echo "<TR>"
			."	<TD CLASS=\"advDegreq\" TITLE=\"$themeId - $themeCat\">$themeId - $themeCat</TD>\n"
			."	<TD CLASS=\"commonCenter\"><INPUT TYPE=\"text\" CLASS=\"commonInput\" NAME=\"CRS_ID-GEDREQ-$themeId-$themeCat\" WIDTH=\"\" VALUE=\"".(!empty($_GET["CRS_ID-GEDREQ-$themeId-$themeCat"])?$_GET["CRS_ID-GEDREQ-$themeId-$themeCat"]:"")."\"/></TD>\n"
			."	<TD CLASS=\"commonRight\"><INPUT TYPE=\"text\" CLASS=\"commonInput\" NAME=\"TERM-GEDREQ-$themeId-$themeCat\" WIDTH=\"\" VALUE=\"".(!empty($_GET["TERM-GEDREQ-$themeId-$themeCat"])?$_GET["TERM-GEDREQ-$themeId-$themeCat"]:"")."\"/></TD>\n"
			."	<TD CLASS=\"commonRight\">\n"
			."		<SELECT CLASS=\"commonInput\" NAME=\"CR_TYPE-GEDREQ-$themeId-$themeCat\">\n"
			."			<OPTION VALUE=\"\">--Credit Type--</OPTION>\n"
			."			<OPTION VALUE=\"H\"".(!empty($_GET["CR_TYPE-GEDREQ-$themeId-$themeCat"])&&$_GET["CR_TYPE-GEDREQ-$themeId-$themeCat"]=="H"?" SELECTED":"")."/>HPU Residency</OPTION>\n"
			."			<OPTION VALUE=\"T\"".(!empty($_GET["CR_TYPE-GEDREQ-$themeId-$themeCat"])?" SELECTED":"")."/>Transfer</OPTION>\n"
			."			<OPTION VALUE=\"N\"".(!empty($_GET["CR_TYPE-GEDREQ-$themeId-$themeCat"])?" SELECTED":"")."/>Non-Traditional</OPTION>\n"
			."		</SELECT>\n"
			."	</TD>\n"
			."</TR>\n";
	}
?>
		<TR><TD CLASS="commonRight" COLSPAN="5"><INPUT CLASS="commonInput" TYPE="SUBMIT" VALUE="Recalculate"></TD></TR>
	</TABLE>
	<?php /*
	<TABLE ID="advDegreqTable">
		<TR><TD CLASS="advTableTitle" COLSPAN="3">Major Requirements</TD></TR>
		<TR><TD CLASS="advColHead">Met?</TD><TD CLASS="advColHead">Required Course</TD><TD CLASS="advColHead">Term Id</TD></TR>
<?php // Get all the Degree Plan's Requirements
	$courseQuery = mysql_query("SELECT C.* FROM ADV_DEGPLN DP, ADV_DEGREQ DR, ADV_COURSE C WHERE DP.DEG_ID='$forcedDegId' AND DR.DEG_ID='$forcedDegId' AND C.CRS_ALPHA=DR.CRS_ALPHA AND C.CRS_NUM=DR.CRS_NUM ORDER BY C.CRS_ALPHA, C.CRS_NUM");
	$degreqCounter = 0;
	while($courseObj = mysql_fetch_object($courseQuery)){
		foreach($courseObj as $key=>$value) $_SESSION["degreeObj"]->DEGREQ->$degreqCounter->$key = $value;
		// Add all the Prereq information
		$prereqQuery = mysql_query("SELECT C.* FROM ADV_PREREQ P, ADV_COURSE C WHERE P.CRS_ALPHA='$courseObj->CRS_ALPHA' AND P.CRS_NUM=$courseObj->CRS_NUM AND C.CRS_ALPHA=P.PRQ_ALPHA AND C.CRS_NUM=P.PRQ_NUM ORDER BY C.CRS_ALPHA, C.CRS_NUM");
		$prereqCounter = 0; // Must be used to represent multi-key PREREQ refrences
		while($prereqObj = mysql_fetch_object($prereqQuery)){
			foreach($prereqObj as $key2=>$value2) $_SESSION["degreeObj"]->DEGREQ->$degreqCounter->PREREQ->$prereqCounter->$key2 = $value2;
			//$_SESSION["degreeObj"]->DEGREQ->$degreqCounter->MET->TERM=NULL;
			//echo "<TR><TD CLASS=\"commonCenter\"><INPUT TYPE=\"checkbox\" CLASS=\"advCheckbox\" NAME=\"DEGREQ-$courseObj->CRS_ALPHA$courseObj->CRS_NUM\"/></TD><TD CLASS=\"advPrereq\" TITLE=\"$prereqObj->CRS_NAME:\n$prereqObj->CRS_DESC\">$prereqObj->CRS_ALPHA $prereqObj->CRS_NUM</TD><TD CLASS=\"advPrereq\" TITLE=\"$prereqObj->CRS_NAME:\n$prereqObj->CRS_DESC\">201120</TD></TR>\n";
			$prereqCounter++;
		}
		//$_SESSION["degreeObj"]->DEGREQ->$degreqCounter->MET->TERM=NULL;
		echo "<TR>"
			."	<TD CLASS=\"commonCenter\"><INPUT TYPE=\"checkbox\" CLASS=\"advCheckbox\" NAME=\"DEGREQ-$courseObj->CRS_ALPHA$courseObj->CRS_NUM\"".($_GET["DEGREQ-$courseObj->CRS_ALPHA$courseObj->CRS_NUM"]=="on"?" CHECKED":"")."/></TD>\n"
			."	<TD CLASS=\"advDegreq\" TITLE=\"$courseObj->CRS_NAME:\n$courseObj->CRS_DESC\">$courseObj->CRS_ALPHA $courseObj->CRS_NUM</TD>\n"
			."	<TD CLASS=\"commonRight\"><INPUT TYPE=\"text\" CLASS=\"commonInput\" NAME=\"DEGREQ-$courseObj->CRS_ALPHA$courseObj->CRS_NUM-TERM\" WIDTH=\"\" VALUE=\"".(!empty($_GET["DEGREQ-$courseObj->CRS_ALPHA$courseObj->CRS_NUM-TERM"])?$_GET["DEGREQ-$courseObj->CRS_ALPHA$courseObj->CRS_NUM-TERM"]:"")."\"/></TD>\n"
			."</TR>\n";
		$degreqCounter++;
	}
?>
		<TR><TD CLASS="commonRight" COLSPAN="3"><INPUT CLASS="commonInput" TYPE="SUBMIT" VALUE="Recalculate"></TD></TR>
	</TABLE>
	</FORM>

<?php */include("../../include_lib/templateBottom.php") ?>