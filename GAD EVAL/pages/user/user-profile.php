<?php
session_start();
include '../../db_connection.php';
// $servername = "localhost";
// $username = "root";
// $password = ""; // or your password
// $dbname = "gad_dbms";


// Check connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " .$conn->connect_error);
}



//if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    //header("Location: login.php?error=Unauthorized access");
    //exit();



$userID = ($_SESSION['user_id']);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/globalstyles.css">
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <title>GAD Management Information System</title>
    <style>

        .nav-back {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background-color: #f0f0f0;
            border-bottom: 1px solid #e0e0e0;
        }

        .nav-back a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .profile-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-info {
            display: flex;
            align-items: center;
        }

        .profile-avatar {
            position: relative;
            width: 100px;
            height: 100px;
            background-color: #2d2d2d;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
            object-fit: cover;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .camera-icon {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background-color: white;
            border-radius: 50%;
            padding: 5px;
            font-size: 14px;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .camera-icon i {
            color: #444;
        }

        .camera-icon input {
            display: none;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .profile-email, .profile-role {
            color: #666;
            margin-bottom: 5px;
        }

        .edit-btn {
            background-color: #8344ad;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .profile-details {
            margin-top: 20px;
        }

        .detail-item {
            margin-bottom: 20px;
        }

        .detail-label {
            font-weight: 500;
            margin-bottom: 8px;
        }

        .detail-value {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            color: #333;
        }

        .email-container {
            margin-top: 20px;
        }

        .email-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .email-icon {
            background-color: #8344ad;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
        }

        .email-icon svg {
            width: 16px;
            height: 16px;
            fill: white;
        }

        .email-text {
            flex-grow: 1;
        }

        .email-date {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .add-email-btn {
            background-color: #f0f5ff;
            color: #8344ad;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background-color: white;
            width: 500px;
            max-width: 90%;
            border-radius: 8px;
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .modal-footer {
            padding: 15px 20px;
            text-align: right;
        }

        .save-btn {
            background-color: #8344ad;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        .three-col-grid {
            display: grid;
            grid-template-columns: 2fr 2fr 1fr;
            gap: 10px;
        }
    </style>
</head>
<body style="overflow: hidden;">
    <div class="view">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">
                    <img src="../../assets/img/logo.svg" alt="GAD Logo" width="100" height="100">
                </div>
                <div class="logo-text">GAD Management Information System</div>
            </div>  
            <div class="header-actions">
                <div class="header-icon-container">
                    <button class="icon-button" title="Evaluator">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span class="header-icon-text">Evaluator</span>
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

        <div class="subheader-container">
            <div class="subheader-header">
                <div class="header-left">
                    <a href="evaluator-home.html" class="back-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="#333333"/>
                        </svg>
                    </a>
                    <div class="subheader-title">Edit Profile</div>
                </div>

    <!-- Profile Content -->
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-info">
                <div class="profile-avatar">
                    <img
                        id="profileImage"
                        src='data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%23ffffff" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>'
                        alt="Profile Picture"
                        />
  
                        <label class="camera-icon" for="uploadProfile" title="Change Profile Picture">
                            <i class="fas fa-camera"></i>
                                <input
                                type="file"
                                id="uploadProfile"
                                accept="image/*"
                                onchange="loadProfileImage(event)"
                                />
                        </label>
                    </div>
                <div>
                    <h2 class="profile-name"></h2>
                    <p class="profile-email"></p>
                    <p class="profile-role"></p>
                </div>
            </div>
            <button class="edit-btn" id="editProfileBtn">Edit</button>
        </div>

        <!-- Profile Details -->
        <div class="profile-details">
            <div class="detail-item">
                <div class="detail-label">Full Name</div>
                <div class="detail-value"></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Department</div>
                <div class="detail-value"></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Specialization</div>
                <div class="detail-value"></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">My email Address</div>
                <div class="email-container">
                    <div class="email-item">
                        <div class="email-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                        </div>
                        <div class="email-text">
                            <div></div>
                            <div class="email-date"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Edit profile</div>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="three-col-grid">
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" class="form-control" id="lastName" value="">
                        </div>
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" class="form-control" id="firstName" value="">
                        </div>
                        <div class="form-group">
                            <label for="middleInitial">M.I</label>
                            <input type="text" class="form-control" id="middleInitial" value="">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" class="form-control" id="department" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" class="form-control" id="specialization" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Re-enter password:</label>
                        <input type="password" class="form-control" id="confirmPassword" value="">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="save-btn" id="saveBtn">Save</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/baseUI.js"></script>   
    <script>
        // Get modal elements
        const editModal = document.getElementById('editModal');
        const editProfileBtn = document.getElementById('editProfileBtn');
        const closeModal = document.getElementById('closeModal');
        const saveBtn = document.getElementById('saveBtn');
        
        // Open modal
        editProfileBtn.addEventListener('click', function() {
            editModal.style.display = 'flex';
        });
        
        // Close modal
        closeModal.addEventListener('click', function() {
            editModal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        });
        
        // Save button functionality
        saveBtn.addEventListener('click', function() {
            // Here you would typically save the data to a server
            // For this example, we'll just close the modal
            alert('Profile updated successfully!');
            editModal.style.display = 'none';
            
            // Update the profile information on the page
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const middleInitial = document.getElementById('middleInitial').value;
            const email = document.getElementById('email').value;
            const department = document.getElementById('department').value;
            const specialization = document.getElementById('specialization').value;
            
            // Update displayed information
            document.querySelector('.profile-name').textContent = firstName + ' ' + lastName;
            document.querySelector('.profile-email').textContent = email;
            document.querySelector('.detail-value').textContent = lastName + ', ' + firstName + ' ' + middleInitial + '.';
            document.querySelectorAll('.detail-value')[1].textContent = department;
            document.querySelectorAll('.detail-value')[2].textContent = specialization;
            document.querySelector('.email-text div').textContent = email;

           function loadProfileImage(event) {
            const image = document.getElementById('profileImage');
            const file = event.target.files[0];
  
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    image.src = e.target.result;
                };
                    reader.readAsDataURL(file);
                } else {
                    alert('Please select a valid image file.');
                    }
                }
        });
    </script>
</body>
</html>