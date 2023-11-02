<?php
class User
{
    private $twig, $pdo;
    
    public function __construct($twig, $db)
    {
        $this->twig = $twig;
        $this->pdo = $db->getConnection();
    }

    public function list()
    {
        $sql = "SELECT * FROM users";

        try {
            $stmt = $this->pdo->query($sql);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->twig->render('users/list.html', ['users' => $users]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function show($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return $this->twig->render('users/show.html', ['user' => $user]);
            } else {
                return 'User tidak ditemukan.';
            }
        } catch (PDOException $e) {
            return 'Database error.';
        }
    }

    public function create()
    {
        return $this->twig->render('users/create.html');
    }
    
    public function insert($requests)
    {
        $name = $requests['name'];
        $email = $requests['email'];
        $password = $requests['password'];
        $role = $requests['role'];

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$name, $email, $hashedPassword, $role]);
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function edit($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return $this->twig->render('users/edit.html', ['user' => $user]);
            } else {
                return 'User tidak ditemukan.';
            }
        } catch (PDOException $e) {
            return 'Database error.';
        }
    }

    public function update($requests)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $requests['id'];
            $name = $requests['name'];
            $email = $requests['email'];
            $role = $requests['role'];

            $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";

            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$name, $email, $role, $id]);
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
        $sql = "DELETE FROM users WHERE id = ?";

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

    public function getUpdatePasswordResponse()
    {
        if(! isset($_SESSION['user_logged_in']) && isset($_SESSION['user_id']) ) return $this->abort();

        $method = $_SERVER['REQUEST_METHOD'];

        if( $method === 'GET' ) {
            return $this->twig->render('users/password.html');
        }

        if( $method === 'POST' ) {
            $current_password       = $_POST['current_password'];
            $new_password           = $_POST['new_password'];
            $confirm_new_password   = $_POST['confirm_new_password'];
            $user                   = $this->authenticated();

            if(! (is_string($current_password) && is_string($new_password) && is_string($confirm_new_password)) ) {
                return $this->abort('Ups, something went wrong!');
            }

            if( $user ) {
                if( password_verify($current_password, $user['password']) ) {
                    if( $new_password === $confirm_new_password ) {
                        return $this->back('Password updated successfully!');
                    }

                    return $this->back('Password dont match!');
                }

                return $this->back('Invalid current password!');
            }

            return $this->abort('Ups, something went wrong!');
        }

        return $this->abort();
    }

    public function abort($message = null)
    {
        if( $message ) return $message;
        
        return 'Forbidden!';
    }

    private function redirectAfterSentMessage($to, $message)
    {
        return '<script>alert("'. $message .'"); window.location.href = "'. $to .'";</script>';
    }

    private function back($message)
    {
        return $this->redirectAfterSentMessage('index.php?route=users&action=password', $message);
    }

    private function authenticated()
    {
        if(! isset($_SESSION['user_id']) ) return false;

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        @$stmt->execute([$_SESSION['user_id']]);
        @$user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user;
    }
}

?>
