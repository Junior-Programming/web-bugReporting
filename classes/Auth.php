<?php
class Auth
{
    private $twig, $pdo;
    
    public function __construct($twig, $db)
    {
        $this->twig = $twig;
        $this->pdo = $db->getConnection();
    }

    public function index() {
        return $this->twig->render('login.html');
    }

    public function verifyLogin($requests)
    {
        $email = $requests['email'];
        $password = $requests['password'];

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                header("Location: index.php?route=dashboard");
            }

            echo '<script>alert("Username atau password salah!."); window.location.href = "index.php?route=login";</script>';
        } catch (PDOException $e) {
            echo "Database error";
            return false;
        }
    }

    public function registrasi()
    {
        return $this->twig->render('registrasi.html');
    }

    public function simpanRegistrasi($requests)
    {
        $name = $requests['name'];
        $email = $requests['email'];
        $password = $requests['password'];
        $role = "student";

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); #Brypt
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

    public function logout()
    {
        $cek = self::isLoggedIn();
        if ($cek === true) {
            session_unset();
            session_destroy();
        }
        header("Location: index.php?route=login");
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    }

    public function getUserId() {
        return $_SESSION['user_id'];
    }

    public function getRole() {
        return $_SESSION['user_role'];
    }

    public function getUserName() {
        return $_SESSION['user_name'];
    }
    
    public function getUserInfo() {
        $info_lengkap = $this->getUserName() . ' (' . $this->getRole(). ')';
        return $info_lengkap;
    }

    public function isAdmin()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public function isStudent()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
    }
}
?>