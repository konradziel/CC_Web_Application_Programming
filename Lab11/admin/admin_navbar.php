<?php
if (!function_exists('WyswietlAdminNav')) {
    function WyswietlAdminNav() {
        echo '<div class="admin-menu">';
        echo '<a href="?idp=admin&action=pages" class="admin-menu-item">Strony</a>';
        echo '<a href="?idp=admin&action=categories" class="admin-menu-item">Kategorie</a>';
        echo '<a href="?idp=admin&action=products" class="admin-menu-item">Produkty</a>';
        echo '<a href="?idp=admin&action=logout" class="admin-menu-item">Wyloguj</a>';
        echo '</div>';
    }
}
?>
