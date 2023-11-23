<?php

session_start();


// Load vendor
require_once 'vendor/autoload.php';

// Load app classes
require_once 'helpers.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';
require_once 'classes/Finding.php';
require_once 'classes/Dashboard.php';
require_once 'classes/Report.php';

$db = new Database();

$loader = new Twig\Loader\FilesystemLoader('templates');
$twig = new Twig\Environment($loader);
$twig->addGlobal('session', $_SESSION);

$auth = new Auth($twig, $db);
$users = new User($twig, $db);
$findings = new Finding($twig, $db, $auth);
$dashboard = new Dashboard($twig, $db, $auth);

$action = isset($_GET['route']) ? $_GET['route'] : 'login';


switch ($action) {
    case 'login':
        echo $auth->index(); #tampilkan form login
        break;
    case 'verify-login': #ketika tombol login ditekan
        $result = $auth->verifyLogin($_POST);
        if ($result === true) {
            header("Location: index.php?route=dashboard");
        } else {
            echo '<script>alert("Username atau password salah!."); window.location.href = "index.php?route=login";</script>';
        }
        break;
    case 'registrasi': #tampilkan form registrasi
        echo $auth->registrasi();
        break;
    case 'simpan-registrasi': #ketika tombol registrasi disimpan
        $result = $auth->simpanRegistrasi($_POST);
        if ($result === true) {
            echo '<script>alert("Registrasi berhasil! Silahkan login!."); window.location.href = "index.php?route=login";</script>';
        } else {
            echo '<script>alert("Username atau password salah!."); window.location.href = "index.php?route=login";</script>';

        }
        break;
    case 'logout': #ketika tombol logout ditekan
        if ($auth->isLoggedIn()) {
            $auth->logout();
        }
        header("Location: index.php?route=login");
        break;
    case 'dashboard': #tampilkan halaman dashboard
        if ($auth->isLoggedIn()) {
            echo $dashboard->index();
        }
        else {
            echo "Anda harus login dulu ya bossss!";
        }
        break;
    case 'users': 
        if ($auth->isLoggedIn() and $auth->isAdmin()) {
            include_once "routes/user.php"; 
            }
        else {
            echo "Anda harus login dulu ya bossss!";
        }
        break;
    case 'findings':
        include_once "routes/finding.php";
        break;
    case 'generate_report': #export finding dengan status approved ke PDF
        $html = $findings->getAll();
        Report::generatePDFFromHTML($html);
        break;
    default:
        echo $twig->render('404.html');
        break;
}

?>