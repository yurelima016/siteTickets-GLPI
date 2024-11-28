function popupAlert(message) {
    new jBox('Notice', {
        content: message,
        color: 'white',
        showCountdown: 'true',
    });
}

function ticketAlert(message) {
    Swal.fire({
        title: "Sucesso!",
        text: message,
        icon: "success"
    });
}

function toErrorPage() {
    window.location.href = '../public/error.php';
}
function toMainPage() {
    window.location.href = '../public/index.php';
}
function toLoginPage() {
    window.location.href = '../public/login.php';
}

const urgencia = document.getElementById('urgencia');
const urgenciaOutput = document.getElementById('urgencia-output');
const impacto = document.getElementById('impacto');
const impactoOutput = document.getElementById('impacto-output');

const rangeLabels = ["Muito Baixa", "Baixa", "Média", "Alta", "Muito Alta"];
const rangeColors = ["#008000", "#32CD32", "#ADFF2F", "#FFD700", "#DC143C"]; // Cores para cada step

const updateRangeStyle = (input, output, rangeColors) => {
    const value = parseInt(input.value, 10) - 1; // Valor base zero
    output.textContent = rangeLabels[value];

    const rangeWidth = input.offsetWidth;
    const thumbWidth = 20;
    const min = parseInt(input.min);
    const max = parseInt(input.max);
    const val = parseInt(input.value);

    const percent = (val - min) / (max - min);
    const offset = percent * (rangeWidth - thumbWidth);

    output.style.left = `${offset + thumbWidth / 2}px`;

    input.style.setProperty("--thumb-color", rangeColors[value]);
    output.style.backgroundColor = rangeColors[value];
};

urgencia.addEventListener('input', () => updateRangeStyle(urgencia, urgenciaOutput, rangeColors));
impacto.addEventListener('input', () => updateRangeStyle(impacto, impactoOutput, rangeColors));