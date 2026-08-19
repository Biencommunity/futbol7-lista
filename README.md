# Futbol 7 - Lista de Jugadores

Plugin de WordPress para organizar partidos de futbol 7: hasta 16 jugadores se apuntan
desde una pagina web, cada uno puede borrar solo su propia inscripcion, y los
administradores del sitio pueden editar la fecha/hora del partido o borrar a cualquiera.
La lista se actualiza sola cada 5 segundos para que todos vean los mismos cambios casi
en tiempo real.

## Por que un plugin de WordPress (y no solo GitHub Pages)

GitHub Pages solo sirve archivos estaticos: no puede guardar ni compartir la lista de
jugadores entre distintas personas. Como el proyecto ya cuenta con un sitio WordPress,
este plugin usa esa base de datos y el sistema de roles de WordPress (administrador)
como backend. Este repositorio se usa para control de versiones del codigo; GitHub
Pages (carpeta `/docs`) solo aloja una pagina informativa del proyecto.

## Instalacion en WordPress

1. Descarga o clona este repositorio.
2. Comprime la carpeta `futbol7-lista/` en un `.zip`.
3. En tu WordPress: **Plugins > Anadir nuevo > Subir plugin**, selecciona el `.zip` y
   pulsa **Instalar ahora**.
4. Activa el plugin.
5. Crea o edita una pagina y anade el shortcode:

   ```
   [futbol7_lista]
   ```

6. Publica la pagina. Cualquier visitante vera el formulario; si inicia sesion como
   administrador del sitio (rol con la capacidad `manage_options`) vera ademas el panel
   para cambiar fecha, hora y notas del partido.

## Como funciona el borrado "solo lo tuyo"

Al apuntarse, el navegador del jugador guarda un token unico en `localStorage`. Ese
token es lo unico que permite borrar esa inscripcion concreta; sin el, el boton de
borrar no aparece para nadie mas que el propio jugador (o un administrador). Si el
jugador borra el historial del navegador o usa otro dispositivo, pierde la capacidad de
autoborrarse, pero un administrador siempre puede quitarlo.

## Estructura del repositorio

```
futbol7-lista/          Plugin de WordPress
  futbol7-lista.php      Archivo principal del plugin
  includes/               Clases PHP (base de datos, API REST, shortcode)
  assets/                 CSS y JS del formulario
docs/                    Pagina estatica publicada con GitHub Pages
```

## Desarrollo local

Requiere un entorno WordPress local (por ejemplo LocalWP, wp-env o XAMPP) con la
carpeta `futbol7-lista` enlazada dentro de `wp-content/plugins/`.
