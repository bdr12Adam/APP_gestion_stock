<?php
session_start();
include "../model/connexion.php";

$error = "";

// Si l'utilisateur est déjà connecté, rediriger vers dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../vue/dashboard.php");
    exit();
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Rechercher l'utilisateur
        $stmt = $conn->prepare("SELECT id, nom, prenom, email, mot_de_passe, role, statut FROM utilisateur WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Vérifier le statut
            if ($user['statut'] !== 'actif') {
                $error = "Votre compte est inactif. Contactez l'administrateur.";
            } 
            // Vérifier le mot de passe
            else if (password_verify($password, $user['mot_de_passe'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];



                
                
                // Rediriger vers le dashboard
                header("Location: ../vue/dashboard.php");
                exit();
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
        $stmt->close();
    }
} 
$password = password_hash("adambendraoui2006", PASSWORD_BCRYPT);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - S-TOCK</title>
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* PARTIE GAUCHE - Visuel */
        .login-visual {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-visual::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .logo-section {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .logo-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .logo-title {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logo-subtitle {
            font-size: 18px;
            opacity: 0.95;
            font-weight: 300;
            letter-spacing: 2px;
        }

        .features-list {
            margin-top: 40px;
            list-style: none;
            position: relative;
            z-index: 1;
        }

        .features-list li {
            padding: 15px 0;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0.95;
        }

        .features-list i {
            font-size: 24px;
        }

        /* PARTIE DROITE - Formulaire */
        .login-form-section {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #64748b;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 20px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            color: #1e293b;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            font-size: 20px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .remember-me label {
            font-size: 14px;
            color: #64748b;
            cursor: pointer;
            user-select: none;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            border-left: 4px solid #ef4444;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            color: #94a3b8;
            font-size: 14px;
            position: relative;
            z-index: 1;
        }

        .demo-credentials {
            background: #f0f9ff;
            border: 2px solid #bae6fd;
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
        }

        .demo-credentials h4 {
            color: #0369a1;
            font-size: 14px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-credentials p {
            color: #0c4a6e;
            font-size: 13px;
            margin: 5px 0;
        }

        .demo-credentials code {
            background: #e0f2fe;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }

            .login-visual {
                padding: 40px 30px;
            }

            .logo-icon {
                font-size: 60px;
            }

            .logo-title {
                font-size: 36px;
            }

            .features-list {
                display: none;
            }

            .login-form-section {
                padding: 40px 30px;
            }

            .form-header h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .login-form-section {
                padding: 30px 20px;
            }

            .form-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        
        <!-- PARTIE GAUCHE - VISUEL -->
        <div class="login-visual">
            <div class="logo-section">
                <div class="logo-icon">📦</div>
                <h1 class="logo-title">S-TOCK</h1>
                <p class="logo-subtitle">Gestion de Stock</p>
            </div>

            <ul class="features-list">
                <li><i class='bx bx-check-circle'></i> Gestion complète du stock</li>
                <li><i class='bx bx-check-circle'></i> Suivi des ventes en temps réel</li>
                <li><i class='bx bx-check-circle'></i> Analyses et statistiques</li>
                <li><i class='bx bx-check-circle'></i> Gestion multi-utilisateurs</li>
            </ul>
        </div>

        <!-- PARTIE DROITE - FORMULAIRE -->
        <div class="login-form-section">
            <div class="form-header">
                <h2>Bienvenue ! 👋</h2>
                <p>Connectez-vous à votre compte</p>
            </div>

            <?php if ($error): ?>
            <div class="error-message">
                <i class='bx bx-error-circle' style="font-size: 20px;"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Adresse Email</label>
                    <div class="input-wrapper">
                        <i class='bx bx-envelope input-icon'></i>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="votre.email@exemple.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class='bx bx-lock-alt input-icon'></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="form-input" 
                            placeholder="••••••••"
                            required
                        >
                        <i class='bx bx-show password-toggle' id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-login">
                    Se Connecter
                </button>
            </form>

            <div class="divider">
                <span>Informations de test</span>
            </div>

            <div class="demo-credentials">
                <h4><i class='bx bx-info-circle'></i> Compte de démonstration</h4>
                <p><strong>Email:</strong> <code>adambdr413@gmail.com</code></p>
                <p><strong>Mot de passe:</strong> <code>qazwsx</code></p>
            </div>
        </div>

    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });

        // Animation au chargement
        window.addEventListener('load', function() {
            document.querySelector('.login-container').style.opacity = '0';
            setTimeout(() => {
                document.querySelector('.login-container').style.opacity = '1';
            }, 100);
        });
    </script>

</body>
</html>