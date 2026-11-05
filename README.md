# Documentación General del Sitio Web: HEALING LITTLE PETS (PAEC_Huellitas)

### integrantes 

NESTOR VIDAL SANTOS ITURBE

SOFIA ELENA CARRAZCO PEREZ

JOSTIN ERNESTO GUTIERREZ MAGAÑA

CESAR ARTURO VALDES MORALES

HERNANDEZ RODRIGUEZ GABRIEL ANTHONI 

## 1. Introducción
El proyecto web HEALING LITTLE PETS (también identificado dentro del árbol del proyecto como PAEC Huellitas) es una plataforma orientada al rescate, cuidado, adopción y soporte veterinario para animales en situación de calle, abandono o maltrato.

## Propósito y Misión
Protección Animal: Rescatar y resguardar animales en condiciones vulnerables. En la vista principal se destaca haber rescatado a 50 animales a la fecha.
Plataforma de Adopción: Facilitar la vinculación entre los animales rescatados y nuevos tutores responsables.

Educación y Cuidado: Proporcionar guías e información clave para la nutrición, peso saludable y bienestar integral de las mascotas.

Red de Atención Veterinaria: Ofrecer información sobre cuidados médicos,  administración de medicamentos y una lista de clínicas veterinarias de confianza.
Secciones Principales de Navegación

Info (index.html): Presentación general del refugio y los objetivos de la plataforma.

Adoptions (nav/Adoptions.html): Catálogo visual de animales disponibles para adopción con detalles físicos y botón de solicitud.

Animal Care (nav/AnimalCare.html): Guías informativas sobre salud, alimentación por raza y cuidados veterinarios.

Veterinary Care (nav/VeterinaryCare.html): Indicaciones sobre la administración de medicamentos, diagnóstico y seguridad.

Feedback (nav/Reviews.html): Sección destinada a opiniones y retroalimentación de los usuarios.

Contacts (nav/Contact.html): Ubicación del refugio y directorio de clínicas veterinarias asociadas.

Acceso de Usuarios (Log In / Sign Up): Gestión de inicio de sesión y registro de cuentas de usuario.

2. Apartado Técnico: Funcionamiento del Sitio Web
Arquitectura de Frontend

HTML5 Semantic Structure: El sitio utiliza etiquetas semánticas (<header>, <nav>, <div>, etc.) para estructurar la barra de navegación superior, los encabezados y las zonas de contenido principal.

Diseño Modular de Estilos (CSS): La plataforma utiliza hojas de estilo dedicadas para cada vista, garantizando modularidad en la presentación visual.
Flujo de Navegación y Usabilidad

Header / Navigation Bar: Barra superior global con el logotipo de la organización a la izquierda, botones de sesión (Log In, Sign Up) y el menú de navegación principal (Info, Adoptions, Animal Care, Veterinary Care, Feedback, Contacts).

Enrutamiento Interno: La página principal se ubica en la raíz (index.html), mientras que las subsiguientes vistas están organizadas dentro del directorio nav/.

Tarjetas y Contenedores: Uso de interfaces basadas en tarjetas (cards) para exhibir los perfiles de los animales en adopción y los artículos de cuidados.


## Despliegue y Dependencias
Vercel Integration: Presencia de archivos .vercelignore y verceljson, indicando que el proyecto está preparado para su despliegue continuo mediante Vercel.
Gestión de Paquetes PHP / Backend (Composer): Incluye los archivos composer.json y composer.lock, sugiriendo el uso de librerías en PHP o microservicios complementarios integrados mediante la carpeta api/.

3. Apartados del Código (Estructura de Archivos del Proyecto)
La estructura del repositorio PAEC_Huellitas se distribuye de la siguiente manera:

