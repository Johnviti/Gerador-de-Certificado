<?php
// session_start();


// Destruir todas as variáveis de sessão.
$_SESSION = array();

// Se é desejado destruir a sessão completamente, apague também o cookie de sessão.
// Note: Isto destruirá a sessão, e não apenas os dados de sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrua a sessão.
session_destroy();

// Redirecionar para a página inicial (index.php)
header("Location: index.php");
exit();
?>
