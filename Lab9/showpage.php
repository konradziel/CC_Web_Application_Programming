<?php

include('cfg.php');

/**
 * Wyświetla zawartość podstrony na podstawie aliasu
 * Zabezpieczone przed SQL Injection poprzez prepared statements
 * 
 * @param string $alias Alias podstrony do wyświetlenia
 * @return string Zawartość podstrony lub komunikat o błędzie
 */
function PokazStrone($alias) {
    global $conn;
    
    // Zabezpieczenie przed SQL Injection
    $query = "SELECT * FROM page_list WHERE alias = ? AND status = 1 LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $alias);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    
    // Zwracanie zawartości strony lub komunikatu o błędzie
    return empty($row['id']) ? '[nie_znaleziono_strony]' : $row['page_content'];
}

// Sprawdzenie czy przekazano parametr idp i wyświetlenie odpowiedniej strony
if (isset($_GET['idp'])) {
    $alias = htmlspecialchars($_GET['idp']); // Dodatkowe zabezpieczenie przed XSS
    echo PokazStrone($alias);
} else {
    echo '[nie_znaleziono_strony]';
}
?>