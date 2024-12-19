let computed = false;
let decimal = 0;

function decToHex(x) {
    x = x.toString(16);
    return (x.length == 1) ? '0' + x : x;
}

function hexToDec(x) {
    return parseInt(x, 16);
}

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

function changeBackground(hexNumber) {
    const container = document.querySelector('.container');
    if (container) {
        container.style.backgroundColor = hexNumber;
    }
}

function changeBackgroundRandom() {
    const r = Math.floor(Math.random() * 256);
    const g = Math.floor(Math.random() * 256);
    const b = Math.floor(Math.random() * 256);
    const hexColor = '#' + decToHex(r) + decToHex(g) + decToHex(b);
    changeBackground(hexColor);
}

// Reset decimal to 0
decimal = 0;
