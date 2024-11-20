=<?php
include_once("global.php");

$basedir = __DIR__;
$dir = $_GET['dir'];
$file = $_GET['file'];
$globe = isset($_GET['dir']) ? __DIR__.'/'.$_GET['dir'] : __DIR__;
$lastdir = substr($dir, 0, strrpos($dir, '/'));

if(is_dir($globe))
{
  $files = glob($globe.'/*'); // List all files and directories in the specified path
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Pkgd.zip</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="retry.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.css" integrity="sha256-NAxhqDvtY0l4xn+YVa6WjAcmd94NNfttjNsDmNatFVc=" crossorigin="anonymous" />


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.12/codemirror.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.12/codemirror.min.js"></script>

</head>

<body>

<div class="container">
  <div class="row">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body">

          <div class="row mb-3">
            <div class="col-lg-4 col-sm-6">
              <div class="search-box mb-2 me-2">
                <div class="position-relative">
                  <input type="text" class="form-control bg-light border-light rounded" value="<?php echo $globe; ?>"> 
                  <a href="?dir=<?php echo $lastdir; ?>"> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" class="eva eva-search-outline search-icon">
                      <g data-name="Layer 2">
                        <g data-name="search">
                          <rect width="24" height="24" opacity="0"></rect>
                          <path fill="currentColor" d="M19 11H7.14l3.63-4.36a1 1 0 1 0-1.54-1.28l-5 6a1 1 0 0 0-.09.15c0 .05 0 .08-.07.13A1 1 0 0 0 4 12a1 1 0 0 0 .07.36c0 .05 0 .08.07.13a1 1 0 0 0 .09.15l5 6A1 1 0 0 0 10 19a1 1 0 0 0 .64-.23a1 1 0 0 0 .13-1.41L7.14 13H19a1 1 0 0 0 0-2"/>
                        </path>
                      </g>
                    </g>
                  </svg>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-lg-8 col-sm-6">
              <div class="mt-0 mt-sm-0 d-flex align-items-center justify-content-sm-end">

                <?php if(isset($_GET['file']) && file_exists($file) && strpos($file, $rootdir) === 0): ?> 
                  <div id="controls">
                    <input type="hidden" id="file-path" value="<?php echo htmlspecialchars($file); ?>">
                    <button onclick="saveFile()">Save</button>
                    <input type="text" id="new-name" placeholder="New name">
                    <button onclick="renameFile()">Rename</button>
                    <button onclick="deleteFile()">Delete</button>
                    <input type="text" id="permissions" placeholder="Permissions (e.g., 0755)">
                    <button onclick="chmodFile()">Chmod</button>
                  </div>
                <?php else: ?>
                  <div class="">
                    <div class="dropdown">
                      <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-plus me-1"></i> Create New
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#"><i class="mdi mdi-folder-outline me-1"></i> Folder</a>
                        <a class="dropdown-item" href="#"><i class="mdi mdi-file-outline me-1"></i> File</a>
                      </div>
                    </div>
                  </div>

                  <div class="dropdown mb-0">
                    <a class="btn btn-link text-muted dropdown-toggle p-1 mt-n2" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                      <i class="mdi mdi-dots-vertical font-size-20"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a class="dropdown-item" href="#">Share Files</a>
                      <a class="dropdown-item" href="#">Share with me</a>
                      <a class="dropdown-item" href="#">Other Actions</a>
                    </div>
                  </div>
                <?php endif; ?> 

              </div>
            </div>
          </div>

          <?php if (isset($_GET['file']) && file_exists($file) && strpos($file, $rootdir) === 0) : ?>
          <?php $code = file_get_contents($_GET['file']); ?> 
          <textarea id="editor" style="width:100%; min-height:100%;"><?=htmlspecialchars($code);?></textarea>
          <?php else: ?>

            <div class="table-responsive">
              <table class="table align-middle table-nowrap table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th scope="col"><input type="checkbox"></th>
                    <th scope="col"><?php echo $globe; ?></th>
                    <th scope="col" style="text-align: right;">File Size</th>
                    <th scope="col" style="text-align: center;">Permissions</th>
                    <th scope="col" style="text-align: left;">Mine Type</th>
                    <th scope="col">Last Modified</th>
                  </tr>
                </thead>
                <tbody>

                  <?php foreach ($files as $filePath): ?>
                    <?php if (is_dir($filePath)): ?>
                      <tr>
                        <td>
                          <input type="checkbox">
                          <div class="dropdown d-inline">
                            <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                              <i class="mdi mdi-dots-horizontal"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                              <a class="dropdown-item" href="#">Modify</a>
                              <a class="dropdown-item" href="#">Copy/Move</a>
                              <a class="dropdown-item" href="#">Download</a>
                              <div class="dropdown-divider"></div>
                              <a class="dropdown-item" href="#">Changelog</a>
                            </div>
                          </div>
                        </td>
                        <td>
                          <i class="mdi mdi-folder-outline font-size-16 align-middle text-primary me-2"></i>
                          <b><a href="?dir=<?php if(isset($dir)) { echo $dir."/"; }  echo basename($filePath); ?>"> 
                            <?php echo htmlspecialchars(basename($filePath)); ?> 
                          </a></b>
                        </td>
                        <td style="text-align: right;"><?php echo round(filesize($filePath) / 1024) . ' KB'; ?></td>
                        <td style="text-align: center;"><?php echo substr(sprintf('%o', fileperms($filePath)), -4); ?></td>
                        
                        <td style="text-align: left;">Directory</td>
                       
                        <td><?php echo timeAgo(date("d-m-Y, H:i", filemtime($filePath))); ?></td>
                        
                      </tr>
                    <?php endif; ?> 
                  <?php endforeach; ?>

                  <?php foreach ($files as $filePath): ?>
                    <?php if (!is_dir($filePath)): ?>
                      <tr>
                        <td><input type="checkbox"></td>
                        <td>
                          <div class="dropdown">
                            <a class="font-size-16 text-muted" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                              <i class="mdi mdi-text-box font-size-16 align-middle text-primary me-2"></i> 
                              <?php echo htmlspecialchars(basename($filePath)); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                              <a class="dropdown-item" href="?dir=<?php echo htmlspecialchars($dir); ?>&file=<?php echo $filePath; ?>">Modify</a> 
                              <a class="dropdown-item" href="#">Copy/Move</a>
                              <a class="dropdown-item" href="#">Download</a>
                              <div class="dropdown-divider"></div>
                              <a class="dropdown-item" href="#">Changelog</a>
                            </div>
                          </div>
                        </td>
                        
                        <td style="text-align: right;"><?php echo round(filesize($filePath) / 1024) . ' KB'; ?></td>
                        <td style="text-align: center;"><?php echo substr(sprintf('%o', fileperms($filePath)), -4); ?></td>
                        <td style="text-align: left;"><?php echo mime_content_type($filePath); ?></td>
                        <td><?php echo timeAgo(date("d-m-Y, H:i", filemtime($filePath))); ?></td>
                        </tr>

                    <?php endif; ?>
                  <?php endforeach; ?> 

                </tbody>
              </table>
            </div> 
          <?php endif; ?>
        </div> 
      </div> 
    </div> 
  </div> 
</div> 

<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
    lineNumbers: true,
    mode: "javascript", // Set the language mode
    theme: "dark" // Choose a theme
});

</script>
</body>
</html>
