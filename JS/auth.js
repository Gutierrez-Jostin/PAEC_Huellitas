document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    const nombre = localStorage.getItem('nombre');
    const userBar = document.querySelector('.user-bar');

    if (!userBar) return; 

    if (token && nombre) {
        userBar.innerHTML = `
            <div class="profile-area">
                <div class="profile-circle">
                    ${nombre.charAt(0).toUpperCase()}
                </div>
                <span class="profile-name">${nombre}</span>
                <button class="btn-logout" id="btn-logout">Log Out</button>
            </div>
        `;

        document.getElementById('btn-logout').addEventListener('click', () => {
            localStorage.removeItem('token');
            localStorage.removeItem('nombre');
            localStorage.removeItem('rol');
            window.location.reload(); 
        });
    }
});