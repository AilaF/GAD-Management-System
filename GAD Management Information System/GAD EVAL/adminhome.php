<?php
session_start(); // Start the session
// Check if the session variable is set
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Unauthorized access");
    exit();
}

// Retrieve adminID (now stored as user_id in session)
$adminID = $_SESSION['user_id']; 

$_SESSION['adminID'] = $adminID; // Explicitly set adminID in session

if (isset($_SESSION['adminID'])) {
    $adminID = $_SESSION['adminID'];
    $adminTooltip = htmlspecialchars($adminID);
} else {
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="header-admin.css">
    <link rel="stylesheet" href="globalstyles.css">
    <title>GAD Management Information System</title>


</head>
<body style="overflow:hidden; margin:0; padding:0; box-sizing:border-box; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color:#f8fafc; color:#334155; line-height:1.5;">
    <div class="container" style="margin:0 auto; padding:20px; box-sizing:border-box;">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; border-radius: 12px;">
            <div class="logo">
                <div class="logo-icon">
                    <img src="img/logo.svg" alt="GAD Logo" width="80" height="80">
                </div>
            </div> 
             
             <nav>
                <ul class="nav-menu">
                    <li><a href="#" class="nav-item active">Dashboard</a></li>
                    <li><a href="admin-usrmgt.php" class="nav-item">Manage users</a></li>
                    <li><a href="admin-papseval.html" class="nav-item">Track PAPs</a></li>
                    <li><a href="adminscoresheet.php" class="nav-item">Scoresheet</a></li>
                </ul>
            </nav>
          <div class="header-actions" style="display: flex; gap: 8px;">
            <div class="header-icon-container" style="display: flex; align-items: center; gap: 6px;">
                    <button class="icon-button" style="position: relative;" data-tooltip="<?php echo $adminTooltip; ?>" onmouseover="this.querySelector('.tooltip').style.display='block'" onmouseout="this.querySelector('.tooltip').style.display='none'">
                        <div class="tooltip" style="display: none; position: absolute; top: 100%; left: 50%; transform: translateX(-50%); background-color: rgba(0, 0, 0, 0.8); color: white; padding: 6px 8px; border-radius: 4px; font-size: 11px; white-space: nowrap; z-index: 1000; margin-top: 5px; pointer-events: none;"><?php echo $adminTooltip; ?></div>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
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
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 35H8.33333C7.44928 35 6.60143 34.6488 5.97631 34.0237C5.35119 33.3986 5.32507 33 5 31.6667V8.33333C5 7.44928 5.35119 6.60143 5.97631 5.97631C6.60143 5.35119 7.44928 5 8.33333 5H15" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M26.6667 28.3333L35 20L26.6667 11.6667" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M35 20H15" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="logout-modal-title">Confirm Logout</h3>
            <p class="logout-modal-text">Are you sure you want to log out?</p>
            <div class="logout-modal-buttons">
                <button class="logout-cancel-btn" id="logout-cancel-btn">Cancel</button>
                <a href="logout.php" class="logout-confirm-btn" id="logout-confirm-btn">Log Out</a>
            </div>
        </div>
                
        <div class="overlay" id="overlay"></div>


        <main style="max-width: 1500px; width: 100%; margin: 0 auto;">
        <div style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; height: 80vh; min-height: 650px;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 80px; width: 100%; margin-top: 60px;">

                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; align-items: center; gap: 24px; padding: 32px 40px; border-radius: 16px; background: #fafafa; min-height: 80px; min-width: 300px;">
                        <div style="width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0; background: #fbbf24;"></div>
                        <div style="font-size: 20px; font-weight: 500; color: #374151; flex: 1;">Pending</div>
                        <div style="font-size: 36px; font-weight: 700; color: #111827; margin-right: 16px;">28</div>
                        <div style="font-size: 20px; color: #6b7280;">62.5%</div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 24px; padding: 32px 40px; border-radius: 16px; background: #fafafa; min-height: 80px; min-width: 300px;">
                        <div style="width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0; background: #f87171;"></div>
                        <div style="font-size: 20px; font-weight: 500; color: #374151; flex: 1;">For correction</div>
                        <div style="font-size: 36px; font-weight: 700; color: #111827; margin-right: 16px;">12</div>
                        <div style="font-size: 20px; color: #6b7280;">25%</div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 24px; padding: 32px 40px; border-radius: 16px; background: #fafafa; min-height: 80px; min-width: 300px;">
                        <div style="width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0; background: #34d399;"></div>
                        <div style="font-size: 20px; font-weight: 500; color: #374151; flex: 1;">Completed</div>
                        <div style="font-size: 36px; font-weight: 700; color: #111827; margin-right: 16px;">6</div>
                        <div style="font-size: 20px; color: #6b7280;">12.5%</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: center; align-items: center;">
                    <div style="position: relative; width: 400px; height: 400px;">
                        <canvas id="donutChart" width="400" height="400"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                            <div style="font-size: 18px; color: #6b7280; margin-bottom: 8px;">Total Issues</div>
                            <div style="font-size: 48px; font-weight: 700; color: #111827;">46</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>



<script src="baseUI.js"></script>
<script src="adminnotifs.js"></script>
<script src="dashboardUI.js"></script>


</body>
</html>
</antArtifact