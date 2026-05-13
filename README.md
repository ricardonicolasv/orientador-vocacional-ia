# 🎓 Orientador Vocacional IA

Aplicación web desarrollada en **PHP + Laravel**, orientada a estudiantes de enseñanza media del **Instituto San José**.

El sistema permite que los estudiantes interactúen con un chat vocacional, seleccionen rutas de orientación y generen reportes individuales para ser revisados por un orientador.

Actualmente el proyecto funciona como un **MVP** con lógica local de respuestas vocacionales. La integración con una IA real mediante API queda preparada como mejora futura.

---

## 📌 Estado actual del proyecto

### Funcionalidades implementadas

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

## 🧰 Tecnologías utilizadas

| Tecnología | Uso |
|---|---|
| **PHP 8.2.12** | Lenguaje principal del backend |
| **Laravel 12.x** | Framework principal |
| **MySQL / MariaDB** | Base de datos relacional |
| **XAMPP** | Entorno local de desarrollo |
| **Composer** | Gestor de dependencias PHP |
| **Node.js + NPM** | Dependencias frontend y compilación |
| **Blade** | Motor de plantillas |
| **Tailwind CSS** | Diseño visual responsive |
| **Laravel Breeze** | Autenticación |
| **DomPDF** | Generación de reportes PDF |
| **Git / GitHub** | Control de versiones |

---

## 🎯 Objetivo del sistema

El objetivo principal es crear una plataforma de orientación vocacional asistida por IA para estudiantes de enseñanza media, especialmente de **3° y 4° medio**.

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

> Esta herramienta no reemplaza el trabajo del orientador. Su función es apoyar el proceso de orientación, ordenar intereses, identificar dudas y entregar información inicial al estudiante.

---

## 🧭 Rutas vocacionales consideradas

### 1. Ruta universitaria

Considera orientación sobre:

- PAES.
- DEMRE.
- Acceso Mineduc.
- NEM, Ranking y ponderaciones.
- Carreras universitarias.
- Acreditación.
- Campo laboral y empleabilidad.

### 2. Ruta técnico-profesional

Considera orientación sobre:

- Institutos Profesionales.
- Centros de Formación Técnica.
- Carreras técnicas.
- Continuidad de estudios.
- Empleabilidad.
- Beneficios estudiantiles.

### 3. Beneficios y financiamiento

Considera orientación sobre:

- FUAS.
- Gratuidad.
- Becas.
- Créditos.
- Requisitos generales.
- Fechas importantes.

### 4. Pedagogías

Considera orientación sobre:

- Vocación docente.
- Requisitos para estudiar pedagogía.
- Acreditación.
- Alternativas en educación.

### 5. Fuerzas Armadas, de Orden y Seguridad Pública

Considera orientación sobre:

- Escuela Militar.
- Armada de Chile.
- FACh.
- Carabineros.
- PDI.
- Gendarmería.

### 6. No sé aún / Ayúdame a explorar

Considera orientación sobre:

- Exploración general de intereses.
- Preguntas orientadoras.
- Detección inicial de áreas vocacionales.
- Comparación de posibles caminos formativos.

---

## 🧩 Módulos principales

### 👨‍🎓 1. Registro del estudiante

El estudiante ingresa:

- Nombre.
- Curso.
- Colegio.
- Ruta vocacional seleccionada.
- Aceptación de consentimiento simple.

El sistema no solicita datos sensibles como RUT, dirección exacta, información médica o datos familiares delicados.

---

### 💬 2. Chat vocacional

El estudiante puede conversar con el asistente vocacional.

Actualmente el chat usa un servicio local:

