/**
 * Moduł wyświetlania czasu i daty
 * Zawiera funkcje do aktualizacji i wyświetlania aktualnego czasu
 */

// Tablica z nazwami miesięcy
const months = [
    "stycznia", "lutego", "marca", "kwietnia", "maja", "czerwca",
    "lipca", "sierpnia", "września", "października", "listopada", "grudnia"
];

// Tablica z nazwami dni tygodnia
const days = [
    "niedziela", "poniedziałek", "wtorek", "środa",
    "czwartek", "piątek", "sobota"
];

/**
 * Formatuje liczbę do dwóch cyfr
 * @param {number} i - Liczba do sformatowania
 * @returns {string} Sformatowana liczba (np. '01' zamiast '1')
 */
function checkTime(i) {
    return (i < 10) ? "0" + i : i;
}

/**
 * Aktualizuje czas na stronie
 * Funkcja jest wywoływana cyklicznie co sekundę
 */
function startclock() {
    // Pobranie aktualnej daty
    const today = new Date();
    
    // Pobranie poszczególnych elementów czasu
    const day = days[today.getDay()];
    const date = today.getDate();
    const month = months[today.getMonth()];
    const year = today.getFullYear();
    const hour = checkTime(today.getHours());
    const minute = checkTime(today.getMinutes());
    const second = checkTime(today.getSeconds());
    
    // Utworzenie sformatowanego stringa z datą i czasem
    const dateString = `${day}, ${date} ${month} ${year}`;
    const timeString = `${hour}:${minute}:${second}`;
    
    // Aktualizacja elementów na stronie
    document.getElementById("data").innerHTML = dateString;
    document.getElementById("zegarek").innerHTML = timeString;
    
    // Wywołanie funkcji ponownie po 1 sekundzie
    setTimeout("startclock()", 1000);
}

function gettheDate() {
    var Toodays = new Date();
    var TheDate = "" + (Toodays.getMonth() + 1) + " / " + Toodays.getDate() + " / " + (Toodays.getFullYear() - 2000);
    document.getElementById("data").innerHTML = TheDate;
}

var timeID = null;
var timerRunning = false;

function stopclock() {
    if (timerRunning) {
        clearTimeout(timeID);
    }
    timerRunning = false;
}

function showtime() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();
    
    var timeValue = "" + ((hours > 12) ? hours - 12 : hours);
    timeValue += (minutes < 10 ? ":0" : ":") + minutes;
    timeValue += (seconds < 10 ? ":0" : ":") + seconds;
    timeValue += (hours >= 12) ? " P.M." : " A.M.";
    
    document.getElementById("zegarek").innerHTML = timeValue;
    
    timeID = setTimeout(showtime, 1000);
    timerRunning = true;
}
