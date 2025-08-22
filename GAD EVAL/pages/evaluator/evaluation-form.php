<?php
session_start();
include '../../db_connection.php';

$papsID = $_GET['papsID'] ?? null;
$evaluatorID = $_SESSION['user_id'] ?? null;

if (!$papsID || !$evaluatorID) {
    die("Invalid request.");
}

$conn = new mysqli($host, $user, $pass, $db);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'evaluator') {
    header("Location: login.php?error=Unauthorized access");
    exit();
}


$evaluatorID = ($_SESSION['user_id']);

// Get latest version
$res = $conn->query("SELECT MAX(versionID) AS latest FROM ScoresheetVersions");
$versionID = $res->fetch_assoc()['latest'] ?? '';
if (!$versionID) die("No scoresheet version found");

// Get scoresheet items (using prepared statement)
$stmt = $conn->prepare("SELECT s.* FROM ScoresheetVersions sv JOIN Scoresheet s ON sv.itemID = s.itemID WHERE sv.versionID = ? ORDER BY CAST(SUBSTRING_INDEX(s.item, '.', 1) AS UNSIGNED), s.itemID");
$stmt->bind_param("s", $versionID);
$stmt->execute();
$result = $stmt->get_result();

$scoresheet = [];
while ($row = $result->fetch_assoc()) {
    $item = $row['item'];
    $subitem = $row['subitem'];

    if (!isset($scoresheet[$item])) {
        $scoresheet[$item] = [
            'parent' => $row,
            'subitems' => [],
        ];
    }

    if (!empty($subitem)) {
        $scoresheet[$item]['subitems'][] = $row;
    }
}
$stmt->close();

// Fetch PAP document info (using same connection)
$stmt = $conn->prepare("SELECT papsID, title, organization, fileLink, status FROM paps WHERE papsID = ?");
$stmt->bind_param("s", $papsID);
$stmt->execute();
$result = $stmt->get_result();
$papInfo = $result->fetch_assoc();
$stmt->close();

// Fetch assigned evaluators
$stmt = $conn->prepare("SELECT fname, lname FROM assignedeval ae JOIN evaluator e ON ae.evaluatorID = e.evaluatorID WHERE ae.papsID = ?");
$stmt->bind_param("s", $papsID);
$stmt->execute();
$result = $stmt->get_result();

$assignedEvaluators = [];
while ($row = $result->fetch_assoc()) {
    $assignedEvaluators[] = $row['fname'] . ' ' . $row['lname'];
}
$stmt->close();

// Get PAP details (additional query)
$stmt = $conn->prepare("SELECT papsID, title, organization, dateSubmitted FROM PAPs WHERE papsID = ?");
$stmt->bind_param("s", $papsID);
$stmt->execute();
$result = $stmt->get_result();
$pap = $result->fetch_assoc();
$stmt->close();

