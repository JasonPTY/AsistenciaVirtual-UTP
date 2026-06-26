<?php
session_start();

require_once __DIR__ . '/../app/modules/user/userRepository.php';

const MAX_ATTEMPTS   = 3;
const BLOCK_DURATION = 180;

$userRepository = new UserRepository();

$showModal     = false;
$blockModal    = false;
$remainingTime = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $pass   =      $_POST['pass']   ?? '';

    if ($userRepository->isBlocked($correo, MAX_ATTEMPTS, BLOCK_DURATION)) {
        $blockModal    = true;
        $remainingTime = $userRepository->getRemainingBlockTime($correo, BLOCK_DURATION);

    } else {
        $usuario = $userRepository->getUserByEmail($correo);

        // Usuario de prueba: contraseña en texto plano
        // Todos los demás: contraseña hasheada con bcrypt
        $passwordOk = false;
        if ($correo === 'jason.arena@utp.ac.pa') {
            $passwordOk = $usuario && $pass === $usuario['pass'];
        } else {
            $passwordOk = $usuario && password_verify($pass, $usuario['pass']);
        }

        if ($passwordOk) {
            $userRepository->resetLoginAttempts($correo);

            $_SESSION['loggedin'] = true;
            $_SESSION['cedula']   = $usuario['cedula'];
            $_SESSION['nombre']   = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];

            $userRepository->registerSession(
                $usuario['cedula'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );

            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(16));
                $userRepository->saveRememberToken($usuario['cedula'], $token);
                setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/');
            }

            header('Location: /Demo-Sas/public/modules/index.php');
            exit();

        } else {
            $userRepository->registerLoginAttempt($correo, BLOCK_DURATION);
            $showModal = true;
        }
    }
}

include __DIR__ . '/../View/login.php';