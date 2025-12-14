 <?php
// Informations de connexion


$host = "localhost";
$user = "root";       
$pass = "";          
$dbname = "gestion_stock";

// Connexion
$conn = new mysqli($host, $user, $pass, $dbname);

// Vérification
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// echo "Connexion réussie !"; // Pour tester
?>