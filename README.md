# Orientador Vocacional IA

Aplicación web desarrollada en **PHP con Laravel**, orientada a estudiantes de enseñanza media del **Instituto San José**.  
El sistema permite que los estudiantes interactúen con un chat vocacional, seleccionen rutas de orientación y generen reportes individuales para ser revisados por un orientador.

Actualmente el proyecto funciona como un **MVP** con lógica local de respuestas vocacionales. La conexión con una IA real mediante API queda preparada como mejora futura.

---

## Estado actual del proyecto

Funcionalidades implementadas:

- Registro básico de estudiantes.
- Selección de ruta vocacional.
- Chat vocacional con respuestas locales inteligentes.
- Guardado de conversaciones en base de datos.
- Login de orientador mediante Laravel Breeze.
- Dashboard privado para el orientador.
- Visualización de estudiantes registrados.
- Visualización del historial de conversación.
- Generación de reporte vocacional individual.
- Vista de reporte en pantalla.
- Descarga de reporte vocacional en PDF.
- Interfaz responsive con Blade y Tailwind CSS.

---

## Tecnologías utilizadas

| Tecnología | Versión / Uso |
|---|---|
| PHP | 8.2.12 |
| Laravel | 12.x |
| MySQL / MariaDB | Base de datos relacional |
| XAMPP | Entorno local de desarrollo |
| Composer | Gestor de dependencias PHP |
| Node.js | Dependencias frontend |
| NPM | Compilación de assets |
| Blade | Motor de plantillas |
| Tailwind CSS | Diseño visual |
| Laravel Breeze | Autenticación |
| DomPDF | Generación de reportes PDF |
| Git / GitHub | Control de versiones |

---

## Objetivo del sistema

El objetivo principal es crear una plataforma de orientación vocacional asistida por IA para estudiantes de enseñanza media, especialmente de 3° y 4° medio.

La aplicación busca apoyar al estudiante en temas como:

- Qué carrera estudiar.
- Qué ruta formativa seguir.
- Diferencias entre universidad, instituto profesional y CFT.
- Opciones técnico-profesionales.
- Beneficios estudiantiles, becas, gratuidad y FUAS.
- Carreras pedagógicas.
- Opciones en Fuerzas Armadas, de Orden y Seguridad Pública.
- Comparación inicial de áreas de interés.
- Generación de reportes para seguimiento del orientador.

La herramienta no reemplaza el trabajo del orientador, sino que funciona como apoyo para ordenar intereses, dudas y posibles caminos vocacionales.

---

## Rutas vocacionales consideradas

El sistema permite orientar al estudiante según una de las siguientes rutas:

1. **Ruta universitaria**
   - PAES.
   - DEMRE.
   - Acceso Mineduc.
   - NEM, Ranking y ponderaciones.
   - Carreras universitarias.
   - Acreditación.
   - Campo laboral y empleabilidad.

2. **Ruta técnico-profesional**
   - Institutos Profesionales.
   - Centros de Formación Técnica.
   - Carreras técnicas.
   - Continuidad de estudios.
   - Empleabilidad.
   - Beneficios estudiantiles.

3. **Beneficios y financiamiento**
   - FUAS.
   - Gratuidad.
   - Becas.
   - Créditos.
   - Requisitos generales.
   - Fechas importantes.

4. **Pedagogías**
   - Vocación docente.
   - Requisitos para estudiar pedagogía.
   - Acreditación.
   - Alternativas en educación.

5. **Fuerzas Armadas, de Orden y Seguridad Pública**
   - Escuela Militar.
   - Armada de Chile.
   - FACh.
   - Carabineros.
   - PDI.
   - Gendarmería.

6. **No sé aún / Ayúdame a explorar**
   - Exploración general de intereses.
   - Preguntas orientadoras.
   - Detección inicial de áreas vocacionales.

---

## Módulos principales

### 1. Registro del estudiante

El estudiante ingresa:

- Nombre.
- Curso.
- Colegio.
- Ruta vocacional seleccionada.
- Aceptación de consentimiento simple.

El sistema no solicita datos sensibles como RUT, dirección exacta, información médica o datos familiares delicados.

---

### 2. Chat vocacional

El estudiante puede conversar con el asistente vocacional.

Actualmente el chat usa un servicio local:

```txt
app/Services/AiVocationalService.php
