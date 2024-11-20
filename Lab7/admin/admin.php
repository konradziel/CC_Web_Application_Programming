<?php 
include 'cfg.php'; // Upewnij się, że plik cfg.php jest poprawnie załadowany

class Admin {
    // Funkcja do wyświetlania formularza logowania
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

    // Funkcja do sprawdzania logowania
    function CheckLogin() {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            return 1;
        }
        if (isset($_POST['login']) && isset($_POST['login_pass'])) {
            return $this->CheckLoginCred($_POST['login'], $_POST['login_pass']);
        }
        return 0;
    }

    // Funkcja do sprawdzania danych logowania
    function CheckLoginCred($login, $pass) {
        if ($login == ADMIN_LOGIN && $pass == ADMIN_PASSWORD) { // Sprawdzenie zdefiniowanych danych logowania
            $_SESSION['loggedin'] = true;
            return 1;
        } else {
            echo "Logowanie się nie powiodło.";
            return 0; // Niepoprawne dane
        }
    }

    // Funkcja do wyświetlania panelu administracyjnego
    function LoginAdmin() {
        $status_login = $this->CheckLogin(); // Sprawdź dane logowania

        if ($status_login == 1) { 
            echo "<h3>Lista Stron</h3>";
            echo $this->ListaPodstron(); // Wyświetl listę podstron
        } else {
            echo $this->FormularzLogowania(); // Wyświetlenie formularza logowania
        }
    }

    // Funkcja do wylogowywania
    function Logout()
    {
        if (isset($_SESSION['loggedin'])) {
            unset($_SESSION['loggedin']);
        }
        header('Location: ?idp=glowna');
        exit;
    }

    // Funkcja do wyświetlania listy podstron
    function ListaPodstron() {
        global $conn;
        $query = "SELECT id, page_title FROM page_list"; // Użyj page_title 
        $result = $conn->query($query);
    
        echo '
        <div>
        <table class="admin-table">
        <tr 
            <th></th>
            <th>ID Strony</th> 
            <th>Tytuł Strony</th> 
            <th>Edytuj</th> 
            <th>Usuń</th> 
        </tr>
        </div>';
        
        while($row = $result->fetch_assoc()) { 
            echo '<tr>
                    <td style="color: black;">'.$row['id'].'</td>
                    <td style="color: black;">'.$row['page_title'].'</td>
                    <td><a class="edit-button" href="?idp=edit&idedit='.$row['id'].'">Edit</a></td>
                    <td><a class="delete-button" href="?idp=delete&iddelete='.$row['id'].'" onclick="return confirm(\'Czy na pewno chcesz usunąć tę stronę?\');">Delete</a></td>
                  </tr>';
        }
        
        echo '</table>';
        
        echo '<div class="create-container">';
        echo '<a class="create-link" href="?idp=create">Dodaj nową stronę</a>';
        echo '</div>';
        echo '<div class="logout-container">';
        echo '<a class="logout-link" href="?idp=logout">Wyloguj się</a>';
        echo '</div>';
    }
    
    function StworzPodstrone()
    {
        $status_login = $this->CheckLogin();
        

        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Dodaj nową stronę</h3>';

            // sprawdzenie czy formularz jest wysłany metoda POST i czy wymagane dane sa wprowadzone
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_title'], $_POST['create_content'], $_POST['create_alias'])) {
                $title = $GLOBALS['conn']->real_escape_string($_POST['create_title']);
                $content = $GLOBALS['conn']->real_escape_string($_POST['create_content']);
                $active = isset($_POST['create_active']) ? 1 : 0;
                $alias = $GLOBALS['conn']->real_escape_string($_POST['create_alias']);

                // zapytanie SQL które doda nowa podstrone do bazy
                $query = "INSERT INTO page_list (page_title, page_content, status, alias) VALUES ('$title', '$content', '$active', '$alias')";

                // sprawdzenie czy jest polaczenie z baza i czy zapytanie zostalo przetworzone poprawnie
                if ($GLOBALS['conn']->query($query) === TRUE) {
                    echo "Nowa strona została dodana pomyślnie.";
                    // przekierowanie na panel admina po skonczonej operacji
                    header("Location: ?idp=admin");
                    exit;
                } else {
                    // wyswietlenie komunikatu błedu w przypadku niepowodzenia
                    echo "Błąd podczas dodawania: " . $GLOBALS['conn']->error;
                }
            } else {
                // Jeśli formularz nie został wysłany, wyświetl formularz do dodania nowej strony
                return '
                    <div class="create-container">
                        <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
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
        } else {
            return $this->FormularzLogowania(); // Jeśli nie jesteś zalogowany, wyświetl formularz logowania
        }
    }

    function EditPage()
    {
        $status_login = $this->CheckLogin();

        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Strona edycji</h3>';

            // sprawdzenie czy w URL strony znajduje sie parametr idedit który jest id edytowanej strony
            if (isset($_GET['idedit'])) {

                // sprawdzenie czy formularz jest wysłany metoda POST i czy wymagane dane sa wprowadzone
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_title'], $_POST['edit_content'], $_POST['edit_alias'])) {
                    // przygotwanie danych do zmiany: tytuł, zawartość, aktywność, alias lub id, zachowanie bezpieczenstwa po przez real_escape_string lub intval
                    $title = $GLOBALS['conn']->real_escape_string($_POST['edit_title']);
                    $content = $GLOBALS['conn']->real_escape_string($_POST['edit_content']);
                    $active = isset($_POST['edit_active']) ? 1 : 0;
                    $alias = $GLOBALS['conn']->real_escape_string($_POST['edit_alias']);
                    $id = intval($_GET['idedit']);

                    // Zapytanie SQL aktualizujace dane podstrony
                    $query = "UPDATE page_list SET page_title='$title', page_content='$content', status='$active', alias='$alias' WHERE id='$id' LIMIT 1";

                    // sprawdzenie czy jest polaczenie z baza i czy zapytanie zostalo przetworzone poprawnie
                    if ($GLOBALS['conn']->query($query) === TRUE) {
                        echo "Strona została zaktualizowana pomyślnie.";
                        // przekierowanie na panel admina
                        header("Location: ?idp=admin");
                        exit;
                    } else {
                        // komunikat o błedzie podczas aktualizacji
                        echo "Błąd podczas aktualizacji: " . $GLOBALS['conn']->error;
                    }
                } else {
                    // jesli formularz nie został wysłany pobieram dane strony do edycji
                    $query = "SELECT * FROM page_list WHERE id='" . intval($_GET['idedit']) . "' LIMIT 1";
                    $result = $GLOBALS['conn']->query($query);

                    // sprawdzam czy strona o wskazanym id istnieje
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();

                        return '
                                <div class="edit-container">
                                    <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                        <div class="form-group">
                                            <label for="edit_title">Tytuł:</label>
                                            <input type="text" id="edit_title" name="edit_title" value="' . htmlspecialchars($row['page_title']) . '" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_content">Zawartość:</label>
                                            <textarea id="edit_content" name="edit_content" required>' . htmlspecialchars($row['page_content']) . '</textarea>
                                        </div>
                                        <div class="form-group-inline">
                                            <label for="edit_active">Aktywna:</label>
                                            <input type="checkbox" id="edit_active" name="edit_active"' . ($row['status'] ? ' checked' : '') . ' />
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_alias">Alias:</label>
                                            <input type="text" id="edit_alias" name="edit_alias" value="' . htmlspecialchars($row['alias']) . '" required />
                                        </div>
                                        <div class="form-group">
                                            <input type="submit" class="submit-button" value="Zapisz zmiany" />
                                        </div>
                                    </form>
                                </div>';
                    } else {
                        return "Nie znaleziono strony do edycji.";
                    }
                }
            } else {
                return "Nie podano ID strony do edycji.";
            }
        } else {
            return $this->FormularzLogowania();
        }
    }

    function DeletePage()
    {
        $status_login = $this->CheckLogin();

        if ($status_login == 1) { // jesli zalogowano to...
            // Sprawdź, czy podano ID do usunięcia
            if (isset($_GET['iddelete'])) {
                // intval słuzacy do zabezpieczenia przed SQL Injection
                $id = intval($_GET['iddelete']);

                // Zapytanie do usunięcia podstrony
                $query = "DELETE FROM page_list WHERE id='$id' LIMIT 1";

                // sprawdzenie czy jest polaczenie z baza i czy zapytanie zostalo przetworzone poprawnie
                if ($GLOBALS['conn']->query($query) === TRUE) {
                    echo "Strona została usunięta pomyślnie.";
                    header("Location: ?idp=admin"); // Przekierowanie po udanym usunięciu na panel admina
                    exit;
                } else {
                    echo "Błąd podczas usuwania: " . $GLOBALS['conn']->error;
                }
            } else {
                echo "Nie podano ID strony do usunięcia.";
            }
        } else {
            return $this->FormularzLogowania(); // Jeśli nie jesteś zalogowany, wyświetl formularz logowania
        }
    }
}
?>