<?php
require_once __DIR__ . '/../admin/admin_navbar.php';

/**
 * System zarządzania kategoriami
 * 
 * Implementacja systemu kategorii.
 */
class Category {
    public function __construct() {
    }

    /**
     * Główna metoda wyświetlająca panel zarządzania kategoriami.
     * Sprawdza uprawnienia użytkownika i wyświetla odpowiedni interfejs.
     */
    public function PokazKategorie() {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            echo '<div class="action-buttons">';
            echo '<a href="?idp=nowa-kategoria" class="action-button">+ Dodaj nową kategorię</a>';
            echo '</div>';
            
            $this->ListaKategorii();
        } else {
            echo $Admin->FormularzLogowania();
        }
    }

    /**
     * Wyświetla listę kategorii.
     */
    public function ListaKategorii() {
        global $conn;
        $query = "SELECT id, matka, nazwa FROM category_list ORDER BY id ASC LIMIT 100";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table class="admin-table">
        <tr>
            <th>ID</th>
            <th>Kategoria Nadrzędna</th>
            <th>Nazwa Kategorii</th>
            <th>Akcje</th>
        </tr>';

        while($row = $result->fetch_assoc()) {
            $parentName = ($row['matka'] == 0) ? '-' : $this->getCategoryName($row['matka']);
            
            echo '<tr>
                    <td class="id-cell">#'.$row['id'].'</td>
                    <td>'.$parentName.'</td>
                    <td>'.$row['nazwa'].'</td>
                    <td class="action-cell">
                        <a href="?idp=edytuj-kategorie&id='.$row['id'].'" class="action-button edit">Edytuj</a>
                        <a href="?idp=usun-kategorie&id='.$row['id'].'" 
                           class="action-button delete" 
                           onclick="return confirm(\'Czy na pewno chcesz usunąć kategorię \\\'\' + \''.htmlspecialchars($row['nazwa']).'\' + \'\\\'?\');">Usuń</a>
                    </td>
                  </tr>';
        }
        
        echo '</table>';

        // Wizualizacja struktury drzewiastej
        echo '<div class="category-tree-container">';
        echo '<div class="category-tree">';
        echo '<h3>Struktura kategorii</h3>';

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
    public function AddCategory() {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login != 1) {
            return $Admin->FormularzLogowania();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nazwa'])) {
            $nazwa = $_POST['nazwa'];
            $matka = isset($_POST['matka']) ? intval($_POST['matka']) : 0;

            $query = "INSERT INTO category_list (nazwa, matka) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $nazwa, $matka);

            if ($stmt->execute()) {
                header("Location: ?idp=admin&action=categories");
                exit;
            } else {
                echo "Błąd podczas dodawania kategorii: " . $conn->error;
            }
            $stmt->close();
        }

        return $this->FormularzDodawaniaKategorii();
    }

    /**
     * Obsługuje edycję istniejącej kategorii.
     * Zawiera zabezpieczenia przed tworzeniem cykli w hierarchii.
     */
    public function EditCategory() {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login != 1) {
            return $Admin->FormularzLogowania();
        }

        if (!isset($_GET['id'])) {
            header("Location: ?idp=admin&action=categories");
            exit;
        }

        $id = intval($_GET['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nazwa'])) {
            $nazwa = $_POST['nazwa'];
            $matka = isset($_POST['matka']) ? intval($_POST['matka']) : 0;

            if ($id == $matka) {
                echo "Kategoria nie może być swoim własnym rodzicem!";
                return $this->FormularzEdycjiKategorii($id);
            }

            $query = "UPDATE category_list SET nazwa=?, matka=? WHERE id=? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sii", $nazwa, $matka, $id);

            if ($stmt->execute()) {
                header("Location: ?idp=admin&action=categories");
                exit;
            } else {
                echo "Błąd podczas aktualizacji: " . $conn->error;
            }
            $stmt->close();
        }

        return $this->FormularzEdycjiKategorii($id);
    }

    /**
     * Obsługuje usuwanie kategorii.
     * Sprawdza czy kategoria nie ma podkategorii przed usunięciem.
     */
    public function DeleteCategory() {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login != 1) {
            return $Admin->FormularzLogowania();
        }

        if (!isset($_GET['id'])) {
            return "Nie podano ID kategorii do usunięcia.";
        }

        $id = intval($_GET['id']);

        // Sprawdź czy kategoria ma podkategorie
        $query = "SELECT COUNT(*) as count FROM category_list WHERE matka = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            echo "Nie można usunąć kategorii, która ma podkategorie!";
            header("Location: ?idp=kategorie");
            exit;
        }

        $query = "DELETE FROM category_list WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: ?idp=kategorie");
            exit;
        } else {
            echo "Błąd podczas usuwania: " . $conn->error;
        }
        $stmt->close();
    }

    /**
     * Generuje formularz do dodawania nowej kategorii.
     * Zawiera pola dla nazwy i kategorii nadrzędnej.
     */
    private function FormularzDodawaniaKategorii() {
        global $conn;
        $query = "SELECT * FROM category_list ORDER BY nazwa ASC";
        $result = $conn->query($query);

        $output = '
        <div class="create-container">
            <h3 class="create-title">Dodawanie Kategorii</h3>
            <form method="post" class="admin-form">
                <div class="form-group">
                    <label for="nazwa">Nazwa kategorii:</label>
                    <input type="text" id="nazwa" name="nazwa" required />
                </div>
                <div class="form-group">
                    <label for="matka">Kategoria nadrzędna:</label>
                    <select id="matka" name="matka">
                        <option value="0">Brak (kategoria główna)</option>';
        
        while($row = $result->fetch_assoc()) {
            $output .= '<option value="'.$row['id'].'">'.htmlspecialchars($row['nazwa']).'</option>';
        }
        
        $output .= '
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" class="submit-button" value="Dodaj kategorię" />
                </div>
            </form>
        </div>';

        return $output;
    }

    /**
     * Generuje formularz do edycji istniejącej kategorii.
     * Wypełnia pola aktualnymi wartościami kategorii.
     */
    private function FormularzEdycjiKategorii($id) {
        global $conn;
        // Pobierz dane kategorii
        $query = "SELECT * FROM category_list WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();

        if (!$category) {
            return "Nie znaleziono kategorii.";
        }

        // Pobierz listę wszystkich kategorii do wyboru jako rodzic
        $query = "SELECT * FROM category_list WHERE id != ? ORDER BY nazwa ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $output = '
        <div class="edit-container">
            <h3>Edytuj kategorię</h3>
            <form method="post">
                <input type="hidden" name="id" value="'.htmlspecialchars($category['id']).'" />
                <div class="form-group">
                    <label for="nazwa">Nazwa kategorii:</label>
                    <input type="text" id="nazwa" name="nazwa" value="'.htmlspecialchars($category['nazwa']).'" required />
                </div>
                <div class="form-group">
                    <label for="matka">Kategoria nadrzędna:</label>
                    <select id="matka" name="matka">
                        <option value="0"'.(0 == $category['matka'] ? ' selected' : '').'>Brak (kategoria główna)</option>';
        
        while($row = $result->fetch_assoc()) {
            $output .= '<option value="'.$row['id'].'"'.
                      ($row['id'] == $category['matka'] ? ' selected' : '').'>'.
                      htmlspecialchars($row['nazwa']).'</option>';
        }
        
        $output .= '
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" value="Zapisz zmiany" class="button" />
                    <a href="?idp=admin&action=categories" class="button">Anuluj</a>
                </div>
            </form>
        </div>';

        return $output;
    }

    private function WyswietlDrzewoKategorii($categories, $parent_id = null, $level = 0) {
        $output = '';
        foreach ($categories as $category) {
            if ($category['matka'] == $parent_id) {
                $prefix = str_repeat("│ ", $level);
                if ($level > 0) {
                    $prefix = substr($prefix, 0, -1);
                    $prefix .= "├── ";
                } else {
                    $prefix = "├── ";
                }
                
                $output .= $prefix . $category['nazwa'] . "\n";
                
                // Get children
                $children = array_filter($categories, function($cat) use ($category) {
                    return $cat['matka'] == $category['id'];
                });
                
                if (!empty($children)) {
                    $output .= $this->WyswietlDrzewoKategorii($categories, $category['id'], $level + 1);
                }
            }
        }
        return $output;
    }

    public function PokazKategorieDrzewo() {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            $query = "SELECT * FROM category_list ORDER BY matka, nazwa";
            $result = $conn->query($query);
            $categories = array();
            
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }

            echo '<h2 class="category-tree-title">Struktura kategorii</h2>';
            echo '<pre class="category-tree">';
            echo $this->WyswietlDrzewoKategorii($categories);
            echo '</pre>';
        } else {
            echo $Admin->FormularzLogowania();
        }
    }
}
?>