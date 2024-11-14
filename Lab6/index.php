<?php

    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    include('cfg.php');

    if (!isset($_GET['idp'])) {
        $_GET['idp'] = 'glowna';
    }

    if ($_GET['idp'] == 'glowna') $strona = 'glowna';
    if ($_GET['idp'] == 'poczatki') $strona = 'poczatki';
    if ($_GET['idp'] == 'wspolczesnosc') $strona = 'wspolczesnosc';
    if ($_GET['idp'] == 'galeria') $strona = 'galeria';
    if ($_GET['idp'] == 'kontakt') $strona = 'kontakt';
    if ($_GET['idp'] == 'js_test') $strona = 'js_test';
    if ($_GET['idp'] == 'filmy') $strona = 'filmy';

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
        <nav class="menu">
            <ul>
                <li><a href="index.php?idp=glowna">Strona główna</a></li>
                <li><a href="index.php?idp=poczatki">Początki</a></li>
                <li><a href="index.php?idp=wspolczesnosc">Współczesność</a></li>
                <li><a href="index.php?idp=galeria">Galeria</a></li>
                <li><a href="index.php?idp=filmy">Filmy</a></li>
                <li><a href="index.php?idp=kontakt">Kontakt</a></li>
                <li><a href="index.php?idp=js_test">JS-Test</a></li>                
            </ul>
        </nav>

        <!-- Główna zawartość strony -->
        <div class="main-content">
            <aside class="left-panel">
                <!-- Lewy panel, można dodać coś później -->
            </aside>

            <section class="content">
                <?php
                    if (isset($_GET['idp']) && $_GET['idp'] !== '') {
                        include('showpage.php');
                        PokazStrone($strona);
                    } else {
                        echo "Wybrana strona nie istnieje.";
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
    $nrGrupy = '2';
    echo 'Autor: Konrad Zieliński '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />';
    ?>
        </footer>
    </div>

    
</body>
</html>
