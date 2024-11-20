<?php
  // Start sesji
  session_start();

  $nr_indeksu = '169397';  
  $nrGrupy = '4';  
  echo 'Konrad Zieliński '.$nr_indeksu.' grupa '.$nrGrupy.'<br /><br />';

  // a) Zastosowanie include()
  echo 'Zastosowanie metody include() <br />';
  include('include.php');
  

  // Użycie require_once()
  echo '<br /> Użycie metody require_once <br />';
  require_once('require_once.php');

  // b) Warunki if, else, elseif
  $wartość = 7;
  echo "Wartość wynosi: $wartość <br />";
  if ($wartość < 4) {
      echo "Wartość jest mniejsza niż 4.<br />";
  } elseif ($wartość > 9) {
      echo "Wartość jest większa niż 9.<br />";
  } else {
      echo "Wartość jest pomiędzy 4 a 9.<br />";
  }

  // Przykład switch
  $kolor = "fioletowy";
  echo "<br />Kolor: ";
  switch ($kolor) {
      case "czarny":
          echo "Kolor to czarny.<br />";
          break;
      case "żółty":
          echo "Kolor to żółty.<br />";
          break;
      case "fioletowy":
          echo "Kolor to fioletowy.<br />";
          break;
      default:
          echo "Kolor nie jest rozpoznany.<br />";
          break;
  }

  // c) Pętle while() i for()
  echo "<br />Przykład pętli while:<br />";
  $i = 10;
  while ($i > 0) {
      echo "Wartość i: $i<br />";
      $i--;
  }

  echo "<br />Przykład pętli for:<br />";
  for ($j = 0; $j < 5; $j++) {
      echo "Wartość j: $j<br />";
  }

  // d) Testowanie $_GET, $_POST, $_SESSION

  // Przykład $_GET http://localhost/siema/labor_169397_ISI4.php?nazwa=Dzień_dobry
  echo "<br />Przykład zmiennej \$_GET:<br />";
  if (isset($_GET['nazwa'])) {
      $nazwa = $_GET['nazwa'];
      echo "Otrzymano z GET nazwę: $nazwa<br />";
  } else {
      echo "Brak wartości 'nazwa' w zapytaniu GET.<br />";
  }

  // Przykład do testowania $_POST
  echo "<br />Przykład zmiennej \$_POST:<br />";
  echo '<form action="labor_169397_ISI4.php" method="POST">
            <label for="slowo">Podaj slowo:</label>
            <input type="text" name="slowo" id="slowo">
            <input type="submit" value="Wyślij">
        </form>';
  
  $slowo = ' ';  
  
  // Sprawdź, czy klucz 'slowo' istnieje w tablicy $_POST
  if (isset($_POST['slowo']) && strlen($_POST['slowo']) > 0) {
      $slowo = $_POST['slowo'];
      echo "Otrzymano z POST slowo: $slowo<br />";
  } else {
      echo "Brak wartości 'slowo' w zapytaniu POST.<br />";
  }

  // Przykład $_SESSION
  echo "<br />Przykład zmiennej \$_SESSION:<br />";
  if (isset($_SESSION['uzytkownik'])) {
      $uzytkownik = $_SESSION['uzytkownik'];
      echo "Otrzymano użytkownika z sesji: $uzytkownik<br />";
  } else {
      $_SESSION['uzytkownik'] = "lab4";
      echo "wartość sesji ustawiona na: lab4<br />";
  }
?>
