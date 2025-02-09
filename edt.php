<?php
// Set allowed IP addresses
$password = "letmein";
$allowed_ips = array("127.0.0.1", "192.168.1.1", "61");

session_start();

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_cookie'] = md5($_SESSION['csrf_token']);
}

// Restrict Access
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die("Not authorized!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === "save") {
    // CSRF Token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $requestedFilename = basename($_POST['file']);
    $scriptFilename = basename(__FILE__);

    // Prevent editing of this script
    if ($requestedFilename === $scriptFilename) {
        die('Editing this script file is not allowed.');
    }

    $filePath = realpath($_POST['file']);
    $filename = $filePath;
    $fileContent = $_POST['myTextArea'];

    // Save file content
    file_put_contents($filename, $fileContent);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report-type']) && !empty($_POST['name'])) {
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/'; // Define base directory

    // Sanitize user input
    $name = trim($_POST['name']);
    $type = $_POST['report-type'] ?? 'file';



    // Ensure no directory traversal
    if (strpos($name, '..') !== false) {
        die("Error: Invalid file name.");
    }

    // Full path of the new file or directory
    $fullPath = $baseDir . $name;

    // Check if file/directory already exists
    if (file_exists($fullPath)) {
        die("Error: File or directory already exists.");
    }

    // Create file or directory
    if ($type === 'file') {
        // Create a file
        if (file_put_contents($fullPath, '') === false) {
            die("Error: Failed to create the file.");
        }
        else {
            die("Success: New file created!");
        }
    } elseif ($type === 'directory') {
        // Create a directory
        if (!mkdir($fullPath, 0755)) {
            die("Error: Failed to create the directory.");
        }
        else {
            die("Success: New directory created!");
        }
       
    

    } else {
        die("Error: Invalid selection.");
    }

    exit;
}


function listFilesAndDirectories($dir, $baseDir = 'public_html') {
    $items = glob($dir . '/*');
    $directories = [];
    $files = [];
    
    foreach ($items as $item) {
        if (is_dir($item)) {
            $directories[] = $item;
        } else {
            $files[] = $item;
        }
    }
    
    natcasesort($directories);
    natcasesort($files);
    $sortedItems = array_merge($directories, $files);
    
    $output = '';
    $parentDir = basename($dir);
    $collapseId = 'collapse-' . md5($parentDir);

    $output .= '<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '"><b>/public_html</b></a>';
    $output .= '<ul class="collapse list-unstyled" id="' . $collapseId . '">';
    
    foreach ($sortedItems as $item) {
        $relativePath = ltrim(str_replace($baseDir, '', $item), '/');
        $name = basename($item);

        if (is_dir($item)) {
            $subItems = listFilesAndDirectories($item, $baseDir);
            $subCollapseId = 'collapse-' . md5($relativePath);
            $output .= '<li class="nav-item">';
            $output .= '<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#' . $subCollapseId . '">';
            $output .= '<span class="nav-link-title">' . htmlspecialchars($name) . '</span>';
            $output .= '</a>';
            $output .= '<ul class="collapse list-unstyled ms-3" id="' . $subCollapseId . '">' . $subItems . '</ul>';
            $output .= '</li>';
        } else {
            $isZip = pathinfo($name, PATHINFO_EXTENSION) === 'zip';
            $output .= '<li class="nav-item file-item">';
            $output .= '<a class="nav-link file-link" href="?file=' . htmlspecialchars($relativePath) . '" data-path="' . htmlspecialchars($relativePath) . '">';
            $output .= '<span class="nav-link-title">' . htmlspecialchars($name) . '</span>';
            $output .= '</a>';
            
            // If the file is a .zip, add an action menu
            if ($isZip) {
                $output .= '<div class="file-menu">';
                $output .= '<button class="btn btn-sm btn-primary unzip" data-file="' . htmlspecialchars($relativePath) . '">Unzip</button>';
                $output .= '<button class="btn btn-sm btn-secondary rename" data-file="' . htmlspecialchars($relativePath) . '">Rename</button>';
                $output .= '<button class="btn btn-sm btn-danger delete" data-file="' . htmlspecialchars($relativePath) . '">Delete</button>';
                $output .= '<a class="btn btn-sm btn-success download" href="?download=' . htmlspecialchars($relativePath) . '">Download</a>';
                $output .= '</div>';
            }

            $output .= '</li>';
        }
    }
    
    $output .= '</ul>';
    
    return $output;
}


// Generate file menu
$directory = '.';
$menu = listFilesAndDirectories($directory, realpath($directory));

