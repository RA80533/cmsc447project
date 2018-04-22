<?php
// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 3600);

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(3600);
session_start();

$allRows = "";

if (!isset($_SESSION["HAS_LOGGED_IN"])) {
    header('Location: login.php');
}


// IF THEY ARE LOGGED IN
if ($_SESSION["HAS_LOGGED_IN"]) {
    include '../utils/dbconfig.php';
	
    // Create meeting first and then add advisor to meeting ; DISPLAY MEETINGS
    // Create meeting form that lets the advisor create a meeting

    // Includes the form for creating meetings
    $advisorID = $_SESSION["ADVISOR_ID"];
	$scheduleView = $_SESSION["SCHEDULE_VIEW"];
	
	//Only time the user posts anything to this page is when they change to a separate schedule view
	
    $open_connection = connectToDB();
	
	
	//If the user clicks the button to show appointments only in the next week,
	//schedulView is changed
	if($_POST){ 
		$scheduleType = $_POST["scheduleType"];
		if($scheduleType == "Weekly View"){
			$scheduleView = true;
		}
		else{
			$scheduleView = false;
		}
	}
	
    $open_connection = connectToDB();
	
	if($scheduleView == false){
		$searchAdvisorMeetings = "
			SELECT 
				Advisor.AdvisorID, 
				Meeting.meetingID,
				Meeting.start, 
				Meeting.end,
				Meeting.buildingName, 
				Meeting.roomNumber,
				Meeting.meetingType,
				Meeting.maxStudents
			From 
				Advisor
			INNER JOIN 
				AdvisorMeeting ON Advisor.AdvisorID = AdvisorMeeting.AdvisorID
			INNER JOIN 
				Meeting ON AdvisorMeeting.MeetingID = Meeting.MeetingID 
			WHERE 
				Advisor.advisorID = '$advisorID'
			ORDER BY 
				DATE(Meeting.start) ASC,
				Meeting.start DESC
			";
	}
	//Used when viewing appointments within the next week only
	else{
		$dateT = new DateTime();
		$dateT->add(new DateInterval('P7D'));
		//$d = strtotime("+1 week");
		$newDate = $dateT->format("Y-m-d H:i:s");
		$searchAdvisorMeetings = "
			SELECT 
				Advisor.AdvisorID, 
				Meeting.meetingID,
				Meeting.start, 
				Meeting.end,
				Meeting.buildingName, 
				Meeting.roomNumber,
				Meeting.meetingType,
				Meeting.maxStudents
			From 
				Advisor
			INNER JOIN 
				AdvisorMeeting ON Advisor.AdvisorID = AdvisorMeeting.AdvisorID
			INNER JOIN 
				Meeting ON AdvisorMeeting.MeetingID = Meeting.MeetingID 
			WHERE 
				Advisor.advisorID = '$advisorID'
			AND
				Meeting.start <= '$newDate'
			ORDER BY 
				DATE(Meeting.start) ASC,
				Meeting.start DESC
		";
	}

    $searchResults = $open_connection->query($searchAdvisorMeetings);

    $allRows = array();
    while ($row = $searchResults->fetch_assoc()) {
        array_push($allRows, $row);
    }

    $open_connection->close();
}

/*
 * Returns an array of objects
 */
function findStudentsInMeeting($meetingID)
{
    $open_connection = connectToDB();

    $queryForStudent = "
        SELECT
          Student.StudentID,
          Student.email,
          Student.firstName,
          Student.lastName,
          Student.schoolID,
          Student.major
        FROM Student
          INNER JOIN
          StudentMeeting ON Student.StudentID = StudentMeeting.StudentID
          INNER JOIN
          Meeting ON StudentMeeting.MeetingID = Meeting.meetingID
        WHERE Meeting.meetingID = '$meetingID'
    ";

    $studentResults = $open_connection->query($queryForStudent);

    $studentInfos = array();

    while ($studentInfo = $studentResults->fetch_assoc()) {
        array_push($studentInfos, $studentInfo);
    }

    return $studentInfos;
}

