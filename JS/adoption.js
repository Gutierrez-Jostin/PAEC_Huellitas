const GOOGLE_FORM_URL = "https://docs.google.com/forms/d/e/1FAIpQLSd4zJjvPRPuB5VTFBDr7r3JbspVq9RE0DlZ7P4rvsAO_dXwtg/viewform";
const ENTRY_DOG_NAME = "entry.275332554";
const STORAGE_KEY = "adoption_status";

function getStatus() {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || "{}");
}

function setStatus(dogId, adopted) {
    const status = getStatus();
    status[dogId] = adopted;
    localStorage.setItem(STORAGE_KEY, JSON.stringify(status));
    renderCard(dogId);
}
function renderCard(dogId) {
    const card = document.querySelector(`.tarjeta[data-dog-id="${dogId}"]`);
    if (!card) return;
    const adopted = !!getStatus()[dogId];
    card.classList.toggle("adoptado", adopted);
    card.querySelector(".badge-adoptado").style.display = adopted ? "inline-block" : "none";
    card.querySelector(".btn-toggle").textContent = adopted
        ? "Marcar como disponible"
        : "Marcar como adoptado";
}
function goToForm(dogId, dogName) {
    const url = `${GOOGLE_FORM_URL}?usp=pp_url&${ENTRY_DOG_NAME}=${encodeURIComponent(dogName)}`;
    sessionStorage.setItem("pending_dog", dogId);
    sessionStorage.setItem("pending_time", Date.now());
    window.open(url, "_blank");
}

function checkReturnFromForm() {
    const pendingDog = sessionStorage.getItem("pending_dog");
    const pendingTime = parseInt(sessionStorage.getItem("pending_time") || "0", 10);
    if (!pendingDog) return;
      const elapsed = Date.now() - pendingTime;
    if (elapsed > 15_000) {
        const dogName = document.querySelector(
            `.tarjeta[data-dog-id="${pendingDog}"] .tarjeta-info p`
        )?.textContent || "este perro";

        const yaAdoptado = confirm(
            `¿Ya enviaste la solicitud de adopción para ${pendingDog.toUpperCase()}?\n\n` +
            `Presiona Aceptar para marcarlo como ADOPTADO.`
        );
        if (yaAdoptado) {
            setStatus(pendingDog, true);
        }
        sessionStorage.removeItem("pending_dog");
         sessionStorage.removeItem("pending_time");
    }
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".tarjeta").forEach(card => {
        renderCard(card.dataset.dogId);
    });

    document.querySelectorAll(".btn-adopcion").forEach(btn => {
        btn.addEventListener("click", () => {
            const card = btn.closest(".tarjeta");
            goToForm(card.dataset.dogId, card.dataset.dogName);
            });
    });

      document.querySelectorAll(".btn-toggle").forEach(btn => {
        btn.addEventListener("click", () => {
            const dogId = btn.dataset.dog;
            const current = !!getStatus()[dogId];
            setStatus(dogId, !current);
        });
    });

        checkReturnFromForm();
    window.addEventListener("focus", checkReturnFromForm);

    });



