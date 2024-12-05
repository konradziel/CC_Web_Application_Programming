<?php
/**
 * System zarządzania kategoriami
 * 
 * Implementacja systemu kategorii.
 * Tworzy hierarchiczną strukturę kategorii z możliwością
 * dodawania, edycji i usuwania kategorii oraz wyświetlania ich w formie
 * drzewa i tabeli.
 */

class Category {
    /**
     * Główna metoda wyświetlająca panel zarządzania kategoriami.
     * Sprawdza uprawnienia użytkownika i wyświetla odpowiedni interfejs.
     */
    function PokazKategorie() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Panel Kategorii</h3>';
            echo '<div class="admin-links">';
            echo '<a href="?idp=admin" class="admin-link">Powrót do Panelu Admina</a>';
            echo '</div>';
            echo $this->ListaKategorii();
        } else {
            echo $Admin->FormularzLogowania();
        }
    }

    /**
     * Wyświetla listę kategorii.
     */
    function ListaKategorii() {
        global $conn;
        
        // Pobieranie listy kategorii z bazy danych
        $query = "SELECT id, matka, nazwa FROM category_list ORDER BY id ASC LIMIT 100";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        // Panel z przyciskiem dodawania
        echo '<div class="category-panel">';
        echo '<div class="category-actions">';
        echo '<a href="?idp=nowa-kategoria" class="new-category-btn">+ Dodaj nową kategorię</a>';
        echo '</div>';

        // Tworzenie tabeli kategorii
        echo '<table class="admin-table">
            <tr>
                <th>ID</th>
                <th>Kategoria Nadrzędna</th>
                <th>Nazwa Kategorii</th>
                <th>Akcje</th>
            </tr>';

        // Wypełnianie tabeli danymi
        while($row = $result->fetch_assoc()) {
            $parentName = ($row['matka'] == 0) ? '-' : $this->getCategoryName($row['matka']);
            
            echo '<tr>
                <td class="id-cell">#'.htmlspecialchars($row['id']).'</td>
                <td>'.htmlspecialchars($parentName).'</td>
                <td>'.htmlspecialchars($row['nazwa']).'</td>
                <td class="action-cell">
                    <a href="?idp=edytuj-kategorie&id='.htmlspecialchars($row['id']).'" class="action-button edit">Edytuj</a>
                    <a href="?idp=usun-kategorie&id='.htmlspecialchars($row['id']).'" 
                       class="action-button delete" 
                       onclick="return confirm(\'Czy na pewno chcesz usunąć kategorię \\\'\' + \''.htmlspecialchars($row['nazwa']).'\' + \'\\\'?\');">Usuń</a>
                </td>
            </tr>';
        }
        echo '</table>';

        // Wizualizacja struktury drzewiastej
        echo '<div class="category-tree" style="font-family: monospace; white-space: pre;">';
        echo '<h3 style="font-family: inherit;">Struktura kategorii</h3>';

        // Pobierz wszystkie kategorie ponownie dla drzewa
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = array();

        // Najpierw zbierz wszystkie kategorie i zorganizuj je w drzewo
        while($row = $result->fetch_assoc()) {
            $categories[$row['id']] = array(
                'id' => $row['id'],
                'nazwa' => $row['nazwa'],
                'matka' => $row['matka'],
                'children' => array()
            );
        }

        // Zbuduj drzewo
        $tree = array();
        foreach($categories as $id => $category) {
            if($category['matka'] == 0) {
                $tree[] = &$categories[$id];
            } else {
                $categories[$category['matka']]['children'][] = &$categories[$id];
            }
        }

        // Wyświetl drzewo
        $this->displayCategoryTree($tree, '');

        echo '</div>';
        echo '</div>';

        $stmt->close();
    }

    /**
     * Pobiera nazwę kategorii na podstawie jej ID.
     * Wykorzystywane do wyświetlania nazw kategorii nadrzędnych.
     */
    private function getCategoryName($id) {
        global $conn;
        $query = "SELECT nazwa FROM category_list WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['nazwa'];
        }
        return 'Nieznana';
    }

    /**
     * Rekurencyjnie wyświetla drzewo kategorii w stylu Linux.
     * @param array $categories Lista kategorii do wyświetlenia
     * @param string $prefix Aktualny prefiks dla linii
     */
    private function displayCategoryTree($categories, $prefix) {
        $total = count($categories);
        
        foreach($categories as $index => $category) {
            $isLast = ($index === $total - 1);
            
            echo $prefix;
            echo $isLast ? '└── ' : '├── ';
            echo htmlspecialchars($category['nazwa']) . "\n";
            
            if (!empty($category['children'])) {
                $newPrefix = $prefix . ($isLast ? '    ' : '│   ');
                $this->displayCategoryTree($category['children'], $newPrefix);
            }
        }
    }

    /**
     * Obsługuje dodawanie nowej kategorii do systemu.
     * Implementuje walidację danych i zabezpieczenia przed błędami.
     */
    function AddCategory() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nazwa'], $_POST['matka'])) {
                global $conn;
                
                $nazwa = trim($_POST['nazwa']);
                $matka = filter_var($_POST['matka'], FILTER_VALIDATE_INT, [
                    "options" => ["default" => 0, "min_range" => 0]
                ]);

                if (empty($nazwa)) {
                    return "Nazwa kategorii nie może być pusta.";
                }

                // Sprawdź czy kategoria nadrzędna istnieje (jeśli nie jest 0)
                if ($matka > 0) {
                    $stmt = $conn->prepare("SELECT id FROM category_list WHERE id = ?");
                    $stmt->bind_param("i", $matka);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows === 0) {
                        return "Wybrana kategoria nadrzędna nie istnieje.";
                    }
                    $stmt->close();
                }

                $stmt = $conn->prepare("INSERT INTO category_list (nazwa, matka) VALUES (?, ?)");
                $stmt->bind_param("si", $nazwa, $matka);
                
                if ($stmt->execute()) {
                    header("Location: ?idp=kategorie");
                    exit();
                } else {
                    return "Błąd podczas dodawania kategorii: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
            
            return $this->FormularzDodawaniaKategorii();
        }
        return $Admin->FormularzLogowania();
    }

    /**
     * Obsługuje edycję istniejącej kategorii.
     * Zawiera zabezpieczenia przed tworzeniem cykli w hierarchii.
     */
    function EditCategory() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            global $conn;
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['nazwa'], $_POST['matka'])) {
                $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                $nazwa = trim($_POST['nazwa']);
                $matka = filter_var($_POST['matka'], FILTER_VALIDATE_INT, [
                    "options" => ["default" => 0, "min_range" => 0]
                ]);

                if ($id === false || empty($nazwa)) {
                    return "Nieprawidłowe dane kategorii.";
                }

                // Sprawdzenie czy kategoria nie jest swoim własnym rodzicem
                if ($id == $matka) {
                    return "Kategoria nie może być swoją własną kategorią nadrzędną.";
                }

                // Sprawdzenie czy nie tworzymy cyklu w hierarchii
                if ($matka > 0) {
                    $current_parent = $matka;
                    while ($current_parent > 0) {
                        $stmt = $conn->prepare("SELECT matka FROM category_list WHERE id = ?");
                        $stmt->bind_param("i", $current_parent);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        
                        if (!$row) {
                            return "Błąd: Nieprawidłowa kategoria nadrzędna.";
                        }
                        
                        if ($row['matka'] == $id) {
                            return "Błąd: Nie można utworzyć cyklu w hierarchii kategorii.";
                        }
                        
                        $current_parent = $row['matka'];
                    }
                    $stmt->close();
                }

                $stmt = $conn->prepare("UPDATE category_list SET nazwa = ?, matka = ? WHERE id = ?");
                $stmt->bind_param("sii", $nazwa, $matka, $id);
                
                if ($stmt->execute()) {
                    header("Location: ?idp=kategorie");
                    exit();
                } else {
                    return "Błąd podczas aktualizacji kategorii: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
            
            return isset($_GET['id']) ? 
                   $this->FormularzEdycjiKategorii($_GET['id']) : 
                   "Nie podano ID kategorii do edycji.";
        }
        return $Admin->FormularzLogowania();
    }

    /**
     * Obsługuje usuwanie kategorii.
     * Sprawdza czy kategoria nie ma podkategorii przed usunięciem.
     */
    function DeleteCategory() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            if (isset($_GET['id'])) {
                global $conn;
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

                if ($id === false) {
                    return "Nieprawidłowe ID kategorii.";
                }

                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM category_list WHERE matka = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($row['count'] > 0) {
                    return "Nie można usunąć kategorii, która posiada podkategorie.";
                }

                $stmt = $conn->prepare("DELETE FROM category_list WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    header("Location: ?idp=kategorie");
                    exit();
                } else {
                    return "Błąd podczas usuwania kategorii: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
            header("Location: ?idp=kategorie");
            exit();
        }
        return $Admin->FormularzLogowania();
    }

    /**
     * Generuje formularz do dodawania nowej kategorii.
     * Zawiera pola dla nazwy i kategorii nadrzędnej.
     */
    private function FormularzDodawaniaKategorii() {
        return '<div class="create-container">
            <h3 class="create-title">Dodawanie Kategorii</h3>
            <form method="post" class="admin-form">
                <div class="form-group">
                    <label for="nazwa">Nazwa kategorii:</label>
                    <input type="text" id="nazwa" name="nazwa" required />
                </div>
                <div class="form-group">
                    <label for="matka">Kategoria nadrzędna (0 dla głównej):</label>
                    <input type="number" id="matka" name="matka" value="0" min="0" />
                </div>
                <div class="form-group">
                    <input type="submit" class="submit-button" value="Dodaj kategorię" />
                </div>
            </form>
        </div>';
    }

    /**
     * Generuje formularz do edycji istniejącej kategorii.
     * Wypełnia pola aktualnymi wartościami kategorii.
     */
    private function FormularzEdycjiKategorii($id) {
        global $conn;
        $id = filter_var($id, FILTER_VALIDATE_INT);
        
        if ($id === false) {
            return "Nieprawidłowe ID kategorii.";
        }

        $stmt = $conn->prepare("SELECT * FROM category_list WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        if (!$category) {
            return "Nie znaleziono kategorii.";
        }

        return '<div class="edit-container">
            <h3 class="edit-title">Edycja Kategorii</h3>
            <form method="post" class="admin-form">
                <input type="hidden" name="id" value="'.htmlspecialchars($category['id']).'" />
                <div class="form-group">
                    <label for="nazwa">Nazwa kategorii:</label>
                    <input type="text" id="nazwa" name="nazwa" value="'.htmlspecialchars($category['nazwa']).'" required />
                </div>
                <div class="form-group">
                    <label for="matka">Kategoria nadrzędna (0 dla głównej):</label>
                    <input type="number" id="matka" name="matka" value="'.htmlspecialchars($category['matka']).'" min="0" />
                </div>
                <div class="form-group">
                    <input type="submit" class="submit-button" value="Zapisz zmiany" />
                </div>
            </form>
        </div>';
    }
}
?>