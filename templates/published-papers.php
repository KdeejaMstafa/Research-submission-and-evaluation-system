<?php
require_once '../includes/db_connection.php';
include '../includes/header.php';

// Query published papers with author info and extra fields
$sqlQuery = "SELECT 
    pp.title AS paper_title,
    pp.file_path,
    u.username AS author_name,
    s.student_id,
    s.study_program,
    pp.category AS category,
    DATE_FORMAT(pp.publication_date, '%d %b %Y') AS publication_date
FROM published_papers pp
JOIN users u ON pp.author_id = u.user_id
JOIN students s ON pp.author_id = s.user_id
ORDER BY pp.publication_date DESC";


$result = $connect_db->query($sqlQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <title>Published Papers</title>
</head>
<body>
  <div class="published-rep-header"> <!-- published repository header -->
    <h2>PUBLISHED PAPERS</h2>
  </div>

  <div class="published-rep-main-div">
    <div class="search-area-div">
      <input type="text" id="searchInput" placeholder="Search by title, author, student ID, program, category, or date..." onkeyup="filterTable()" />
      <button onclick="filterTable()">SEARCH</button>
    </div>

    <div class="published-papers-list">
      <table id="papersTable">
        <thead>
          <tr>
            <th>TITLE</th>
            <th>AUTHOR</th>
            <th>STUDENT ID</th>
            <th>STUDY PROGRAM</th>
            <th>CATEGORY</th>
            <th>PUBLISHED DATE</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td>
                  <a class="published-p-title" href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">
                    <?= htmlspecialchars($row['paper_title']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($row['author_name']) ?></td>
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['study_program']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['publication_date']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6">No published papers found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function filterTable() {
      const input = document.getElementById("searchInput").value.toLowerCase();
      const rows = document.querySelectorAll("#papersTable tbody tr");

      rows.forEach(row => {
        const title        = row.cells[0].innerText.toLowerCase();
        const author       = row.cells[1].innerText.toLowerCase();
        const studentId    = row.cells[2].innerText.toLowerCase();
        const studyProgram = row.cells[3].innerText.toLowerCase();
        const category     = row.cells[4].innerText.toLowerCase();
        const pubDate      = row.cells[5].innerText.toLowerCase();

        row.style.display = (
          title.includes(input) ||
          author.includes(input) ||
          studentId.includes(input) ||
          studyProgram.includes(input) ||
          category.includes(input) ||
          pubDate.includes(input)
        ) ? "" : "none";
      });
    }
  </script>
</body>
</html>