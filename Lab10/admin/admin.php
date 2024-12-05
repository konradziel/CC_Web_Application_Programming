<?php 
include 'cfg.php';

/**
 * Class Admin
 * Klasa zarządzająca panelem administracyjnym
 * Zawiera funkcje do zarządzania stronami i autoryzacją użytkowników
 */
class Admin {
    
    /**
     * Sprawdza status logowania użytkownika
     * @return int Zwraca 1 jeśli zalogowany, 0 jeśli nie
     */
    public function CheckLogin() {
        // Sprawdzenie czy użytkownik jest już zalogowany
        if (isset($_SESSION['loggedin'])) {
            return 1;
        }
        
        // Sprawdzenie czy przesłano dane logowania
        if (isset($_POST['login'], $_POST['login_pass'])) {
            $login = $_POST['login'];
            $pass = $_POST['login_pass'];
            return $this->CheckLoginCred($login, $pass);
        }
        
        return 0;
    }

    /**
     * Sprawdza poświadczenia logowania
     * @param string $login Login użytkownika
     * @param string $pass Hasło użytkownika
     * @return int Status logowania (1 - sukces, 0 - błąd)
     */
    function CheckLoginCred($login, $pass) {
        if ($login == ADMIN_LOGIN && $pass == ADMIN_PASSWORD) {
            $_SESSION['loggedin'] = true;
            return 1;
        }
        echo "Logowanie się nie powiodło.";
        return 0;
    }

    /**
     * Wyświetla listę wszystkich podstron
     * Zabezpieczone przed SQL Injection poprzez prepared statements
     */
    function ListaPodstron() {
        global $conn;
        $query = "SELECT id, page_title FROM page_list LIMIT 100"; // Dodano LIMIT dla bezpieczeństwa
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '
        <div>
        <table class="admin-table">
        <tr>
            <th>ID Strony</th>
            <th>Tytuł Strony</th>
            <th>Edytuj</th>
            <th>Usuń</th>
        </tr>';
        
        while($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td style="color: black;">'.htmlspecialchars($row['id']).'</td>
                    <td style="color: black;">'.htmlspecialchars($row['page_title']).'</td>
                    <td><a class="edit-button" href="?idp=edit&idedit='.htmlspecialchars($row['id']).'">Edit</a></td>
                    <td><a class="delete-button" href="?idp=delete&iddelete='.htmlspecialchars($row['id']).'" onclick="return confirm(\'Czy na pewno chcesz usunąć tę stronę?\');">Delete</a></td>
                  </tr>';
        }
        
        $stmt->close();
        
        echo '</table>';
        echo '<div class="create-container">';
        echo '<a class="create-link" href="?idp=create">Dodaj nową stronę</a>';
        echo '</div>';
        echo '<div class="logout-container">';
        echo '<a class="logout-link" href="?idp=logout">Wyloguj się</a>';
        echo '</div>';
    }