function checkShutdownStatus(){
	
	$open_connection = connectToDB();

	//checks if any of the advisors have directed the website to shut down
	//$shutoff_val_query = "SELECT * FROM Advisor WHERE shutDown = 'TRUE'";
	$shutoff_val_query = "SELECT * FROM Advisor WHERE shutDown = 1";
	$shutoff_query = $open_connection->query($shutoff_val_query);
	
	
	$numRows = $shutoff_query->num_rows;
	//if the website is shut down on the student side, a banner is displayed on the homepage of the advisor side.
	//if(mysql_num_rows($shutoff_query) > 0){
	if($numRows > 0){
		return 1;
	}
	return 0;
}

?>
    <html>

    <head>
        <title>Advisor Homepage</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@0.6.0/build/pure-min.css">
        <link rel="stylesheet" href="css/homepage.css">
    </head>

    <body>


        <div id='banner' class='shutdownBanner' <?php $bol=checkShutdownStatus(); if ($bol==1){ echo ( " "); } else { echo ( "hidden"); } ?>
            >
            <!--<marquee direction="right" loop="1" scrollamount="20">-->The Student Use of Advising Scheduling is currently unavailable
            <!--</marquee>-->
        </div>
        <div id="homepage">
            <div>
                <?php if ($_SESSION["HAS_LOGGED_IN"]) { ?>
                    <h1>
                Welcome <?php echo htmlspecialchars($_SESSION["ADVISOR_FNAME"]); ?>, here are your meetings.
            </h1>
                    <div id="options">
                        <button id="settings" onclick=modalstuff() class="pure-button">
                            <i class="fa fa-cog"></i> Settings
                        </button>
                        <a href="logout.php">
                            <button type="button" class="button-error pure-button"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</button>
                        </a>

                        <div id="modalSetting" class="modal">
                            <div class="modal-content">
                                <h2>Options</h2>
                                <h4>Edit Your Information <i class="fa fa-pencil editInfo" onclick="enableEdit(this)" id="editIcon" aria-hidden="true"></i></h4>
                                <form class="pure-form pure-form-aligned" action="../utils/forms/editAdvisor.php" method="post">
                                    <fieldset>
                                        <div class="pure-control-group">
                                            <label for="fName">First Name </label>
                                            <p class="settingText">
                                                <?php echo htmlspecialchars($_SESSION["ADVISOR_FNAME"]); ?>
                                            </p>
                                            <input id="fName" class="editinput" pattern="^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð,.'-]+$" type="text" name="fName" value="<?php echo htmlspecialchars($_SESSION['ADVISOR_FNAME']); ?>" hidden>
                                        </div>

                                        <div class="pure-control-group">
                                            <label for="lName">Last Name </label>
                                            <p class="settingText">
                                                <?php echo htmlspecialchars($_SESSION["ADVISOR_LNAME"]); ?>
                                            </p>
                                            <input id="lName" class="editinput" pattern="^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð,.'-]+$" type="text" name="lName" value="<?php echo htmlspecialchars($_SESSION['ADVISOR_LNAME']); ?>" hidden> </div>

                                        <div class="pure-control-group">
                                            <label for="email">Email </label>
                                            <p class="settingText">
                                                <?php echo htmlspecialchars($_SESSION["ADVISOR_EMAIL"]);?>
                                            </p>
                                            <input id="email" class="editinput" pattern="^[a-zA-Z0-9._-]+@umbc.edu$" type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['ADVISOR_EMAIL']);?>" hidden>
                                        </div>

                                        <div class="pure-control-group">
                                            <label for="bldgName">Office Building Name </label>
                                            <p class="settingText">
                                                <?php echo htmlspecialchars($_SESSION["ADVISOR_BLDG_NAME"]); ?>
                                            </p>
                                            <input type="text" class="editinput" id="bldgName" name="bldgName" value="<?php echo htmlspecialchars($_SESSION['ADVISOR_BLDG_NAME']); ?>" hidden> </div>

                                        <div class="pure-control-group">
                                            <label for="officeRm">Office Room </label>
                                            <p class="settingText">
                                                <?php echo htmlspecialchars($_SESSION["ADVISOR_RM_NUM"]); ?>
                                            </p>
                                            <input type="text" id="officeRm" class="editinput" name="officeRm" value="<?php echo htmlspecialchars($_SESSION['ADVISOR_RM_NUM']); ?>" hidden> </div>
                                        <div id="chngepass">
                                            <span id="chngepasstext" class="editinput" onclick="enableChangePass(this)" hidden>Change Password</span>
                                        </div>
                                        <div class="pure-control-group">
                                            <label for="password" class="editpass" hidden>Old Password </label>
                                            <input type="password" class="editpass" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$" id="oldpassword" name="oldpassword" value="" hidden>
                                        </div>
                                        <div class="pure-control-group">
                                            <label for="password" class="editpass" hidden>New Password </label>
                                            <input type="password" class="editpass" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$" id="password" name="password" value="" hidden>
                                        </div>

                                        <div class="pure-control-group">
                                            <label for="confirm_password" class="editpass" hidden>Confirm New Password </label>
                                            <input type="password" class="editpass" placeholder="Confirm Password" id="confirm_password" name="confirm_password" value="" hidden>
                                        </div>

                                        <div class="pure-controls">
                                            <button type="submit" class="editinput pure-button pure-button-primary" hidden>Confirm</button>
                                        </div>
                                    </fieldset>
                                </form>
                                <span id="closeSetting" onclick=closeSetting() class="close button-error pure-button">Cancel <i class="fa fa-times" aria-hidden="true"></i></span>
                                <div id="shutdownBtn">
                                    <?php                   
				$bol=checkShutdownStatus(); 
				if ($bol==0){  
				    echo("<Button type='submit' class='pure-button' name='ToggleShutdown' onclick=shutoff()><i class='fa fa-power-off' aria-hidden='true'></i> Shutdown Website</Button>");
					
				}else { 
					echo("<Button type='submit' class='pure-button' name='ToggleShutdown' onclick=turnon()><i class='fa fa-power-off' aria-hidden='true'></i> Turn On Website</Button>");
				} 
				
				//when scheduleView = false, homepage will show all appointments
				//when scheudleView = true, homepage will show appointments only within the next week
				//By pressing the following button, the user can toggle between the two views
                ?>


                                </div>
                            </div>
                            <div class="space"></div>
                        </div>

                    </div>
                    <div id="week">
                        <div class='sortmeeting'>
                            <button class='pure-button pure-u-1' onclick="openViewAll()">View all Advisor Meeting</button>
                            <!-- The Modal -->

                        </div>
                        <div class='sortmeeting'>
                            <form action='homepage.php' method='POST'>
                                <button type='submit' class='sortmeeting pure-button pure-u-1' name='scheduleType' value="<?php if($scheduleView == false){echo('Weekly View"> Next 7 days');}else{echo('All"> View All Meetings');} ?></button>
                            </form>
                        </div>
                        <div class='sortmeeting'>
                            <a href="#CreateMeeting">
                                <button class='pure-button pure-u-1'>Create Meeting</button>
                            </a>
                        </div>
                    </div>
            </div>
            <div id="allmeeting">

                <?php $count=0;
                      if($allRows == null){
                    ?>
                    <h3>You do no have any meetings</h3>
                    <h4>Please create one below</h4>
                    <?php }
                      foreach ($allRows as $aRow) { 
                          $count +=1;
                     ?>
                        <div id="meeting">
                            <header>
                                <h3>Meeting <?php echo $count; ?> </h3>
                                <!--
                                <span class="editButton" onclick="openEditMeeting(this)" id="<?php echo $count; ?>"> Edit <?php echo htmlspecialchars($aRow["meetingID"]); ?>
                               <i class="fa fa-pencil" aria-hidden="true"></i>
                                </span>
                                <!-- NOTE add modal back here and fix it
                                <!-- The Modal
                                <div class="modalEdit">
                                    <!-- Modal content
                                    <div class="modalEdit-content">
                                        <div class="modalEdit-header">
                                            <span class="close">&times;</span>
                                            <h2>Edit Meeting <?php echo htmlspecialchars($aRow["meetingID"]); ?></h2>
                                        </div>
                                        <div class="modaledit-body">

                                        </div>
                                    </div>

                                </div> -->

                            </header>
                            <div id="meetingInfo">
                                <table class="pure-table pure-table-horizontal pure-u-1">
                                    <thead class="pure-u-1">
                                        <tr class="pure-u-1">
                                            <!-- Will need to use this value for selecting future values -->
                                            <th class="pure-u-1-4">
                                                ID:
                                            </th>
                                            <td class="pure-u-5-8">
                                                <?php echo htmlspecialchars($aRow["meetingID"]); ?>
                                            </td>
                                        </tr>
                                        <tr class="pure-u-1">
                                            <th class="pure-u-1-4">
                                                Date:
                                            </th>
                                            <td class="pure-u-5-8">
                                                <?php $d=strtotime(htmlspecialchars($aRow["start"]));
                                                   echo date("l, F d, Y", $d);
                                        ?>
                                            </td>
                                        </tr>
                                        <tr class="pure-u-1">
                                            <th class="pure-u-1-4">
                                                Start:
                                            </th>
                                            <td class="pure-u-5-8">
                                                <?php $d=strtotime(htmlspecialchars($aRow["start"]));
                                                   echo date("h:i A", $d);
                                        ?>
                                            </td>
                                        </tr>
                                        <tr class="pure-u-1">
                                            <th class="pure-u-1-4">
                                                End:
                                            </th>
                                            <td class="pure-u-5-8">
                                                <?php $d=strtotime(htmlspecialchars($aRow["end"]));
                                                   echo date("h:i A", $d);
                                        ?>
                                            </td>
                                        </tr>
                                        <tr class="pure-u-1">
                                            <th class="pure-u-1-4">
                                                Building Name:
                                            </th>
                                            <td class="pure-u-5-8">
                                                <?php echo htmlspecialchars($aRow["buildingName"]) ?>
                                            </td>
                                        </tr>
                                        <tr class="pure-u-1">
                                            <th class="pure-u-1-4">
                                                Room Number:
                                            </th>
                                            <td class="pure-u-5-8">
                                                <?php echo htmlspecialchars($aRow["roomNumber"]) ?>
                                            </td>
                                        </tr>
                                        <tr class="pure-u-1">
                                            <th class="pure-u-1-4">
                                                Meeting Type:
                                            </th>
                                            <td class="pure-u-5-8">
                                                <?php
                                            if ($aRow["meetingType"] == 0) {
                                                echo htmlspecialchars("Individual");
                                            } else {
                                                echo htmlspecialchars("Group");
                                            }
                                        ?>
                                            </td>
                                        </tr>
                                        <tr class="pure-u-1">
                                            <th class="pure-u-1-4">
                                                Max Students:
                                            </th>
                                            <td class="pure-u-5-8">
                                                <?php echo htmlspecialchars($aRow["maxStudents"]) ?>
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div id="studentInfo">
                                <h4>Students in Meeting</h4>
                                <table class="pure-table pure-table-horizontal pure-u-1">
                                    <thead class="pure-u-1">
                                        <tr class="pure-u-1">
                                            <th class="pure-u-1-24">ID</th>
                                            <th class="pure-u-1-8">Email</th>
                                            <th class="pure-u-1-8">First Name</th>
                                            <th class="pure-u-1-8">Last Name</th>
                                            <th class="pure-u-1-8">Student ID</th>
                                            <th class="pure-u-1-8">Major</th>
                                            <th class="pure-u-1-24">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody class="pure-u-1">
                                        <?php
            $studentsInfo = findStudentsInMeeting($aRow["meetingID"]);
			?>

                                            <?php
            foreach ($studentsInfo as $studentInfo) { ?>
                                                <tr class="pure-u-1">
                                                    <td class="pure-u-1-24">
                                                        <?php echo htmlspecialchars($studentInfo["StudentID"]) ?>
                                                    </td>
                                                    <td class="pure-u-1-8">
                                                        <?php echo htmlspecialchars($studentInfo["email"]) ?>
                                                    </td>
                                                    <td class="pure-u-1-8">
                                                        <?php echo htmlspecialchars($studentInfo["firstName"]) ?>
                                                    </td>
                                                    <td class="pure-u-1-8">
                                                        <?php echo htmlspecialchars($studentInfo["lastName"]) ?>
                                                    </td>
                                                    <td class="pure-u-1-8">
                                                        <?php echo htmlspecialchars($studentInfo["schoolID"]) ?>
                                                    </td>
                                                    <td class="pure-u-1-8">
                                                        <?php echo htmlspecialchars($studentInfo["major"]) ?>
                                                    </td>
                                                    <td class="pure-u-1-24">
                                                        <form action="../utils/forms/removeStudent.php" onsubmit="return confirm('Do you really want to remove <?php echo htmlspecialchars($studentInfo['firstName']).' '.htmlspecialchars($studentInfo['lastName']) ?> from the meeting?'); " method="POST">
                                                            <button class="button-error button-xsmall pure-button" name="removeStudent" type="submit" value="<?php echo htmlspecialchars($studentInfo[ 'StudentID']) ?>">
                                                                <i class="fa fa-times" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <br>
                            <form action="../utils/forms/deleteMeeting.php" onsubmit="return confirm('Do you really want to remove your <?php echo htmlspecialchars($aRow['start']); ?> meeting ?')" method="POST">
                                <input name="meetingID" value="<?php echo htmlspecialchars($aRow['meetingID']); ?>" hidden>
                                <Button type="submit" class="button-error pure-button"><i class="fa fa-trash" aria-hidden="true"></i> Delete Meeting</Button>
                            </form>

                        </div>
                        <hr>
                        <?php } ?>
                            <div id="create">
                                <a name="CreateMeeting"></a>
                                <form class="pure-form pure-form-aligned" action="../utils/forms/createMeeting.php" method="POST">
                                    <fieldset>
                                        <legend>Create a Meeting</legend>
                                        <div class="pure-control-group">
                                            <label for="date">Meeting Start Date</label>
                                            <?php
                                $d = date("Y-m-d") . "T" . date("h:i:00"); 
                                echo ("<input type='datetime-local' id='date' min=$d name='meetingStartTime' required>");
                                ?>
                                                <?php
                                if (isset($_SESSION["ERROR_ADVISOR_MEETING_DATE_OR_TIME"])) {
                                    echo $_SESSION["ERROR_ADVISOR_MEETING_DATE_OR_TIME"];
                                    unset($_SESSION["ERROR_ADVISOR_MEETING_DATE_OR_TIME"]);
                                }
                                            ?>
                                        </div>
                                        <div class="pure-control-group">
                                            <label>
                                                Meeting Type
                                            </label>
                                            <div class="pure-u-1-24">
                                                <label for="indiv" class="pure-radio">
                                                    <input type="radio" onclick="numGroupSelect(this)" name="meetingType" id="indiv" value="individual"> Individual
                                                </label>
                                                <label for="group" class="pure-radio">
                                                    <input type="radio" onclick="numGroupSelect(this)" name="meetingType" id="group" value="group"> Group
                                                </label>
                                            </div>
                                        </div>
                                        <div class="pure-control-group" id="ifGroup" style="display:none">
                                            <label for="numStudents">How many students? </label>
                                            <input id="numStudents" type="number" class="pure-u-6-24" name="groupNum" value="4" min="2" required>
                                        </div>
                                        <div class="pure-control-group">
                                            <label for="buildingName">Building Name </label>
                                            <input type="text" id="buildingName" class="pure-u-6-24" name="buildingName" required>

                                            <?php
                                    if (isset($_SESSION["ERROR_ADVISOR_MEETING_BUILDING"])) {
                                        echo $_SESSION["ERROR_ADVISOR_MEETING_BUILDING"];
                                        unset($_SESSION["ERROR_ADVISOR_MEETING_BUILDING"]);
                                    }
                                ?>
                                        </div>
                                        <div class="pure-control-group">
                                            <label for="roomNumber">Room Number </label>
                                            <input type="text" name="roomNumber" class="pure-u-6-24" id="roomNumber" required>


                                            <?php
                                        if (isset($_SESSION["ERROR_ADVISOR_MEETING_ROOM"])) {
                                            echo $_SESSION["ERROR_ADVISOR_MEETING_ROOM"];
                                            unset($_SESSION["ERROR_ADVISOR_MEETING_ROOM"]);
                                        }
                                    ?>
                                        </div>
                                        <div class="pure-control-group">
                                            <label for="CreateMeeting"></label>
                                            <button type="submit" class="pure-button pure-u-6-24 pure-button-primary" id="CreateMeeting">Create Meeting</button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            <?php } ?>
            </div>
        </div>
        <div class="space">


        </div>
        <div id="modalViewAll" class="modalViewAll">
            <!-- Modal content -->
            <div class="modalViewAll-content">
                <span class="closeViewAll">&times;</span>
                <h2>All Advisor Meeting</h2>
                <div class="modaledit-body">
                    <!-- NOTE end of view all -->
                    <?php
                                                       $open_connection = connectToDB();
                                                       $sAdvisorMeetings = "
		SELECT 
			Advisor.firstName, 
			Advisor.lastName, 
			Advisor.AdvisorID, 
			Meeting.meetingID,
			Meeting.start, 
			Meeting.end,
			Meeting.buildingName, 
			Meeting.roomNumber,
			Meeting.meetingType,
			Meeting.maxStudents, 
			Meeting.numStudents 
		FROM 
			Advisor 
		INNER JOIN 
			AdvisorMeeting ON Advisor.AdvisorID = AdvisorMeeting.AdvisorID 
		INNER JOIN 
			Meeting ON AdvisorMeeting.MeetingID = Meeting.MeetingID 
			";
	
	//Used when viewing appointments within the next week onl\
	$sResults = $open_connection->query($sAdvisorMeetings);

    $listAllRows = array();
    while ($row = $sResults->fetch_assoc()) {
        array_push($listAllRows, $row);
    }
    $open_connection->close();


