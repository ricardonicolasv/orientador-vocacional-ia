# 🎓 Orientador Vocacional IA

Plataforma web de orientación vocacional asistida por inteligencia artificial, desarrollada para estudiantes de enseñanza media del **Instituto San José**.

La aplicación permite registrar estudiantes, iniciar y retomar conversaciones vocacionales, explorar distintas rutas formativas, consultar dudas sobre admisión y beneficios, y generar informes versionados para el seguimiento del orientador.

> **Estado actual:** MVP avanzado y funcional, desplegado en Render, con proveedores de IA configurables, dashboard privado, generación de informes y exportación PDF.

## 🌐 Demo

**Aplicación desplegada:**  
https://orientador-vocacional-ia.onrender.com

---

## 📌 Funcionalidades implementadas

### 👨‍🎓 Experiencia del estudiante

- Registro con nombre, curso, colegio y consentimiento de uso orientativo.
- Selección de una ruta vocacional antes de iniciar el chat.
- Chat vocacional con historial persistente.
- Código de acceso único para **retomar una orientación** posteriormente.
- Visualización del proveedor de IA activo.
- Preguntas de seguimiento para profundizar intereses, habilidades y preocupaciones.
- Bloqueo de nuevos mensajes cuando una conversación ha sido finalizada.
- Interfaz responsive para computador y dispositivos móviles.

### 🤖 Inteligencia artificial configurable

El proveedor se selecciona mediante la variable `AI_MODE`:

- `local`: respuestas controladas sin consumir una API externa.
- `openai`: integración con OpenAI mediante Responses API.
- `gemini`: integración con Google Gemini.
- `groq`: integración con modelos disponibles en GroqCloud.

La configuración permite cambiar de proveedor sin modificar el flujo principal del chat.

### 🛡️ Seguridad y control de respuestas

- Prompt vocacional centralizado y compartido entre proveedores.
- Control de alcance para evitar que el chat responda consultas externas al objetivo de la plataforma.
- Respuestas locales seguras para información sensible o cambiante.
- Validación especial para:
  - PAES, NEM, Ranking y ponderaciones.
  - Admisión universitaria.
  - FUAS, gratuidad, becas, créditos y financiamiento.
  - Instituciones específicas y sus carreras.
  - Fuerzas Armadas, de Orden y Seguridad Pública.
- Recomendación de verificar información oficial cuando pueda cambiar.
- Prevención de respuestas duplicadas mediante control del historial enviado al proveedor.
- Manejo de errores, cuotas, rate limits, respuestas vacías y fallos temporales de API.

### 🧑‍🏫 Panel privado del orientador

- Autenticación mediante Laravel Breeze.
- Listado de estudiantes registrados.
- Búsqueda por nombre y filtros por:
  - Curso.
  - Ruta vocacional.
  - Claridad vocacional.
  - Fecha inicial y final.
- Visualización del detalle de cada estudiante.
- Revisión de todas sus conversaciones y mensajes.
- Indicadores generales de uso.
- Visualización del modo de IA activo.

### 📊 Estadísticas del dashboard

- Total de estudiantes registrados.
- Total de conversaciones.
- Conversaciones activas.
- Informes generados.
- Rutas vocacionales más consultadas.
- Distribución de claridad vocacional.
- Cursos con mayor uso.
- Estudiantes con y sin informe.

### 📄 Informes vocacionales

Cada informe se construye a partir de la conversación del estudiante e incluye:

- Datos del estudiante.
- Ruta explorada.
- Intereses y antecedentes mencionados.
- Áreas vocacionales detectadas.
- Dudas principales.
- Nivel de claridad vocacional: bajo, medio o alto.
- Recomendaciones sugeridas.
- Resumen en lenguaje claro para el estudiante.
- Sección técnica para el orientador.
- Fecha, conversación y versión del informe.

### 🗂️ Versionado e historial de informes

Si el estudiante continúa conversando después de haber generado un informe, el orientador puede crear una nueva versión.

