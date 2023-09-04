<?php
if (!isset($_SESSION)) {
    session_start();
}

define("DBHOST", "localhost");
define("DBUSER", "root");
define("DBPASS", "");
define("DBNAME", "credit");
$dsn = "mysql:dbname=" . DBNAME . ";host=" . DBHOST;

$message = "";

// Connexion à la base de données
try {
    $db = new PDO($dsn, DBUSER, DBPASS);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die($e->getMessage());
}

// Récupération de l'ID de l'utilisateur depuis l'URL
if (isset($_GET["id_user"])) {
    $id_user = $_GET["id_user"];
} else {
    $message = "Erreur";
    exit();
}

// Code de filtrer par catégorie
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    $searchCategoryId = $_POST["category"];
    if (!empty($searchCategoryId)) {
        $sql = "SELECT * FROM event WHERE id_cat = :category";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":category", $searchCategoryId, PDO::PARAM_INT);
        $stmt->execute();
        $filtered_events = $stmt->fetchAll();
    }
}

// Récupération des événements pour le participant
$sql = "SELECT * FROM event";
$stmt = $db->query($sql);
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0eaf1;
            margin: 0;
            padding: 0;
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
            width: 400px;
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        form label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
            display: block;
        }

        input, textarea {
            width: 100%;
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

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }

        ul li:last-child {
            border-bottom: none;
        }

        ul li a {
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s;
            cursor: pointer;
        }

        ul li a:hover {
            color: #2980b9;
        }

        .footer {
            position: fixed;
            bottom: 0;
            right: 20px;
            margin: 20px;
        }

        .button {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 3px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #2980b9;
        }
    </style>
    <title>Liste des Événements</title>
</head>
<body>
<div class="container mt-5">
    <h2>Liste des Événements</h2>

    <form method="post" action="">
        <label for="category">Filtrer par catégorie:</label>
        <select name="category" id="category">
            <option value="">Toutes les catégories</option>
            <?php
            $sqlCategories = "SELECT * FROM cat";
            $stmtCategories = $db->query($sqlCategories);
            $allCategories = $stmtCategories->fetchAll();

            foreach ($allCategories as $category) {
                echo '<option value="' . $category["id_cat"] . '">' . $category["nom"] . '</option>';
            }
            ?>
        </select>
        <button type="submit" name="submit">FILTRER</button>
    </form>

    <table class="table">
        <thead>
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Date</th>
            <th>Catégorie</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Utiliser $filtered_events s'il est défini, sinon $categories
        $events_to_display = isset($filtered_events) ? $filtered_events : $categories;

        foreach ($events_to_display as $event) {
            $sqlCategory = "SELECT nom FROM cat WHERE id_cat = :id_cat";
            $stmtCategory = $db->prepare($sqlCategory);
            $stmtCategory->bindParam(":id_cat", $event["id_cat"], PDO::PARAM_INT);
            $stmtCategory->execute();
            $category = $stmtCategory->fetchColumn();
            ?>
            <tr>
                <td><?php echo $event["titre"]; ?></td>
                <td><?php echo $event["description"]; ?></td>
                <td><?php echo $event["dat"]; ?></td>
                <td><?php echo $category; ?> </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Intégration du chat en direct Chatwoot -->
<script>
  (function(d,t) {
    var BASE_URL="https://app.chatwoot.com";
    var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=BASE_URL+"/packs/js/sdk.js";
    g.defer = true;
    g.async = true;
    s.parentNode.insertBefore(g,s);
    g.onload=function(){
      window.chatwootSDK.run({
        websiteToken: 'uHwmWvriw9566Rs9iyXDmwgx',
        baseUrl: BASE_URL
      })
    }
  })(document,"script");
</script>

<div class="footer">
    <a href="Untitled-1.php" class="button">Retour à la page de connexion</a>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
