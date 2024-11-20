<?php

class Contact {
    // Funkcja 'WyslijMailKontakt' jest odpowiedzialna za wysłanie wiadomości e-mail na adres podany przez administratora.
    function WyslijMailKontakt($odbiorca) {
        // Sprawdzenie, czy pola formularza są puste (email, tytuł i treść)
        if (empty($_POST['email']) || empty($_POST['title']) || empty($_POST['content'])) {
            // Jeśli dane są puste, wyświetl formularz kontaktowy do wypełnienia
            echo $this->PokazKontakt();
        } else {
            // Przygotowanie danych do wysyłki e-mail
            $mail['sender'] = $_POST['email']; // Adres e-mail nadawcy
            $mail['subject'] = $_POST['title']; // Temat wiadomości
            $mail['body'] = $_POST['content']; // Treść wiadomości
            $mail['recipient'] = $odbiorca; // Odbiorca wiadomości

            // Nagłówki e-mail
            $header = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
            $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\n";

            // Wysłanie e-maila i sprawdzenie, czy operacja się powiodła
            if (mail($mail['recipient'], $mail['subject'], $mail['body'], $header)) {
                echo '<div class="alert">Wiadomość została wysłana!</div>'; // Potwierdzenie wysłania
            } else {
                echo '<div class="alert">Wystąpił błąd podczas wysyłania wiadomości.</div>'; // Błąd podczas wysyłania
            }
        }
    }

    // Funkcja 'PokazKontakt' wyświetla formularz do wprowadzenia emaila, tytułu oraz treści.
    function PokazKontakt() {
        return '
        <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
            <table class="form_email">
                <tr>
                    <td>Email:</td>
                    <td><input type="text" name="email" required style="width: 100%;" /></td>
                </tr>
                <tr>
                    <td>Tytuł:</td>
                    <td><input type="text" name="title" required style="width: 100%;" /></td>
                </tr>
                <tr>
                    <td>Zawartość:</td>
                    <td><textarea name="content" required style="width: 100%; height: 150px;"></textarea></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="Wyślij" class="submit-button" /></td> <!-- Przycisk do wysyłania -->
                </tr>
            </table>
            <div class="buttons2">
                 <a class="contact-button" href="?idp=haslo">Odzyskiwanie hasła</a> <!-- Link do odzyskiwania hasła -->
            </div>
        </form>';
    }

    // Funkcja 'PrzypomnijHaslo' jest odpowiedzialna za wyświetlenie formularza do wprowadzenia emaila.
    function PrzypomnijHaslo($odbiorca) {
        // Sprawdzenie, czy pole emaila do odzyskiwania hasła jest puste
        if (empty($_POST['email_recov'])) {
            echo $this->PokazKontaktHaslo(); // Wyświetl pole emaila do wypełnienia
        } else {
            // Przygotowanie danych do wysyłki e-mail z hasłem
            $mail['sender'] = $_POST['email_recov']; // Adres e-mail nadawcy
            $mail['subject'] = "Odzyskanie hasła"; // Temat wiadomości
            $mail['body'] = "Twoje hasło to: test"; // Treść wiadomości (przykład)
            $mail['recipient'] = $odbiorca; // Odbiorca wiadomości

            // Nagłówki e-mail
            $header = "From: Formularz odzyskiwania hasła <" . $mail['sender'] . ">\n";
            $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\n";

            // Wysłanie e-maila i sprawdzenie, czy operacja się powiodła
            if (mail($mail['recipient'], $mail['subject'], $mail['body'], $header)) {
                echo '<div class="alert">Hasło zostało wysłane na podany adres e-mail!</div>'; // Potwierdzenie wysłania hasła
            } else {
                echo '<div class="alert">Wystąpił błąd podczas wysyłania hasła.</div>'; // Błąd podczas wysyłania
            }
        }
    }

    // Funkcja 'PokazKontaktHaslo' wyświetla formularz do wprowadzenia emaila.
    function PokazKontaktHaslo() {
        return '
        <div class="form_passrecov">
            <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                <table class="form_passrecov">
                    <tr>
                        <td>Email:</td>
                        <td><input type="text" name="email_recov" required style="width: 100%;" /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="Wyślij" class="submit-button" /></td> <!-- Przycisk do wysyłania -->
                    </tr>
                </table>
            </form>
            <div class="buttons2">
                 <a class="contact-button" href="?idp=kontakt">Kontakt</a> <!-- Link do formularza kontaktowego -->
            </div>
        </div>';
    }
}

?>