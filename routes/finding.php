<?php

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'list':
            echo $findings->list();
            break;
        case 'create':
            echo $findings->create();
            break;  
        case 'insert':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $requests = $_POST;    
                $uploadDirectory = 'uploads/';
                $file = $_FILES['proofOfConcept'];
    
                $uploadResult = $findings->uploadFile($file, $uploadDirectory);
    
                if ($uploadResult !== false) {
                    $requests['proofOfConcept'] = $uploadResult;
    
                    $result = $findings->insert($requests);
    
                    if ($result === true) {
                        echo '<script>alert("Finding berhasil diinput."); window.location.href = "index.php?route=findings&action=list";</script>';
                    } else {
                        echo "Gagal membuat finding";
                    }
                } else {
                    echo 'Gagal mengupload file proof of concept.';
                }
            }
            break;
        case 'edit':
            if( $data = getIfExist('id') ) {
                echo $findings->edit($data);
            }

            break;
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if( $id = getIfExist('id', $_POST) ) {
                    $result = $findings->update($_POST, $_POST['id']); 
                    if ($result !== false) {
                        echo '<script>alert("Finding updated successfully."); window.location.href = "index.php?route=findings&action=list";</script>';
                    } else {
                        echo 'Failed to update finding.';
                    }
                } else {
                    echo 'The ID field is required!';
                }
            }
            break;
        case 'delete':
            if( $data = getIfExist('id') ) {
                $result = $findings->delete($data);
                if ($result !== false) {
                    echo '<script>alert("Finding berhasil dihapus."); window.location.href = "index.php?route=findings&action=list";</script>';
                } else {
                    echo 'Gagal menghapus finding.';
                }
            }

            break;
        case 'show':
            if( $data = getIfExist('id') ) {
                echo $findings->show($data);
            }

            break;
        case 'approve':
            if (isset($_GET['id'])) {
                $findingId = $_GET['id'];
                $approve = $findings->approveFinding($findingId);
                
                if ($approve === true) {
                    echo '<script>alert("Finding berhasil di-approve."); window.location.href = "index.php?route=findings&action=list";</script>';
                }
                else {
                    echo '<script>alert("Finding gagal di-approved."); window.location.href = "index.php?route=findings&action=list";</script>';
                }
                
            } else {
                echo 'User ID tidak ditemukan!';
            }
            break;
        case 'reject':
            if (isset($_GET['id'])) {
                $findingId = $_GET['id'];
                $approve = $findings->rejectFinding($findingId);
                
                if ($approve === true) {
                    echo '<script>alert("Finding berhasil di-reject."); window.location.href = "index.php?route=findings&action=list";</script>';
                }
                else {
                    echo '<script>alert("Finding gagal di-reject."); window.location.href = "index.php?route=findings&action=list";</script>';
                }
                
            } else {
                echo 'User ID tidak ditemukan!';
            }
            break;   
        case "search":
            # impelemtasi code search 
        break;               
    }
}
?>