document.addEventListener("DOMContentLoaded", () => {
    cargarResenas();
});

async function cargarResenas() {
    const contenedor = document.getElementById("contenedor-resenas");
    
    try {
        const respuesta = await fetch('../api/reviews.php');
        
        if (!respuesta.ok) throw new Error('Server connection error');

        const resenas = await respuesta.json();
        contenedor.innerHTML = "";

        if (resenas.length === 0) {
            contenedor.innerHTML = "<p>No reviews yet.</p>";
            return;
        }

        resenas.forEach(resena => {
            let estrellasHTML = "";
            for (let i = 1; i <= 5; i++) {
                estrellasHTML += i <= (resena.estrellas || 5) ? "★" : "☆";
            }

            const article = document.createElement("article");
            article.classList.add("resena");
            article.innerHTML = `
                <div class="avatar">
                    <img src="../files/icons/user-icon.png" alt="User">
                </div>
                <div class="info">
                    <h3>${resena.usuario || "Anonymous"}</h3>
                    <p class="estrellas">${estrellasHTML}</p>
                    <p>${resena.comentario || "No comment"}</p>
                </div>
            `;
            contenedor.appendChild(article);
        });

    } catch (error) {
        console.error("Error:", error);
        contenedor.innerHTML = "<p>Error loading reviews.</p>";
    }
}

const formulario = document.getElementById("form-resena");
const inputUsuario = document.getElementById("input-usuario"); 
const token = localStorage.getItem('token');

if (!token && formulario) {
    formulario.innerHTML = "<p style='color: red; font-weight: bold;'>You must <a href='Login.html' style='color: blue;'>log in</a> to leave a review.</p>";
} else if (formulario && token) {
    if (inputUsuario) {
        inputUsuario.style.display = 'none';
        inputUsuario.removeAttribute('required'); 
    }

    formulario.addEventListener("submit", async (e) => {
        e.preventDefault(); 

        const estrellas = parseInt(document.getElementById("select-estrellas").value);
        const comentario = document.getElementById("input-comentario").value;
        const nuevaResena = { estrellas, comentario };

        try {
            const respuesta = await fetch('../api/save_reviews.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token 
                },
                body: JSON.stringify(nuevaResena)
            });

            if (respuesta.ok) {
                formulario.reset();
                cargarResenas();
            } else {
                const errorData = await respuesta.json();
                alert(errorData.error || "An error occurred while saving.");
            }
        } catch (error) {
            console.error("Error sending review:", error);
        }
    });
}