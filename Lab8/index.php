<?php
    session_start();
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    include('cfg.php');
    include('admin/admin.php');
    include('php/contact.php');
    include('php/navbar.php');

    if (!isset($_GET['idp'])) {
        $_GET['idp'] = 'glowna';
    }

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
                    $alias = htmlspecialchars($_GET['idp']);

                    static $Admin = null;
                     
                    switch ($alias) {                         
                        case 'kontakt':                            
                            $contact = new Contact();
                            echo "<h2> Kontakt </h2>";
                            echo $contact->WyslijMailKontakt("169399@student.uwm.edu.pl"); 		// Wyświetlenie formularza do kontaktu email'em
                            echo "<br></br>";
                            break;
                        case 'admin':
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            echo $Admin->LoginAdmin();
                            break;
             
                        case 'logout':
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            $Admin->logout();
                            break;
             
                        case 'edit':
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            echo $Admin->EditPage();
                            break;
             
                        case 'delete':
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            echo $Admin->DeletePage();
                            break;
                        case 'create': // Zmiana na 'create'
                            if ($Admin === null) {
                                $Admin = new Admin();
                            }
                            echo $Admin->StworzPodstrone();
                            break;
                            
                        case 'haslo':
                            $contact = new contact();
                            echo "<h2> Odzyskanie hasła </h2>";
                            echo $contact->PrzypomnijHaslo("169399@student.uwm.edu.pl");
                            echo "<br></br>";                
                            break;
                                
                        default:
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
    $nr_indeksu = '169397';
    $nrGrupy = 'ISI 4 ';
    echo 'Autor: Konrad Zieliński '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />';
    ?>
        </footer>
    </div>

    
</body>
</html>
