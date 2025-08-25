<?php
include "db_connection.php";

$users = [];

// Get end users
$endUserSql = "SELECT userID AS id, fname, lname, mname, email, orgname, sex, contactNo, dob FROM enduser ORDER BY lname ASC";
$endUserResult = $conn->query($endUserSql);

if ($endUserResult && $endUserResult->num_rows > 0) {
    while ($row = $endUserResult->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'firstName' => $row['fname'],
            'lastName' => $row['lname'],
            'fullName' => $row['fname'] . ' ' . $row['lname'],
            'middleName' => $row['mname'],
            'email' => $row['email'],
            'role' => 'End User',
            'organization' => $row['orgname'],
            'sex' => $row['sex'],
            'contactNo' => $row['contactNo'],
            'dob' => $row['dob'],
            'specialization' => '', // not applicable to end users
            'userGroup' => 'End User',
            'dateJoined' => '' // optional, if you add a column later
        ];
    }
}

// Get evaluators
$evalSql = "SELECT evaluatorID AS id, fname, lname, email, department, expertise FROM evaluator ORDER BY lname ASC";
$evalResult = $conn->query($evalSql);

if ($evalResult && $evalResult->num_rows > 0) {
    while ($row = $evalResult->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'firstName' => $row['fname'],
            'lastName' => $row['lname'],
            'fullName' => $row['fname'] . ' ' . $row['lname'],
            'email' => $row['email'],
            'role' => 'Evaluator',
            'department' => $row['department'],
            'specialization' => $row['expertise'],
            'userGroup' => 'Evaluator',
            'dateJoined' => ''
        ];
    }
}

echo json_encode($users);
$conn->close();
?>