    /**
     * Edytuje istniejącą podstronę
     * Zabezpieczone przed SQL Injection poprzez prepared statements
     */
    function EditPage() {
        $status_login = $this->CheckLogin();

        if ($status_login != 1) {
            return $this->FormularzLogowania();
        }

        echo '<h3 class="h3-admin">Strona edycji</h3>';

        if (!isset($_GET['idedit'])) {
            return "Nie podano ID strony do edycji.";
        }

        $id = intval($_GET['idedit']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_title'], $_POST['edit_content'], $_POST['edit_alias'])) {
            $title = $_POST['edit_title'];
            $content = $_POST['edit_content'];
            $active = isset($_POST['edit_active']) ? 1 : 0;
            $alias = $_POST['edit_alias'];

            $query = "UPDATE page_list SET page_title=?, page_content=?, status=?, alias=? WHERE id=? LIMIT 1";
            $stmt = $GLOBALS['conn']->prepare($query);
            $stmt->bind_param("ssisi", $title, $content, $active, $alias, $id);

            if ($stmt->execute()) {
                header("Location: ?idp=admin");
                exit;
            } else {
                echo "Błąd podczas aktualizacji: " . $GLOBALS['conn']->error;
            }
            $stmt->close();
        }

        // Pobieranie danych strony do edycji
        $query = "SELECT * FROM page_list WHERE id=? LIMIT 1";
        $stmt = $GLOBALS['conn']->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();

            return '
                <div class="edit-container">
                    <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
                        <div class="form-group">
                            <label for="edit_title">Tytuł:</label>
                            <input type="text" id="edit_title" name="edit_title" value="'.htmlspecialchars($row['page_title']).'" required />
                        </div>
                        <div class="form-group">
                            <label for="edit_content">Zawartość:</label>
                            <textarea id="edit_content" name="edit_content" required>'.htmlspecialchars($row['page_content']).'</textarea>
                        </div>
                        <div class="form-group-inline">
                            <label for="edit_active">Aktywna:</label>
                            <input type="checkbox" id="edit_active" name="edit_active"'.($row['status'] ? ' checked' : '').' />
                        </div>
                        <div class="form-group">
                            <label for="edit_alias">Alias:</label>
                            <input type="text" id="edit_alias" name="edit_alias" value="'.htmlspecialchars($row['alias']).'" required />
                        </div>
                        <div class="form-group">
                            <input type="submit" class="submit-button" value="Zapisz zmiany" />
                        </div>
                    </form>
                </div>';
        }
        return "Nie znaleziono strony do edycji.";
    }

    /**
     * Usuwa podstronę
     * Zabezpieczone przed SQL Injection poprzez prepared statements
     */
    function DeletePage() {
        $status_login = $this->CheckLogin();

        if ($status_login != 1) {
            return $this->FormularzLogowania();
        }

        if (!isset($_GET['iddelete'])) {
            echo "Nie podano ID strony do usunięcia.";
            return;
        }

        $id = intval($_GET['iddelete']);
        $query = "DELETE FROM page_list WHERE id=? LIMIT 1";
        $stmt = $GLOBALS['conn']->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: ?idp=admin");
            exit;
        } else {
            echo "Błąd podczas usuwania: " . $GLOBALS['conn']->error;
        }
        $stmt->close();
    }

    /**
     * Funkcja do wyświetlania formularza logowania
     */
    function FormularzLogowania() {
        return '
        <div">
            <h3 class="heading">Panel CMS:</h3>
            <form method="post" name="LoginForm" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].'">
                <table class="logowanie">
                    <tr><td class="log4_t">login</td><td><input type="text" name="login" required /></td></tr>
                    <tr><td class="log4_t">haslo</td><td><input type="password" name="login_pass" required /></td></tr>
                    <tr><td></td><td><input type="submit" name="x1_submit" value="zaloguj" /></td></tr>
                </table>
            </form>
        </div>';
    }

    /**
     * Funkcja do wylogowywania
     */
    function Logout()
    {
        if (isset($_SESSION['loggedin'])) {
            unset($_SESSION['loggedin']);
        }
        header('Location: ?idp=glowna');
        exit;
    }

    /**
     * Funkcja do wyświetlania panelu administracyjnego
     */
    function LoginAdmin() {
        $status_login = $this->CheckLogin(); // Sprawdź dane logowania

        if ($status_login == 1) { 
            echo "<h3>Lista Stron</h3>";
            echo $this->ListaPodstron(); // Wyświetl listę podstron
        } else {
            echo $this->FormularzLogowania(); // Wyświetlenie formularza logowania
        }
    }

    /**
     * Funkcja do tworzenia nowej podstrony
     */
    function StworzPodstrone()
    {
        $status_login = $this->CheckLogin();

        if ($status_login != 1) {
            return $this->FormularzLogowania();
        }

        echo '<h3 class="h3-admin">Dodaj nową stronę</h3>';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_title'], $_POST['create_content'], $_POST['create_alias'])) {
            $title = $_POST['create_title'];
            $content = $_POST['create_content'];
            $active = isset($_POST['create_active']) ? 1 : 0;
            $alias = $_POST['create_alias'];

            $query = "INSERT INTO page_list (page_title, page_content, status, alias) VALUES (?, ?, ?, ?)";
            $stmt = $GLOBALS['conn']->prepare($query);
            $stmt->bind_param("ssis", $title, $content, $active, $alias);

            if ($stmt->execute()) {
                echo "Nowa strona została dodana pomyślnie.";
                header("Location: ?idp=admin");
                exit;
            } else {
                echo "Błąd podczas dodawania: " . $GLOBALS['conn']->error;
            }
            $stmt->close();
        } else {
            return '
                <div class="create-container">
                    <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
                        <div class="form-group">
                            <label for="create_title">Tytuł:</label>
                            <input type="text" id="create_title" name="create_title" required />
                        </div>
                        <div class="form-group">
                            <label for="create_content">Zawartość:</label>
                            <textarea id="create_content" name="create_content" required></textarea>
                        </div>
                        <div class="form-group-inline">
                            <label for="create_active">Aktywna:</label>
                            <input type="checkbox" id="create_active" name="create_active" />
                        </div>
                        <div class="form-group">
                            <label for="create_alias">Alias:</label>
                            <input type="text" id="create_alias" name="create_alias" required />
                        </div>
                        <div class="form-group">
                            <input type="submit" class="submit-button" value="Dodaj stronę" />
                        </div>
                    </form>
                </div>';
        }
    }
}
?>