// Get total number of items in the version
$stmt = $conn->prepare("
    SELECT COUNT(*) AS totalItems
    FROM ScoresheetVersions
    WHERE versionID = ?
");
$stmt->bind_param("s", $versionID);
$stmt->execute();
$result = $stmt->get_result();
$totalItems = $result->fetch_assoc()['totalItems'] ?? 0;
$stmt->close();


// Check if evaluator has already submitted any scores for this papsID
$stmt = $conn->prepare("
    SELECT COUNT(*) AS hasScored
    FROM Score
    WHERE papsID = ? AND evaluatorID = ?
");
$stmt->bind_param("ss", $papsID, $evaluatorID);
$stmt->execute();
$result = $stmt->get_result();
$hasScored = $result->fetch_assoc()['hasScored'] ?? 0;
$stmt->close();

$conn->close();

// Redirect if evaluator is done
if ($hasScored > 0) {
    header("Location: view_scores.php?papsID=" . urlencode($papsID));
    exit();
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../assets/css/globalstyles.css">
  <link rel="stylesheet" href="../../assets/css/evalform.css">
  <title>GAD Management Information System</title>

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

      <div class="subheader-container">
        <div class="subheader-header">
            <div class="header-left">
                <a href="evalhome.php" class="back-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="#333333"/>
                    </svg>
                </a>
                <div class="subheader-title">Evaluation Form</div>
            </div>
        </div>
      </div>

      <div class="evaluator-modal" id="evaluator-modal">
        <div class="evaluator-modal-content">
            <div class="evaluator-options">
                <a href="/profile" class="evaluator-option" id="edit-profile-btn">
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

<!-- attachment tab removed -->

    <div class="content">
      <div class="sidebar">
        <div class="info-section">
          <h2 class="info-title">Main Information</h2>
          
          <div class="info-row">
            <div class="info-label">Document No:</div>
            <div class="info-value"><?php echo htmlspecialchars($papInfo['papsID'] ?? 'N/A'); ?></div>
          </div>
<!-- /here -->
          <div class="info-row">
            <div class="info-label">Link:</div>
            <div class="info-value">
              <a href="<?php echo htmlspecialchars($papInfo['fileLink'] ?? '#'); ?>" target="_blank">
                <?php echo !empty($papInfo['fileLink']) ? 'Open Document' : 'No link available'; ?>
              </a>
            </div>
          </div>

          
          <div class="info-row">
            <div class="info-label">Date Needed:</div>
            <div class="info-value">N/A</div>
          </div>
          
          <div class="info-row">
            <div class="info-label">Request Status:</div>
            <div class="info-value">
              <span class="badge">FOR EVALUATION</span>
            </div>
          </div>
        </div>
        
        <div class="info-section">
          <h2 class="info-title">Other Information</h2>
          
          <div class="info-row">
            <div class="info-label">College/Unit:</div>
            <div class="info-value"><?php echo htmlspecialchars($papInfo['organization'] ?? 'N/A'); ?></div>
          </div>

          <div class="info-row">
            <div class="info-label">Proposal Title:</div>
            <div class="info-value"><?php echo htmlspecialchars($papInfo['title'] ?? 'N/A'); ?></div>
          </div>
          
<div class="info-row">
  <div class="info-label">Assigned Evaluators:</div>
  <div class="info-value">
    <?php echo !empty($assignedEvaluators) ? implode('<br>', array_map('htmlspecialchars', $assignedEvaluators)) : 'None assigned'; ?>
  </div>
</div>

        </div>
      </div>
<form id="evaluationForm" method="POST" action="save_scores.php" onsubmit="return true;">
<input type="hidden" name="versionID" value="<?= htmlspecialchars($versionID) ?>">
<input type="hidden" name="papsID" value="<?= htmlspecialchars($papsID) ?>">
<div class="table-container">
<table class="sticky-header">
  <thead>
    <tr>
      <th>Elements and Items/Questions</th>
      <th>No</th>
      <th>Partly</th>
      <th>Yes</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($scoresheet as $item => $group): ?>
    <tr>
      <td><strong><?= htmlspecialchars($item) ?></strong></td>
      <?php if (count($group['subitems']) === 0): ?>
      
      <td><input type="radio" name="score[<?= $group['parent']['itemID'] ?>]" value="<?= $group['parent']['noValue'] ?>" data-parent="<?= $group['parent']['itemID'] ?>"></td>
      <td><input type="radio" name="score[<?= $group['parent']['itemID'] ?>]" value="<?= $group['parent']['partlyValue'] ?>" data-parent="<?= $group['parent']['itemID'] ?>"></td>
      <td><input type="radio" name="score[<?= $group['parent']['itemID'] ?>]" value="<?= $group['parent']['yesValue'] ?>" data-parent="<?= $group['parent']['itemID'] ?>"></td>
      
      <td class="total" id="total_item_<?= $group['parent']['itemID'] ?>">0.00</td>
      <?php else: ?>
      <td colspan="3" style="text-align: center;">--</td>
      <td class="total" id="total_item_<?= $group['parent']['itemID'] ?>">0.00</td>
      <?php endif; ?>
    </tr>

    <?php 
    usort($group['subitems'], function($a, $b) {
        $aVal = floatval(preg_replace('/[^0-9.]/', '', $a['subitem']));
        $bVal = floatval(preg_replace('/[^0-9.]/', '', $b['subitem']));
        return $aVal <=> $bVal;
    });

    foreach ($group['subitems'] as $sub): ?>
    <tr>
      <td><?= htmlspecialchars($sub['subitem']) ?></td>
      
      <td><input type="radio" name="score[<?= $sub['itemID'] ?>]" value="<?= $sub['noValue'] ?>" data-parent="<?= $group['parent']['itemID'] ?>"></td>
      <td><input type="radio" name="score[<?= $sub['itemID'] ?>]" value="<?= $sub['partlyValue'] ?>" data-parent="<?= $group['parent']['itemID'] ?>"></td>
      <td><input type="radio" name="score[<?= $sub['itemID'] ?>]" value="<?= $sub['yesValue'] ?>" data-parent="<?= $group['parent']['itemID'] ?>"></td>
      <td></td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<button class="submit-btn" type="submit">Submit Evaluation</button>
</form>

              </div>

      </div>
    </div>
  </div>

  <form id="demoEvaluationForm">
    <h2>Score Submission Form</h2>
    <div>
      <label for="demoScore">Score:</label>
      <input type="number" id="demoScore" name="demoScore" min="0" max="100" required>
    </div>
    <br>
    <button type="submit">Submit Score</button>
  </form>

  <!-- Modal overlay -->
  <div class="custom-modal-backdrop" id="customScoreModalBackdrop">
    <div class="custom-score-container">
      <button class="custom-close-btn" id="customCloseModalBtn">×</button>
      <div class="custom-success-badge">
        <svg class="custom-checkmark-icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </div>
      <p class="custom-notification-text">Score has been submitted. <a href="#" class="custom-score-link" id="customViewScoreLink">Click here to view score</a>.</p>
    </div>
  </div>


<!-- ///////////JSCRIPT////////// -->
  <script src="../../assets/js/baseUI.js"></script>
  <script>
   const demoForm = document.getElementById('demoEvaluationForm');
    const customModal = document.getElementById('customScoreModalBackdrop');
    const customCloseBtn = document.getElementById('customCloseModalBtn');
    
    // Form submit handler
    demoForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get the score value
      const demoScoreValue = document.getElementById('demoScore').value;
      
      // Show the modal
      customModal.style.display = 'flex';
    });
    
    // Close button handler
    customCloseBtn.addEventListener('click', function() {
      customModal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
      if (e.target === customModal) {
        customModal.style.display = 'none';
      }
    });

    // If you only need the modal functionality for your existing form:
    document.getElementById('evaluationForm').addEventListener('submit', function(e) {
      e.preventDefault();
      customModal.style.display = 'flex';
    });

    document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Get document data from URL parameters
    const docNo = urlParams.get('docNo') || 'N/A';
    const college = urlParams.get('college') || 'N/A';
    const title = urlParams.get('title') || 'N/A';
    const dateSubmitted = urlParams.get('dateSubmitted') || 'N/A';
    const dateNeeded = urlParams.get('dateNeeded') || 'N/A';
    const status = urlParams.get('status') || 'FOR EVALUATION'
    
    // Populate the form fields with the data
    // Find the elements by class and update their content
    document.querySelector('.info-row:nth-child(1) .info-value').textContent = docNo;
    
    // Update college/unit
    const collegeElement = document.querySelector('.info-row:has(.info-label:contains("College/Unit")) .info-value');
    if (collegeElement) {
        collegeElement.textContent = college;
    } else {
        // Alternative selector if the :has pseudo-class is not supported
        document.querySelectorAll('.info-row').forEach(row => {
            if (row.querySelector('.info-label').textContent.includes('College/Unit')) {
                row.querySelector('.info-value').textContent = college;
            }
        });
    }
    
    // Update proposal title
    const titleElement = document.querySelector('.info-row:has(.info-label:contains("Proposal Title")) .info-value');
    if (titleElement) {
        titleElement.textContent = title;
    } else {
        // Alternative selector
        document.querySelectorAll('.info-row').forEach(row => {
            if (row.querySelector('.info-label').textContent.includes('Proposal Title')) {
                row.querySelector('.info-value').textContent = title;
            }
        });
    }
    
    // Update status badge
    const statusElement = document.querySelector('.badge');
    if (statusElement) {
        statusElement.textContent = status;
    }
    
    // You might want to store the date submitted somewhere or use it for calculations
    console.log('Date Submitted:', dateSubmitted);
});

