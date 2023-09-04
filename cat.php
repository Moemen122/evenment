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

// Ajout, modification et suppression d'une catégorie
// Ajout, modification et suppression d'une catégorie
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["add_category"])) {
        $nom = $_POST["nom"];
        $description = $_POST["description"];
        
        // Vérification de la longueur du nom et de la non-nullité
        if (strlen($nom) >= 3 && !empty($nom)) {
            
            // Vérification de la longueur de la description
            if (strlen($description) > 5) {
                
                $sql = "INSERT INTO cat (nom, description) VALUES (:nom, :description)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":nom", $nom);
                $stmt->bindParam(":description", $description);
                
                if ($stmt->execute()) {
                    $message = "Catégorie ajoutée avec succès";
                } else {
                    $message = "Erreur lors de l'ajout de la catégorie";
                }
            } else {
                $message = "La description doit contenir au moins 6 caractères";
            }
        } else {
            $message = "Le nom doit contenir au moins 3 caractères et ne peut pas être vide";
        }
    } elseif (isset($_POST["update_category"])) {
        $category_id = $_POST["category_id"];
        $nom = $_POST["nom"];
        $description = $_POST["description"];
        
        $sql = "UPDATE cat SET nom = :nom, description = :description WHERE id_cat = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":nom", $nom);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $category_id);
        
        if ($stmt->execute()) {
            $message = "Catégorie mise à jour avec succès";
        } else {
            $message = "Erreur lors de la mise à jour de la catégorie";
        }
    } elseif (isset($_POST["delete_category"]) && is_numeric($_POST["delete_category"])) {
        $idToDelete = $_POST["delete_category"];
        
        $sql = "DELETE FROM cat WHERE id_cat = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":id", $idToDelete);
        
        if ($stmt->execute()) {
            $message = "Catégorie supprimée avec succès";
        } else {
            $message = "Erreur lors de la suppression de la catégorie";
        }
    }
}


// Récupération de la liste des catégories
$sql = "SELECT * FROM cat";
$stmt = $db->query($sql);
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories</title>
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
</head>
<body>
<?php include 'template.php' ?>
    <div class="container">
        <h2>Ajouter/Modifier une Catégorie</h2>
        <form method="post">
            <input type="hidden" id="category_id" name="category_id">
            <label for="nom">Nom de la catégorie:</label>
            <input type="text" id="nom" name="nom" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
            
            <button type="submit" id="addBtn" name="add_category">Ajouter</button>
            <button type="submit" id="updateBtn" name="update_category" style="display:none;">Mettre à jour</button>
        </form>
        <p class="message"><?php echo $message; ?></p>
    </div>

    <div class="container">
    <h2>Liste des Catégories</h2>
    <div class="search-container">
            <input type="text" id="search" placeholder="Rechercher par nom et description">
        </div>
        <ul id="category-list">
            <?php foreach ($categories as $category) { ?>
                <li>
                    <?php echo htmlspecialchars($category["nom"]); ?> - <?php echo htmlspecialchars($category["description"]); ?>
                    <a href="#" onclick="editCategory('<?php echo addslashes($category['id_cat']); ?>', '<?php echo addslashes($category['nom']); ?>', '<?php echo addslashes($category['description']); ?>')">Modifier</a>
                    <a href="#" onclick="deleteCategory('<?php echo addslashes($category['id_cat']); ?>')">Supprimer</a>
                </li>
            <?php } ?>
        </ul>
    </div>
   
<div class="footer">
    <a href="Untitled-1.php" class="button">Retour à la page de connexion</a>
    <p><a href="event.php"class="button">Gerer Les Evenements</a></p>
</div>


    <script>
         const searchInput = document.getElementById("search");
        const categoryList = document.getElementById("category-list");
        const categories = <?php echo json_encode($categories); ?>;

        searchInput.addEventListener("input", () => {
    const searchText = searchInput.value.trim().toLowerCase();
    categoryList.innerHTML = "";

    categories.forEach(category => {
        const nom = category.nom.toLowerCase();
        const description = category.description.toLowerCase();

        if (nom.includes(searchText) && description.includes(searchText)) {
            categoryList.innerHTML += `
                <li>
                    ${category.nom} - ${category.description}
                    <a href="#" onclick="editCategory('${category.id_cat}', '${category.nom}', '${category.description}')">Modifier</a>
                    <a href="#" onclick="deleteCategory('${category.id_cat}')">Supprimer</a>
                </li>
            `;
        }
    });
});

function editCategory(id, nom, description) {
            document.querySelector("#category_id").value = id; //deplacement des champs 
            document.querySelector("#nom").value = nom;
            document.querySelector("#description").value = description;
            document.querySelector("#addBtn").style.display = "none";
            document.querySelector("#updateBtn").style.display = "inline-block";
        }

        function deleteCategory(id) {
            if (confirm("Voulez-vous vraiment supprimer cette catégorie ?")) {
                const form = document.createElement("form");
                form.method = "post";
                form.action = "";
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "delete_category";
                input.value = id;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
