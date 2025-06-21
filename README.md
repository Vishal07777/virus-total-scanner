VirusTotal Scanner 🔍

This is a simple web application that allows users to securely upload files and scan them for viruses using the VirusTotal API.


🌐 Live demo: https://virus-total-scanner.free.nf


🚀 Features

✅Upload files (.pdf, .docx, .zip, .txt) and scan for malware.
✅View detailed scan results from multiple antivirus engines.
✅Expandable rows to show additional details, including antivirus websites.
✅Modern responsive design.
✅Powered by VirusTotal API.


📂 Project Structure

/virus-total-scanner
 ├── index.html        # Frontend upload form
 ├── scan.php          # PHP backend to handle upload and API call
 ├── README.md         # Project documentation


🔑 Requirements

PHP server (e.g., XAMPP, InfinityFree, 000webhost)
Internet connection (to access VirusTotal API)
VirusTotal API key (replace VT_API_KEY in scan.php)


📝 Notes

⚠ API Rate Limits: Free VirusTotal API keys have usage limits (e.g., 4 requests per minute).
⚠ Security: This app is for educational purposes. Do not use for sensitive or large-scale commercial applications without proper security measures.


👉 Replace Placeholders

define('VT_API_KEY', 'YOUR_VT_API_KEY_HERE');
Replace YOUR_VT_API_KEY_HERE with your actual VirusTotal API key.


🛠 GitHub Repo URL
https://github.com/Vishal07777/virus-total-scanner.git


🙌 Credits

VirusTotal API
Developed by Vishal Ghadi