// ------------------------------------------------------------------------
// Then, add this to your evaluation-form.html page
// This will be a separate script

document.addEventListener('DOMContentLoaded', function() {
    let docData = null;
    try {
        const storedData = sessionStorage.getItem('evaluationDocData');
        if (storedData) {
            docData = JSON.parse(storedData);
            
            // Clear the data after use to avoid stale data on page refresh
            sessionStorage.removeItem('evaluationDocData');
        }
    } catch (e) {
        console.error('Error parsing stored document data:', e);
    }
    
    // If no data in sessionStorage, try URL parameters
    if (!docData) {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('docNo')) {
            docData = {
                docNo: urlParams.get('docNo'),
                college: urlParams.get('college'),
                title: urlParams.get('title'),
                dateSubmitted: urlParams.get('dateSubmitted'),
                dateNeeded: urlParams.get('dateNeeded'),
                status: urlParams.get('status') || 'FOR EVALUATION'
            };
        }
    }
    
    // If we have data from either source, update the form
    if (docData) {
        console.log('Document data loaded:', docData);
        
        // Update document number
        const docNoElements = document.querySelectorAll('.info-row');
        docNoElements.forEach(row => {
            const label = row.querySelector('.info-label');
            if (label && label.textContent.includes('Document No:')) {
                const valueElement = row.querySelector('.info-value');
                if (valueElement) valueElement.textContent = docData.docNo;
            }
        });
        
        // Update college/unit
        document.querySelectorAll('.info-row').forEach(row => {
            const label = row.querySelector('.info-label');
            if (label && label.textContent.includes('College/Unit:')) {
                const valueElement = row.querySelector('.info-value');
                if (valueElement) valueElement.textContent = docData.college;
            }
        });
        
        // Update proposal title
        document.querySelectorAll('.info-row').forEach(row => {
            const label = row.querySelector('.info-label');
            if (label && label.textContent.includes('Proposal Title:')) {
                const valueElement = row.querySelector('.info-value');
                if (valueElement) valueElement.textContent = docData.title;
            }
        });

        // Update date submitted
        document.querySelectorAll('.info-row').forEach(row => {
            const label = row.querySelector('.info-label');
            if (label && label.textContent.includes('Date Submitted:')) {
                const valueElement = row.querySelector('.info-value');
                if (valueElement) valueElement.textContent = docData.dateSubmitted;
            }
        });

        // Update date needed
        document.querySelectorAll('.info-row').forEach(row => {
            const label = row.querySelector('.info-label');
            if (label && label.textContent.includes('Date Needed:')) {
                const valueElement = row.querySelector('.info-value');
                if (valueElement) valueElement.textContent = docData.dateNeeded;
            }
        });
        
        // Update status badge
        const statusBadge = document.querySelector('.badge');
        if (statusBadge) {
            statusBadge.textContent = docData.status;
        }
    } else {
        console.warn('No document data found - using default values');
    }
});

