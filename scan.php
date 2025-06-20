<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Results</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php
    define('VT_API_KEY', '27d1f21d50ab35a0382587175fdd76a3e34323a22b9b813d2aeb72a9d250fbc1');
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
                    if(isset($report['positives'])) {
                        if($report['positives'] == 0) {
                            echo "<p>The file is clean. No viruses detected.</p>";
                        } else {
                            echo "<p>Virus detected! Detections: " . $report['positives'] . "/" . $report['total'] . "</p>";
                        }
                    } else {
                        echo "<p>No detections found.</p>";
                    }
                    if(isset($report['scans'])) {
                        echo "<h3>Detailed results:</h3>";
                        echo "<table>";
                        echo "<tr><th>Antivirus</th><th>Detected</th><th>Version</th><th>Result</th><th>Last Updated</th></tr>";
                        foreach ($report['scans'] as $antivirus => $scan) {
                            echo "<tr>";
                            echo "<td>$antivirus</td>";
                            echo "<td class='" . ($scan['detected'] ? 'detected' : 'not-detected') . "'>" . ($scan['detected'] ? 'Yes' : 'No') . "</td>";
                            echo "<td>" . ($scan['version'] ?: 'N/A') . "</td>";
                            echo "<td class='highlight'>" . ($scan['result'] ?: 'No result') . "</td>";
                            echo "<td>" . (date('d-m-Y', strtotime($scan['update'])) ?: 'N/A') . "</td>";
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
</body>
</html>