## Análisis del Encabezado (index.html)
El código fuente expuesto muestra una estructura limpia para el encabezado:
Metadatos e Inclusión de CSS: Declaración de viewport para diseño adaptativo y vinculación a CSS/main_style.css
Estructura del Header:
Un contenedor .logo-bar que incluye el logotipo (files/icons/logo.png) y el título HEALING LITTLE PETS.
Un contenedor .content-right con el bloque de registro/inicio de sesión (.user-bar) y el menú de navegación general (.nav-bar).
4. Vista de Interfaz y Administración
1. Vista de Inicio / Información (Info)
Banner y Mensaje Principal: Muestra un encabezado "Welcome to HEALING LITTLE PETS"
Bloques Informativos:
About us...: Explica la misión de proteger animales abandonados y menciona el logro de haber rescatado 50 mascotas.
Objective of our website: Describe el funcionamiento interactivo de la plataforma y su meta de dar mayor visibilidad a la problemática del abandono animal.
Galería Visual: Fotos del refugio y perros al aire libre.
2. Sección de Adopción (Adoptions)
Catálogo en Grid: Muestra tarjetas informativas de los perros listos para adopción.
Ejemplos de Fichas:
Pugberto: Raza Pug, 6 meses, peso 3 kg, frecuencia de alimentación.
Luis: Raza Akita Inu, 6 años, peso 34 kg, frecuencia de alimentación.
Brian: Raza Pitbull France, 6 meses, peso 6 kg, frecuencia de alimentación.
Acciones: Cada ficha dispone de un botón de acción "APPLY FOR ADOPTION".
3. Cuidado Animal (Animal Care)
Organizado mediante una retícula de tarjetas que abordan la salud del animal:
Healthy Weight: Hábitos de alimentación y ejercicio.
Breed-Specific Needs: Ajuste de dietas según predisposición genética.
Specific Nutrition: Nutrición especializada por etapa de vida (cachorro, adulto, senior).
The Importance of the Veterinarian: Enfoque preventivo y diagnóstico profesional.
4. Atención Veterinaria (Veterinary Care)
Presenta artículos y guías detalladas para el tratamiento de mascotas:
Correct Administration of Medication: Vías de administración (oral, tópica, inyectable), dosificación y regularidad horaria.
Monitoring and Side Effect Management: Observación contínua de reacciones alérgicas o malestares (letargo, vómitos).
Safety and Storage: Advertencias contra la automedicación (evitar medicamentos humanos como paracetamol o ibuprofeno) y correcto almacenamiento.
5. Contactos y Ubicación (Contacts)
Ubicación del Refugio: Imagen y dirección de las instalaciones del refugio de animales
Clínicas Veterinarias de Confianza (Tijuana, B.C.):
Veterinaria D' Sol: Blvr. Simón Bolívar 92-LOC 2, Los Venados, Tijuana, B.C. (Tel/WhatsApp: +52 664 875 7533).
Hospital de Mascotas SAJI: Blvr. Vía Rápida Oriente 6876, Río Tijuana 3a. Etapa, Tijuana, B.C. (Tel/WhatsApp: +52 664 866 6457).
EMEVEET: Blvr. Blvd. Alberto Limón Padilla 9000, Plaza Mercado 45, Col. Mesa de Otay, Tijuana, B.C. (Tel/WhatsApp: +52 664 356 3990).
6. Formularios de Autenticación (Log In / Sign Up)
Inicio de Sesión (Sign in): Interfaz modal/pantalla centralizada sobre fondo del refugio con campos para User or Mail y Password, junto con el botón de envío y opción a registro.
Registro de Cuenta (Make you account): Formulario con inputs para Full Name, Email Address, Choose a Password y Confirm Password. Pie de página con derechos reservados © Los Quinesteticos (2026 Edition).

Análisis Detallado del Backend (Servidor y Lógica)
El backend del proyecto está estructurado con tecnologías de servidor en PHP, respaldado por la carpeta api/ y la gestión de paquetes a través de Composer:
1. Gestión de Dependencias (composer.json y vendor/)
El archivo composer.json define las librerías necesarias para el funcionamiento del backend (como controladores de base de datos, gestores de variables de entorno o librerías de autenticación JWT/sesiones).
La carpeta vendor/ almacena el código de terceros descargado y el autoloader (vendor/autoload.php), permitiendo requerir clases backend de manera eficiente.
2. Servicios del Directorio api/
El directorio api/ procesa las peticiones enviadas desde las vistas HTML y los scripts de JavaScript mediante peticiones HTTP (GET/POST/PUT/DELETE):
Autenticación y Registro: Procesa las credenciales enviadas desde Login.html y Register.html. Valida que las contraseñas coincidan, hashea la contraseña para su almacenamiento seguro y genera una sesión activa o un token de usuario.
Procesamiento de Adopciones: Recibe la solicitud efectuada al presionar APPLY FOR ADOPTION en Adoptions.html, asociando el ID de la mascota (ej. Pugberto) con el ID del usuario autenticado.
Gestión de Reseñas (Reviews): Recibe las opiniones escritas por los usuarios, las almacena en la base de datos y retorna el listado de comentarios para desplegarlos dinámicamente en la interfaz.
3. Configuración de Despliegue Backend (vercel.json)
El archivo vercel.json gestiona las reglas de enrutamiento (rewrites/routes), permitiendo que las llamadas dirigidas a /api/... sean atendidas por los scripts backend en el entorno serverless de Vercel.
Análisis del Frontend (Interfaz y Estilos)
Estructura HTML5 Semántica: En index.html se aprecia la división limpia mediante etiquetas de estructura (<header>, <nav>, <div>).
Navegación Visual Dinámica: Las clases CSS .button-on y .button-off controlan el resaltado de la pestaña actual en el menú.
Asignación de Estilos Modularizada: Cada vista cuenta con su propio archivo .css dentro del directorio CSS/, optimizando el tiempo de carga y manteniendo aislado el diseño de cada pantalla.


