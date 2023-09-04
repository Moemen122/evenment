<?php
session_start(); // Démarrer la session

define("DBHOST", "localhost");
define("DBUSER", "root");
define("DBPASS", "");
define("DBNAME", "credit");
$dsn = "mysql:dbname=" . DBNAME . ";host=" . DBHOST;

$message = ""; // Initialisation de la variable $message

// Vérification de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   try {
       $db = new PDO($dsn, DBUSER, DBPASS);
       $db->exec("SET NAMES utf8");
       $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

       // Récupération des données du formulaire
       $email = $_POST["email"];
       $password = $_POST["password"];

       // Requête pour vérifier les informations de connexion
       $sql = "SELECT * FROM `user` WHERE mail = :email AND mdp = :password";
       $stmt = $db->prepare($sql);
       $stmt->bindParam(":email", $email);
       $stmt->bindParam(":password", $password);
       $stmt->execute();
       $user = $stmt->fetch();

       if ($user) {
         // Stocker les données de l'utilisateur dans la session
         $_SESSION["user_id"] = $user["id"];
         $_SESSION["role"] = $user["role"];
       
         // Rediriger en fonction du rôle de l'utilisateur
         if ($user["role"] == "administrateur") {
            $id_user = $_SESSION["user_id"];
            header("Location: event.php?id_user=$id_user"); // Passer l'ID de l'utilisateur via l'URL
            exit();
         } elseif ($user["role"] == "organisateur") {
            $id_user = $_SESSION["user_id"];
             header("Location: event.php?id_user=$id_user"); // Redirection pour l'organisateur
             exit();
         } elseif ($user["role"] == "participant") {
            $id_user = $_SESSION["user_id"];
             header("Location: participant.php?id_user=$id_user"); // Redirection pour le participant
             exit();
         } else {
             $message = "Rôle non reconnu";
         }
     } else {
         $message = "Email ou mot de passe incorrect";
     }
   } catch (PDOException $e) {
       die($e->getMessage());
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            text-align: center;
        }
        
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        form {
            display: flex;
            flex-direction: column;
        }
        
        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        
        input {
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        
        button {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .message {
            margin-top: 10px;
            color: red;
        }
    </style>
    <?php include 'template.php' ?>
</head>
<body>
  
<div class="container">
        <h2>Connexion</h2>
        <form method="post">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            
            <label for="password">Mot de passe:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Se connecter</button>
        </form>
        <p class="message"><?php echo $message; ?></p>
    </div>
    
   
</body>
</html>
