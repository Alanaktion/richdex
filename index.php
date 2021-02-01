<?php

// Richdex - Content-focused directory index

$config = [
    'recursive' => true,
    'hidden' => false,
    // Dark can be true, false, or 'auto'
    'dark' => 'auto',
    'autoplay' => true,
    'loop' => false,
];

if (is_file(".config.php")) {
    $config = array_merge($config, include(".config.php"));
}

// Get directory and selected file
if ($config['recursive']) {
    $dir = (string)($_GET['dir'] ?? '');
    if (strpos($dir, '..') !== false) {
        $dir = '';
    }
    $realDir = realpath(__DIR__ . DIRECTORY_SEPARATOR . $dir);
    if ($realDir === false) {
        $dir = '';
        $realDir = __DIR__;
    }
} else {
    $dir = '';
    $realDir = __DIR__;
}
$file = $_GET['file'] ?? null;
$fileType = null;
$fileMime = null;

// Read directory
$dh = opendir($realDir);
$dirs = [];
$files = [];
while (($path = readdir($dh)) !== false) {
    if ($path == '.' || $path == '..') {
        continue;
    }
    if ($path[0] == '.' && !$config['hidden']) {
        continue;
    }
    if (is_dir($path)) {
        $dirs[] = [
            'name' => $path,
            'type' => 'dir',
            'mime' => null,
            'size' => null,
        ];
    } else {
        $currentFile = [
            'name' => $path,
            'type' => 'file',
            'mime' => mime_content_type($realDir . DIRECTORY_SEPARATOR . $path),
            'size' => filesize($realDir . DIRECTORY_SEPARATOR . $path),
        ];
        $files[] = $currentFile;
        if ($path == $file) {
            $fileType = substr($currentFile['mime'], 0, strpos($currentFile['mime'], '/'));
            $fileMime = $currentFile['mime'];
        }
    }
}
closedir($dh);

$size = function (int $bytes): string {
    if ($bytes > 1e9) {
        return round($bytes / 1e9) . ' GB';
    }
    if ($bytes > 1e6) {
        return round($bytes / 1e6) . ' MB';
    }
    if ($bytes > 1e3) {
        return round($bytes / 1e3) . ' KB';
    }
    return $bytes . ' bytes';
};

$icon = function (string $type): string {
    if (stripos($type, '/')) {
        $type = substr($type, 0, strpos($type, '/'));
    }
    $svg = '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
    switch ($type) {
        case 'dir':
            $svg .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>';
            break;
        case 'image':
            $svg .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>';
            break;
        case 'video':
            $svg .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>';
            break;
        default:
            $svg .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>';
    }
    $svg .= '</svg>';
    return $svg;
};

$linkDir = function (string $newDir) use ($dir): string {
    if ($newDir == '..') {
        $dest = dirname($dir);
        if ($dest == '.') {
            $dest = '';
        }
    } elseif ($dir) {
        $dest = $dir . '/' . $newDir;
    } else {
        $dest = $newDir;
    }
    $query = [
        'dir' => $dest,
    ];
    return htmlspecialchars('?' . http_build_query($query));
};

$linkFile = function (string $file) use ($dir): string {
    $query = [
        'dir' => $dir,
        'file' => $file,
    ];
    return htmlspecialchars('?' . http_build_query($query));
};

