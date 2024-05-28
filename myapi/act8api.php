<?php
header("Content-Type: application/json"); // sets the content type tp JSON
$servername = "localhost"; //variable, used to store the db connection details
$username = "root";
$password = "";
$dbname = "hospital";

// Create connection creates the mysli connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') { //handling GET reqs
    if (isset($_GET['department'])) {
        $department = $conn->real_escape_string($_GET['department']);

        // Fetch doctor and available date based on department
        $stmt = $conn->prepare("SELECT d.DocName AS doctor, a.AvailableDate AS available_date
                                FROM doctors d
                                LEFT JOIN appointments a ON d.DoctorID = a.DoctorID
                                WHERE d.Specialization = ?");
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo json_encode($data);
        } 
        else {
            echo json_encode(["error" => "No doctor found for the selected department"]);
        }
    }
    else {
    // Default GET request to fetch all required data
    $sql = "SELECT
        p.FirstName, 
        p.LastName, 
        p.Department, 
        d.DocName, 
        a.AvailableDate
    FROM 
        patients p
    INNER JOIN 
        doctors d ON p.Department = d.Specialization
    INNER JOIN 
        appointments a ON d.DoctorId = a.DoctorId";
    
    $result = $conn->query($sql);

    $data = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            }
        }   
    echo json_encode($data);

    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $first_name = $conn->real_escape_string($input['first_name']);
    $last_name = $conn->real_escape_string($input['last_name']);
    $department = $conn->real_escape_string($input['department']);

    // Insert data into patients table
    $stmt = $conn->prepare("INSERT INTO patients (FirstName, LastName, Department) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $first_name, $last_name, $department);
    $stmt->execute();

    echo json_encode("New patient added successfully");
}

$conn->close();
?>
