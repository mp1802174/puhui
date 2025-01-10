<?php /*a:1:{s:59:"I:\phpstudy_pro\WWW\puhui\app\view\hierarchy\hierarchy.html";i:1736513869;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hierarchy Data Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 0 auto; }
        .level { margin-bottom: 20px; }
        .level h2 { margin-bottom: 10px; }
        .level ul { list-style-type: none; padding: 0; }
        .level li { padding: 5px; border: 1px solid #ccc; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="level">
            <h2><?php echo htmlentities($level); ?> 层级</h2>
            <ul>
                <?php foreach($data as $item): ?>
                <li>
                    <a href="?level=<?php echo htmlentities($next_level); ?>&<?php if(isset($item['id'])): ?>parent_id=<?php echo htmlentities($item['id']); else: ?>parent_name=<?php echo htmlentities($item['name']); ?><?php endif; ?>" target="_blank">
                        <?php echo htmlentities($item['name']); ?> - 余额: <?php if(isset($item['balance'])): ?><?php echo htmlentities($item['balance']); else: ?><?php echo htmlentities($item['total_balance']); ?><?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html> 