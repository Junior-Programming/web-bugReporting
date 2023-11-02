<?php

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'list':
            echo $users->list();
            break;
        case 'show':
            if (isset($_GET['id'])) {
                $userId = $_GET['id'];
                echo $users->show($userId);
            } else {
                echo 'User ID tidak ditemukan!';
            }
            break;
        case 'create':
            echo $users->create();
            break;
        case 'insert':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $users->insert($_POST); 
                if ($result !== false) {
                    echo '<script>alert("User created successfully."); window.location.href = "index.php?route=users&action=list";</script>';
                } else {
                    echo 'Failed to create user.';
                }
            }
            break; 
        case 'edit':
            if (isset($_GET['id'])) {
                $userId = $_GET['id'];
                echo $users->edit($userId);
            } else {
                echo 'User ID tidak ditemukan!';
            }
            break;
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $users->update($_POST, $_POST['id']); 
                if ($result !== false) {
                    echo '<script>alert("User updated successfully."); window.location.href = "index.php?route=users&action=list";</script>';
                } else {
                    echo 'Failed to update user.';
                }
            }
            break;
        case 'delete':
            if (isset($_GET['id'])) {
                $userId = $_GET['id'];
                $result = $users->delete($userId);
                if ($result !== false) {
                    echo '<script>alert("User berhasil dihapus."); window.location.href = "index.php?route=users&action=list";</script>';
                } else {
                    echo 'Gagal menghapus user.';
                }
            } else {
                echo 'User ID tidak ditemukan!';
            }
            break;
        case 'password':
            if( isset($_SESSION['user_logged_in']) ) {
                return render( $users->getUpdatePasswordResponse() );
            } else {
                return render( $users->abort() );
            }
            break;
    }
}
?>