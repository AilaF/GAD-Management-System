<?php
session_start();
include '../../db_connection.php';

$papsID = $_GET['papsID'] ?? null;
$evaluatorID = $_SESSION['user_id'] ?? null;

if (!$papsID || !$evaluatorID) {
    die("Invalid request.");
}

// Connect
$conn = new mysqli($host, $user, $pass, $db);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'evaluator') {
    header("Location: login.php?error=Unauthorized access");
    exit();
}


$evaluatorID = ($_SESSION['user_id']);

// Get evaluator's scores for this PAP
$stmt = $conn->prepare("
    SELECT s.itemID, sc.score
    FROM Score sc
    JOIN Scoresheet s ON sc.itemID = s.itemID
    WHERE sc.papsID = ? AND sc.evaluatorID = ?
");
$stmt->bind_param("ss", $papsID, $evaluatorID);
$stmt->execute();
$scoresResult = $stmt->get_result();

$scores = [];
$totalScore = 0;

while ($row = $scoresResult->fetch_assoc()) {
    $scores[$row['itemID']] = $row['score'];
    $totalScore += floatval($row['score']);
}
$stmt->close();

// Get versionID used by this evaluator
$stmt = $conn->prepare("SELECT DISTINCT versionID FROM Score WHERE papsID = ? AND evaluatorID = ?");
$stmt->bind_param("ss", $papsID, $evaluatorID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$versionID = $row['versionID'] ?? '';
$stmt->close();

if (!$versionID) {
    die("No scoresheet version used by this evaluator.");
}

// Get items for the used version
$stmt = $conn->prepare("
    SELECT s.*
    FROM ScoresheetVersions sv
    JOIN Scoresheet s ON sv.itemID = s.itemID
    WHERE sv.versionID = ?
    ORDER BY CAST(SUBSTRING_INDEX(s.item, '.', 1) AS UNSIGNED), s.itemID
");
$stmt->bind_param("s", $versionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No scoresheet items found for version $versionID.");
}


// Organize into parent/subitems
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
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Scores</title>
  <link rel="stylesheet" href="../../assets/css/globalstyles.css">
  <link rel="stylesheet" href="../../assets/css/evalform.css">
  
    <style>
       
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

      <div class="subheader-container">
        <div class="subheader-header">
            <div class="header-left">
                <a href="evalhome.php" class="back-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="#333333"/>
                    </svg>
                </a>
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

<h2>Evaluator Scores - PAP: <?= htmlspecialchars($papsID) ?></h2>
<div class="table-container" style="height: 100vh !important; overflow: auto;">
<table class="sticky-header">
    <thead>
        <tr>
            <th>Element / Item</th>
            <th>No</th>
            <th>Partly</th>
            <th>Yes</th>
            <th>Score</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($scoresheet as $group): ?>
        <tr>
            <td class="left"><strong><?= htmlspecialchars($group['parent']['item']) ?></strong></td>
            <?php if (count($group['subitems']) === 0): ?>
                <td><?= ($scores[$group['parent']['itemID']] == $group['parent']['noValue']) ? '✓' : '' ?></td>
                <td><?= ($scores[$group['parent']['itemID']] == $group['parent']['partlyValue']) ? '✓' : '' ?></td>
                <td><?= ($scores[$group['parent']['itemID']] == $group['parent']['yesValue']) ? '✓' : '' ?></td>
                <td><?= number_format($scores[$group['parent']['itemID']] ?? 0, 2) ?></td>
            <?php else: ?>
                <td colspan="3" style="text-align: center;">--</td>
                <td>--</td>
            <?php endif; ?>
        </tr>

        <?php 
        usort($group['subitems'], function($a, $b) {
            return floatval(preg_replace('/[^0-9.]/', '', $a['subitem'])) <=> floatval(preg_replace('/[^0-9.]/', '', $b['subitem']));
        });

        foreach ($group['subitems'] as $sub): ?>
        <tr>
            <td class="left"><?= htmlspecialchars($sub['subitem']) ?></td>
            <td><?= ($scores[$sub['itemID']] == $sub['noValue']) ? '✓' : '' ?></td>
            <td><?= ($scores[$sub['itemID']] == $sub['partlyValue']) ? '✓' : '' ?></td>
            <td><?= ($scores[$sub['itemID']] == $sub['yesValue']) ? '✓' : '' ?></td>
            <td><?= number_format($scores[$sub['itemID']] ?? 0, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="total">Total Score</td>
            <td class="total"><?= number_format($totalScore, 2) ?></td>
        </tr>
    </tfoot>
</table>
</div>
  </div>
      <script src="../../assets/js/baseUI.js"></script>
</body>

</html>