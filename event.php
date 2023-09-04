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
// Paramètre GET pour déterminer l'ordre de tri
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';

// Récupération de l'ID de l'utilisateur depuis l'URL
if (isset($_GET["id_user"])) {
    $id_user = $_GET["id_user"];
} 
// Récupération de la liste des catégories pour la liste déroulante
$sqlCategories = "SELECT * FROM cat";
$stmtCategories = $db->query($sqlCategories);
$categories = $stmtCategories->fetchAll();

// Créer un nouvel événement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_event"])) {
    $titre = $_POST["titre"];
    $description = $_POST["description"];
    $date = $_POST["date"];
    $id_cat = $_POST["id_cat"];
    
    // Vérification du titre non nul
    if (!empty($titre)) {
        
        // Vérification de la description non nulle et de la longueur > 5
        if (!empty($description) && strlen($description) > 5) {
            
            // Vérification de la date par rapport à la date système
            $currentDate = date("Y-m-d"); // Date système au format "YYYY-MM-DD"
            
            if ($date >= $currentDate) { // La date doit être supérieure ou égale à la date système
                
                $sql = "INSERT INTO event (titre, description, dat, id_cat, id_user) VALUES (:titre, :description, :date, :id_cat, :id_user)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":titre", $titre);
                $stmt->bindParam(":description", $description);
                $stmt->bindParam(":date", $date);
                $stmt->bindParam(":id_cat", $id_cat);
                $stmt->bindParam(":id_user", $id_user);
                
                if ($stmt->execute()) {
                    $message = "Événement ajouté avec succès";
                } else {
                    $message = "Erreur lors de l'ajout de l'événement";
                }
            } else {
                $message = "La date doit être supérieure ou égale à la date système";
            }
        } else {
            $message = "La description doit contenir au moins 6 caractères";
        }
    } else {
        $message = "Le titre ne peut pas être vide";
    }
}


// Fonction pour supprimer un événement
function supprimerEvenement($db, $id_event) {
    $sql = "DELETE FROM event WHERE id_even = :id_even";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":id_even", $id_event);

    if ($stmt->execute()) {
        $message = "Catégorie supprimée avec succès";
        return true;
    } else {
        $message = "Erreur lors de la suppression de la catégorie";
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["delete_event"])) {
        $id_event = $_POST["delete_event"];
        if (supprimerEvenement($db, $id_event)) {
            $message = "Événement supprimé avec succès";
        } else {
            $message = "Erreur lors de la suppression de l'événement";
        }
    }
}
// Code pour mettre à jour un événement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_event"])) {
    $event_id = $_POST["event_id"];
    $titre = $_POST["titre"];
    $description = $_POST["description"];
    $date = $_POST["date"];
    $id_cat = $_POST["id_cat"];

    $sql = "UPDATE event SET titre = :titre, description = :description, dat = :date, id_cat = :id_cat WHERE id_even = :event_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":event_id", $event_id);
    $stmt->bindParam(":titre", $titre);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":date", $date);
    $stmt->bindParam(":id_cat", $id_cat);

    if ($stmt->execute()) {
        $message = "Événement mis à jour avec succès";
    } else {
        $message = "Erreur lors de la mise à jour de l'événement";
    }
}
function getCategoryName($catId, $categories) {
    foreach ($categories as $category) {
        if ($category['id_cat'] == $catId) {
            return $category['nom'];
        }
    }
    return "Catégorie inconnue";
}
// Récupération de la liste des événements
$sqlEvents = "SELECT * FROM event";
$stmtEvents = $db->query($sqlEvents);
$events = $stmtEvents->fetchAll();
// Fonction de comparaison pour le tri par date
function compareDate($event1, $event2) {
    return strtotime($event1['dat']) - strtotime($event2['dat']);
}

