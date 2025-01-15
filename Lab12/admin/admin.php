<?php 
include 'cfg.php';
require_once __DIR__ . '/admin_navbar.php';

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
    function CheckLogin() {
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
        
        echo '<div class="action-buttons">';
        echo '<a href="?idp=create" class="action-button">+ Dodaj nową stronę</a>';
        echo '</div>';
        
        $query = "SELECT id, page_title FROM page_list LIMIT 100";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table class="admin-table">
        <tr>
            <th>ID</th>
            <th>Tytuł Strony</th>
            <th>Akcje</th>
        </tr>';
        
        while($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td class="id-cell">#'.htmlspecialchars($row['id']).'</td>
                    <td>'.htmlspecialchars($row['page_title']).'</td>
                    <td class="action-cell">
                        <a href="?idp=edit&idedit='.htmlspecialchars($row['id']).'" class="action-button edit">Edytuj</a>
                        <a href="?idp=delete&iddelete='.htmlspecialchars($row['id']).'" 
                           class="action-button delete" 
                           onclick="return confirm(\'Czy na pewno chcesz usunąć tę stronę?\');">Usuń</a>
                    </td>
                  </tr>';
        }
        
        $stmt->close();
        echo '</table>';
    }

    /**
     * Edytuje istniejącą podstronę
     * Zabezpieczone przed SQL Injection poprzez prepared statements
     */
    function EditPage() {
        global $conn;
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
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssisi", $title, $content, $active, $alias, $id);

            if ($stmt->execute()) {
                header("Location: ?idp=admin");
                exit;
            } else {
                echo "Błąd podczas aktualizacji: " . $conn->error;
            }
            $stmt->close();
        }

        // Pobieranie danych strony do edycji
        $query = "SELECT * FROM page_list WHERE id=? LIMIT 1";
        $stmt = $conn->prepare($query);
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
                        <div class="form-group">
                            <label for="edit_alias">Alias:</label>
                            <input type="text" id="edit_alias" name="edit_alias" value="'.htmlspecialchars($row['alias']).'" required />
                        </div>
                        <div class="form-group-inline">
                            <label for="edit_active">Aktywna:</label>
                            <input type="checkbox" id="edit_active" name="edit_active"'.($row['status'] ? ' checked' : '').' />
                        </div>
                        <div class="form-group">
                            <input type="submit" value="Zapisz zmiany" class="button" />
                            <a href="?idp=admin" class="button">Anuluj</a>
                        </div>
                    </form>
                </div>';
        } else {
            $stmt->close();
            return "Nie znaleziono strony o podanym ID.";
        }
    }

    /**
     * Usuwa podstronę
     * Zabezpieczone przed SQL Injection poprzez prepared statements
     */
    function DeletePage() {
        global $conn;
        $status_login = $this->CheckLogin();

        if ($status_login != 1) {
            return $this->FormularzLogowania();
        }

        if (!isset($_GET['iddelete'])) {
            return "Nie podano ID strony do usunięcia.";
        }

        $id = intval($_GET['iddelete']);
        $query = "DELETE FROM page_list WHERE id=? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: ?idp=admin");
            exit;
        } else {
            echo "Błąd podczas usuwania: " . $conn->error;
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
        global $conn;
        $status_login = $this->CheckLogin();

        if ($status_login != 1) {
            return $this->FormularzLogowania();
        }

        echo '<h3 class="h3-admin">Dodaj nową stronę</h3>';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['page_title'], $_POST['page_content'], $_POST['page_alias'])) {
            $title = $_POST['page_title'];
            $content = $_POST['page_content'];
            $alias = $_POST['page_alias'];
            $status = isset($_POST['page_active']) ? 1 : 0;

            $query = "INSERT INTO page_list (page_title, page_content, alias, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $title, $content, $alias, $status);

            if ($stmt->execute()) {
                header("Location: ?idp=admin");
                exit;
            } else {
                echo "Błąd podczas dodawania strony: " . $conn->error;
            }
            $stmt->close();
        }

        return '
            <div class="edit-container">
                <form method="post">
                    <div class="form-group">
                        <label for="page_title">Tytuł:</label>
                        <input type="text" id="page_title" name="page_title" required />
                    </div>
                    <div class="form-group">
                        <label for="page_content">Zawartość:</label>
                        <textarea id="page_content" name="page_content" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="page_alias">Alias:</label>
                        <input type="text" id="page_alias" name="page_alias" required />
                    </div>
                    <div class="form-group-inline">
                        <label for="page_active">Aktywna:</label>
                        <input type="checkbox" id="page_active" name="page_active" checked />
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Dodaj stronę" class="button" />
                        <a href="?idp=admin" class="button">Anuluj</a>
                    </div>
                </form>
            </div>';
    }

    private function WyswietlAdminContent($panel_type, $content) {
        echo '<div class="admin-container">';
        echo '<h2 class="admin-main-header">Panel Administracyjny</h2>';
        WyswietlAdminNav();
        echo '<h3 class="admin-panel-type">' . $panel_type . '</h3>';
        echo '<div class="admin-content">';
        echo $content;
        echo '</div>';
        echo '</div>';
    }

    /**
     * Wyświetla główny panel administracyjny
     */
    function WyswietlPanel() {
        $status_login = $this->CheckLogin();

        if ($status_login == 1) {
            // Obsługa akcji
            $action = isset($_GET['action']) ? $_GET['action'] : 'pages';
            
            switch($action) {
                case 'pages':
                    ob_start();
                    $this->ListaPodstron();
                    $content = ob_get_clean();
                    $this->WyswietlAdminContent('Strony', $content);
                    break;
                case 'categories':
                    $Category = new Category();
                    ob_start();
                    $Category->PokazKategorie();
                    $content = ob_get_clean();
                    $this->WyswietlAdminContent('Kategorie', $content);
                    break;
                case 'products':
                    $ProductManager = new ProductManager();
                    ob_start();
                    $ProductManager->PokazProdukty();
                    $content = ob_get_clean();
                    $this->WyswietlAdminContent('Produkty', $content);
                    break;
                case 'logout':
                    $this->Logout();
                    break;
            }
        } else {
            echo $this->FormularzLogowania();
        }
    }
}
?>