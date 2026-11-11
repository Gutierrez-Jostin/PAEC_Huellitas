document.addEventListener("DOMContentLoaded", async () => {
    const rol = localStorage.getItem('rol');
    if (rol !== 'admin') return;

    const token = localStorage.getItem('token');

    const contenedorAdopciones = document.getElementById("contenedor-admin-mascotas");
    if (contenedorAdopciones) {
        const res = await fetch('/api/admin_mascotas.php', { headers: { 'Authorization': 'Bearer ' + token }});
        const mascotas = await res.json();
        
        let html = `<div class="admin-panel"><h2>Admin Panel: All Pets</h2><table class="tabla-admin"><tr><th>Name</th><th>Breed</th><th>Owner (ID)</th></tr>`;
        mascotas.forEach(m => { 
            html += `<tr><td>${m.nombre}</td><td>${m.raza}</td><td>${m.usuario_id || 'N/A'}</td></tr>`; 
        });
        html += `</table></div>`;
        contenedorAdopciones.innerHTML = html;
    }

    const contenedorUsuarios = document.getElementById("contenedor-admin-usuarios");
    if (contenedorUsuarios) {
        const res = await fetch('/api/admin_usuarios.php', { headers: { 'Authorization': 'Bearer ' + token }});
        const usuarios = await res.json();
        
        let html = `<div class="admin-panel"><h2>Admin Panel: All Accounts</h2><table class="tabla-admin"><tr><th>Name</th><th>Email</th><th>Role</th></tr>`;
        usuarios.forEach(u => { 
            html += `<tr><td>${u.nombre}</td><td>${u.email}</td><td>${u.rol || 'user'}</td></tr>`; 
        });
        html += `</table></div>`;
        contenedorUsuarios.innerHTML = html;
    }

    const contenedorCitas = document.getElementById("contenedor-admin-citas");
    if (contenedorCitas) {
        const res = await fetch('/api/admin_citas.php', { headers: { 'Authorization': 'Bearer ' + token }});
        const citas = await res.json();
        
        let html = `<div class="admin-panel"><h2>Admin Panel: All Appointments</h2><table class="tabla-admin"><tr><th>Pet</th><th>Species</th><th>Date</th><th>Problem</th></tr>`;
        citas.forEach(c => { 
            html += `<tr><td>${c.mascota}</td><td>${c.especie}</td><td>${c.fecha_registro}</td><td>${c.problema}</td></tr>`; 
        });
        html += `</table></div>`;
        contenedorCitas.innerHTML = html;
    }
});