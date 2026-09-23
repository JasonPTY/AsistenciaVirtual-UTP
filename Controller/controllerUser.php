<?php
session_start();

require_once __DIR__ . '/../app/core/auth/auth.php';
require_once __DIR__ . '/../app/modules/user/userRepository.php';

const MAX_ATTEMPTS   = 3;
const BLOCK_DURATION = 180;

$userRepository = new UserRepository();

$showModal     = false;
$blockModal    = false;
$remainingTime = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf($_POST['csrf_token'] ?? null);
    $correo = trim($_POST['correo'] ?? '');
    $pass   =      $_POST['pass']   ?? '';

    if ($userRepository->isBlocked($correo, MAX_ATTEMPTS, BLOCK_DURATION)) {
        $blockModal    = true;
        $remainingTime = $userRepository->getRemainingBlockTime($correo, BLOCK_DURATION);

    } else {
        $usuario = $userRepository->getUserByEmail($correo);

        $passwordNeedsMigration = false;
        $passwordOk = false;

        if ($usuario && password_verify($pass, $usuario['pass'])) {
            $passwordOk = true;
        } elseif ($usuario && password_get_info($usuario['pass'])['algo'] === 0
            && hash_equals($usuario['pass'], $pass)) {
            $passwordOk = true;
            $passwordNeedsMigration = true;
        }

        if ($passwordOk) {
            $userRepository->resetLoginAttempts($correo);
            if ($passwordNeedsMigration) {
                $userRepository->updatePasswordHash($usuario['cedula'], $pass);
            }
            session_regenerate_id(true);

            $_SESSION['loggedin'] = true;
            $_SESSION['cedula']   = $usuario['cedula'];
            $_SESSION['nombre']   = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['id_tipoUsuario'] = (int) $usuario['id_tipoUsuario'];

            $userRepository->registerSession(
                $usuario['cedula'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );

            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(16));
                $userRepository->saveRememberToken($usuario['cedula'], $token);
                setcookie('remember_me', $token, [
                    'expires'  => time() + (30 * 24 * 60 * 60),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                ]);
            }

            header('Location: /AsistenciaVirtual-UTP/public/modules/index.php');
            exit();

        } else {
            $userRepository->registerLoginAttempt($correo, BLOCK_DURATION);
            $showModal = true;
        }
    }
}

include __DIR__ . '/../View/login.php';