<?php
function loadNav() {
    global $conn; // Użycie globalnej zmiennej połączenia z bazą danych

    // Zapytanie SQL do pobrania wszystkich aktywnych podstron
    $query = "SELECT alias, page_title FROM page_list WHERE status = 1"; // Pobieramy tylko aktywne strony
    $result = $conn->query($query); 

    // Inicjalizacja zmiennej do przechowywania HTML nawigacji
    $navHtml = '<nav><ul>';

    // Iteracja przez wyniki zapytania
    while ($row = $result->fetch_assoc()) {
        // Zmień tytuł strony jeśli to panel logowania i użytkownik jest zalogowany
        $pageTitle = $row['page_title'];
        if (isset($_SESSION['loggedin']) && $row['alias'] === 'admin') {
            $pageTitle = 'LISTA STRON';
        }
        // Dodanie linku do nawigacji dla każdej podstrony
        $navHtml .= '<li><a href="?idp=' . htmlspecialchars($row['alias']) . '">' . htmlspecialchars($pageTitle) . '</a></li>';
    }

    // Sprawdzenie, czy użytkownik jest zalogowany
    if (isset($_SESSION['loggedin'])) {
        // Jeśli użytkownik jest zalogowany, dodaj linki do panelu administracyjnego i kategorii
        $navHtml .= '<li><a href="?idp=kategorie">KATEGORIE</a></li>';
        $navHtml .= '<li><a class="logout" href="?idp=logout">WYLOGUJ</a></li>';
    }else{
        $navHtml .= '<li><a href="?idp=haslo">ODZYSKIWANIE HASŁA</a></li>';
    }

    $navHtml .= '</ul></nav>'; // Zamknięcie listy i nawigacji

    return $navHtml; // Zwrócenie HTML nawigacji
}
?>