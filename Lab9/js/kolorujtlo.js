let computed = false;
let decimal = 0;

const convert = (entryform, from, to) => {
    const convertfrom = from.selectedIndex;
    const convertto = to.selectedIndex;
    const inputValue = parseFloat(entryform.input.value);
    const fromValue = parseFloat(from[convertfrom].value);
    const toValue = parseFloat(to[convertto].value);
    
    entryform.display.value = (inputValue / fromValue * toValue).toFixed(2);
};

const addChar = (input, character) => {
    if ((character === '.' && decimal === 0) || character !== '.') {
        input.value = input.value === "" || input.value === "0" ? character : input.value + character;
        convert(input.form, input.form.measure1, input.form.measure2);
        computed = true;

        if (character === '.') {
            decimal = 1;
        }
    }
};

const openVothcom = () => {
    window.open("", "Display window", "toolbar=no, directories=no, menubar=no");
};

const clearForm = (form) => {
    form.input.value = '0';
    form.display.value = '0';
    decimal = 0;
};

/**
 * Moduł zmiany koloru tła
 * Zawiera funkcje do manipulacji kolorem tła strony
 */

/**
 * Zmienia kolor tła elementu na wybrany
 * @param {string} color - Nazwa lub kod koloru do ustawienia
 */
function changeBackground(color) {
    document.body.style.backgroundColor = color;
}

/**
 * Generuje losowy kolor w formacie heksadecymalnym
 * @returns {string} Kolor w formacie #RRGGBB
 */
function getRandomColor() {
    // Generowanie losowych wartości dla składowych RGB
    const r = Math.floor(Math.random() * 256);
    const g = Math.floor(Math.random() * 256);
    const b = Math.floor(Math.random() * 256);
    
    // Konwersja na format heksadecymalny
    return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

/**
 * Zmienia kolor tła na losowy
 * Funkcja łączy generowanie losowego koloru ze zmianą tła
 */
function changeBackgroundRandom() {
    const randomColor = getRandomColor();
    changeBackground(randomColor);
}