if (isset($_GET['file'])) {
    $filePath = realpath($directory . '/' . $_GET['file']);
  $filePath = $_GET['file'];
    $fileContent = file_get_contents($filePath);
        $fileName = basename($filePath);
    
   
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit File</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0/dist/css/tabler.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.1/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.1/theme/dracula.min.css">
    <style>
        html, body {
            height: 100vh;
            margin: 0;
            padding: 0;

        }
        .CodeMirror {
            height: 100vh !important;
            width: 100vw !important;
        }
        .context-menu {
            position: absolute;
            background: #222;
            color: #fff;
            border-radius: 4px;
            padding: 5px 0;
            display: none;
            z-index: 1000;
            border: 1px solid #444;
        }
        .context-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .context-menu ul li {
            padding: 8px 15px;
            cursor: pointer;
            background: #222;
        }
        .context-menu ul li:hover {
            background: #555;
        }
        .floating-button {
    position: absolute;
    top: 10px;  /* Adjust as needed */
    right: 10px; /* Adjust as needed */
}

    </style>
</head>
<body>



<div>
    <form id="myForm" method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="file" value="<?php echo htmlspecialchars($filePath); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <textarea id="editor" name="myTextArea"><?php echo htmlspecialchars($fileContent); ?></textarea>
        <button type="button" class="btn btn-success floating-button" onclick="saveFile();" style="z-index: 1001;">Save</button>
    </form>

</div>


    <div class="context-menu" id="contextMenu">
        <ul>
            <li onclick="copyText()">Copy</li>
            <li onclick="pasteText()">Paste</li>
            <li onclick="selectAllText()">Select All</li>
            <li onclick="saveFile()">Save File</li>
        </ul>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.63.1/codemirror.min.js"></script>
    <script>
        var editor = CodeMirror.fromTextArea(document.getElementById('editor'), {
            lineNumbers: true,
            mode: "javascript",
            theme: "dracula"
        });

        var contextMenu = document.getElementById("contextMenu");

        document.addEventListener("contextmenu", function(event) {
            event.preventDefault();
            if (event.target.closest(".CodeMirror")) {
                contextMenu.style.display = "block";
                contextMenu.style.left = event.pageX + "px";
                contextMenu.style.top = event.pageY + "px";
            }
        });

        document.addEventListener("click", function() {
            contextMenu.style.display = "none";
        });

        function copyText() {
            navigator.clipboard.writeText(editor.getSelection());
            alert("Copied to clipboard");
        }

        function pasteText() {
            navigator.clipboard.readText().then(text => {
                editor.replaceSelection(text);
            });
        }

        function selectAllText() {
            editor.execCommand("selectAll");
        }

        function saveFile() {
            
                // Update the hidden textarea with the editor's content before submission
                document.getElementById("editor").value = editor.getValue();

                var formData = new FormData(document.getElementById("myForm"));

                fetch("<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert("File Saved!");
                    console.log("Response:", data);
                })
                .catch(error => {
                    alert("Failed to save the file! Check console for details.");
                    console.error("Error:", error);
                });
            
        }
        
    </script>
</body>
</html>
<?php
exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tab System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0/dist/css/tabler.min.css">
 <link href="https://preview.tabler.io/dist/libs/dropzone/dist/dropzone.css?1738448791" rel="stylesheet"/>
  
  
  
  <style>
      .iframe-container { width: 100%; height: calc(100vh - 50px); border: none; }
      .close-tab { cursor: pointer; margin-left: 8px; color: red; }


  </style>
</head>
<body>
 <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0/dist/js/tabler.min.js"></script>
<div class="page">
  <!-- Sidebar -->
  <aside class="navbar navbar-vertical navbar-expand-sm" data-bs-theme="light">
    <div class="container-fluid">
      <h1 class="navbar-brand">
        <a href="#">Pkgd Editor</a>
      </h1>
      <div class="navbar" id="sidebar-menu">
        <ul class="navbar-nav pt-lg-3">
          <li class="nav-item"><a class="nav-link" href="">Home</a></li>
          <?php echo $menu; ?>
          </ul>
      </div>
     
        <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
  Create new File or Folder
</a>        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal2">
  Upload Files
</button>
    </div>
    
    
  </aside>

  <!-- Main Content -->
  <div class="page-wrapper" style="overflow: hide; scroll: no;">
    <div class="card">
      <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="tabList">
          <li class="nav-item">
            <a href="#tabs-home-1" class="nav-link active" data-bs-toggle="tab">Home</a>
          </li>
        </ul>
      </div>
    
        <div class="tab-content" id="tab-content" style="overflow: none;">
          <div class="tab-pane fade show active" id="tabs-home-1">
            <iframe class="iframe-container" src="index.php"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".file-link").forEach(link => {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            const filePath = this.getAttribute("data-path");
            const fileName = this.querySelector('.nav-link-title').textContent.trim();
            addTab(filePath, fileName);
        });
    });
});