/*
 * Returns an array of objects
 */
function findStudentsInMeet($meetingID)
{
    $open_connection = connectToDB();

    $qForStudent = "
        SELECT
          Student.StudentID,
          Student.email,
          Student.firstName,
          Student.lastName,
          Student.schoolID,
          Student.major
        FROM Student
          INNER JOIN
          StudentMeeting ON Student.StudentID = StudentMeeting.StudentID
          INNER JOIN
          Meeting ON StudentMeeting.MeetingID = Meeting.meetingID
        WHERE Meeting.meetingID = '$meetingID'
    ";

    $sResults = $open_connection->query($qForStudent);

    $sInfos = array();

    while ($sInfo = $sResults->fetch_assoc()) {
        array_push($sInfos, $sInfo);
    }

    return $sInfos;
}

?>
                        <h1> Here are all meetings. </h1>
                        <br>
                        <?php foreach ($listAllRows as $aRow) { ?>

                            <div id="allmeeting1">
                                <header>
                                    <h3>Meeting </h3>
                                </header>
                                <div id="meetingInfo1">
                                    <table class="pure-table pure-table-horizontal pure-u-1">
                                        <thead class="pure-u-1">
                                            <tr class="pure-u-1">
                                                <!-- Will need to use this value for selecting future values -->
                                                <th class="pure-u-1-4">
                                                    ID:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php echo htmlspecialchars($aRow["meetingID"]); ?>
                                                </td>
                                            </tr>
                                            <tr class="pure-u-1">
                                                <!-- Will need to use this value for selecting future values -->
                                                <th class="pure-u-1-4">
                                                    Advisor Name:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php echo htmlspecialchars($aRow["firstName"]);
                                           echo(" "); 
                                           echo htmlspecialchars($aRow["lastName"]);
                                ?>
                                                </td>
                                            </tr>
                                            <tr class="pure-u-1">
                                                <th class="pure-u-1-4">
                                                    Date:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php $d=strtotime(htmlspecialchars($aRow["start"]));
                                                   echo date("l, F d, Y", $d);
                                        ?>
                                                </td>
                                            </tr>
                                            <tr class="pure-u-1">
                                                <th class="pure-u-1-4">
                                                    Start:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php $d=strtotime(htmlspecialchars($aRow["start"]));
                                                   echo date("h:i A", $d);
                                        ?>
                                                </td>
                                            </tr>
                                            <tr class="pure-u-1">
                                                <th class="pure-u-1-4">
                                                    End:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php $d=strtotime(htmlspecialchars($aRow["end"]));
                                                   echo date("h:i A", $d);
                                        ?>
                                                </td>
                                            </tr>
                                            <tr class="pure-u-1">
                                                <th class="pure-u-1-4">
                                                    Building Name:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php echo htmlspecialchars($aRow["buildingName"]) ?>
                                                </td>
                                            </tr>
                                            <tr class="pure-u-1">
                                                <th class="pure-u-1-4">
                                                    Room Number:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php echo htmlspecialchars($aRow["roomNumber"]) ?>
                                                </td>
                                            </tr>
                                            <tr class="pure-u-1">
                                                <th class="pure-u-1-4">
                                                    Meeting Type:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php
                                            if ($aRow["meetingType"] == 0) {
                                                echo htmlspecialchars("Individual");
                                            } else {
                                                echo htmlspecialchars("Group");
                                            }
                                        ?>
                                                </td>
                                            </tr>
                                            <tr class="pure-u-1">
                                                <th class="pure-u-1-4">
                                                    Max Students:
                                                </th>
                                                <td class="pure-u-5-8">
                                                    <?php echo htmlspecialchars($aRow["maxStudents"]) ?>
                                                </td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div id="studentInfo1">
                                    <h4>Students in Meeting</h4>
                                    <table class="pure-table pure-table-horizontal pure-u-1">
                                        <thead class="pure-u-1">
                                            <tr class="pure-u-1">
                                                <th class="pure-u-1-24">ID</th>
                                                <th class="pure-u-1-8">Email</th>
                                                <th class="pure-u-1-8">First Name</th>
                                                <th class="pure-u-1-8">Last Name</th>
                                                <th class="pure-u-1-8">Student ID</th>
                                                <th class="pure-u-1-8">Major</th>
                                                <th class="pure-u-1-24">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody class="pure-u-1">
                                            <?php
            $sInfos = findStudentsInMeet($aRow["meetingID"]);
			?>
                                                <?php
            foreach ($sInfos as $studentInfo) { ?>
                                                    <tr class="pure-u-1">
                                                        <td class="pure-u-1-24">
                                                            <?php echo htmlspecialchars($studentInfo["StudentID"]) ?>
                                                        </td>
                                                        <td class="pure-u-1-8">
                                                            <?php echo htmlspecialchars($studentInfo["email"]) ?>
                                                        </td>
                                                        <td class="pure-u-1-8">
                                                            <?php echo htmlspecialchars($studentInfo["firstName"]) ?>
                                                        </td>
                                                        <td class="pure-u-1-8">
                                                            <?php echo htmlspecialchars($studentInfo["lastName"]) ?>
                                                        </td>
                                                        <td class="pure-u-1-8">
                                                            <?php echo htmlspecialchars($studentInfo["schoolID"]) ?>
                                                        </td>
                                                        <td class="pure-u-1-8">
                                                            <?php echo htmlspecialchars($studentInfo["major"]) ?>
                                                        </td>
                                                        <td class="pure-u-1-24">
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <br>

                            </div>
                            <?php } ?>
                                <!-- NOTE end of view -->
                </div>
            </div>
        </div>
        <script src="js/homepage.js"></script>
        <script src="https://use.fontawesome.com/20f213b85b.js"></script>
        </div>
    </body>

    </html>