// The rendered page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index of <?= htmlspecialchars(basename($realDir)) ?></title>
    <style>
        /* https://tailwindcss.com/docs/customizing-colors */
        :root {
            --white: #fff;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-900: #111827;
            --true-gray-100: #F5F5F5;
            --true-gray-400: #A3A3A3;
            --true-gray-700: #404040;
            --true-gray-800: #262626;
            --true-gray-900: #171717;
            --indigo-600: #6366F1;
            height: 100%;
        }
        .light {
            --bg: var(--white);
            --text: var(--gray-900);
            --striped: var(--gray-50);
            --hover: var(--gray-200);
            --border: var(--gray-200);
            --muted: var(--gray-500);
            background-color: var(--bg);
            color: var(--text);
        }
        .dark {
            --bg: var(--true-gray-900);
            --text: var(--true-gray-100);
            --striped: var(--true-gray-800);
            --hover: var(--true-gray-700);
            --border: var(--true-gray-700);
            --muted: var(--true-gray-400);
            background-color: var(--bg);
            color: var(--text);
        }
        body {
            display: flex;
            margin: 0;
            height: 100%;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            overflow: hidden;
        }
        * {
            box-sizing: border-box;
        }
        .app {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }
        .toggle {
            position: absolute;
            top: 0;
            right: 0;
            padding: 0.5rem;
            list-style: none;
            -webkit-backdrop-filter: blur(15px);
            backdrop-filter: blur(15px);
        }
        .toggle::-webkit-details-marker {
            display: none;
        }
        @media (min-width: 768px) {
            .toggle {
                right: auto;
                left: 0;
            }
            details[open] .toggle {
                left: calc(20rem - 2rem);
            }
        }
        .list {
            border-bottom: 2px solid var(--border);
            overflow-y: auto;
            overflow-x: hidden;
            max-height: 30vh;
        }
        .preview {
            flex: 1;
        }
        @media (min-width: 768px) {
            .app {
                flex-direction: row;
            }
            .list {
                max-height: none;
                height: 100%;
                width: 20rem;
                border-right: 2px solid var(--border);
                border-bottom: none;
            }
        }

        .item {
            display: flex;
            align-items: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
            text-decoration: none;
            color: inherit;
        }
        @media (min-width: 768px) and (min-height: 768px) {
            .item {
                padding-top: 0.35rem;
                padding-bottom: 0.35rem;
            }
        }
        .item:nth-child(even) {
            background-color: var(--striped);
        }
        .item:hover,
        .item:focus {
            background-color: var(--hover);
        }
        .item.active {
            background-color: var(--indigo-600);
            color: var(--white);
        }
        .icon {
            width: 1rem;
            height: 1rem;
        }
        .item .icon {
            margin-right: 0.25rem;
        }
        .item-name {
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .preview > * {
            width: 100%;
            height: 100%;
        }
        .preview .image,
        .preview .video {
            object-fit: contain;
        }
        .preview .php {
            padding: 0.5rem;
            overflow: auto;
        }
        .dark .preview .php {
            background-color: var(--gray-50);
        }
        .preview .text {
            padding: 0.5rem;
            overflow: auto;
            white-space: pre-wrap;
        }

        /* Utilities */
        .ml-auto {
            margin-left: auto;
        }
        .ml-2 {
            margin-left: 0.5rem;
        }
        .text-muted {
            color: var(--muted);
        }
        .active .text-muted {
            color: inherit;
        }
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
    </style>
</head>
<body class="<?= $config['dark'] === true ? 'dark' : 'light'; ?>">
    <?php if ($config['dark'] == 'auto') : ?>
        <script>
            var m = matchMedia('(prefers-color-scheme: dark)');
            document.body.className = m.matches ? 'dark' : 'light';
            m.addEventListener('change', (e) => {
                document.body.className = e.matches ? 'dark' : 'light';
            })
        </script>
    <?php endif; ?>
    <div class="app">
        <details open>
            <summary class="toggle">
                <span class="sr-only">Toggle content</span>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </summary>
            <div class="list">
                <?php if ($dir) : ?>
                    <a class="item" href="<?= $linkDir('..') ?>">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span class="item-name">Back</span>
                    </a>
                <?php endif; ?>
                <?php foreach ($dirs as $item) : ?>
                    <a href="<?= $linkDir($item['name']) ?>" class="item dir">
                        <?= $icon('dir') ?>
                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                    </a>
                <?php endforeach; ?>
                <?php foreach ($files as $item) : ?>
                    <a href="<?= $linkFile($item['name']) ?>"
                        class="item file <?= $item['name'] == $file ? 'active' : null ?>">
                        <?= $icon($item['type']) ?>
                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        <span class="ml-auto text-muted"><?php echo $size($item['size']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </details>
        <div class="preview">
            <?php if ($fileType == 'image') : ?>
                <img class="image"
                    src="<?= htmlspecialchars($dir . '/' . $file) ?>"
                    type="<?= htmlspecialchars($fileMime) ?>"
                    alt="<?= htmlspecialchars($file) ?>">
            <?php elseif ($fileType == 'video') : ?>
                <video class="video" controls
                    <?= $config['autoplay'] ? 'autoplay' : '' ?>
                    <?= $config['loop'] ? 'loop' : '' ?>>
                    <source src="<?= htmlspecialchars($dir . '/' . $file) ?>"
                        type="<?= htmlspecialchars($fileMime) ?>">
                </video>
            <?php elseif ($fileType == 'text') : ?>
                <?php if (substr($file, -4) == '.php') : ?>
                    <div class="php"><?php highlight_file(($dir ?: '.') . '/' . $file) ?></div>
                <?php else : ?>
                    <div class="text"><?= htmlspecialchars(file_get_contents(($dir ?: '.') . '/' . $file)) ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
