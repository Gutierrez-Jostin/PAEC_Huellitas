
// Funcion para hacer que la seccion se muestre acorde a la seleccion del usuario, sin generar otro archivo .html
function show_content(idDestiny, clickedButton) {
    const sections = document.querySelectorAll('.section');
    const buttons = document.querySelectorAll('.button');

    sections.forEach(section => section.classList.remove('on'));
    buttons.forEach(button => button.classList.remove('show'));

    const sectionDestiny = document.getElementById(idDestiny);
    if (sectionDestiny) {
        sectionDestiny.classList.add('on');
    }

    if (clickedButton) {
        clickedButton.classList.add('show');
    }
}