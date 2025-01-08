<?php /*a:1:{s:58:"I:\phpstudy_pro\WWW\puhui\app\view\daily_record\index.html";i:1735631278;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Excel导入</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/bootstrap/css/bootstrap.min.css">
    <style>
        .progress {
            width: 100%;
            min-width: 200px;
            background-color: var(--bs-gray-200);
        }
        .progress-bar {
            transition: width 0.5s ease-in-out;
            min-width: 2em;
        }
        .import-result {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Excel文件导入
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="file" class="form-label">选择Excel文件</label>
                                <input type="file" class="form-control" id="file" name="file" accept=".xls,.xlsx">
                                <small class="form-text text-muted">支持.xls和.xlsx格式</small>
                            </div>
                            <button type="submit" class="btn btn-primary">开始导入</button>
                            <button type="button" class="btn btn-secondary" id="dataButton">数据处理</button>
                        </form>
                        <div id="progress" class="mt-3 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="flex-grow-1">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger ms-3" id="stopImport">停止导入</button>
                            </div>
                            <small class="text-muted">
                                总记录数（含表头行）：<span id="totalCount">0</span>条<br>
                                已处理：<span id="currentCount">0</span>条
                                （成功：<span id="successCount" class="text-success">0</span>条，
                                失败：<span id="errorCount" class="text-danger">0</span>条）
                            </small>
                        </div>
                        <div id="result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../static/jquery/jquery.min.js"></script>
    <script src="../static/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function() {
            var minDisplayTime = 2000; // 最小显示时间，单位为毫秒
            var startTime;
            var loadingInterval;

            function startLoadingAnimation() {
                var dots = 0;
                loadingInterval = setInterval(function() {
                    dots = (dots + 1) % 10; // 修改为 10，这样会显示 0 到 9 个点
                    var text = '正在解析文件，请稍候' + '.'.repeat(dots);
                    $('#loadingMessage').text(text);
                }, 500);
            }

            function stopLoadingAnimation() {
                clearInterval(loadingInterval);
            }

            function checkProgress() {
                var filePath = $('#uploadForm').data('filepath');
                
                $.ajax({
                    url: '/daily_record/import_progress',
                    data: { filePath: filePath },
                    success: function(res) {
                        if (res.code === 1 && res.data) {
                            var data = res.data;
                            
                            // 更新进度条
                            var $bar = $('.progress-bar');
                            $bar.css('width', data.percent + '%')
                               .text(data.percent + '%');

                            // 更新计数
                            $('#totalCount').text(data.total);
                            $('#currentCount').text(data.current);
                            $('#successCount').text(data.success);
                            $('#errorCount').text(data.error);

                            if (data.status === 'completed' || data.status === 'error') {
                                var resultMessage = '<div class="alert alert-' + 
                                    (data.status === 'completed' ? 'success' : 'danger') + '">' +
                                    '<p>' + (data.message || '导入完成，成功：' + data.success + '条，失败：' + data.error + '条') + '</p>';
                                
                                // 添加错误详情显示
                                if (data.errorMessages && data.errorMessages.length > 0) {
                                    resultMessage += '<p>错误详情：</p><ul>';
                                    data.errorMessages.forEach(function(error) {
                                        resultMessage += '<li>' + error + '</li>';
                                    });
                                    resultMessage += '</ul>';
                                }
                                
                                resultMessage += '</div>';
                                $('#result').html(resultMessage);
                            } else {
                                setTimeout(checkProgress, 500);
                            }
                        }
                    }
                });
            }

            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                if (!$('#file').val()) {
                    $('#result').html('<div class="alert alert-danger">请选择要导入的Excel文件</div>');
                    return;
                }

                $('#progress').removeClass('d-none');
                $('.progress-bar').css('width', '1%').text('1%'); // 初始进度
                $('#totalCount').text('0');
                $('#currentCount').text('0');
                $('#successCount').text('0');
                $('#errorCount').text('0');
                $('#result').html('<div id="loadingMessage" class="alert alert-info">正在解析文件，请稍候</div>'); // 提示信息

                startLoadingAnimation(); // 开始动画
                startTime = new Date().getTime(); // 记录开始时间

                $.ajax({
                    url: '/daily_record/import',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.code === 1) {
                            $('#uploadForm').data('filepath', response.filePath);
                            checkProgress();
                        } else {
                            stopLoadingAnimation(); // 停止动画
                            $('#progress').addClass('d-none');
                            $('#result').html(
                                '<div class="alert alert-danger">' + 
                                response.msg +
                                '</div>'
                            );
                        }
                    }
                });
            });

            $('#stopImport').on('click', function() {
                if (confirm('确定要停止导入吗？')) {
                    $.get('/daily_record/stopImport', function(response) {
                        if (response.code === 1) {
                            stopLoadingAnimation(); // 停止动画
                            $('#progress').addClass('d-none');
                            $('#result').html(
                                '<div class="alert alert-warning">导入已停止</div>'
                            );
                        }
                    });
                }
            });

            // 数据处理按钮点击事件
            $('#dataButton').on('click', function() {
                window.location.href = 'http://puhui/import/data';
            });
        });
    </script>
</body>
</html> 