```txt
app/Services/AiVocationalService.php

Este servicio:

Normaliza el texto ingresado.
Detecta palabras clave.
Identifica áreas de interés.
Considera la ruta seleccionada.
Sugiere carreras o caminos posibles.
Realiza preguntas de seguimiento.
Entrega orientación inicial.

Ejemplo de áreas detectadas:

Tecnología, computación e informática.
Matemáticas, física e ingeniería.
Biología, salud y ciencias.
Área social, psicología y trabajo con personas.
Educación y pedagogía.
Fuerzas Armadas, Orden y Seguridad Pública.
Beneficios estudiantiles y financiamiento.
🧑‍🏫 3. Panel del orientador

El orientador accede mediante login privado.

Desde el panel puede:

Ver estudiantes registrados.
Buscar estudiantes.
Revisar curso y colegio.
Ver conversaciones asociadas.
Consultar el detalle del chat.
Generar reportes vocacionales.
Descargar reportes en PDF.
📄 4. Reporte vocacional individual

El sistema genera un reporte a partir de la conversación del estudiante.

El reporte incluye:

Nombre del estudiante.
Curso.
Colegio.
Ruta explorada.
Intereses mencionados.
Áreas detectadas.
Dudas principales.
Nivel de claridad vocacional.
Recomendaciones sugeridas.
Resumen para el estudiante.
Sección técnica para el orientador.
Niveles de claridad vocacional
Nivel	Descripción
Bajo	El estudiante aún no identifica áreas o alternativas claras.
Medio	Tiene intereses generales, pero necesita comparar opciones.
Alto	Tiene alternativas concretas y necesita información específica.
🧾 5. Exportación PDF

Los reportes pueden descargarse como PDF mediante DomPDF.

Vista PDF:

resources/views/reports/pdf.blade.php

Controlador asociado:

app/Http/Controllers/ReportController.php
🗂️ Estructura principal del proyecto
app/
├── Http/
│   └── Controllers/
│       ├── StudentController.php
│       ├── ChatController.php
│       ├── ReportController.php
│       └── OrientadorDashboardController.php
│
├── Models/
│   ├── Student.php
│   ├── Conversation.php
│   ├── Message.php
│   ├── VocationalReport.php
│   └── VocationalRoute.php
│
└── Services/
    ├── AiVocationalService.php
    └── ReportGeneratorService.php

resources/
└── views/
    ├── welcome.blade.php
    ├── student/
    │   └── create.blade.php
    ├── chat/
    │   └── show.blade.php
    ├── orientador/
    │   ├── dashboard.blade.php
    │   └── student-show.blade.php
    └── reports/
        ├── show.blade.php
        └── pdf.blade.php

database/
└── migrations/
    ├── create_students_table.php
    ├── create_conversations_table.php
    ├── create_messages_table.php
    ├── create_vocational_reports_table.php
    └── create_vocational_routes_table.php
🗄️ Modelo de datos principal
students

Guarda los datos básicos del estudiante.

Campo	Descripción
id	Identificador del estudiante
name	Nombre del estudiante
course	Curso
school	Colegio
consent_accepted	Aceptación de consentimiento
timestamps	Fechas de creación y actualización
conversations

Guarda las conversaciones iniciadas por cada estudiante.

Campo	Descripción
id	Identificador de la conversación
student_id	Relación con estudiante
selected_route	Ruta vocacional seleccionada
status	Estado de la conversación
started_at	Fecha de inicio
finished_at	Fecha de término
timestamps	Fechas de creación y actualización
messages

Guarda los mensajes del estudiante y del asistente.

Campo	Descripción
id	Identificador del mensaje
conversation_id	Relación con conversación
sender	Emisor del mensaje
content	Contenido del mensaje
timestamps	Fechas de creación y actualización

Valores posibles para sender:

student
ai
vocational_reports

Guarda el reporte vocacional generado desde una conversación.

Campo	Descripción
id	Identificador del reporte
student_id	Relación con estudiante
conversation_id	Relación con conversación
interests	Intereses mencionados
detected_areas	Áreas detectadas
explored_routes	Ruta explorada
main_questions	Dudas principales
clarity_level	Nivel de claridad vocacional
recommendations	Recomendaciones sugeridas
student_summary	Resumen para estudiante
orientador_notes	Notas técnicas para orientador
timestamps	Fechas de creación y actualización
vocational_routes

Tabla preparada para administrar rutas vocacionales.

Campo	Descripción
id	Identificador de la ruta
name	Nombre de la ruta
slug	Identificador amigable
description	Descripción
timestamps	Fechas de creación y actualización
⚙️ Instalación del proyecto en local
1. Clonar el repositorio
git clone https://github.com/ricardonicolasv/orientador-vocacional-ia.git
cd orientador-vocacional-ia
2. Instalar dependencias PHP
composer install
3. Instalar dependencias frontend
npm install
4. Crear archivo .env

En Windows:

copy .env.example .env

En Linux/Mac:

cp .env.example .env
5. Generar clave de aplicación
php artisan key:generate
6. Configurar base de datos

Crear una base de datos en MySQL/phpMyAdmin:

CREATE DATABASE orientador_vocacional_ia;

Configurar el archivo .env:

APP_NAME="Orientador Vocacional IA"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=orientador_vocacional_ia
DB_USERNAME=root
DB_PASSWORD=
7. Ejecutar migraciones
php artisan migrate

Si se necesita reiniciar completamente la base de datos:

php artisan migrate:fresh
8. Levantar servidor Laravel
php artisan serve

URL local:

http://127.0.0.1:8000
9. Compilar assets con Vite

En otra terminal:

npm run dev

Si PowerShell bloquea NPM, usar:

npm.cmd run dev
🧪 Comandos útiles
Acción	Comando
Limpiar caché general	php artisan optimize:clear
Limpiar rutas	php artisan route:clear
Ver rutas registradas	php artisan route:list
Ejecutar migraciones	php artisan migrate
Reiniciar base de datos	php artisan migrate:fresh
Ejecutar servidor local	php artisan serve
Compilar frontend	npm run dev
Compilar frontend en PowerShell	npm.cmd run dev
🌐 Rutas principales del sistema
Método	Ruta	Descripción
GET	/	Pantalla de inicio
GET	/estudiante/inicio	Formulario de registro del estudiante
POST	/estudiante	Guarda estudiante y crea conversación
GET	/chat/{conversation}	Muestra chat vocacional
POST	/chat/{conversation}/mensaje	Guarda mensaje y genera respuesta
GET	/login	Login del orientador
GET	/orientador/dashboard	Dashboard del orientador
GET	/orientador/estudiantes/{student}	Detalle del estudiante
POST	/orientador/conversaciones/{conversation}/generar-reporte	Genera reporte
GET	/orientador/reportes/{report}	Muestra reporte
GET	/orientador/reportes/{report}/pdf	Descarga reporte PDF
🔐 Seguridad y privacidad

El sistema considera las siguientes medidas básicas:

No se sube el archivo .env al repositorio.
Las credenciales se manejan mediante variables de entorno.
El panel del orientador está protegido por autenticación.
No se solicita RUT ni datos sensibles innecesarios.
Los reportes son de carácter orientativo.
La información debe utilizarse solo con fines de orientación vocacional escolar.

Archivos que no deben subirse a GitHub:

.env
/vendor
/node_modules
/public/build
🔁 Uso de Git y GitHub
Inicializar repositorio
git init
Agregar archivos
git add .
Crear commit
git commit -m "Primer commit - MVP Orientador Vocacional IA"
Configurar rama principal
git branch -M main
Conectar con GitHub
git remote add origin https://github.com/ricardonicolasv/orientador-vocacional-ia.git
Subir cambios
git push -u origin main
Guardar cambios futuros
git add .
git commit -m "Descripción del cambio"
git push
🔄 Flujo actual del estudiante
Inicio
→ Registro del estudiante
→ Selección de ruta vocacional
→ Inicio de conversación
→ Chat vocacional
→ Guardado de mensajes
🔄 Flujo actual del orientador
Login
→ Dashboard
→ Lista de estudiantes
→ Detalle del estudiante
→ Revisión de conversación
→ Generación de reporte
→ Visualización de reporte
→ Descarga PDF
🚧 Próximas mejoras planificadas
Estadísticas avanzadas en el dashboard.
Filtros por curso, ruta y fecha.
Mejoras visuales en el chat.
Integración con una IA real mediante API.
Modo configurable AI_MODE=local / AI_MODE=openai.
Conexión con fuentes oficiales.
Base documental actualizable.
Exportación PDF con diseño institucional mejorado.
Gestión de usuarios por roles.
Panel administrativo.
Reportes generales por curso.
Gráficos de rutas más consultadas.
Detección más avanzada de intereses vocacionales.
📚 Fuentes oficiales consideradas para futuras etapas

La aplicación está pensada para consultar o considerar información oficial de:

DEMRE.
Acceso Educación Superior - Mineduc.
Mi Futuro - Mineduc.
Comisión Nacional de Acreditación, CNA Chile.
FUAS / Beneficios Estudiantiles.
ChileAtiende.
Elige Educar.
Quiero Ser Profe.
Escuela Militar.
Armada de Chile.
Escuela Naval Arturo Prat.
FACh.
Carabineros de Chile.
PDI.
Gendarmería de Chile.
⚠️ Consideraciones importantes

Este sistema entrega orientación informativa y preliminar.

No reemplaza la entrevista con el orientador del colegio ni la revisión directa de fuentes oficiales.

Las fechas, requisitos, beneficios, ponderaciones, procesos de admisión y condiciones institucionales pueden cambiar, por lo que siempre deben verificarse en las páginas oficiales correspondientes.

👨‍💻 Autor

Proyecto desarrollado por:

Ricardo Vidal
Ingeniería en Informática
GitHub: @ricardonicolasv

📄 Licencia

Proyecto desarrollado con fines académicos y de prototipado.

El framework Laravel es software open-source bajo licencia MIT.
