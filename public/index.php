<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Seiten Übersicht</title>
    <style>
        :root {
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --primary: #4f46e5;
            --border-color: #e5e7eb;
        }

        body { 
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            margin-top: 0;
            font-size: 1.5rem;
            color: var(--text-main);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        ul { 
            list-style: none; 
            padding: 0; 
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        li a { 
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            background-color: #f9fafb;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main); 
            text-decoration: none; 
            font-weight: 500;
            transition: all 0.2s ease;
        }

        li a::before {
            content: "📄";
            margin-right: 12px;
            font-size: 1.2rem;
            opacity: 0.8;
        }

        li a:hover { 
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        .empty-state {
            text-align: center;
            color: var(--text-muted);
            padding: 2rem 0;
            font-style: italic;
            background: transparent;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Verfügbare PHP-Seiten</h1>
        <ul>
            <?php
            $php_files = glob("*.php");
            $files_found = false;

            foreach ($php_files as $file) {
                if ($file !== "index.php") {
                    $files_found = true;
                    echo "<li><a href=\"" . htmlspecialchars($file) . "\">" . htmlspecialchars($file) . "</a></li>";
                }
            }

            if (!$files_found) {
                echo "<li class=\"empty-state\">Keine weiteren PHP-Dateien in diesem Verzeichnis gefunden.</li>";
            }
            ?>
        </ul>
    </div>
</body>
</html>