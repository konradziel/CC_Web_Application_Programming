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
    include('php/navbar.php');   // Menu nawigacyjne

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
                <!-- Lewy panel, można dodać coś później -->
            </aside>

            <section class="content">
                <?php
                    // Zabezpieczenie przed XSS
                    $alias = htmlspecialchars($_GET['idp']);

                    // Inicjalizacja obiektu Admin jako singleton
                    static $Admin = null;
                     
                    // Router - obsługa różnych podstron na podstawie parametru GET['idp']
                    switch ($alias) {                         
                        case 'kontakt':                            
                            $contact = new Contact();
                            echo "<h2> Kontakt </h2>";
                            // Wyświetlenie formularza kontaktowego
                            echo $contact->WyslijMailKontakt("169397@student.uwm.edu.pl");
                            echo "<br></br>";
                            break;

                        case 'admin':
                            // Lazy loading dla obiektu Admin
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            echo $Admin->LoginAdmin();
                            break;
             
                        case 'logout':
                            // Wylogowanie użytkownika
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            $Admin->logout();
                            break;
             
                        case 'edit':
                            // Edycja istniejącej podstrony
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            echo $Admin->EditPage();
                            break;
             
                        case 'delete':
                            // Usuwanie podstrony
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            echo $Admin->DeletePage();
                            break;

                        case 'create':
                            // Tworzenie nowej podstrony
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            echo $Admin->StworzPodstrone();
                            break;
                            
                        case 'haslo':
                            // Obsługa formularza odzyskiwania hasła
                            $contact = new contact();
                            echo "<h2> Odzyskanie hasła </h2>";
                            echo $contact->PrzypomnijHaslo("169399@student.uwm.edu.pl");
                            echo "<br></br>";                
                            break;
                                
                        default:
                            // Wyświetlenie wybranej podstrony lub strony głównej
                            include('showpage.php');        
                            PokazStrone($alias);              
                            break;
                    }                    
                ?>
            </section>

            <aside class="right-panel">
                <!-- Prawy panel, można dodać coś później -->
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
