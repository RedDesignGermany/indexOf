<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>IndexOfRDG</title>
    <link rel="stylesheet" href="fontawesome.css">
    <link rel="icon" href="http://localhost/favicon.ico" type="image/x-icon">

    <style>
        body {
            font-family: Arial, sans-serif; text-align:center;
        }
        a {
          padding: 5px 15px;
        }
        a:hover {
          color: #fff;
          background: #b00808;
          border-radius: 930px;
          padding: 5px 15px;
          transition: all 0.2s ease-in-out;
        }
        .dir { background-color: #f0f0f0; }
        table { border-collapse: collapse; table-layout: auto; margin:0 auto;}
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }

        @media (prefers-color-scheme: light) {
            body {
                background-color: #ffffff;
                color: #000000;
            }
            a {color: #1e1e1e}
            th, td { border: 1px solid #ccc; }
            th { background-color: #f9f9f9; }
            .dir { background-color: #e0e0e0; }
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1e1e1e;
                color: #ffffff;
            }
            a {color: #f2f2f2;}
            th, td { border: 1px solid #444; }
            th { background-color: #333; }
            .dir { background-color: #2a2a2a; }
        }
    </style>
</head>
<body>
<h1>Index Of RDG Server</h1>
<?php
function listFilesAndDirs($dir) {
    $folders = [];
    $files = [];
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != ".." && $file[0] != ".") { // Ignoriere versteckte Dateien
                    $path = $dir . DIRECTORY_SEPARATOR . $file;
                    $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($path));
                    $item = [
                        'name' => $file,
                        'path' => $relativePath,
                        'is_dir' => is_dir($path),
                        'size' => is_dir($path) ? 0 : filesize($path),
                        'mtime' => filemtime($path)
                    ];
                    if ($item['is_dir']) {
                        $folders[] = $item;
                    } else {
                        $files[] = $item;
                    }
                }
            }
            closedir($dh);
        }
    }

    usort($folders, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    usort($files, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    echo "<table>";
    echo "<tr><th>Name</th><th>Größe</th><th>Datum</th></tr>";

    $parentDir = dirname($dir);
    if ($parentDir != $dir) { // Verhindert, dass es sich selbst verlinkt
        $relativeParentPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($parentDir));
        echo "<tr class='dir'><td><a href='?dir=" . urlencode($relativeParentPath) . "'><i class='fas fa-level-up-alt'></i> Parent Directory</a></td><td>-</td><td>-</td></tr>";
    }

    foreach ($folders as $item) {
        $name = htmlspecialchars($item['name']);
        $path = htmlspecialchars($item['path']);
        $size = '-';
        $date = date("Y-m-d H:i:s", $item['mtime']);
        echo "<tr class='dir'><td><a href='?dir=" . urlencode($path) . "'><i class='fas fa-folder'></i> $name</a></td><td>$size</td><td>$date</td></tr>";
    }

    foreach ($files as $item) {
        $name = htmlspecialchars($item['name']);
        $path = htmlspecialchars($item['path']);
        $size = filesize_format($item['size']);
        $date = date("Y-m-d H:i:s", $item['mtime']);
        echo "<tr><td><a href='$path'>$name</a></td><td>$size</td><td>$date</td></tr>";
    }

    echo "</table>";
}

function filesize_format($bytes, $decimals = 2) {
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . $sizes[$factor];
}

$directory = isset($_GET['dir']) ? realpath($_GET['dir']) : "/Users/exit/Sites"; // Startet im Root-Verzeichnis oder im angegebenen Verzeichnis
listFilesAndDirs($directory);
?>
</body>
</html>
