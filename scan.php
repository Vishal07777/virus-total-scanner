<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scan Results</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #d4f6d6, #2e747d);
      padding: 20px;
      color: #333;
      margin: 0;
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
      overflow-x: auto;
      animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h2, h3 {
      color: #333;
      margin-top: 0;
      position: relative;
    }

    h2::after, h3::after {
      content: '';
      display: block;
      width: 50px;
      height: 2px;
      background-color: #ADD8E6;
      margin-top: 4px;
      transition: width 0.3s ease;
    }

    h2:hover::after, h3:hover::after {
      width: 100px;
    }

    p {
      margin-bottom: 12px;
      line-height: 1.6;
      transition: color 0.3s;
    }

    p:hover {
      color: #000;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      font-size: 14px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: box-shadow 0.3s;
    }

    table:hover {
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
      vertical-align: middle;
      word-break: break-word;
      transition: background-color 0.3s;
    }

    th {
      background-color: #ADD8E6;
      color: #333;
    }

    td {
      background-color: #f9f9f9;
      color: #555;
    }

    tr:hover td {
      background-color: #eee;
    }

    .detected {
      background-color: #ffcccc;
      transition: transform 0.2s;
    }

    .detected:hover {
      transform: scale(1.02);
    }

    .not-detected {
      background-color: #ccffcc;
      transition: transform 0.2s;
    }

    .not-detected:hover {
      transform: scale(1.02);
    }

    .highlight {
      font-weight: bold;
      color: #ff6600;
    }

    input[type="file"] {
      display: block;
      margin-bottom: 15px;
      padding: 8px;
      border: 2px dashed #ccc;
      border-radius: 5px;
      width: 100%;
      transition: border-color 0.3s, background-color 0.3s;
    }

    input[type="file"]:hover {
      border-color: #999;
      background-color: #fafafa;
    }

    input[type="submit"] {
      background-color: #4CAF50;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.2s, box-shadow 0.3s;
      font-size: 14px;
    }

    input[type="submit"]:hover {
      background-color: #45a049;
      transform: scale(1.05);
      box-shadow: 0 4px 10px rgba(76, 175, 80, 0.4);
    }

    .antivirus-row {
      cursor: pointer;
    }

    .detail-row td {
      background-color: #f0f8ff;
      font-size: 13px;
    }

    @media (max-width: 600px) {
      th, td {
        font-size: 12px;
        padding: 8px;
      }
      input[type="submit"] {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <?php
    define('VT_API_KEY', 'YOUR_VT_API_KEY_HERE');
    define('VT_SCAN_URL', 'https://www.virustotal.com/vtapi/v2/file/scan');
    define('VT_REPORT_URL', 'https://www.virustotal.com/vtapi/v2/file/report');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
        if (!class_exists('CURLFile')) {
            class CURLFile extends \CURLFile {}
        }

        if (!empty(VT_API_KEY)) {
            if (!empty($_FILES["fileToUpload"]["tmp_name"])) {
                $fileToUpload = $_FILES["fileToUpload"]["tmp_name"];
                $file = new CURLFile($fileToUpload);

                $postData = array(
                    "file" => $file,
                    "apikey" => VT_API_KEY
                );

                $ch = curl_init(VT_SCAN_URL);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:multipart/form-data'));
              
                $response = curl_exec($ch);
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
              
                curl_close($ch);
              
                if ($statusCode == 200) {
                    $data = json_decode($response, true);
                    $fileId = $data["resource"];

                    $fileReportUrl = VT_REPORT_URL . "?apikey=" . VT_API_KEY . "&resource=" . $fileId;
                    $report = json_decode(file_get_contents($fileReportUrl), true);

                    echo "<h2>Scan Results</h2>";
                    if (isset($report['positives'])) {
                        if ($report['positives'] == 0) {
                            echo "<p>The file is clean. No viruses detected.</p>";
                        } else {
                            echo "<p>Virus detected! Detections: " . $report['positives'] . "/" . $report['total'] . "</p>";
                        }
                    } else {
                        echo "<p>No detections found.</p>";
                    }

                    if (isset($report['scans'])) {
                        echo "<h3>Detailed results:</h3>";
                        echo "<table>";
                        echo "<tr><th>Antivirus</th><th>Detected</th><th>Version</th><th>Result</th><th>Last Updated</th></tr>";
                        foreach ($report['scans'] as $antivirus => $scan) {
                            echo "<tr class='antivirus-row' onclick=\"toggleDetails(this)\">";
                            echo "<td>$antivirus</td>";
                            echo "<td class='" . ($scan['detected'] ? 'detected' : 'not-detected') . "'>" . ($scan['detected'] ? 'Yes' : 'No') . "</td>";
                            echo "<td>" . ($scan['version'] ?: 'N/A') . "</td>";
                            echo "<td class='highlight'>" . ($scan['result'] ?: 'No result') . "</td>";
                            echo "<td>" . (date('d-m-Y', strtotime($scan['update'])) ?: 'N/A') . "</td>";
                            echo "</tr>";
                            echo "<tr class='detail-row' style='display: none;'>";
                            echo "<td colspan='5'>";
                            echo "<strong>Engine:</strong> $antivirus<br>";
                            echo "<strong>Detected:</strong> " . ($scan['detected'] ? 'Yes' : 'No') . "<br>";
                            echo "<strong>Version:</strong> " . ($scan['version'] ?: 'N/A') . "<br>";
                            echo "<strong>Result:</strong> " . ($scan['result'] ?: 'No result') . "<br>";
                            echo "<strong>Last Update:</strong> " . (date('d-m-Y', strtotime($scan['update'])) ?: 'N/A') . "<br>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "<p>Detailed results not available.</p>";
                    }
                } else {
                    echo "<p>Error occurred while uploading the file. HTTP Status Code: $statusCode</p>";
                }
            } else {
                echo "<p>Error: No file uploaded.</p>";
            }
        } else {
            echo "<p>Error: VirusTotal API key is not set.</p>";
        }
    }
    ?>
  </div>

  <script>
    function toggleDetails(row) {
      const detailRow = row.nextElementSibling;
      if (detailRow.style.display === 'table-row') {
        detailRow.style.display = 'none';
      } else {
        detailRow.style.display = 'table-row';
      }
    }
  </script>
</body>
</html>