//displayLink & AE///
document.addEventListener('DOMContentLoaded', function () {
  const urlParams = new URLSearchParams(window.location.search);
  const papsID = urlParams.get('papsID');

  if (papsID) {
    fetch(`getPapInfo.php?papsID=${encodeURIComponent(papsID)}`)
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          console.error('Error:', data.error);
          return;
        }

        // Update the PAPs file link
        const fileLinkDiv = document.getElementById('papsLink');
        if (fileLinkDiv && data.fileLink) {
          const link = document.createElement('a');
          link.href = data.fileLink;
          link.target = '_blank';
          link.textContent = 'View File';
          fileLinkDiv.innerHTML = ''; // clear content
          fileLinkDiv.appendChild(link);
        }

        // Update the assigned evaluators
        const evalsDiv = document.getElementById('assignedEvaluators');
        if (evalsDiv && data.assignedEvaluators.length > 0) {
          evalsDiv.innerHTML = '<ul>' + data.assignedEvaluators.map(name => `<li>${name}</li>`).join('') + '</ul>';
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
      });
  }
});


///AYAW ERESA!!!!

document.addEventListener('DOMContentLoaded', function () {
  // Function to calculate and update totals per parent item
  function updateTotals() {
    const groupedScores = {};

    // Loop over all checked radios and sum scores grouped by parent item ID
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
      // Extract itemID from input name: score[<itemID>]
      const match = radio.name.match(/\[(.*?)\]/);
      if (!match) return; // safety check

      const itemID = match[1];
      // Get parent item ID from data-parent attribute, fallback to own itemID
      const parentItem = radio.dataset.parent || itemID;

      // Parse value as float (score)
      const score = parseFloat(radio.value) || 0;

      // Initialize if not set yet
      if (!groupedScores[parentItem]) {
        groupedScores[parentItem] = 0;
      }

      // Accumulate score for this parent item
      groupedScores[parentItem] += score;
    });

    // Update each total cell with the calculated sum for that parent
    document.querySelectorAll('.total').forEach(cell => {
      const itemID = cell.id.replace('total_item_', '');
      const total = groupedScores[itemID] || 0;
      cell.textContent = total.toFixed(2);
    });
  }

  // Attach 'change' event listeners to all radios for dynamic totals update
  document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', updateTotals);
  });

  // Initial totals calculation on page load
  updateTotals();

  // AJAX form submission handler for evaluation form
  const form = document.getElementById('evaluationForm');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    try {
      const response = await fetch(form.action || 'save_scores.php', {
        method: 'POST',
        body: new FormData(form)
      });

      const result = await response.json();

      if (result.success) {
        alert('Evaluation submitted successfully!');
        // Optional: Reload or redirect after submission
        window.location.reload();
      } else {
        alert('Error: ' + (result.message || 'Unknown error'));
      }
    } catch (error) {
      alert('Network error: ' + error.message);
    }
  });
});

document.getElementById('evaluationForm').addEventListener('submit', function(e) {
    const groups = new Set();
    document.querySelectorAll('input[type=radio]').forEach(input => {
        groups.add(input.name);
    });

    for (const group of groups) {
        const selected = document.querySelector(`input[name="${group}"]:checked`);
        if (!selected) {
            alert("Please select a score for all items.");
            e.preventDefault();
            return false;
        }
    }
});
  </script>
</body>
</html>