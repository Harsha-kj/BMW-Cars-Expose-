<?php
require_once 'session_init.php';

// Validate admin session
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "test_drive");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $id = intval($_POST['id']);
        switch ($_POST['action']) {
            case 'confirm':
                $stmt = $conn->prepare("UPDATE test_drive_submissions SET status = 'confirmed' WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $_SESSION['message'] = "Test drive #$id confirmed successfully!";
                break;
            case 'reject':
                $stmt = $conn->prepare("UPDATE test_drive_submissions SET status = 'rejected' WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $_SESSION['message'] = "Test drive #$id rejected.";
                break;
        }
        header("Location: testdrive_submissions.php");
        exit();
    }
}

// Get all test drive submissions
$query = "SELECT * FROM test_drive_submissions ORDER BY submission_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Drive Submissions | Admin Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&family=Syncopate:wght@700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #0a0a0a;
            color: #fff;
            margin: 0;
            padding: 20px;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(20, 20, 20, 0.9);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid #FF6B00;
            box-shadow: 0 0 30px rgba(255, 107, 0, 0.3);
        }
        
        h1 {
            color: #FF6B00;
            font-family: 'Syncopate', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2rem;
            text-shadow: 0 0 10px rgba(255, 107, 0, 0.7);
        }
        
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: rgba(0, 200, 0, 0.2);
            border: 1px solid #00C851;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-group label {
            font-size: 0.9rem;
            color: #aaa;
        }
        
        .filter-group select, .filter-group input {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255, 107, 0, 0.3);
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 107, 0, 0.3);
        }
        
        th {
            background-color: rgba(255, 107, 0, 0.2);
            color: #FF6B00;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        
        tr:hover {
            background-color: rgba(255, 107, 0, 0.05);
        }
        
        .status-pending {
            color: #FFA500;
            font-weight: bold;
        }
        
        .status-confirmed {
            color: #00FF00;
            font-weight: bold;
        }
        
        .status-rejected {
            color: #ff4444;
            font-weight: bold;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .confirm-btn {
            background: linear-gradient(135deg, #00C851, #007E33);
            color: white;
        }
        
        .reject-btn {
            background: linear-gradient(135deg, #ff4444, #CC0000);
            color: white;
        }
        
        .view-btn {
            background: linear-gradient(135deg, #33b5e5, #0099CC);
            color: white;
        }
        
        .document-link {
            color: #FF6B00;
            text-decoration: none;
            margin: 0 5px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        
        .document-link:hover {
            text-decoration: underline;
            color: #FF9500;
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 1rem;
            color: #FF6B00;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-btn:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <h1><i class="fas fa-car"></i> Test Drive Submissions</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="filters">
            <div class="filter-group">
                <label for="status-filter"><i class="fas fa-filter"></i> Status:</label>
                <select id="status-filter">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="date-from"><i class="fas fa-calendar"></i> From:</label>
                <input type="date" id="date-from">
            </div>
            <div class="filter-group">
                <label for="date-to"><i class="fas fa-calendar"></i> To:</label>
                <input type="date" id="date-to">
            </div>
            <div class="filter-group">
                <label for="search"><i class="fas fa-search"></i> Search:</label>
                <input type="text" id="search" placeholder="Name, Email, or Car Model">
            </div>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <table id="submissions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Car Model</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Documents</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-status="<?= strtolower($row['status']) ?>" 
                            data-date="<?= date('Y-m-d', strtotime($row['submission_date'])) ?>">
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td><?= htmlspecialchars($row['car_model']) ?></td>
                            <td><?= date('M d, Y H:i', strtotime($row['submission_date'])) ?></td>
                            <td class="status-<?= strtolower($row['status']) ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])) ?>
                            </td>
                            <td>
                                <a href="<?= htmlspecialchars($row['photo_path']) ?>" class="document-link" target="_blank">
                                    <i class="fas fa-image"></i> Photo
                                </a>
                                <a href="<?= htmlspecialchars($row['dl_path']) ?>" class="document-link" target="_blank">
                                    <i class="fas fa-id-card"></i> License
                                </a>
                                <a href="<?= htmlspecialchars($row['aadhar_path']) ?>" class="document-link" target="_blank">
                                    <i class="fas fa-address-card"></i> Aadhaar
                                </a>
                            </td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="button" class="action-btn view-btn" onclick="viewDetails(<?= $row['id'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <button type="submit" name="action" value="confirm" class="action-btn confirm-btn">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                        <button type="submit" name="action" value="reject" class="action-btn reject-btn">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No test drive submissions found.</p>
        <?php endif; ?>
    </div>

    <script>
        // View details function
        function viewDetails(id) {
            window.location.href = 'testdrive_details.php?id=' + id;
        }
        
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('status-filter');
            const dateFrom = document.getElementById('date-from');
            const dateTo = document.getElementById('date-to');
            const searchInput = document.getElementById('search');
            const rows = document.querySelectorAll('#submissions-table tbody tr');
            
            function applyFilters() {
                const statusValue = statusFilter.value;
                const dateFromValue = dateFrom.value;
                const dateToValue = dateTo.value;
                const searchValue = searchInput.value.toLowerCase();
                
                rows.forEach(row => {
                    const rowStatus = row.getAttribute('data-status');
                    const rowDate = row.getAttribute('data-date');
                    const rowText = row.textContent.toLowerCase();
                    
                    const statusMatch = statusValue === 'all' || rowStatus === statusValue;
                    const dateMatch = (!dateFromValue || rowDate >= dateFromValue) && 
                                     (!dateToValue || rowDate <= dateToValue);
                    const searchMatch = !searchValue || rowText.includes(searchValue);
                    
                    row.style.display = statusMatch && dateMatch && searchMatch ? '' : 'none';
                });
            }
            
            statusFilter.addEventListener('change', applyFilters);
            dateFrom.addEventListener('change', applyFilters);
            dateTo.addEventListener('change', applyFilters);
            searchInput.addEventListener('input', applyFilters);
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>