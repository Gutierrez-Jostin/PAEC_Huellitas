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

let todosLosArticulos = []; 

async function cargarArticulos() {
    try {
        const respuesta = await fetch('../api/articles.php');
        if (!respuesta.ok) throw new Error('Server error');
        
        todosLosArticulos = await respuesta.json();
        mostrarArticulos(todosLosArticulos);
    } catch (error) {
        console.error("Error al cargar artículos:", error);
        document.getElementById("contenedor-articulos").innerHTML = "<p>Error loading products.</p>";
    }
}

function mostrarArticulos(articulos) {
    const contenedor = document.getElementById("contenedor-articulos");
    contenedor.innerHTML = ""; 

    if (articulos.length === 0) {
        contenedor.innerHTML = "<p>No products found.</p>";
        return;
    }

    articulos.forEach(articulo => {
        const div = document.createElement("div");
        div.innerHTML = `
            <div class="item">
                <img src="../files/articles/${articulo.imagen}" alt="${articulo.nombre}" class="article-img">
                <div>
                    <h2>${articulo.nombre}</h2>
                    <p><b>Category: </b>${articulo.categoria}</p>
                    <p><b>Form: </b>${articulo.forma}</p>
                    <p><b>Description: </b>${articulo.descripcion}</p>
                </div>
            </div>
        `;
        contenedor.appendChild(div);
    });
}

function filtrarArticulos() {
    const textoBuscado = document.getElementById("buscador").value.toLowerCase();
    const categoriaSeleccionada = document.getElementById("categoria").value;

    const filtrados = todosLosArticulos.filter(articulo => {
        const coincideNombre = articulo.nombre.toLowerCase().includes(textoBuscado);
        const coincideCategoria = categoriaSeleccionada === "all-categories" || articulo.categoria === categoriaSeleccionada;

        return coincideNombre && coincideCategoria;
    });

    mostrarArticulos(filtrados);
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("contenedor-articulos")) {
        if (todosLosArticulos.length === 0) {
            cargarArticulos();
        }

        document.getElementById("buscador").addEventListener("keyup", filtrarArticulos);
        document.getElementById("categoria").addEventListener("change", filtrarArticulos);
    }
});