- Los informes anteriores se conservan.
- Un informe queda marcado como **actual**.
- Cada versión registra hasta qué mensaje fue considerada.
- El detalle del estudiante muestra el historial de informes.
- Cada versión puede visualizarse y descargarse en PDF.

### 🧾 Exportación PDF

- Generación de PDF con DomPDF.
- Diseño institucional y responsive en la vista web.
- Descarga directa mediante rutas relativas compatibles con HTTPS.
- Identificación de estudiante, conversación, informe y versión.

---

## 🎯 Objetivo del sistema

Apoyar el proceso de orientación vocacional de estudiantes de enseñanza media, especialmente de **3° y 4° medio**, mediante una herramienta que permita:

- Ordenar intereses, habilidades y dudas.
- Explorar carreras profesionales y técnicas.
- Comparar universidad, Instituto Profesional y CFT.
- Comprender rutas de admisión.
- Revisar beneficios estudiantiles y financiamiento.
- Explorar pedagogías.
- Conocer rutas de Fuerzas Armadas, de Orden y Seguridad Pública.
- Entregar información de apoyo al orientador del colegio.

> La plataforma entrega orientación informativa y preliminar. No reemplaza la entrevista con el orientador ni la revisión de fuentes oficiales.

---

## 🧭 Rutas vocacionales disponibles

### 1. Ruta universitaria

Orientación sobre:

- PAES.
- NEM y Ranking.
- Ponderaciones.
- DEMRE y Acceso Educación Superior.
- Carreras universitarias.
- Mallas curriculares.
- Acreditación.
- Campo laboral y continuidad académica.

### 2. Ruta técnico-profesional

Orientación sobre:

- Institutos Profesionales.
- Centros de Formación Técnica.
- Carreras técnicas y profesionales.
- Duración y enfoque práctico.
- Continuidad de estudios.
- Empleabilidad y prácticas.
- Beneficios estudiantiles.

### 3. Beneficios y financiamiento

Orientación sobre:

- FUAS.
- Gratuidad.
- Becas.
- Créditos.
- Requisitos generales.
- Documentación y etapas del proceso.
- Instituciones adscritas.

### 4. Pedagogías

Orientación sobre:

- Vocación docente.
- Requisitos para estudiar pedagogía.
- Alternativas del área educacional.
- Acreditación.
- Campo laboral.

### 5. Fuerzas Armadas, de Orden y Seguridad Pública

Orientación general sobre:

- Ejército.
- Armada.
- Fuerza Aérea de Chile.
- Carabineros.
- Policía de Investigaciones.
- Gendarmería.
- Escuelas de oficiales y especialidades técnicas.
- Etapas de admisión y preparación general.

### 6. Exploración vocacional general

Para estudiantes que todavía no tienen una decisión clara:

- Identificación de intereses.
- Preferencias de trabajo.
- Habilidades percibidas.
- Dificultades académicas.
- Comparación inicial de áreas y rutas formativas.

---

## 🧰 Tecnologías utilizadas

| Tecnología | Uso |
|---|---|
| **PHP 8.2** | Backend |
| **Laravel 12.x** | Framework principal |
| **Blade** | Vistas del servidor |
| **Tailwind CSS** | Interfaz responsive |
| **Alpine.js** | Interacciones ligeras de interfaz |
| **Vite** | Compilación de recursos frontend |
| **Laravel Breeze** | Autenticación del orientador |
| **DomPDF** | Exportación de informes PDF |
| **MySQL / MariaDB** | Base de datos en desarrollo local |
| **PostgreSQL** | Base de datos en producción |
| **OpenAI API** | Proveedor de IA mediante Responses API |
| **Google Gemini API** | Proveedor alternativo de IA |
| **Groq API** | Proveedor alternativo de IA |
| **Docker + Apache** | Contenedor de producción |
| **Render** | Hosting de aplicación y PostgreSQL |
| **Git / GitHub** | Control de versiones |

---

## 🧠 Arquitectura de IA

### Servicios principales

