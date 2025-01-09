<?php /*a:1:{s:59:"I:\phpstudy_pro\WWW\puhui\app\view\hierarchy\hierarchy.html";i:1736412788;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hierarchy Data Viewer</title>
    <style>
        /* 添加一些基本样式 */
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 0 auto; }
        .level { margin-bottom: 20px; }
        .level h2 { margin-bottom: 10px; }
        .level ul { list-style-type: none; padding: 0; }
        .level li { padding: 5px; border: 1px solid #ccc; margin-bottom: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div id="city-level" class="level">
            <h2>市行层级</h2>
            <ul id="city-list"></ul>
        </div>
        <div id="branch-level" class="level" style="display: none;">
            <h2>支行层级</h2>
            <ul id="branch-list"></ul>
        </div>
        <div id="accounting-level" class="level" style="display: none;">
            <h2>核算机构层级</h2>
            <ul id="accounting-list"></ul>
        </div>
        <div id="employee-level" class="level" style="display: none;">
            <h2>员工层级</h2>
            <ul id="employee-list"></ul>
        </div>
    </div>
    <script src="/static/app.js"></script>
</body>
</html> 