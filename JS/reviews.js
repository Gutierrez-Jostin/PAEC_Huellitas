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
                <div class="avatar">👤</div>
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