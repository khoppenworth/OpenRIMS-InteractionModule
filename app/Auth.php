<?php
class Auth
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function attempt(string $email, string $password): bool
    {
        $statement = $this->pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        session_regenerate_id(true);

        return true;
    }

    public function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public function hasRole(string ...$roles): bool
    {
        if (!$this->check()) {
            return false;
        }

        return in_array($_SESSION['user']['role'], $roles, true);
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
    }
}