```text
app/Services/
├── AiVocationalService.php
├── OpenAiVocationalService.php
├── GeminiVocationalService.php
├── GroqVocationalService.php
├── VocationalSystemPromptService.php
├── SafeVocationalResponseService.php
├── VocationalScopeGuardService.php
└── ReportGeneratorService.php
```

### Flujo de procesamiento del mensaje

```text
1. Validar y guardar el mensaje del estudiante
2. Verificar si la consulta está fuera del alcance vocacional
3. Aplicar respuestas seguras para información sensible o cambiante
4. Seleccionar proveedor según AI_MODE
5. Enviar historial reciente y prompt compartido al proveedor
6. Guardar la respuesta del asistente
7. Mostrar la conversación actualizada
```

### Prompt compartido

`VocationalSystemPromptService` concentra las reglas comunes para que OpenAI, Gemini y Groq mantengan un comportamiento coherente:

- Español chileno neutro.
- Respuestas claras y breves.
- Seguimiento contextual.
- Máximo de alternativas y preguntas de seguimiento.
- Prohibición de inventar requisitos, fechas, puntajes, beneficios o carreras.
- Redirección de consultas externas al objetivo de la aplicación.

### Respuestas seguras

`SafeVocationalResponseService` intercepta consultas en las que no es conveniente depender exclusivamente del modelo externo, como:

- Requisitos de admisión.
- Beneficios estudiantiles.
- Instituciones específicas.
- Comparaciones de carreras.
- Fuerzas Armadas y de Orden.

### Control de alcance

`VocationalScopeGuardService` evita que el chat sea utilizado como asistente general para temas ajenos a la orientación vocacional.

---

## 🤖 Configuración de proveedores

Archivo principal:

```text
config/ai.php
```

### Modo local

```env
AI_MODE=local
```

No requiere API externa.

### OpenAI

```env
AI_MODE=openai
OPENAI_API_KEY=tu_api_key
OPENAI_MODEL=gpt-5-mini
```

La integración utiliza:

```text
https://api.openai.com/v1/responses
```

El uso de OpenAI API requiere una cuenta con billing o créditos disponibles.

### Google Gemini

```env
AI_MODE=gemini
GEMINI_API_KEY=tu_api_key
GEMINI_MODEL=gemini-2.5-flash
```

El servicio incluye configuración de tokens, manejo de respuestas truncadas y motivos de finalización.

### Groq

```env
AI_MODE=groq
GROQ_API_KEY=tu_api_key
GROQ_MODEL=llama-3.1-8b-instant
```

El identificador del modelo debe coincidir con un modelo activo en GroqCloud. Puede reemplazarse por otro modelo compatible desde las variables de entorno.

> Nunca publiques claves reales en GitHub. Deben almacenarse únicamente en `.env` local o en las variables de entorno de Render.

---

## 🗄️ Modelo de datos principal

### `students`

- `id`
- `name`
- `course`
- `school`
- `consent_accepted`
- `access_code`
- `created_at`
- `updated_at`

### `conversations`

- `id`
- `student_id`
- `selected_route`
- `status`
- `started_at`
- `finished_at`
- `created_at`
- `updated_at`

### `messages`

- `id`
- `conversation_id`
- `sender`
- `content`
- `created_at`
- `updated_at`

Valores principales de `sender`:

```text
student
ai
```

### `vocational_reports`

- `id`
- `student_id`
- `conversation_id`
- `version`
- `is_current`
- `generated_until_message_id`
- `interests`
- `detected_areas`
- `explored_routes`
- `main_questions`
- `clarity_level`
- `recommendations`
- `student_summary`
- `orientador_notes`
- `created_at`
- `updated_at`

---

## 🔄 Flujo del estudiante

```text
Inicio
→ Registro y consentimiento
→ Selección de ruta
→ Generación de código de acceso
→ Chat vocacional
→ Continuación o finalización de la conversación
→ Retomar posteriormente mediante código
```

## 🔄 Flujo del orientador

```text
Login
→ Dashboard
→ Búsqueda y filtros
→ Detalle del estudiante
→ Revisión de conversaciones
→ Generación de informe
→ Visualización y descarga PDF
→ Generación de nuevas versiones
→ Consulta del historial de informes
```

