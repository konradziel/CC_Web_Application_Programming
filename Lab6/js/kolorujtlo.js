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

const changeBackground = (hexNumber) => {
    document.querySelector('.container').style.backgroundColor = hexNumber;
};
