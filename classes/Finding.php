<?php
class Finding
{    
    private $twig, $pdo, $auth;
    
    public function __construct($twig, $db, $auth)
    {
        $this->twig = $twig;
        $this->pdo = $db->getConnection();
        $this->auth = $auth;
    }

    public function list()
    {
        $keyword = $_GET['keyword'] ?? '';

        try {
            if ($this->auth->isAdmin() === true) {
                $stmt = $this->pdo->prepare("select * from `findings` where `title` LIKE '%".$keyword."%' ORDER BY `id` DESC");
            } else {
                $user_id = $this->auth->getUserId();
                $stmt = $this->pdo->prepare("select * from `findings` where `findings`.`user_id` = ".$user_id." and `findings`.`user_id` is not null and `title` LIKE '%".$keyword."%' ORDER BY `id` DESC");
            }

            $stmt->execute();
            $findings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->twig->render('findings/list.html', ['findings' => $findings, 'keyword' => $keyword]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function show($id)
    {
        $sql = "SELECT * FROM findings WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $finding = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($finding) {
                return $this->twig->render('findings/show.html', ['finding' => $finding]);
            } else {
                return 'Finding tidak ditemukan.';
            }
        } catch (PDOException $e) {
            return 'Database error.';
        }
    }

    public function create()
    {
        return $this->twig->render('findings/create.html');
    }

    public function insert($requests)
    {
        $title = isset($requests['title']) ? $requests['title'] : null;
        $description = isset($requests['description']) ? $requests['description'] : null;
        $asset_name = isset($requests['asset_name']) ? $requests['asset_name'] : null;
        $severity = isset($requests['severity']) ? $requests['severity'] : null;
        $proofOfConcept = isset($requests['proofOfConcept']) ? $requests['proofOfConcept'] : null;
        $poc_video_url = isset($requests['poc_video_url']) ? $requests['poc_video_url'] : null;        
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $status = 'submitted'; #approved dan rejected

        if (!$title or !$description or !$asset_name or !$severity or !$proofOfConcept or !$status) {
            echo "Ada field yang lupa diisi.";
            return;
        }
    
        $sql = "INSERT INTO findings (title, severity, asset_name, user_id, description, proofOfConcept, poc_video_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$title, $severity, $asset_name, $user_id, $description, $proofOfConcept, $poc_video_url, $status]);
    
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                throw new Exception("Insert finding gagal disimpan!");
            }
        } catch (PDOException $e) {
            throw new Exception("PDO Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function edit($id)
    {
        $sql = "SELECT * FROM findings WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $finding = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($finding) {
                return $this->twig->render('findings/edit.html', ['finding' => $finding]);
            } else {
                return 'Finding not found.';
            }
        } catch (PDOException $e) {
            return 'Database error.';
        }
    }

    public function update($requests)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $requests['id'];
            $title = $requests['title'];
            $severity = $requests['severity'];
            $description = $requests['description'];
            $asset_name = $requests['asset_name'];
            $poc_video_url = $requests['poc_video_url'];

            $sql = "UPDATE findings SET title = ?, severity = ?, description = ?, asset_name = ?, poc_video_url = ? WHERE id = ?";

            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$title, $severity, $description, $asset_name, $poc_video_url, $id]);
                if ($stmt->rowCount() > 0) {
                    return true;
                } else {
                    return false;
                }
            } catch (PDOException $e) {
                return false;
            }
        }

        return false;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM findings WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                return true; 
            } else {
                return false; 
            }
        } catch (PDOException $e) {
            echo "Database error";
            return false;
        }
    }

    public function uploadFile($file, $uploadDirectory)
    {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $proofOfConceptFileName = $file['name'];
            $proofOfConceptTmpName = $file['tmp_name'];
            
            $uniqueFileName = uniqid() . '_' . $proofOfConceptFileName;

            if (move_uploaded_file($proofOfConceptTmpName, $uploadDirectory . $uniqueFileName)) {
                return $uniqueFileName;
            } else {
                return false; 
            }
        } else {
            return false; 
        }
    }

    public function generateReport()
    {
        $sql = "SELECT * FROM findings WHERE status='closed'";

        try {
            $stmt = $this->pdo->query($sql);
            $findings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->twig->render('findings/report.html', ['findings' => $findings]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function approveFinding($id) {
        $sql = "UPDATE findings SET status = ?  WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['approved', $id]);

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function rejectFinding($id) {
        $sql = "UPDATE findings SET status = ?  WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['rejected', $id]);

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAll()
    {
        $sql = "SELECT * FROM findings f LEFT JOIN users u ON f.user_id = u.id WHERE f.status='approved'";

        try {
            $stmt = $this->pdo->query($sql);
            $findings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            date_default_timezone_set('Asia/Jakarta');
            $current_date = date('d/m/Y H:i:s');

            return $this->twig->render('findings/report.html', ['findings' => $findings, 'current_date'=>$current_date]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function searchFinding($keyword) {
        /* $query = "SELECT * FROM findings WHERE title LIKE :keyword AND severity LIKE :keywoard AND asset_name LIKE :keyword";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function close() {
        $this->pdo = null; */

    }

}
?>