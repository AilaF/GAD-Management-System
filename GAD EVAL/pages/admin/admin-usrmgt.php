<?php
session_start(); // Start the session
// Check if the session variable is set
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Unauthorized access");
    exit();
}

// Retrieve adminID (now stored as user_id in session)
$adminID = $_SESSION['user_id']; 

$_SESSION['adminID'] = $adminID; // Explicitly set adminID in session

if (isset($_SESSION['adminID'])) {
    $adminID = $_SESSION['adminID'];
    $adminTooltip = htmlspecialchars($adminID);
} else {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/globalstyles.css">
    <title>GAD Management Information System</title>
    <style>      
    </style>
</head>
<body style="overflow: hidden;">
    <div class="view" style="min-height: 90vh;">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">
                    <img src="../../assets/img/logo.svg" alt="GAD Logo" width="100" height="100">
                </div>
                <div class="logo-text">GAD Management Information System</div>
            </div>  
            <div class="header-actions">
                <div class="header-icon-container">
                    <button class="icon-button" title="Admin">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="header-icon-text">Admin</span>
                </div>
                <div class="header-icon-container">
                    <button class="icon-button" id="notification-button" title="Notifications">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="header-icon-text">Notifications</span>
                </div>
                <div class="header-icon-container">
                    <button class="icon-button" id="menu-button" title="Log out">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 17L21 12L16 7" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12H9" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="header-icon-text">Log out</span>
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
                    <path d="M15 35H8.33333C7.44928 35 6.60143 34.6488 5.97631 34.0237C5.35119 33.3986 5 32.5507 5 31.6667V8.33333C5 7.44928 5.35119 6.60143 5.97631 5.97631C6.60143 5.35119 7.44928 5 8.33333 5H15" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M26.6667 28.3333L35 20L26.6667 11.6667" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M35 20H15" stroke="#8458B3" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="logout-modal-title">Confirm Logout</h3>
            <p class="logout-modal-text">Are you sure you want to log out?</p>
            <div class="logout-modal-buttons">
                <button class="logout-cancel-btn" id="logout-cancel-btn">Cancel</button>
                <a href="../../modules/auth/logout.php" class="logout-confirm-btn" id="logout-confirm-btn">Log Out</a>
            </div>
        </div>
                
        <div class="overlay" id="overlay"></div>
        
        <div class="subheader-container">
            <div class="subheader-header">
                <div class="header-left">
                    <a href="adminhome.php" class="back-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="#333333"/>
                        </svg>
                    </a>
                    <div class="subheader-title">User Management</div>
                </div>
                <div class="filter-options">
                    <button class="filter-button active" data-filter="all">All users</button>
                    <button class="filter-button" data-filter="End User">End Users</button>
                    <button class="filter-button" data-filter="Evaluator">Evaluators</button>
                </div>
            </div>
            
            <div class="table-section">
            <div class="table-wrapper">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Specialization</th>
                            <th>Date Joined</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                    </tbody>
                </table>
            </div>
            </div>
            </div>
        </div>
            
            <div class="no-results" id="noResults">
                No users match the selected filter
            </div>
            
            <div class="add-user-container">
                <button class="add-user-button" id="addUserBtn">+</button>
                <span class="add-user-text">Add Evaluator</span>
            </div>
        </div>
    </div>

    <div class="pagination-container">
        <div class="entries-container">
            Show 
            <select id="entriesDropdown">
                <option value="10" selected>10</option>
                <option value="20">20</option>
                <option value="30">30</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            entries
        </div>
        <div class="showing-entries">
            showing <span id="startEntry">1</span> to <span id="endEntry">6</span> out of <span id="totalEntries">6</span> entries
        </div>
        <div class="pagination-controls">
            <button class="pagination-button" id="firstPageBtn" title="First page">
                «
            </button>
            <button class="pagination-button" id="prevPageBtn" title="Previous page">
                ‹
            </button>
            <button class="pagination-button" id="nextPageBtn" title="Next page">
                ›
            </button>
            <button class="pagination-button" id="lastPageBtn" title="Last page">
                »
            </button>
        </div>
    </div>
    
    <div id="userDetailsModal" class="modal-usermgt">
        <div class="modal-content-usermgt" style="margin: 12% auto;">
            <div class="modal-header-usermgt">
                <h3>User Account Information</h3>
                <button class="close-btn" onclick="closeUserDetailsModal()">×</button>
            </div>
            <div class="modal-body-usermgt">
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="../../assets/img/profile-icon.svg" alt="Profile Icon" />
                    </div>
                    <div style="flex: 1;">
                        <div class="user-info">
                            <span id="detailFullname"></span>
                        </div>
                        <div class="user-info">
                            <span id="detailEmail"></span>
                        </div>
                        <div class="user-info">
                            <span id="detailUserGroup"></span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="user-info">
                        <span class="user-info-label">Department:</span>
                        <span id="detailDepartment"></span>
                    </div>
                    
                    <div class="user-info">
                        <span class="user-info-label">Specialization:</span>
                        <span id="detailSpecialization"></span>
                    </div>
                </div>
                
                <div class="button-row">
                    <button class="btn" id="deleteUserBtn">Delete user</button>
                    <button class="btn" id="editUserBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="addUserModal" class="modal-usermgt">
        <div class="modal-content-usermgt">
            <div class="modal-header-usermgt">
                <h3>Add New User</h3>
                <button class="close-btn" onclick="closeAddUserModal()">×</button>
            </div>
            <div class="modal-body-usermgt">
                <div class="form-group-usermgt">
                    <label for="lastName">Last Name</label>
                    <input type="text" class="form-control-usermgt" id="lastName" placeholder="Enter last name" required>
                </div>
                
                <div class="form-group-usermgt">
                    <label for="firstName">First Name</label>
                    <input type="text" class="form-control-usermgt" id="firstName" placeholder="Enter first name" required>
                </div>
                
                <div class="form-group-usermgt">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control-usermgt" id="email" placeholder="Enter email" required>
                </div>
                
                <div class="form-group-usermgt">
                    <label for="department">Department</label>
                    <select class="form-select-usermgt" id="department">
                        <option selected disabled>Select Department</option>
                        <option value="CAS">CAS - College of Arts and Sciences</option>
                        <option value="CED">CED - College of Education</option>
                        <option value="CoE">CoE - College of Engineering</option>
                        <option value="CIC">CIC - College of Information and Computing</option>
                        <option value="CBA">CBA - College of Business Administration</option>
                        <option value="CAEc">CAEc - College of Applied Economics</option>
                        <option value="CoT">CT - College of Technology</option>
                    </select>
                </div>
                
                <div class="form-group-usermgt">
                    <label for="specialization">Specialization</label>
                    <input type="text" class="form-control-usermgt" id="specialization" placeholder="Enter specialization" required>
                </div>
                
                <div class="form-group-usermgt">
                    <label for="userGroup">User Group</label>
                    <select class="form-select-usermgt" id="userGroup">
                        <option selected disabled>Select User Group</option>
                        <option value="Evaluator">Evaluator</option>
                    </select>
                </div>
                
                <div class="form-group-usermgt">
                    <label for="sex">Sex</label>
                    <select class="form-select-usermgt" id="sex" required>
                        <option selected disabled>Select Sex</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>
                <div class="form-group-usermgt">
                    <label for="contactNo">Contact No.</label>
                    <input type="text" class="form-control-usermgt" id="contactNo" placeholder="Enter contact number" required>
                </div>
                
                <button class="btn" id="addUserSubmitBtn">Add</button>
            </div>
        </div>
    </div>
    
    <div id="editUserModal" class="modal-usermgt">
        <div class="modal-content-usermgt">
            <div class="modal-header-usermgt">
                <h3>Edit User</h3>
                <button class="close-btn" onclick="closeEditUserModal()">×</button>
            </div>
            <div class="modal-body-usermgt">
                <input type="hidden" id="editUserId">
                
                <div class="form-group-usermgt">
                    <label for="editLastName">Last Name</label>
                    <input type="text" class="form-control-usermgt" id="editLastName">
                </div>
                
                <div class="form-group-usermgt">
                    <label for="editFirstName">First Name</label>
                    <input type="text" class="form-control-usermgt" id="editFirstName">
                </div>
                
                <div class="form-group-usermgt">
                    <label for="editEmail">Email Address</label>
                    <input type="email" class="form-control-usermgt" id="editEmail">
                </div>
                
                <div class="form-group-usermgt">
                    <label for="editDepartment">Department</label>
                    <select class="form-select-usermgt" id="editDepartment">
                        <option selected disabled>Select Department</option>
                        <option value="CAS">CAS - College of Arts and Sciences</option>
                        <option value="CED">CED - College of Education</option>
                        <option value="CoE">CoE - College of Engineering</option>
                        <option value="CIC">CIC - College of Information and Computing</option>
                        <option value="CBA">CBA - College of Business Administration</option>
                        <option value="CAEc">CAEc - College of Applied Economics</option>
                        <option value="CoT">CT - College of Technology</option>
                    </select>
                </div>
                
                <div class="form-group-usermgt">
                    <label for="editSpecialization">Specialization</label>
                    <input type="text" class="form-control-usermgt" id="editSpecialization">
                </div>
                
                <div class="form-group-usermgt">
                    <label for="editUserGroup">User Group</label>
                    <select class="form-select-usermgt" id="editUserGroup">
                        <option selected disabled>Select User Group</option>
                        <option value="Evaluator">Evaluator</option>
                    </select>
                </div>
                <div class="form-group-usermgt">
                    <label for="editSex">Sex</label>
                    <select class="form-select-usermgt" id="editSex" required>
                        <option selected disabled>Select Sex</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>
                <div class="form-group-usermgt">
                    <label for="editContactNo">Contact No.</label>
                    <input type="text" class="form-control-usermgt" id="editContactNo" placeholder="Enter contact number" required>
                </div>
                <button class="btn" id="saveEditUserBtn">Save</button>
            </div>
        </div>
    </div>

    <div id="deleteConfirmationModal" class="modal-usermgt" style="display: none; align-items: center; justify-content: center;">
        <div class="modal-content-usermgt" style="text-align: center; padding: 20px; position: relative; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 30%; max-width: 90vw; margin: 12% auto;">
            <h2 style="margin-bottom: 10px;">Delete user</h2>
            <button class="close-btn" onclick="closeDeleteConfirmationModal()" style="position: absolute; top: 10px; right: 15px; font-size: 20px; background: none; border: none; cursor: pointer;">×</button>
    
            <div class="trash-icon" style="margin: 20px 0;">
                <img src="../../assets/img/trash-icon.svg" alt="Delete Icon" style="width: 60px; height: 60px;">
            </div>
            <p style="margin-bottom: 20px;">Are you sure you want to delete this user?</p>
            <div class="confirmation-buttons">
                <button class="btn-confirm" id="confirmDeleteBtn">Yes</button>
                <button class="btn-cancel" onclick="closeDeleteConfirmationModal()">No</button>
            </div>
        </div>
    </div>    
    <script src="../../assets/js/baseUI.js"></script>
    <script src="../../assets/js/admin-usermngmt.js"></script>
</body>
</html>