<?php
class Dashboard {
    private $twig, $pdo, $auth;
    
    public function __construct($twig, $db, $auth)
    {
        $this->twig = $twig;
        $this->pdo = $db->getConnection();
        $this->auth = $auth;
    }

    public function index()
    {
        $user_info = $this->auth->getUserInfo();
        return $this->twig->render('dashboard.html', ['user_info' => $user_info]);
    }
}
?>