---

## 🌐 Rutas principales

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/` | Pantalla de inicio |
| `GET` | `/estudiante/inicio` | Registro del estudiante |
| `POST` | `/estudiante` | Crea estudiante y conversación |
| `GET` | `/estudiante/retomar` | Formulario para retomar orientación |
| `POST` | `/estudiante/retomar` | Busca conversación mediante código |
| `GET` | `/chat/{conversation}` | Muestra la conversación |
| `POST` | `/chat/{conversation}/mensaje` | Envía mensaje y genera respuesta |
| `POST` | `/chat/{conversation}/finalizar` | Finaliza la conversación |
| `GET` | `/login` | Acceso del orientador |
| `GET` | `/orientador/dashboard` | Dashboard privado |
| `GET` | `/orientador/estudiantes/{student}` | Detalle del estudiante |
| `POST` | `/orientador/conversaciones/{conversation}/generar-reporte` | Genera una nueva versión del informe |
| `GET` | `/orientador/reportes/{report}` | Vista de un informe |
| `GET` | `/orientador/reportes/{report}/pdf` | Descarga PDF |

---

## ⚙️ Instalación local

### Requisitos

- PHP 8.2 o superior.
- Composer.
- Node.js y NPM.
- MySQL o MariaDB.
- Extensiones PHP requeridas por Laravel y DomPDF.

### 1. Clonar el repositorio

```bash
git clone https://github.com/ricardonicolasv/orientador-vocacional-ia.git
cd orientador-vocacional-ia
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Crear `.env`

Windows:

```powershell
copy .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

### 4. Generar clave de Laravel

```bash
php artisan key:generate
```

### 5. Configurar MySQL local

```env
APP_NAME="Orientador Vocacional IA"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=orientador_vocacional_ia
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

Crear la base de datos:

```sql
CREATE DATABASE orientador_vocacional_ia;
```

### 6. Configurar IA

Ejemplo con OpenAI:

```env
AI_MODE=openai
OPENAI_API_KEY=tu_api_key
OPENAI_MODEL=gpt-5-mini
```

### 7. Ejecutar migraciones

```bash
php artisan migrate
```

### 8. Compilar frontend

Desarrollo:

```bash
npm run dev
```

Producción local:

```bash
npm run build
```

### 9. Levantar Laravel

```bash
php artisan serve
```

Aplicación local:

```text
http://127.0.0.1:8000
```

---

## 🚀 Despliegue en Render

El repositorio incluye:

- `Dockerfile`
- `.dockerignore`
- `start.sh`
- Configuración de Apache para servir `/public`
- Compilación de Vite durante el build
- Ejecución automática de migraciones
- Seeder para crear o actualizar el usuario orientador de producción
- Caché de configuración, rutas y vistas

### Variables principales de producción

```env
APP_NAME="Orientador Vocacional IA"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://orientador-vocacional-ia.onrender.com
ASSET_URL=https://orientador-vocacional-ia.onrender.com

DB_CONNECTION=pgsql
DB_URL=postgresql://...

SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=database

AI_MODE=openai
OPENAI_API_KEY=tu_api_key
OPENAI_MODEL=gpt-5-mini

ADMIN_NAME=Orientador
ADMIN_EMAIL=orientador@test.cl
ADMIN_PASSWORD=una_clave_segura
```

> En Render debe utilizarse la URL interna de PostgreSQL. El archivo `.env` local no debe reemplazarse por la configuración de producción.

---

## 🗂️ Estructura principal

```text
app/
├── Http/Controllers/
│   ├── StudentController.php
│   ├── ChatController.php
│   ├── ReportController.php
│   └── OrientadorDashboardController.php
├── Models/
│   ├── Student.php
│   ├── Conversation.php
│   ├── Message.php
│   ├── VocationalReport.php
│   ├── VocationalRoute.php
│   └── User.php
└── Services/
    ├── AiVocationalService.php
    ├── OpenAiVocationalService.php
    ├── GeminiVocationalService.php
    ├── GroqVocationalService.php
    ├── VocationalSystemPromptService.php
    ├── SafeVocationalResponseService.php
    ├── VocationalScopeGuardService.php
    └── ReportGeneratorService.php

resources/views/
├── welcome.blade.php
├── student/
│   ├── create.blade.php
│   └── resume.blade.php
├── chat/
│   └── show.blade.php
├── orientador/
│   ├── dashboard.blade.php
│   └── student-show.blade.php
└── reports/
    ├── show.blade.php
    └── pdf.blade.php
```

