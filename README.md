# Futbol 7 - Lista de Jugadores

Formulario para organizar partidos de futbol 7: hasta 16 jugadores se apuntan desde una
pagina web, cada uno puede borrar solo su propia inscripcion, y los administradores
pueden editar la fecha/hora del partido o borrar a cualquiera. La lista se actualiza
sola cada 5 segundos para que todos vean los mismos cambios casi en tiempo real.

Hay dos implementaciones equivalentes en este repositorio; usa la que mejor te encaje.

## Opcion recomendada: Google Apps Script (`google-apps-script/`)

No necesita hosting, FTP ni instalar nada: se despliega desde una Google Sheet en unos
minutos y da una URL publica al momento.

1. Ve a [sheets.google.com](https://sheets.google.com) y crea una hoja de calculo nueva
   (puedes llamarla "Futbol 7").
2. Menu **Extensiones > Apps Script**.
3. Borra el contenido de `Code.gs` que aparece por defecto y pega ahi el contenido de
   [`google-apps-script/Code.gs`](google-apps-script/Code.gs).
4. En el panel izquierdo, pulsa **+ > HTML**, ponle de nombre exactamente `Index`, borra
   el contenido por defecto y pega ahi el contenido de
   [`google-apps-script/Index.html`](google-apps-script/Index.html).
5. Pulsa el icono de engranaje **Configuracion del proyecto**, baja hasta
   **Propiedades del script**, y anade una propiedad:
   - Propiedad: `ADMIN_PASSWORD`
   - Valor: la contrasena que quieras usar como administrador (para editar la fecha o
     borrar a cualquier jugador).
6. Pulsa **Implementar > Nueva implementacion**. Tipo: **Aplicacion web**. En
   "Ejecutar como" deja **Yo (tu cuenta)**; en "Quien tiene acceso" elige **Cualquier
   usuario**. Pulsa **Implementar** y autoriza los permisos (es tu propio script, solo
   hace falta la primera vez).
7. Copia la URL de la aplicacion web que te da: esa es la pagina publica para compartir
   con los jugadores.

Para volver a publicar cambios de codigo, usa siempre **Implementar > Gestionar
implementaciones > editar (icono lapiz) > Nueva version**.

## Opcion alternativa: plugin de WordPress (`futbol7-lista/`)

Si prefieres tenerlo dentro de un sitio WordPress ya existente:

1. Descarga o clona este repositorio.
2. Comprime la carpeta `futbol7-lista/` en un `.zip` (o genera uno limpio con
   `git archive --format=zip --output=futbol7-lista.zip --prefix=futbol7-lista/ HEAD:futbol7-lista`
   para evitar problemas de carpetas duplicadas al subirlo).
3. En tu WordPress: **Plugins > Anadir nuevo > Subir plugin**, selecciona el `.zip` sin
   abrirlo antes, y pulsa **Instalar ahora**.
4. Activa el plugin.
5. Crea o edita una pagina y anade el shortcode `[futbol7_lista]`.
6. Publica la pagina. Cualquier visitante vera el formulario; si inicia sesion como
   administrador del sitio (rol con la capacidad `manage_options`) vera ademas el panel
   para cambiar fecha, hora y notas del partido.

GitHub Pages (carpeta `/docs`) solo aloja una pagina informativa del proyecto: no puede
guardar ni compartir datos entre visitantes, por eso ninguna de las dos opciones vive
ahi directamente.

## Como funciona el borrado "solo lo tuyo" (ambas versiones)

Al apuntarse, el navegador del jugador guarda un token unico en `localStorage`. Ese
token es lo unico que permite borrar esa inscripcion concreta; sin el, el boton de
borrar no aparece para nadie mas que el propio jugador (o un administrador). Si el
jugador borra el historial del navegador o usa otro dispositivo, pierde la capacidad de
autoborrarse, pero un administrador siempre puede quitarlo.

## Estructura del repositorio

```
google-apps-script/     Version Google Sheets + Apps Script (recomendada)
  Code.gs                 Backend: base de datos (la hoja) y logica de permisos
  Index.html               Pagina publica (formulario + lista)
futbol7-lista/           Plugin de WordPress (alternativa)
  futbol7-lista.php        Archivo principal del plugin
  includes/                 Clases PHP (base de datos, API REST, shortcode)
  assets/                    CSS y JS del formulario
docs/                    Pagina estatica publicada con GitHub Pages
```
