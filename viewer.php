<?php if(isset($_GET['f'])) { $f = trim($_GET['f']); } else { die("404"); } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Viewer</title>
    <link rel="manifest" href="manifest.json">

    <style>
        #fileContent {
            width: 100%;
            min-height: 800px;
        }
    </style>
</head>
<body>
    <label for="fileSelect">Choose a file:</label>
    <select id="fileSelect" onchange="loadFileContent(this.value)">
        <option value="">--Select a file--</option>
        <option value="a.txt">a.txt</option>
        <option value="asd.html"s>asd.html</option>
        <option value="image.php">image.php</option>
    </select>
    <br><br>
    <textarea id="fileContent"></textarea>
    <script src="assets.js"></script>
</body>
</html>