---

## 🔐 Seguridad y privacidad

- Las API keys y contraseñas se administran con variables de entorno.
- `.env` no debe subirse al repositorio.
- El panel del orientador está protegido mediante autenticación.
- La aplicación no solicita RUT, dirección exacta, información médica ni datos familiares delicados.
- Las consultas externas al objetivo vocacional se bloquean o redirigen.
- La información institucional cambiante debe verificarse en fuentes oficiales.
- Los informes tienen carácter orientativo y preliminar.

Archivos y directorios que no deben publicarse:

```text
.env
/vendor
/node_modules
/public/hot
/storage/logs/*
```

---

## 📚 Fuentes oficiales consideradas

La plataforma está preparada para orientar al estudiante hacia fuentes como:

- DEMRE.
- Acceso Educación Superior – Mineduc.
- Mi Futuro – Mineduc.
- Comisión Nacional de Acreditación.
- FUAS y Beneficios Estudiantiles Mineduc.
- ChileAtiende.
- Elige Educar.
- Quiero Ser Profe.
- Sitios oficiales de universidades, IP y CFT.
- Escuela Militar.
- Armada de Chile y Escuela Naval.
- Fuerza Aérea de Chile.
- Carabineros de Chile.
- Policía de Investigaciones.
- Gendarmería de Chile.

La aplicación no realiza todavía una búsqueda web automática en tiempo real. Las fechas, requisitos, carreras, sedes, aranceles, ponderaciones y beneficios deben confirmarse directamente en las fuentes oficiales.

---

## 🧪 Comandos útiles

| Acción | Comando |
|---|---|
| Limpiar cachés | `php artisan optimize:clear` |
| Limpiar configuración | `php artisan config:clear` |
| Limpiar vistas | `php artisan view:clear` |
| Ver rutas | `php artisan route:list` |
| Ejecutar migraciones | `php artisan migrate` |
| Reiniciar DB local | `php artisan migrate:fresh` |
| Ejecutar servidor | `php artisan serve` |
| Compilar frontend | `npm run dev` |
| Build frontend | `npm run build` |

---

## 🚧 Próximas mejoras

- Mejorar el contenido profesional de los informes.
- Agregar resumen ejecutivo, fortalezas, preocupaciones y próximos pasos.
- Incorporar preguntas sugeridas para la entrevista del orientador.
- Integrar una base documental con fuentes oficiales actualizables.
- Conectar fuentes externas mediante búsqueda autorizada o RAG.
- Incorporar perfil académico con notas, NEM, Ranking y PAES en una etapa posterior.
- Agregar pruebas automatizadas para servicios, reportes y control de alcance.
- Incorporar roles y gestión avanzada de usuarios.
- Exportar estadísticas y reportes generales por curso.
- Mejorar observabilidad, monitoreo y métricas de consumo de IA.

---

## ⚠️ Consideraciones importantes

- La orientación generada no define una decisión final.
- El sistema no reemplaza el acompañamiento profesional del orientador.
- Las respuestas de IA pueden requerir revisión humana.
- Los datos oficiales pueden cambiar entre procesos de admisión.
- El uso de APIs externas puede generar costos, límites de cuota o indisponibilidad temporal.

---

## 👨‍💻 Autor

**Ricardo Vidal**  
Ingeniero en Informática  
GitHub: [@ricardonicolasv](https://github.com/ricardonicolasv)

---

## 📄 Licencia

Proyecto desarrollado con fines académicos, de prototipado y validación de una solución de orientación vocacional escolar.

Laravel se distribuye bajo licencia MIT.
