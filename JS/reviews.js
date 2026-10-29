document.addEventListener("DOMContentLoaded", () => {
    cargarResenas();
});

async function cargarResenas() {
    const contenedor = document.getElementById("contenedor-resenas");
    
    try {
       
        const respuesta = await fetch('../api/reviews.php');
        
        if (!respuesta.ok) {
            throw new Error('Error al conectar con el servidor');
        }

        const resenas = await respuesta.json();
        
        contenedor.innerHTML = "";

        if (resenas.length === 0) {
            contenedor.innerHTML = "<p>No hay reseñas todavía.</p>";
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
                    <h3>${resena.usuario || "Anónimo"}</h3>
                    <p class="estrellas">${estrellasHTML}</p>
                    <p>${resena.comentario || "Sin comentario"}</p>
                </div>
            `;
            
            contenedor.appendChild(article);
        });

    } catch (error) {
        console.error("Error:", error);
        contenedor.innerHTML = "<p>Error al cargar las reseñas. Revisa la consola.</p>";
    }
}

const formulario = document.getElementById("form-resena");

if (formulario) {
    formulario.addEventListener("submit", async (e) => {
        e.preventDefault(); 

       
        const usuario = document.getElementById("input-usuario").value;
        const estrellas = parseInt(document.getElementById("select-estrellas").value);
        const comentario = document.getElementById("input-comentario").value;

        const nuevaResena = { usuario, estrellas, comentario };

        try {
            
            const respuesta = await fetch('../api/save_reviews.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nuevaResena)
            });

            if (respuesta.ok) {
           
                formulario.reset();
                
                
                cargarResenas();
            } else {
                alert("Hubo un error al guardar la reseña.");
            }
        } catch (error) {
            console.error("Error al enviar reseña:", error);
            alert("Error de conexión con el servidor.");
        }
    });
}