<?php
session_start(); // Start the sessio
include '../../db_connection.php';

// Ensure the user is logged in via session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit();
}

$userID = ($_SESSION['user_id']);

// Retrieve user details from DB using email
$email = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT * FROM EndUser WHERE userID = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $userID = $user['userID'];  
    $fname = $user['fname'] ?? '';
    $lname = $user['lname'] ?? '';
    $fullName = trim($fname . ' ' . $lname);
} else {
    // Session corrupted or user not found
    session_destroy();
    header("Location: ../../modules/auth/login.php?error=Session expired");
    exit();
}

// Search   
$search = isset($_GET['search']) ? trim($_GET['search']) : '';


if ($search !== '') {
    $searchParam = '%' . $search . '%';
    $stmt = $mysqli->prepare("
        SELECT p.*, 
               EXISTS (
                   SELECT 1 FROM Certification c WHERE c.papsID = p.papsID
               ) AS hasCertificate
        FROM paps p
        WHERE p.userID = ? AND p.title LIKE ?
        ORDER BY p.dateSubmitted DESC
    ");
    $stmt->bind_param("ss", $email, $searchParam);
} else {
    $stmt = $mysqli->prepare("
        SELECT p.*, 
               EXISTS (
                   SELECT 1 FROM Certification c WHERE c.papsID = p.papsID
               ) AS hasCertificate
        FROM paps p
        WHERE p.userID = ?
        ORDER BY p.dateSubmitted DESC
    ");
    $stmt->bind_param("s", $email);
}

$stmt->execute();
$result = $stmt->get_result();

$paps = [];
while ($row = $result->fetch_assoc()) {
    // Make sure 'hasCertificate' is int (0 or 1)
    $row['hasCertificate'] = (int)$row['hasCertificate'];
    $paps[] = $row;
}
?>
<!DOCTYPE html>
<html lang=en>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/globalstyless.css">
    <link rel="stylesheet" href="../../assets/css/enduserhome.css">
    
    <title> GAD End-User </title>
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
                        <button class="icon-button" title="Profile">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#8458B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <span class="header-icon-text">Profile</span>
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

    <div class="main-container">
        <div class="sidebar">
            <div class="sidebar-name">
                 <h2>Welcome, <?php echo htmlspecialchars($fname ?: 'User'); ?>!</h2>
            </div>

            <div class="sidebar-item active">
                <div class="submitted-section">
                    <div>Submitted Files</div>
                <div class="sidebar-item icon">▼</div>
                </div>
            </div>

            <div class="sidebar-item">
                <div class="recent-section">
                    <div class="sidebar-item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor"    stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                <div>Recent</div>
            </div>
        </div>
    </div>

    <div class="content">
    <div class="content-header"></div>
      <div class="welcome-bar">
        <form method="get" action="enduser.php" style="display: inline;">
            <input type="search" class="search-bar" name="search" placeholder="Search" value="<?php echo @htmlspecialchars($search); ?>">
        </form>
        <button class="upload-btn" onclick="showUploadForm()">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: middle;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
          Upload
        </button>
      </div>
          <div id="uploadForm" class="container form-container">
          <div class="form-header"></div>
          <div class="form-body">

            <div class="text-block">
                <p style="font-weight: 600; margin: 0;">Upload File</p>
                <p>Please fill out the form below with the necessary project details. Make sure to provide accurate information, including the project title, department, author, and the link to the supporting document.</p>
            </div>

            <form id="uploadPapsForm" method="post" action="paps.php">
              <div class="form-group mb-3">
                <label for="title" class="form-label">Project Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
              </div>

              <div class="radio-group form-group mb-3">
                <label class="form-label"><strong>Department</strong></label><br>
                <label><input type="radio" name="organization" value="CIC" required> CIC - College of Information and Computing</label><br>
                <label><input type="radio" name="organization" value="CEd"> CEd - College of Education</label><br>
                <label><input type="radio" name="organization" value="CT"> CT - College of Technology</label><br>
                <label><input type="radio" name="organization" value="CAS"> CAS - College of Arts and Sciences</label><br>
                <label><input type="radio" name="organization" value="CBA"> CBA - College of Business Administration</label><br>
                <label><input type="radio" name="organization" value="CoE"> CoE - College of Engineering</label><br>
                <label><input type="radio" name="organization" value="CAEc"> CAEc - College of Applied Economics</label>
              </div>

              <div class="form-group mb-3">
                <label for="fileLink" class="form-label">File Link</label>
                <input type="url" class="form-control" id="fileLink" name="fileLink" required>
                <div class="form-text">Please provide a valid URL (Google Drive, etc.)</div>
              </div>

              <button type="submit" class="btn btn-primary submit-btn">Submit</button>
            </form>

            <div id="formMessage"></div>
          </div>
        </div>


 
      <div class="files-header">
        <div class="files-title">
          My files
          <span class="arrow-up">↑</span>
        </div>
        <div class="filters">
          <button class="filter-btn active">All</button>
          <button class="filter-btn">Completed</button>
          <button class="filter-btn">Pending</button>
        </div>
      </div>
      
    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Forms</th>
                <th>Date Uploaded </th>
                <th>Status</th>
                <th> </th>
            </tr>
        </thead>
<tbody>
    <?php if (!empty($paps)): ?>
        <?php foreach ($paps as $pap): ?>
            <tr data-has-cert="<?= (int)$pap['hasCertificate'] ?>" data-papsid="<?= htmlspecialchars($pap['papsID']) ?>">
                <td>
                    <a class="pap-link" href="<?= htmlspecialchars($pap['fileLink']) ?>" target="_blank" rel="noopener noreferrer">
                        <?= htmlspecialchars($pap['title']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($pap['dateSubmitted']) ?></td>
                <td>
                    <?php
                        $statusRaw = strtolower(trim($pap['status']));
                        $statusText = ucfirst($statusRaw);
                        $color = match ($statusRaw) {
                            'completed', 'approved' => 'green',
                            'pending' => 'red',
                            'unassigned' => 'gray',
                            default => 'black'
                        };
                    ?>
                    <span style="color: <?= $color ?>;">
                        <?= $statusText ?>
                    </span>
                </td>
                <td>
                    <button class="view-button"
                        data-title="<?= htmlspecialchars($pap['title']) ?>"
                        data-link="<?= htmlspecialchars($pap['fileLink']) ?>"
                        data-status="<?= htmlspecialchars($pap['status']) ?>"
                        data-date="<?= htmlspecialchars($pap['dateSubmitted']) ?>"
                        data-papsid="<?= htmlspecialchars($pap['papsID']) ?>"
                        data-organization="<?= htmlspecialchars($pap['organization']) ?>"
                        data-has-cert="<?= (int)$pap['hasCertificate'] ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align: center;">No uploaded forms found.</td>
        </tr>
    <?php endif; ?>
</tbody>

    </table>
</div>


    <div id="documentDetailsModal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Document Details</h3>
                <button class="close-btn" onclick="closeDocumentDetailsModal()">×</button>
            </div>

        <div class="modal-body">
            <p><strong>Project Title:</strong> <span id="projTitle"></span></p>
            <p><strong>Document ID:</strong> <span id="papsID"></span></p>
            <p><strong>Department:</strong> <span id="department"></span></p>
            <p><strong>Date Submitted:</strong> <span id="dateNeeded"></span></p>
            <p><strong>Status:</strong> <span id="status" ></span></p>
            <!-- Add file link if needed -->
            <p><strong>Link:</strong> <a id="fileLinks" class="fileLinks" href="#" target="_blank">Open submitted link</a></p>
        </div>

      <div class="modal-footer">
        <button class="btn-primary" onclick="viewCertificate()">View certificate</button>
      </div>
    </div>
  </div>
</div>

    </div> <!--main-container div end-->

  <div class="pagination-container">
  <div class="entries-dropdown">
    Show 
    <select id="entriesPerPageSelect">
      <option value="10" selected>10</option>
      <option value="25">25</option>
      <option value="50">50</option>
    </select> 
    entries
  </div>

  <div class="pagination-info"></div>

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
            <a class="logout-confirm-btn" id="logout-confirm-btn" href="../../modules/auth/logout.php">Log Out</a>
        </div>
    </div>
    <!-- Overlay for upload modal -->
    <div id="modal-overlay" class="modal-overlay" style="display: none;">
        <div id="uploadForm" class="container form-container">
          <div class="form-header"></div>
          <div class="form-body">
    
            <div class="text-block">
                <p style="font-weight: 600; margin: 0;">Upload File</p>
                <p>Please fill out the form below with the necessary project details. Make sure to provide accurate information, including the project title, department, author, and the link to the supporting document.</p>
            </div>
    
            <form id="uploadPapsForm" method="post" action="paps.php">
              <div class="form-group mb-3">
                <label for="title" class="form-label">Project Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
              </div>
    
              <div class="radio-group form-group mb-3">
                <label class="form-label"><strong>Department</strong></label><br>
                <label><input type="radio" name="organization" value="CIC" required> CIC - College of Information and Computing</label><br>
                <label><input type="radio" name="organization" value="CEd"> CEd - College of Education</label><br>
                <label><input type="radio" name="organization" value="CT"> CT - College of Technology</label><br>
                <label><input type="radio" name="organization" value="CAS"> CAS - College of Arts and Sciences</label><br>
                <label><input type="radio" name="organization" value="CBA"> CBA - College of Business Administration</label><br>
                <label><input type="radio" name="organization" value="CoE"> CoE - College of Engineering</label><br>
                <label><input type="radio" name="organization" value="CAEc"> CAEc - College of Applied Economics</label>
              </div>
    
              <div class="form-group mb-3">
                <label for="fileLink" class="form-label">File Link</label>
                <input type="url" class="form-control" id="fileLink" name="fileLink" required>
                <div class="form-text">Please provide a valid URL (Google Drive, etc.)</div>
              </div>
    
              <button type="submit" class="btn btn-primary submit-btn">Submit</button>
            </form>
    
            <div id="formMessage"></div>
          </div>
        </div>
    </div>
    <div class="overlay" id="overlay"></div>
    <script src="../../assets/js/baseUI.js"></script>

    <script>

function showDocumentDetails(paps, button) {  // Added button parameter
    // Set all the modal content
    document.getElementById('projTitle').innerText = paps.title;
    document.getElementById('papsID').innerText = paps.papsID;
    document.getElementById('department').innerText = paps.organization;
    document.getElementById('dateNeeded').innerText = paps.dateSubmitted;
    document.getElementById('status').innerText = paps.status;

    // Set file link
    const hrefFile = document.getElementById('fileLinks');
    if (hrefFile) {
        hrefFile.href = paps.fileLink;
    }

    // Show modal
    const modal = document.getElementById('documentDetailsModal');
    if (modal) {
        modal.style.display = 'flex';
    }

    // Certificate check - fixed version
    const hasCert = button.getAttribute('data-has-cert') === '1';
    const viewBtn = document.querySelector("#documentDetailsModal .modal-footer button");
    if (viewBtn) {
        viewBtn.style.display = hasCert ? 'inline-block' : 'none';
    }
}

function closeDocumentDetailsModal() {
    const modal = document.getElementById('documentDetailsModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.querySelectorAll('.view-button').forEach(button => {
    button.addEventListener('click', function() {
        const paps = {
            papsID: this.getAttribute('data-papsid'),
            title: this.getAttribute('data-title'),
            fileLink: this.getAttribute('data-link'),
            status: this.getAttribute('data-status'),
            dateSubmitted: this.getAttribute('data-date'),
            organization: this.getAttribute('data-organization')
        };
        showDocumentDetails(paps, this);  // Pass the button element
    });
});

        function showUploadForm() {
        const form = document.getElementById('uploadForm');
        const overlay = document.getElementById('modal-overlay');
        const mainContent = document.getElementById('main-content');
        
        // Show the modal and overlay
        form.style.display = 'flex';
        overlay.style.display = 'flex';
        
        // Add blur effect to main content
        mainContent.classList.add('blur-background');
        
        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';    
        }

        function hideUploadForm() {
            const form = document.getElementById('uploadForm');
            const overlay = document.getElementById('modal-overlay');
            const mainContent = document.getElementById('main-content');
            
            // Hide the modal and overlay
            form.style.display = 'none';
            overlay.style.display = 'none';
            
            // Remove blur effect from main content
            mainContent.classList.remove('blur-background');
            
            // Restore body scroll
            document.body.style.overflow = 'auto';
        }

        function viewCertificate() {
            const status = document.getElementById('status').textContent.trim();
            const papsID = document.getElementById('papsID').textContent.trim();

            if (status.toLowerCase() === "completed") {
                window.open("certificate.php?papsID=" + encodeURIComponent(papsID), "_blank");
            } else {
                alert("Certificate is only available once it's completed.");
            }
        }

        function submitUpload() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            alert(`Upload submitted!\nDate Range: ${startDate} – ${endDate}`);
        }
        document.getElementById('modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
            hideUploadForm();
            }
        });
        
        function handleOutsideClick(event) {
        const form = document.getElementById('uploadForm');
        const button = event.target.closest('button');

        if (form.style.display === 'block' && !form.contains(event.target) && !button) {
            form.style.display = 'none';
            document.removeEventListener('click', handleOutsideClick);
        }
    }

        const filterButtons = document.querySelectorAll('.filter-btn');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
            
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Get selected filter
                const status = button.textContent.trim();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowStatus = cells[2]?.textContent.trim(); // The status column

                
                    if (status === 'All' || rowStatus === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Sort functionality
        document.querySelector('.arrow-up').addEventListener('click', () => {
        const arrowEl = document.querySelector('.arrow-up');
        const isAscending = arrowEl.textContent === '↑';

        arrowEl.textContent = isAscending ? '↓' : '↑';

        const tbody = document.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const textA = a.querySelector('td').textContent;
            const textB = b.querySelector('td').textContent;
            
            return isAscending ? 
            textB.localeCompare(textA) : 
            textA.localeCompare(textB);
        });

        rows.forEach(row => tbody.appendChild(row));
        });


        // Sort functionality for "Date Uploaded"
        document.querySelector('.arrow-up').addEventListener('click', () => {
            const arrowEl = document.querySelector('.arrow-up');
            const isAscending = arrowEl.textContent === '↑';

            // Toggle arrow symbol
            arrowEl.textContent = isAscending ? '↓' : '↑';

            const tbody = document.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
            const dateA = new Date(a.children[1].textContent.trim());
            const dateB = new Date(b.children[1].textContent.trim());

            // Sort descending if ascending symbol is shown
            return isAscending ? dateB - dateA : dateA - dateB;
            });

            rows.forEach(row => tbody.appendChild(row));
        });


        // Sidebar toggle
        document.querySelector('.sidebar-item-icon').addEventListener('click', () => {
        const sidebarItem = document.querySelector('.sidebar-item');
        sidebarItem.classList.toggle('active');
        });

        // Pagination buttons
        document.querySelectorAll('.pagination-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Pagination logic would go here in a real application
            alert('Pagination action: ' + btn.textContent);
        });
        });

        // Entries dropdown
        document.querySelector('select').addEventListener('change', (e) => {
        alert('Changed display to show ' + e.target.value + ' entries');
        });
    

    </script>
</body>
</html>