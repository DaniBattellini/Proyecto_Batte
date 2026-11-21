<?php
function obtenerMateriasPorCarrera(int $carreraId): array
{
    $materiasSoftware = [
        "Sistemas Operativos",
        "Comunicación",
        "Unidad de Definición Institucional I - Lengua",
        "Matemática",
        "Inglés Técnico I",
        "Administración",
        "Tecnologías de la Información",
        "Lógica y Estructura de Datos",
        "Ingeniería de Software I",
        "Unidad de Definición Institucional II",
        "Problemáticas Socio Contemporáneas",
        "Inglés Técnico II",
        "Innovación y Desarrollo Emprendedor",
        "Estadística",
        "Programación I",
        "Ingeniería de Software II",
        "Base de Datos I",
        "Práctica Profesionalizante I",
        "Derecho y Legislación Laboral",
        "Ética y Responsabilidad Social",
        "Redes y Comunicación",
        "Programación II",
        "Gestión de Proyectos de Software",
        "Base de Datos II",
        "Práctica Profesionalizante II",
    ];

    $materiasGestion = [
        "Sistemas Operativos",
        "Comunicación",
        "Unidad de Definición Institucional I - Lengua",
        "Matemática",
        "Inglés Técnico I",
        "Administración",
        "Tecnologías de la Información",
        "Lógica y Estructura de Datos",
        "Estadística",
        "Unidad de Definición Institucional II",
        "Problemáticas Socio Contemporáneas",
        "Inglés Técnico II",
        "Innovación y Desarrollo Emprendedor",
        "Estrategia Empresarial",
        "Sistema de Información para la Gestión de las Organizaciones",
        "Gestión Financiera",
        "Práctica Profesionalizante I",
        "Ética y Responsabilidad Social",
        "Evaluación y Gestión de Proyectos de Inversión",
        "Control de Gestión",
        "Gestión de Seguridad, Salud Ocupacional y Medio Ambiente",
        "Práctica Profesionalizante II",
    ];

    if ($carreraId === 2) {
        $materias = $materiasSoftware;
    } elseif ($carreraId === 1) {
        $materias = $materiasGestion;
    } else {
        return [];
    }

    return array_combine(range(1, count($materias)), $materias);
}

function obtenerIdCarreraAlumno(string $nombreCarrera): ?int
{
    $carrera = mb_strtolower($nombreCarrera, 'UTF-8');
    if (str_contains($carrera, 'software') || str_contains($carrera, 'soft')) {
        return 2;
    }

    if (str_contains($carrera, 'gesti') || str_contains($carrera, 'organiz')) {
        return 1;
    }

    return null;
}
?>
