<?php

/**
 * Klasa Contact
 * Obsługuje funkcjonalności związane z formularzem kontaktowym i odzyskiwaniem hasła
 */
class Contact {
    
    /**
     * Wyświetla formularz kontaktowy
     * @return string HTML formularza kontaktowego
     */
    function PokazKontakt() {
        return '
        <form method="post" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
            <table class="form_email">
                <tr>
                    <td>Email:</td>
                    <td><input type="email" name="email" required style="width: 100%;" /></td>
                </tr>
                <tr>
                    <td>Tytuł:</td>
                    <td><input type="text" name="title" required style="width: 100%;" maxlength="100" /></td>
                </tr>
                <tr>
                    <td>Zawartość:</td>
                    <td><textarea name="content" required style="width: 100%; height: 150px;" maxlength="1000"></textarea></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="Wyślij" class="submit-button" /></td>
                </tr>
            </table>
            <div class="buttons2">
                 <a class="contact-button" href="?idp=haslo">Odzyskiwanie hasła</a>
            </div>
        </form>';
    }

    /**
     * Wyświetla formularz odzyskiwania hasła
     * @return string HTML formularza odzyskiwania hasła
     */
    function PokazKontaktHaslo() {
        return '
        <div class="form_passrecov">
            <form method="post" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
                <table class="form_passrecov">
                    <tr>
                        <td>Email:</td>
                        <td><input type="email" name="email_recov" required style="width: 100%;" /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="Wyślij" class="submit-button" /></td>
                    </tr>
                </table>
            </form>
            <div class="buttons2">
                 <a class="contact-button" href="?idp=kontakt">Kontakt</a>
            </div>
        </div>';
    }

    /**
     * Wysyła email kontaktowy
     * @param string $odbiorca Adres email odbiorcy
     */
    function WyslijMailKontakt($odbiorca) {
        if (empty($_POST['email']) || empty($_POST['title']) || empty($_POST['content'])) {
            echo $this->PokazKontakt();
            return;
        }

        // Walidacja i czyszczenie danych wejściowych
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo '<div class="alert">Nieprawidłowy adres email!</div>';
            echo $this->PokazKontakt();
            return;
        }

        $title = htmlspecialchars(substr($_POST['title'], 0, 100));
        $content = htmlspecialchars(substr($_POST['content'], 0, 1000));
        
        // Symulacja wysyłki emaila - zawsze zwraca sukces
        echo '<div class="alert">Wiadomość została wysłana!</div>';
        
        // Opcjonalnie możemy wyświetlić dane, które byłyby wysłane
        /*
        echo '<div class="debug">Debug: Email zostałby wysłany do: ' . htmlspecialchars($odbiorca) . 
             '<br>Od: ' . htmlspecialchars($email) . 
             '<br>Tytuł: ' . htmlspecialchars($title) . '</div>';
        */
    }

    /**
     * Obsługuje proces odzyskiwania hasła
     * @param string $odbiorca Adres email odbiorcy
     */
    function PrzypomnijHaslo($odbiorca) {
        if (empty($_POST['email_recov'])) {
            echo $this->PokazKontaktHaslo();
            return;
        }

        // Walidacja adresu email
        $email = filter_var($_POST['email_recov'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo '<div class="alert">Nieprawidłowy adres email!</div>';
            echo $this->PokazKontaktHaslo();
            return;
        }

        // Symulacja wysyłki emaila - zawsze zwraca sukces
        echo '<div class="alert">Wiadomość została wysłana!</div>';
        
        // Opcjonalnie możemy wyświetlić dane, które byłyby wysłane
        /*
        echo '<div class="debug">Debug: Email zostałby wysłany do: ' . htmlspecialchars($odbiorca) . 
             '<br>Od: ' . htmlspecialchars($email) . 
             '<br>Tytuł: ' . htmlspecialchars($title) . '</div>';
        */
    }
}
?>