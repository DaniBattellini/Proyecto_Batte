<?php
session_start();

// Solo alumnos
if (!isset($_SESSION["dnialu"])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/periodo_inscripcion.php';
$periodoInscripcionAbierto = obtenerPeriodoInscripcionAbierto($conex);

include('primero.php');
?>
<style>
        :root {
                --accent: #2563eb;
                --accent-dark: #1e40af;
                --success: #10b981;
                --card: #ffffff;
                --bg: #f3f6fb;
                --muted: #6b7280;
        }

        .page-wrapper {
                background: var(--bg);
                min-height: calc(100vh - 160px);
        }

        .container-soft {
                max-width: 980px;
                margin: 20px auto;
                padding: 18px;
                border-radius: 12px;
                background: var(--card);
                box-shadow: 0 6px 20px rgba(2, 6, 23, 0.05)
        }

        form .row {
                display: flex;
                gap: 16px;
                align-items: center;
                flex-wrap: wrap
        }

        label.input-label {
                display: block;
                font-weight: 600;
                margin-bottom: 6px
        }

        input[type=email].correo-input {
                width: 100%;
                padding: 10px;
                border-radius: 8px;
                border: 1px solid #e6e9ef;
                font-size: 0.98rem
        }

        h2.section-title,
        .anio-container h3 {
                margin: 18px 0 12px;
                padding: 8px;
                border-radius: 8px;
                background: #eef2ff;
                color: var(--accent-dark);
                font-size: 1rem;
                text-align: left;
        }

        .materia-box {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
                border-radius: 8px;
                background: #fbfdff;
                border: 1px solid #eef2f7;
                margin-bottom: 10px;
                gap: 12px
        }

        .materia-box.ya-inscripto {
                background: #f0fdf4;
                border-color: #86efac;
        }

        .badge-prev {
                font-size: 0.75rem;
                background: #dcfce7;
                color: #166534;
                padding: 2px 8px;
                border-radius: 20px;
                font-weight: 700;
                margin-left: 8px;
                white-space: nowrap;
        }

        .materia-label {
                flex: 1;
                font-weight: 600;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 4px;
        }

        .opciones-cursado {
                display: flex;
                gap: 8px;
                align-items: center;
                flex-wrap: wrap
        }

        .opciones-cursado label {
                margin-right: 10px;
                display: inline-flex;
                align-items: center;
                cursor: pointer;
        }

        .opciones-cursado select {
                padding: 8px;
                border-radius: 6px;
                border: 1px solid #e6e9ef;
                font-size: 0.95rem
        }

        .botones-soft {
                display: flex;
                gap: 12px;
                justify-content: center;
                margin-top: 18px;
                flex-wrap: wrap
        }

        .btn-soft {
                padding: 10px 18px;
                border-radius: 8px;
                border: none;
                font-weight: 600;
                cursor: pointer;
                font-size: 0.95rem;
                text-decoration: none;
                display: inline-block;
        }

        .btn-soft-primary  { background: var(--accent); color: #fff }
        .btn-soft-secondary{ background: #6b7280; color: #fff }
        .btn-soft-success  { background: var(--success); color: #fff }
        .btn-soft-pdf-prev { background: #f59e0b; color: #fff }

        button:disabled {
                opacity: 0.6;
                cursor: not-allowed;
        }

        #mensajeExito {
                display: none;
                margin-top: 14px;
                padding: 12px;
                border-radius: 8px;
                background: #ecfdf5;
                color: #065f46;
                text-align: center;
                font-weight: 600
        }

        #bannerPrevio {
                display: none;
                background: #eff6ff;
                border: 1px solid #93c5fd;
                border-radius: 8px;
                padding: 12px 16px;
                margin-bottom: 16px;
                color: #1e40af;
                font-weight: 600;
        }

        @media (max-width:720px) {
                .materia-box { flex-direction: column; align-items: flex-start }
                .opciones-cursado { width: 100% }
                .botones-soft { flex-direction: column }
        }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<?php include('header_pagina.php'); ?>

<div class="page-wrapper">
        <main class="container-soft">

                <?php if ($periodoInscripcionAbierto): ?>
                        <div class="alert alert-success" role="status">
                                Período de inscripción abierto hasta <?= date('d/m/Y', strtotime($periodoInscripcionAbierto['fecha_fin'])) ?>.
                        </div>
                <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                                El período de inscripción está cerrado. No se pueden guardar inscripciones.
                        </div>
                <?php endif; ?>

                <!-- Banner inscripciones previas -->
                <div id="bannerPrevio">
                        ✅ Ya tenés materias inscriptas de una sesión anterior. Están pre-marcadas abajo.
                        <button type="button" id="btnRedescargarPDF" class="btn-soft btn-soft-pdf-prev" style="margin-left:12px;">
                                📄 Re-descargar último PDF
                        </button>
                </div>

                <form id="formInscripcion" novalidate>

                <label for="correo" style="font-weight:bold; margin-top: 20px;">Correo electrónico <span style="font-weight:normal;color:#6b7280;">(opcional, para el PDF)</span>:</label><br>
                <input type="email" name="correo" id="correo" class="correo-input" placeholder="Tu correo electrónico"
                        style="margin-bottom:20px;">

                        <!-- PRIMER AÑO -->
                        <div class="anio-container primer-anio">
                                <h3>PRIMER AÑO</h3>

                                <div class="materia-box" data-materia="Sistemas Operativos">
                                        <span class="materia-label">Sistemas Operativos</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia1" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia1" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia1" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia1" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Comunicación">
                                        <span class="materia-label">Comunicación</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia2" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia2" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia2" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia2" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Unidad de Definición Institucional I - Lengua">
                                        <span class="materia-label">UDI I - Lengua</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia3" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia3" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia3" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia3" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Matemática">
                                        <span class="materia-label">Matemática</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia4" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia4" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia4" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia4" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Inglés Técnico I">
                                        <span class="materia-label">Inglés Técnico I</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia5" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia5" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia5" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia5" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Administración">
                                        <span class="materia-label">Administración</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia6" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia6" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia6" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia6" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Tecnologías de la Información">
                                        <span class="materia-label">Tecnologías de la Información</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia7" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia7" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia7" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia7" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Lógica y Estructura de Datos">
                                        <span class="materia-label">Lógica y Estructura de Datos</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia8" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia8" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia8" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia8" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Ingeniería de Software I">
                                        <span class="materia-label">Ingeniería de Software I</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia9" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia9" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia9" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia9" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>
                        </div>

                        <!-- SEGUNDO AÑO -->
                        <div class="anio-container segundo-anio">
                                <h3>SEGUNDO AÑO</h3>

                                <div class="materia-box" data-materia="Unidad de Definición Institucional II">
                                        <span class="materia-label">UDI II</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia10" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia10" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia10" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia10" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Problemáticas Socio Contemporáneas">
                                        <span class="materia-label">Problemáticas Socio Contemporáneas</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia11" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia11" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia11" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia11" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Inglés Técnico II">
                                        <span class="materia-label">Inglés Técnico II</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia12" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia12" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia12" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia12" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Innovación y Desarrollo Emprendedor">
                                        <span class="materia-label">Innovación y Desarrollo Emprendedor</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia13" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia13" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia13" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia13" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Estadística">
                                        <span class="materia-label">Estadística</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia14" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia14" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia14" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia14" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Programación I">
                                        <span class="materia-label">Programación I</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia15" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia15" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia15" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia15" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Ingeniería de Software II">
                                        <span class="materia-label">Ingeniería de Software II</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia16" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia16" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia16" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia16" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Base de Datos I">
                                        <span class="materia-label">Base de Datos I</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia17" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia17" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia17" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia17" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Práctica Profesionalizante I">
                                        <span class="materia-label">Práctica Profesionalizante I</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia18" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia18" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia18" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia18" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>
                        </div>

                        <!-- TERCER AÑO -->
                        <div class="anio-container tercer-anio">
                                <h3>TERCER AÑO</h3>

                                <div class="materia-box" data-materia="Derecho y Legislación Laboral">
                                        <span class="materia-label">Derecho y Legislación Laboral</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia19" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia19" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia19" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia19" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Ética y Responsabilidad Social">
                                        <span class="materia-label">Ética y Responsabilidad Social</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia20" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia20" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia20" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia20" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Redes y Comunicación">
                                        <span class="materia-label">Redes y Comunicación</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia21" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia21" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia21" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia21" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Programación II">
                                        <span class="materia-label">Programación II</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia22" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia22" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia22" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia22" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Gestión de Proyectos de Software">
                                        <span class="materia-label">Gestión de Proyectos de Software</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia23" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia23" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia23" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia23" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Práctica Profesionalizante II">
                                        <span class="materia-label">Práctica Profesionalizante II</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia24" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia24" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia24" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia24" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>

                                <div class="materia-box" data-materia="Base de Datos II">
                                        <span class="materia-label">Base de Datos II</span>
                                        <span class="opciones-cursado">
                                                <label><input type="radio" name="materia25" value="presencial"> Presencial</label>
                                                <label><input type="radio" name="materia25" value="semipresencial"> Semi Presencial</label>
                                                <label><input type="radio" name="materia25" value="libre"> Libre</label>
                                                <label><input type="radio" name="materia25" value="no" checked> No me inscribo</label>
                                        </span>
                                </div>
                        </div>

                        <!-- Botones -->
                        <div class="botones-soft">
                                <button type="submit" id="btnGuardar" class="btn-soft btn-soft-primary" <?= $periodoInscripcionAbierto ? '' : 'disabled' ?>>Guardar Inscripción</button>
                                <button type="button" id="btnDescargarPDF" class="btn-soft btn-soft-secondary" disabled>📄 Descargar PDF</button>
                                <button type="button" id="btnInicio" class="btn-soft btn-soft-success">Volver a Inicio Alumno</button>
                        </div>
                </form>

                <div id="mensajeExito"></div>
        </main>
</div>

<script>
// ─── Función para generar y descargar el PDF ───────────────────────────────
function generarPDF(emailOverride) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFont("helvetica", "normal");

        doc.setFontSize(18);
        doc.text("Comprobante de Inscripción", 105, 20, { align: "center" });

        doc.setFontSize(14);
        doc.text("Tecnicatura Superior en Desarrollo de Software", 105, 30, { align: "center" });

        const email = emailOverride || document.getElementById('correo').value;
        const fecha = new Date().toLocaleDateString('es-AR');

        doc.setFontSize(12);
        doc.text("Alumno: " + email, 20, 50);
        doc.text("Fecha: " + fecha, 20, 60);
        doc.line(20, 65, 190, 65);

        doc.setFontSize(14);
        doc.text("Materias Inscriptas:", 20, 80);

        doc.setFontSize(11);
        let y = 90;
        let total = 0;

        const anioContainers = document.querySelectorAll('.anio-container');
        anioContainers.forEach(container => {
                const tituloAnio = container.querySelector('h3').innerText.toUpperCase();
                const materiaBoxes = container.querySelectorAll('.materia-box');
                let materiasAnio = [];

                materiaBoxes.forEach(box => {
                        const label = box.querySelector('.materia-label').childNodes[0].textContent.trim();
                        const inputs = box.querySelectorAll('input[type="radio"]');
                        let seleccion = null;
                        inputs.forEach(input => {
                                if (input.checked && input.value !== "no") {
                                        seleccion = input.value;
                                }
                        });
                        if (seleccion) {
                                materiasAnio.push({
                                        label: label,
                                        condicion: seleccion.charAt(0).toUpperCase() + seleccion.slice(1)
                                });
                        }
                });

                if (materiasAnio.length > 0) {
                        if (y > 270) { doc.addPage(); y = 20; }
                        doc.setFontSize(12);
                        doc.setFont("helvetica", "bold");
                        doc.text(tituloAnio, 20, y);
                        y += 7;
                        doc.setFont("helvetica", "normal");
                        doc.setFontSize(11);
                        materiasAnio.forEach(m => {
                                if (y > 280) { doc.addPage(); y = 20; }
                                doc.text("• " + m.label + ": " + m.condicion, 25, y);
                                y += 7;
                        });
                        y += 5;
                        total += materiasAnio.length;
                }
        });

        if (total === 0) {
                doc.text("No se seleccionaron materias para inscripción.", 25, y);
        }

        doc.setFontSize(10);
        doc.text("Documento generado automáticamente.", 105, 290, { align: "center" });
        doc.save("inscripcion_soft_" + email + ".pdf");
}

document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formInscripcion');
        const btnGuardar = document.getElementById('btnGuardar');
        const btnDescargarPDF = document.getElementById('btnDescargarPDF');
        const btnInicio = document.getElementById('btnInicio');
        const bannerPrevio = document.getElementById('bannerPrevio');
        const btnRedescargar = document.getElementById('btnRedescargarPDF');
        const msgDiv = document.getElementById('mensajeExito');

        function showMsg(text, ok = true) {
                msgDiv.textContent = text;
                msgDiv.style.background = ok ? '#ecfdf5' : '#fee2e2';
                msgDiv.style.color     = ok ? '#065f46' : '#991b1b';
                msgDiv.style.display   = 'block';
                setTimeout(() => msgDiv.style.display = 'none', 5000);
        }

        // ── 1. Cargar inscripciones previas al iniciar ───────────────────────────
        fetch('get_inscripciones_alumno.php')
                .then(r => r.json())
                .then(data => {
                        if (!data.success || data.inscripciones.length === 0) return;

                        // Construir mapa: nombreMateria -> condicion
                        const mapa = {};
                        data.inscripciones.forEach(i => {
                                mapa[i.materia.trim().toLowerCase()] = i.condicion_cursado.toLowerCase();
                        });

                        let hayPrevias = false;

                        // Recorrer cada materia-box y pre-marcar
                        document.querySelectorAll('.materia-box').forEach(box => {
                                const nombreBD = (box.dataset.materia || '').trim().toLowerCase();
                                if (!nombreBD || !mapa[nombreBD]) return;

                                const condicion = mapa[nombreBD];
                                const radios = box.querySelectorAll('input[type="radio"]');
                                radios.forEach(r => {
                                        if (r.value === condicion) {
                                                r.checked = true;
                                        }
                                });

                                // Marcar visualmente la box
                                box.classList.add('ya-inscripto');
                                const labelSpan = box.querySelector('.materia-label');
                                if (!labelSpan.querySelector('.badge-prev')) {
                                        const badge = document.createElement('span');
                                        badge.className = 'badge-prev';
                                        badge.textContent = '✔ Ya inscripto';
                                        labelSpan.appendChild(badge);
                                }

                                hayPrevias = true;
                        });

                        if (hayPrevias) {
                                bannerPrevio.style.display = 'block';
                                // Habilitar descarga PDF desde el inicio
                                btnDescargarPDF.disabled = false;
                        }
                })
                .catch(err => console.warn('No se pudieron cargar inscripciones previas:', err));

        // Volver al inicio
        btnInicio.addEventListener('click', () => {
                window.location.href = 'inicio_alumno.php';
        });

        // Re-descargar PDF con datos actuales (marcados de BD)
        btnRedescargar.addEventListener('click', () => {
                generarPDF(null);
        });

        // ── 2. Guardar nueva inscripción ─────────────────────────────────────────
        form.addEventListener('submit', function (e) {
                e.preventDefault();

                btnGuardar.disabled = true;
                btnGuardar.textContent = "Guardando...";

                const formData = new FormData(form);

                let algunaMateria = false;
                for (let i = 1; i <= 25; i++) {
                        const valor = formData.get(`materia${i}`);
                        if (valor && valor !== "no") {
                                algunaMateria = true;
                                break;
                        }
                }

                if (!algunaMateria) {
                        alert("Debes seleccionar al menos una materia.");
                        btnGuardar.disabled = false;
                        btnGuardar.textContent = "Guardar Inscripción";
                        return;
                }

                fetch("guardar_inscripcionesM.php", {
                        method: "POST",
                        body: formData
                })
                .then(response => response.text())
                .then(texto => {
                        let data;
                        try { data = JSON.parse(texto); }
                        catch (e) {
                                alert("El servidor envió una respuesta inválida.");
                                btnGuardar.disabled = false;
                                btnGuardar.textContent = "Guardar Inscripción";
                                return;
                        }

                        if (data.success) {
                                btnDescargarPDF.disabled = false;
                                showMsg("Inscripción guardada correctamente.");
                        } else {
                                showMsg(data.message || "Error al guardar inscripción.", false);
                        }

                        btnGuardar.disabled = false;
                        btnGuardar.textContent = "Guardar Inscripción";
                })
                .catch(error => {
                        console.error("Error:", error);
                        alert("No se pudo conectar al servidor.");
                        btnGuardar.disabled = false;
                        btnGuardar.textContent = "Guardar Inscripción";
                });
        });

        // ── 3. Descargar PDF ─────────────────────────────────────────────────────
        btnDescargarPDF.addEventListener('click', () => {
                generarPDF(null);
        });
});
</script>

<?php include('footer.php'); ?>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