function addTab(filePath, fileName) {
    let existingTab = document.getElementById(`tab-${fileName}`);

    if (existingTab) {
        new bootstrap.Tab(existingTab.querySelector("a")).show();
        return;
    }

    let tabList = document.getElementById("tabList");
    let tabContent = document.getElementById("tab-content");

    document.querySelectorAll(".tab-pane").forEach(tab => tab.classList.remove("show", "active"));

    let newTab = document.createElement("li");
    newTab.className = "nav-item";
    newTab.id = `tab-${fileName}`;
    newTab.innerHTML = `<a href="#tab-${fileName}-pane" class="nav-link" data-bs-toggle="tab">${fileName} 
        <span class="close-tab" onclick="removeTab('${fileName}')">×</span></a>`;
    tabList.appendChild(newTab);

    let newContent = document.createElement("div");
    newContent.className = "tab-pane fade show active";
    newContent.id = `tab-${fileName}-pane`;
    newContent.innerHTML = `<iframe src="?file=${filePath}" class="iframe-container"></iframe>`;
    tabContent.appendChild(newContent);

    new bootstrap.Tab(newTab.querySelector('a')).show();
}

function removeTab(fileName) {
    let tab = document.getElementById(`tab-${fileName}`);
    let content = document.getElementById(`tab-${fileName}-pane`);
    let nextTab = tab.previousElementSibling || tab.nextElementSibling;

    tab.remove();
    content.remove();

    if (nextTab) {
        new bootstrap.Tab(nextTab.querySelector("a")).show();
        document.getElementById(nextTab.querySelector("a").getAttribute("href").substring(1)).classList.add("show", "active");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".unzip").forEach(button => {
        button.addEventListener("click", function () {
            let file = this.getAttribute("data-file");
            if (confirm("Are you sure you want to unzip " + file + "?")) {
                window.location.href = "?unzip=" + encodeURIComponent(file);
            }
        });
    });

    document.querySelectorAll(".rename").forEach(button => {
        button.addEventListener("click", function () {
            let file = this.getAttribute("data-file");
            let newName = prompt("Enter new name for " + file + ":");
            if (newName) {
                window.location.href = "?rename=" + encodeURIComponent(file) + "&newname=" + encodeURIComponent(newName);
            }
        });
    });

    document.querySelectorAll(".delete").forEach(button => {
        button.addEventListener("click", function () {
            let file = this.getAttribute("data-file");
            if (confirm("Are you sure you want to delete " + file + "?")) {
                window.location.href = "?delete=" + encodeURIComponent(file);
            }
        });
    });
});

</script>



<form id="createForm" method="POST">
    <div class="modal" id="exampleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
            <div class="modal-header">
        <h5 class="modal-title">Create a File or Directory</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
                <div class="modal-body">
                    <div class="form-selectgroup-boxes row mb-3">
                        <div class="col-md-6">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="report-type" value="file" class="form-selectgroup-input" checked />
                                <span class="form-selectgroup-label d-flex align-items-center p-3">
                                    <span class="form-selectgroup-label-content">
                                        <span class="form-selectgroup-title strong mb-1">File</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="report-type" value="directory" class="form-selectgroup-input" />
                                <span class="form-selectgroup-label d-flex align-items-center p-3">
                                    <span class="form-selectgroup-label-content">
                                        <span class="form-selectgroup-title strong mb-1">Directory</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="mb">
                        <label class="form-label">Name</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text">
                                <?php $f = explode("/", $_SERVER['DOCUMENT_ROOT']); echo end($f) . "/"; ?>
                            </span>
                            <input type="text" name="name" id="name" class="form-control ps-0" placeholder="new_file.txt" autocomplete="off" required />
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">

            
                <div id="responseMessage"></div>
                    
                    <button type="submit" class="btn btn-primary ms-auto">Create</button>
                </div>
            </div>
        </div>
    </div>
</form>


   <form id="createForm2" method="POST">
    <div class="modal" id="exampleModal2" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
            <div class="modal-header">
        <h5 class="modal-title">Upload Files</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
                <div class="modal-body">
                    <div class="card">

		<div class="card-body">
				<h3 class="card-title">Upload Files</h3>
<form class="dropzone dz-clickable" id="dropzone-custom" action="./" autocomplete="off" novalidate="">
	
	<div class="dz-message">
		<h3 class="dropzone-msg-title">Drag and Drop</h3>
		<span class="dropzone-msg-desc">Open File Manager</span>
	</div>
</form>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    new Dropzone("#dropzone-custom")
  })
</script>
			</div></div>
                    
                </div>
                <div class="modal-footer">

            
                <div id="responseMessage"></div>
                    
                    <button type="submit" class="btn btn-primary ms-auto">Create</button>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
window.addEventListener("beforeunload", function (event) {
    event.preventDefault(); // Required for modern browsers
    event.returnValue = "Are you sure you want to leave this page?";
});
</script>
</body>
</html>
