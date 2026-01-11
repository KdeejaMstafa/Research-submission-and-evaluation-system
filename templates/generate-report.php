<?php
require_once '../includes/db_connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userRole   = $_SESSION['role_level'] ?? null;
$userId     = $_SESSION['user_id'] ?? null;
$reportType = $_POST['reportType'] ?? null;
$result     = null;
$error      = null;

if ($reportType) {
    switch ($reportType) {
        case 'submission_stats':
            if ($userRole === 'admin') {
                $sql = "SELECT s.study_program, COUNT(rp.paper_id) AS submissions
                        FROM research_papers rp
                        JOIN students s ON rp.submitted_by = s.user_id
                        GROUP BY s.study_program";
                $result = $connect_db->query($sql);
            }
            break;

        case 'evaluation_progress':
            if ($userRole === 'admin') {
                $sql = "SELECT status, COUNT(evaluation_id) AS count
                        FROM evaluations
                        GROUP BY status";
                $result = $connect_db->query($sql);
            }
            break;

        case 'published_papers':
            if ($userRole === 'admin') {
                $sql = "SELECT pp.title, u.username AS author_name, pp.category,
                               DATE_FORMAT(pp.publication_date, '%d %b %Y') AS publication_date
                        FROM published_papers pp
                        JOIN users u ON pp.author_id = u.user_id
                        ORDER BY pp.publication_date DESC";
                $result = $connect_db->query($sql);
            }
            break;

        case 'student_evaluations':
            if ($userRole === 'supervisor') {
                $sql = "SELECT a.title, e.status, e.rating, e.feedback, u.username AS student_name
                        FROM evaluations e
                        JOIN research_papers rp ON e.paper_id = rp.paper_id
                        JOIN assignments a ON rp.assignment_id = a.assignment_id
                        JOIN users u ON rp.submitted_by = u.user_id
                        WHERE e.evaluated_by = ?";
                $stmt = $connect_db->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $connect_db->error);
                }
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
            }
            break;

        default:
            $error = "Invalid report type selected.";
    }

    if (!$result && !$error) {
        $error = "Query failed: " . $connect_db->error;
    }

    // ✅ Log report generation into 'reports' table if successful
    if ($result && $result->num_rows >= 0) {
        $reportTypeMap = [
            'submission_stats'   => 'submission_statistics',
            'evaluation_progress'=> 'evaluation_progress',
            'published_papers'   => 'published_list',
            'student_evaluations'=> 'student_evaluation'
        ];
        $dbReportType = $reportTypeMap[$reportType] ?? null;
        if ($dbReportType) {
            $reportTitle = ucfirst(str_replace('_', ' ', $dbReportType)) . " Report";
            $filePath    = "N/A"; // placeholder

            $insert = $connect_db->prepare("INSERT INTO reports 
                (generated_by, generated_by_role, report_type, report_title, report_date, file_path) 
                VALUES (?, ?, ?, ?, CURDATE(), ?)");
            if ($insert) {
                $insert->bind_param("issss", $userId, $userRole, $dbReportType, $reportTitle, $filePath);
                $insert->execute();
            } else {
                error_log("Insert failed: " . $connect_db->error);
            }
        }
    }
}
?>

<style>
.gen-report-sec button{
  margin-bottom: 1.5em;
}
.gen-report-div{
  font-family: "open sans", 'Courier New', Courier, monospace;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  text-align: center;
  padding: 12px;
  background-color: #faf7e9;
  border-radius: 3px;
}
.gen-report-div h2{
  font-family:"Libre Baskerville", serif, 'Courier New', Courier, monospace;
}
select {
padding: 10px 12px;
border: 1px solid #ccc;
border-radius: 3px;
background-color: #fff;
font-size: 14px;
color: #333;
}
select:focus {
border-color: #00324A;
outline: none;
box-shadow: 0 0 4px rgba(0,120,215,0.4);
}

/* Scope styles to report content only */
.report-results {
  margin-top: 20px;
  padding: 15px;
  border-radius: 5px;
}

.report-results table {
  width: 100%;
  border-collapse: collapse;
}

.report-results th,
.report-results td {
  border: 1px solid #ccc;
  padding: 8px 10px;
  text-align: left;
  background-color: white;
}
  .report-results th {
  background-color: #00324A;
  color: white;
}

.report-gen-btns {
  margin-top: 10px;
  background-color: #00324A;
  color: aliceblue;
  border: none;
  padding: 8px 12px;
  border-radius: 3px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.report-gen-btns:hover {
  background-color: #0d4d6b;
}
</style>

<section class="gen-report-sec">
  <button class="report-gen-btns" type="button"
        onclick="window.location.href='<?php echo ($userRole === 'admin') ? '/rses/templates/admin-interface.php' : '/rses/templates/supervisor-dashboard.php'; ?>'">
    Back
  </button>
</section>

<div class="gen-report-div">
  <h2>Generate Reports</h2>

  <!-- Report selection form -->
  <form class="select-form" method="POST">
    <?php if ($userRole === 'admin'): ?>
      <select name="reportType" required>
        <option value="">-- Select Report Type --</option>
        <option value="submission_stats">Submission Statistics</option>
        <option value="evaluation_progress">Evaluation Progress</option>
        <option value="published_papers">Published Papers</option>
      </select>
    <?php elseif ($userRole === 'supervisor'): ?>
      <select name="reportType" required>
        <option value="">-- Select Report Type --</option>
        <option value="student_evaluations">Student Evaluations</option>
      </select>
    <?php endif; ?>
    <button class="report-gen-btns" type="submit">Confirm Report Generation</button>
  </form>

  <!-- Report results -->
  <div class="report-results">
    <?php if ($error): ?>
      <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($result && $result->num_rows > 0): ?>
      <table border="1" cellpadding="8" id="reportTable">
        <thead>
          <tr>
            <?php foreach ($result->fetch_fields() as $field): ?>
              <th><?= htmlspecialchars($field->name) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <?php foreach ($row as $value): ?>
                <td><?= htmlspecialchars($value) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <!-- Export / Print buttons -->
      <button class="report-gen-btns" onclick="window.print()">Print Report</button>
      <button class="report-gen-btns" onclick="exportCSV()">Export CSV</button>
      <button class="report-gen-btns" onclick="exportPDF()">Export PDF</button>
    <?php elseif ($reportType): ?>
      <p>No data available for this report.</p>
    <?php endif; ?>
  </div>

  <script>
    // Export table to CSV
    function exportCSV() {
      const rows = document.querySelectorAll("#reportTable tr");
      let csv = [];
      rows.forEach(row => {
        const cols = row.querySelectorAll("td, th");
        let rowData = [];
        cols.forEach(col => rowData.push('"' + col.innerText + '"'));
        csv.push(rowData.join(","));
      });
      const blob = new Blob([csv.join("\n")], { type: "text/csv" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = "report.csv";
      link.click();
    }

    // Export table to PDF (simple approach using print-to-PDF)
    function exportPDF() {
      window.print(); // user can select "Save as PDF" in print dialog
    }
  </script>
</div>
