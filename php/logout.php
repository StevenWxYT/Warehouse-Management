<?php
// 启动 Session
session_start();

// 清除 Session 变量
session_unset();

// 销毁 Session
session_destroy();

// 可选：设置登出提示（通过 URL 参数）
header("Location: dashboard.php?logout=success");
exit();
?>
