<?php
require_once __DIR__ . '/../admin/admin_navbar.php';

class ProductManager {
    public function __construct() {
    }

    public function DodajProdukt() {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login != 1) {
            return $Admin->FormularzLogowania();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dane = array(
                'tytul' => $_POST['tytul'],
                'opis' => $_POST['opis'],
                'data_wygasniecia' => $_POST['data_wygasniecia'],
                'cena_netto' => $_POST['cena_netto'],
                'podatek_vat' => $_POST['podatek_vat'],
                'ilosc_magazyn' => $_POST['ilosc_magazyn'],
                'status_dostepnosci' => $_POST['status_dostepnosci'],
                'kategoria' => $_POST['kategoria'],
                'gabaryt' => $_POST['gabaryt'],
                'zdjecie' => $_POST['zdjecie']
            );

            $query = "INSERT INTO product_list (tytul, opis, data_wygasniecia, cena_netto, 
                      podatek_vat, ilosc_magazyn, status_dostepnosci, kategoria, gabaryt, zdjecie) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssddiisss",
                $dane['tytul'],
                $dane['opis'],
                $dane['data_wygasniecia'],
                $dane['cena_netto'],
                $dane['podatek_vat'],
                $dane['ilosc_magazyn'],
                $dane['status_dostepnosci'],
                $dane['kategoria'],
                $dane['gabaryt'],
                $dane['zdjecie']
            );

            if ($stmt->execute()) {
                header("Location: ?idp=admin&action=products");
                exit;
            } else {
                echo "Błąd podczas dodawania produktu: " . $conn->error;
            }
        }

        // Pobierz listę kategorii dla formularza
        $query = "SELECT id, nazwa FROM category_list";
        $result = $conn->query($query);
        $kategorie = array();
        while ($row = $result->fetch_assoc()) {
            $kategorie[] = $row;
        }

        // Wyświetl formularz
        echo '<div class="form-container">';
        echo '<form method="POST" action="?idp=nowy-produkt" class="admin-form">';
        echo '<div class="form-group">';
        echo '<label for="tytul">Tytuł produktu:</label>';
        echo '<input type="text" id="tytul" name="tytul" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="opis">Opis:</label>';
        echo '<textarea id="opis" name="opis" required></textarea>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="data_wygasniecia">Data wygaśnięcia:</label>';
        echo '<input type="date" id="data_wygasniecia" name="data_wygasniecia" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="cena_netto">Cena netto:</label>';
        echo '<input type="number" step="0.01" id="cena_netto" name="cena_netto" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="podatek_vat">Podatek VAT:</label>';
        echo '<select id="podatek_vat" name="podatek_vat" required>';
        echo '<option value="23">23% - Stawka podstawowa (większość towarów)</option>';
        echo '<option value="8">8% - Stawka obniżona (niektóre towary)</option>';
        echo '<option value="5">5% - Stawka obniżona (wybrane towary)</option>';
        echo '<option value="0">0% - Stawka zerowa (eksport)</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="ilosc_magazyn">Ilość w magazynie:</label>';
        echo '<input type="number" id="ilosc_magazyn" name="ilosc_magazyn" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="status_dostepnosci">Status dostępności:</label>';
        echo '<select id="status_dostepnosci" name="status_dostepnosci" required>';
        echo '<option value="dostępny">Dostępny</option>';
        echo '<option value="niedostępny">Niedostępny</option>';
        echo '<option value="oczekujący">Oczekujący</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="kategoria">Kategoria:</label>';
        echo '<select id="kategoria" name="kategoria" required>';
        foreach ($kategorie as $kategoria) {
            echo '<option value="' . $kategoria['id'] . '">' . $kategoria['nazwa'] . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="gabaryt">Gabaryt:</label>';
        echo '<select id="gabaryt" name="gabaryt" required>';
        echo '<option value="mały">Mały</option>';
        echo '<option value="średni">Średni</option>';
        echo '<option value="duży">Duży</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="zdjecie">URL zdjęcia:</label>';
        echo '<input type="text" id="zdjecie" name="zdjecie">';
        echo '</div>';
        
        echo '<div class="form-buttons">';
        echo '<button type="submit" class="action-button">Dodaj produkt</button>';
        echo '<a href="?idp=admin&action=products" class="action-button">Anuluj</a>';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
    }

    public function UsunProdukt($id = null) {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login != 1) {
            return $Admin->FormularzLogowania();
        }

        // Get ID from URL if not provided as parameter
        if ($id === null) {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
        }

        if ($id === null) {
            echo "Błąd: Nie podano ID produktu";
            return;
        }

        $query = "DELETE FROM product_list WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: ?idp=admin&action=products");
            exit;
        } else {
            echo "Błąd podczas usuwania produktu: " . $conn->error;
        }
    }

    public function EdytujProdukt($id = null, $status = null) {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login != 1) {
            return $Admin->FormularzLogowania();
        }

        // Get ID from URL if not provided as parameter
        if ($id === null) {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
        }

        if ($id === null) {
            echo "Błąd: Nie podano ID produktu";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // First, update the status separately to ensure it's set properly
            $status_query = "UPDATE product_list SET status_dostepnosci = ? WHERE id = ?";
            $status_stmt = $conn->prepare($status_query);
            $new_status = $_POST['status_dostepnosci'];
            $status_stmt->bind_param("si", $new_status, $id);
            $status_stmt->execute();

            // Then update the rest of the fields
            $dane = array(
                'tytul' => $_POST['tytul'],
                'opis' => $_POST['opis'],
                'data_wygasniecia' => $_POST['data_wygasniecia'],
                'cena_netto' => $_POST['cena_netto'],
                'podatek_vat' => $_POST['podatek_vat'],
                'ilosc_magazyn' => $_POST['ilosc_magazyn'],
                'kategoria' => $_POST['kategoria'],
                'gabaryt' => $_POST['gabaryt'],
                'zdjecie' => $_POST['zdjecie']
            );

            $query = "UPDATE product_list SET 
                     tytul = ?, 
                     opis = ?, 
                     data_wygasniecia = ?, 
                     cena_netto = ?, 
                     podatek_vat = ?, 
                     ilosc_magazyn = ?, 
                     kategoria = ?, 
                     gabaryt = ?, 
                     zdjecie = ? 
                     WHERE id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssddiissi",
                $dane['tytul'],
                $dane['opis'],
                $dane['data_wygasniecia'],
                $dane['cena_netto'],
                $dane['podatek_vat'],
                $dane['ilosc_magazyn'],
                $dane['kategoria'],
                $dane['gabaryt'],
                $dane['zdjecie'],
                $id
            );

            if ($stmt->execute()) {
                header("Location: ?idp=admin&action=products");
                exit;
            } else {
                echo "Błąd podczas aktualizacji produktu: " . $conn->error;
            }
        }

        // Pobierz dane produktu
        $query = "SELECT p.*, c.nazwa as kategoria_nazwa 
                 FROM product_list p 
                 LEFT JOIN category_list c ON p.kategoria = c.id 
                 WHERE p.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $produkt = $result->fetch_assoc();

        if (!$produkt) {
            echo "Produkt nie został znaleziony";
            return;
        }

        // Pobierz listę kategorii
        $query = "SELECT id, nazwa FROM category_list";
        $result = $conn->query($query);
        $kategorie = array();
        while ($row = $result->fetch_assoc()) {
            $kategorie[] = $row;
        }

        // Wyświetl formularz edycji
        echo '<div class="form-container">';
        echo '<form method="POST" action="?idp=edytuj-produkt&id='.$id.'" class="admin-form">';
        
        echo '<div class="form-group">';
        echo '<label for="tytul">Tytuł produktu:</label>';
        echo '<input type="text" id="tytul" name="tytul" value="'.htmlspecialchars($produkt['tytul']).'" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="opis">Opis:</label>';
        echo '<textarea id="opis" name="opis" required>'.htmlspecialchars($produkt['opis']).'</textarea>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="data_wygasniecia">Data wygaśnięcia:</label>';
        echo '<input type="date" id="data_wygasniecia" name="data_wygasniecia" value="'.$produkt['data_wygasniecia'].'" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="cena_netto">Cena netto:</label>';
        echo '<input type="number" step="0.01" id="cena_netto" name="cena_netto" value="'.$produkt['cena_netto'].'" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="podatek_vat">Podatek VAT:</label>';
        echo '<select id="podatek_vat" name="podatek_vat" required>';
        $vat_rates = array(23 => "23% - Stawka podstawowa", 8 => "8% - Stawka obniżona", 5 => "5% - Stawka obniżona", 0 => "0% - Stawka zerowa");
        foreach ($vat_rates as $rate => $label) {
            $selected = ($produkt['podatek_vat'] == $rate) ? 'selected' : '';
            echo '<option value="'.$rate.'" '.$selected.'>'.$label.'</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="ilosc_magazyn">Ilość w magazynie:</label>';
        echo '<input type="number" id="ilosc_magazyn" name="ilosc_magazyn" value="'.$produkt['ilosc_magazyn'].'" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="status_dostepnosci">Status dostępności:</label>';
        echo '<select id="status_dostepnosci" name="status_dostepnosci" required>';
        $statusy = array('dostępny' => 'Dostępny', 'niedostępny' => 'Niedostępny', 'oczekujący' => 'Oczekujący');
        foreach ($statusy as $value => $label) {
            $selected = ($produkt['status_dostepnosci'] === $value) ? 'selected' : '';
            echo '<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="kategoria">Kategoria:</label>';
        echo '<select id="kategoria" name="kategoria" required>';
        foreach ($kategorie as $kat) {
            $selected = ($produkt['kategoria'] == $kat['id']) ? 'selected' : '';
            echo '<option value="'.$kat['id'].'" '.$selected.'>'.$kat['nazwa'].'</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="gabaryt">Gabaryt:</label>';
        echo '<select id="gabaryt" name="gabaryt" required>';
        $gabaryty = array('mały' => 'Mały', 'średni' => 'Średni', 'duży' => 'Duży');
        foreach ($gabaryty as $value => $label) {
            $selected = ($produkt['gabaryt'] === $value) ? 'selected' : '';
            echo '<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="zdjecie">URL zdjęcia:</label>';
        echo '<input type="text" id="zdjecie" name="zdjecie" value="'.htmlspecialchars($produkt['zdjecie']).'">';
        echo '</div>';
        
        echo '<div class="form-buttons">';
        echo '<button type="submit" class="action-button">Zapisz zmiany</button>';
        echo '<a href="?idp=admin&action=products" class="action-button">Anuluj</a>';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
    }

    public function EdytujProduktForm($id) {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login != 1) {
            return $Admin->FormularzLogowania();
        }

        echo '<h3 class="h3-admin">Edytuj produkt</h3>';

        if (!isset($_GET['id'])) {
            return "Nie podano ID produktu do edycji.";
        }

        // Rest of the edit function
        // ...
    }

    private function WyswietlListeProduktow($products) {
        echo '<div class="action-buttons">';
        echo '<a href="?idp=nowy-produkt" class="action-button">+ Dodaj nowy produkt</a>';
        echo '</div>';

        echo '<table class="admin-table">
        <tr>
            <th class="id-cell">ID</th>
            <th class="title-cell">Nazwa Produktu</th>
            <th>Kategoria</th>
            <th class="price-cell">Cena netto (PLN)</th>
            <th class="vat-cell">VAT</th>
            <th class="status-cell">Status</th>
            <th class="action-cell">Akcje</th>
        </tr>';

        foreach($products as $product) {
            $status = $product['status_dostepnosci'] ?: 'dostępny';
            echo '<tr>
                    <td class="id-cell">#'.$product['id'].'</td>
                    <td class="title-cell">'.$product['tytul'].'</td>
                    <td>'.$product['kategoria_nazwa'].'</td>
                    <td class="price-cell">'.number_format($product['cena_netto'], 2).'</td>
                    <td class="vat-cell">'.$product['podatek_vat'].'%</td>
                    <td class="status-cell" data-status="'.$status.'">'.$status.'</td>
                    <td class="action-cell">
                        <a href="?idp=edytuj-produkt&id='.$product['id'].'" class="action-button edit">Edytuj</a>
                        <a href="?idp=usun-produkt&id='.$product['id'].'" 
                           class="action-button delete" 
                           onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\');">Usuń</a>
                    </td>
                  </tr>';
        }
        
        echo '</table>';
    }

    public function PokazProdukty() {
        global $conn;
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            $query = "SELECT p.*, c.nazwa as kategoria_nazwa 
                     FROM product_list p 
                     LEFT JOIN category_list c ON p.kategoria = c.id 
                     ORDER BY p.id DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $products = array();
            
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            
            $this->WyswietlListeProduktow($products);
        } else {
            echo $Admin->FormularzLogowania();
        }
    }

    public function SprawdzDostepnosc($id) {
        global $conn;
        $query = "SELECT status_dostepnosci, ilosc_magazyn, data_wygasniecia 
                 FROM product_list WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $produkt = $result->fetch_assoc();

        if (!$produkt) {
            return false;
        }

        // Sprawdzanie warunków dostępności
        if ($produkt['status_dostepnosci'] !== 'dostępny') {
            return false;
        }

        if ($produkt['ilosc_magazyn'] <= 0) {
            return false;
        }

        if ($produkt['data_wygasniecia'] && strtotime($produkt['data_wygasniecia']) < time()) {
            return false;
        }

        return true;
    }
}
?>
