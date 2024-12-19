<?php
    /**
     * Główny plik index.php
     * Odpowiada za routing i wyświetlanie odpowiednich podstron
     * Obsługuje panel administracyjny, kontakt i wyświetlanie treści
     */

    // Inicjalizacja sesji i konfiguracja błędów
    session_start();
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

    // Dołączenie wymaganych plików
    include('cfg.php');          // Konfiguracja bazy danych i stałych
    include('admin/admin.php');  // Funkcje panelu administracyjnego
    include('php/contact.php');  // Obsługa formularza kontaktowego
    include('php/categories.php');  // Obsługa kategorii
    include('php/products.php');  // Obsługa produktów
    include('php/navbar.php');   // Menu nawigacyjne
    include('showpage.php');     // Funkcja do wyświetlania zawartości podstron

    // Ustawienie domyślnej strony głównej
    if (!isset($_GET['idp'])) {
        $_GET['idp'] = 'glowna';
    }

    // Dołączenie arkusza stylów
    echo '<link rel="stylesheet" href="css/style.css" />';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Konrad Zieliński" />
    <title>Historia lotów kosmicznych</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
    <script src="js/timedate.js" type="text/javascript"></script>
    <script src="jquery/jQuery_v7.3.1.js"></script>
</head>
<body onload="startclock()">
    <!-- Główny kontener strony -->
    <div class="container">
        <!-- Nagłówek strony -->
        <header class="header">
            <h1>Historia lotów kosmicznych</h1>
        </header>

        <!-- Menu nawigacyjne -->
        <div class="menu">
            <?php echo loadNav(); ?>
        </div>

        <!-- Główna zawartość strony -->
        <div class="main-content">
            <aside class="left-panel">
                <!-- Lewy panel -->
            </aside>

            <section class="content">
                <?php
                $admin = new Admin();
                $idp = $_GET['idp'];
                switch ($idp) {
                    case 'admin':
                        $admin = new Admin();
                        $admin->WyswietlPanel();
                        break;
                    case 'kontakt':
                        $contact = new Contact();
                        echo "<h2> Kontakt </h2>";
                        // Wyświetlenie formularza kontaktowego
                        echo $contact->WyslijMailKontakt("169397@student.uwm.edu.pl");
                        echo "<br></br>";
                        break;
                    case 'edit':
                        echo $admin->EditPage();
                        break;
                    case 'delete':
                        echo $admin->DeletePage();
                        break;
                    case 'create':
                        echo $admin->StworzPodstrone();
                        break;
                    case 'kategorie':
                        $Category = new Category();
                        echo $Category->PokazKategorie();
                        break;
                    case 'nowa-kategoria':
                        $Category = new Category();
                        echo $Category->AddCategory();
                        break;
                    case 'edytuj-kategorie':
                        $Category = new Category();
                        echo $Category->EditCategory();
                        break;
                    case 'usun-kategorie':
                        $Category = new Category();
                        echo $Category->DeleteCategory();
                        break;
                    case 'nowy-produkt':
                        $ProductManager = new ProductManager();
                        $ProductManager->DodajProdukt();
                        break;
                    case 'edytuj-produkt':
                        $ProductManager = new ProductManager();
                        $ProductManager->EdytujProdukt();
                        break;
                    case 'usun-produkt':
                        $ProductManager = new ProductManager();
                        $ProductManager->UsunProdukt();
                        break;
                    default:
                        echo PokazStrone($_GET['idp']);
                }
                ?>
            </section>

            <aside class="right-panel">
                <!-- Prawy panel -->
            </aside>
        </div>

        <!-- Stopka strony -->
        <footer class="footer">
        <?php
    // Informacje o autorze
    $nr_indeksu = '169397';
    $nrGrupy = 'ISI 4 ';
    echo 'Autor: Konrad Zieliński '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />';
    ?>
        </footer>
    </div>
</body>
</html>