// Tri du tableau d'événements en fonction de l'ordre et de la date
if ($order === 'asc') {
    usort($events, 'compareDate');
} else {
    usort($events, 'compareDate');
    $events = array_reverse($events);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Événements</title>
    <!-- Ajoutez des styles CSS ici -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
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

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
            display: block;
        }

        input, textarea, select {
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

        a {
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s;
            cursor: pointer;
        }

        a:hover {
            color: #2980b9;
        }
        .footer {
    position: fixed;
    bottom: 0;
    right: 20px;
    margin: 20px;
}
.bottom-buttons {
            position: absolute;
            bottom: 0;
            left: 20px;
            margin-bottom: 20px;
        }
    </style>
    
</head>
<body>
<?php include 'template.php' ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="container">
        <h2>Gestion des Événements</h2>
        <!-- Formulaire pour ajouter ou modifier un événement -->
        <form method="post">
            <input type="hidden" name="event_id" id="event_id" value="">
            <label for="titre">Titre:</label>
            <input type="text" id="titre" name="titre" required>
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
            <label for="id_cat">Catégorie:</label>
            <select id="id_cat" name="id_cat">
                <?php foreach ($categories as $category) { ?>
                    <option value="<?php echo $category['id_cat']; ?>"><?php echo $category['nom']; ?></option>
                <?php } ?>
            </select>
            <button type="submit" name="add_event">Ajouter</button>
            <button type="submit" name="update_event">Enregistrer les modifications</button>
        </form>
        <p class="message"><?php echo $message; ?></p>
    </div>
    <div class="container">
    <h2>Liste des Événements</h2>
    <ul>
        <?php foreach ($events as $event) { ?>
            <li>
                <?php echo $event["titre"]; ?> - <?php echo $event["description"]; ?> - <?php echo $event["dat"]; ?>
                - <?php echo getCategoryName($event["id_cat"], $categories); ?> - <?php echo $event["id_user"]; ?>
                <button onclick="editEvent('<?php echo $event['id_even']; ?>', '<?php echo $event['titre']; ?>', '<?php echo $event['description']; ?>', '<?php echo $event['dat']; ?>', '<?php echo $event['id_cat']; ?>')">Modifier</button>
                <form method="post" style="display: inline-block;">
                    <input type="hidden" name="delete_event" value="<?php echo $event['id_even']; ?>">
                    <button type="submit">Supprimer</button>
                </form>
            </li>
        <?php } ?>
    </ul>
</div>
<div class="footer">
    <a href="Untitled-1.php" class="button">Retour à la page de connexion</a>
</div>

    <script>
        function editEvent(eventId, titre, description, date, id_cat) {
            document.getElementById("event_id").value = eventId;
            document.getElementById("titre").value = titre;
            document.getElementById("description").value = description;
            document.getElementById("date").value = date;
            document.getElementById("id_cat").value = id_cat;
        }
       

    </script>
</body>
<html>
  <head>
  <div class="bottom-buttons">
  <?php
            // Vérifie le rôle de l'utilisateur avant d'afficher le lien
            if(isset($_SESSION['role']) && $_SESSION['role'] === 'administrateur') {
                echo '<p><a href="cat.php">Gerer les cathegories</a></p>';
            }
            ?>

        
  <p><a href="?order=asc">Tri Croissant</a> | <a href="?order=desc">Tri Décroissant</a></p>
        </div>
 
     

    
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var jsonData = <?php
         $sql = "SELECT dat, COUNT(*) as event_count FROM event GROUP BY dat";
         $stmt = $db->query($sql);
         $eventStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
         echo json_encode($eventStats);
         ?>;

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', 'Nombre d\'événements');

        for (var i = 0; i < jsonData.length; i++) {
          data.addRow([jsonData[i].dat, parseInt(jsonData[i].event_count)]);
        }

        var options = {
          title: 'Statistiques des Événements par Date'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));

        chart.draw(data, options);
      }
    </script>
  </head>
 
  <body>
    <div id="piechart" style="width: 900px; height: 500px;"></div>
  </body>
</html>

</html>

