<?php
session_start();
include 'db_connection.php'; // your DB connection logic

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$userID = $_SESSION['user_id'];
$papsID = $_GET['papsID'] ?? null;

if (!$papsID) {
    die("No PAP specified.");
}

// Check if user is certified for this PAP
$certStmt = $mysqli->prepare("SELECT * FROM certification WHERE userID = ? AND papsID = ? LIMIT 1");
$certStmt->bind_param("ss", $userID, $papsID);
$certStmt->execute();
$certResult = $certStmt->get_result();

if ($certResult && $certResult->num_rows === 1) {
    // Now fetch the project title from the paps table
    $papsStmt = $mysqli->prepare("SELECT title FROM paps WHERE papsID = ? LIMIT 1");
    $papsStmt->bind_param("s", $papsID);
    $papsStmt->execute();
    $papsResult = $papsStmt->get_result();

    if ($papsResult && $papsResult->num_rows === 1) {
        $papsTitle = $papsResult->fetch_assoc()['title'];
    } else {
        die("PAP not found.");
    }

    // Fetch user full name (assuming from EndUser table)
    $userStmt = $mysqli->prepare("SELECT fname, lname FROM EndUser WHERE userID = ? LIMIT 1");
    $userStmt->bind_param("s", $userID);
    $userStmt->execute();
    $userRes = $userStmt->get_result();
    if ($userRes && $userRes->num_rows === 1) {
        $userData = $userRes->fetch_assoc();
        $fullName = $userData['fname'] . ' ' . $userData['lname'];
    } else {
        $fullName = "Unknown User";
    }

} else {
    die("This PAP is not certified for you.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="globalstyles.css">
    <link rel="stylesheet" href="certificate.css">
    <link rel="stylesheet" href="endUser.css">
    <title>GAD Management Information System - Certificate</title>
</head>

<body style="overflow:hidden; margin:0; padding:0; box-sizing:border-box; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color:#f8fafc; color:#334155; line-height:1.5;">
    <div class="container" style="margin:0 auto; padding:20px; box-sizing:border-box;">
        <div class="header">
              <div class="logo">
                <div class="logo-icon">
                    <img src="img/logo.svg" alt="GAD Logo" width="80" height="80">
                </div>
        </div>
            <div class="header-actions">
                <div class="header-icon-container">
                    <button class="icon-button" title="Evaluator">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="header-icon-text"></span>
                </div>
                <div class="header-icon-container">
                    <button class="icon-button" id="notification-button" title="Notifications">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="header-icon-text"></span>
                </div>
                <div class="header-icon-container">
                    <button class="icon-button" id="menu-button" title="Log out">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 17L21 12L16 7" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12H9" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="header-icon-text"></span>
                </div>
            </div>
        </div>

        <div class="evaluator-modal" id="evaluator-modal">
        <div class="evaluator-modal-content">
            <div class="evaluator-options">
            <a href="user-profile.php" class="evaluator-option" id="edit-profile-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.5 2.50001C18.8978 2.10219 19.4374 1.87869 20 1.87869C20.5626 1.87869 21.1022 2.10219 21.5 2.50001C21.8978 2.89784 22.1213 3.4374 22.1213 4.00001C22.1213 4.56262 21.8978 5.10219 21.5 5.50001L12 15L8 16L9 12L18.5 2.50001Z" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Edit profile
                </a>
            </div>
        </div>
    </div>

    <div class="menu-popup" id="menu-popup">
        <div class="menu-item" id="logout-btn">Log out</div>
    </div>
    
    <div class="notification-popup" id="notification-popup">
        <div class="notification-header">
            <div class="notification-title">Notifications</div>
            <button class="mark-read" id="mark-read-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 6L9 17L4 12" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Mark all as read
            </button>
        </div>
        <div class="notification-list" id="notification-list">
            <div class="notification-item">
                <p class="notification-text"></p>
                <a class="notification-link"></a>
            </div>
            <div class="notification-item">
                <p class="notification-text"></p>
                <a class="notification-link"></a>
            </div>
            <div class="notification-item">
                <p class="notification-text"></p>
                <a class="notification-link"></a>
            </div>
        </div>
        <div class="empty-notifications" id="empty-notifications">
            No new notifications
        </div>
    </div>
    
    <div class="logout-modal" id="logout-modal">
        <div class="logout-modal-icon">
            <svg width="40" height="40" viewBox="0 0 s40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 35H8.33333C7.44928 35 6.60143 34.6488 5.97631 34.0237C5.35119 33.3986 5 32.5507 5 31.6667V8.33333C5 7.44928 5.35119 6.60143 5.97631 5.97631C6.60143 5.35119 7.44928 5 8.33333 5H15" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M26.6667 28.3333L35 20L26.6667 11.6667" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M35 20H15" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="logout-modal-title">Confirm Logout</h3>
        <p class="logout-modal-text">Are you sure you want to log out?</p>
        <div class="logout-modal-buttons">
            <button class="logout-cancel-btn" id="logout-cancel-btn">Cancel</button>
            <button class="logout-confirm-btn" id="logout-confirm-btn">Log Out</button>
        </div>
    </div>
    <div class="overlay" id="overlay"></div>


    <div class="certificate-container">
        <div class="certificate" id="certificate">
            <div class="watermark">APPROVED</div>
            
            <div class="certificate-content">
                <div class="university-logo">
                <img src="usep-logo.png" alt="University Logo" style="width: 80px; height: 80px; border-radius: 50%;">
                </div>
                
                <div class="university-name">University of Southeastern Philippines</div>
                <div class="university-details">
                    Republic of the Philippines<br>
                    Iñigo Street, Bo. Obrero, Davao City, Davao Del Sur, 8000 +6382 227 8192<br>
                    www.usep.edu.ph
                </div>

                <div class="certificate-title">CERTIFICATION</div>

                <div class="certificate-content" style="text-align: center;">
                    This is to certify that the proposed project entitled <span class="project-title">"<?php echo htmlspecialchars($papsTitle); ?>"</span> has been reviewed and evaluated using the Gender and Development (GAD) Scorecard and has satisfactorily met the criteria for gender-responsiveness.
                    <br><br>
                    Accordingly, the project is hereby certified as gender-sensitive and has passed the GAD test, demonstrating alignment with the principles of gender equality and inclusivity in its planning, implementation, and expected outcomes.
                </div>

                <div class="date-info" style="text-align: center;">
                    Date: <span id="currentDate"></span>
                </div>

                <div class="signature-section">
                    <div class="signature-box" style="text-align: center;">
                        <div class="signature-text">APPROVED BY: GAD</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">Authorized Officer, MC</div>
                    </div>
                </div>

            </div>
        </div>

    <script src="endUbaseUI.js"></script>
    <script>
        // Set current date
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        function downloadCertificate() {
            const printWindow = window.open('', '_blank');
            const certificateContent = document.getElementById('certificate').outerHTML;
            
        }
    </script>
</body>
</html>