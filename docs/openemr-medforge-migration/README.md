# Alta Vision - Migracion OpenEMR a MedForge

Estado: auditoria inicial, sin migracion masiva.
Owner humano para decisiones clinicas y datos reales: Jorge Luis De Vera.
Agente responsable OpenEMR/AltaVision: Codex.
Agente responsable implementaciones MedForge: Claude Code.

## Alcance

Este directorio versiona contratos, mappings y handoffs del programa de migracion Alta Vision - OpenEMR a MedForge. La fuente de codigo activa para MedForge es `origin/staging`; la fuente local de OpenEMR/AltaVision es este repositorio `altavision52`.

## Fuentes verificadas

- OpenEMR/AltaVision repo: `/Users/jorgeluisdevera/PhpstormProjects/altavision52`, rama `master`, worktree con cambios previos no modificados por esta auditoria.
- MedForge repo: `/Users/jorgeluisdevera/PhpstormProjects/MedForge`, `origin/staging=08f85d29c9f011d3c139ab56e3a6f0082c7a6d4d`.
- Notion Knowledge OS: Protocolo Codex / Claude Code y Operational Core - Inventario Maestro.
- Base de datos viva OpenEMR: no verificada; no habia configuracion DB usable en `.env` y `sites/default/config.php` no inicializa credenciales por si solo. `requires verification`.

## Roadmap de ejecucion

1. Dominio Pacientes: cerrar contrato `patient-v1`, identidad clinica MedForge y piloto 20 pacientes anonimizados.
2. Dominio Encuentros/Consultas: mapear `forms.form_id`, `form_eye_base`, `consulta_data` y `core_episode_id`.
3. Dominio Procedimientos proyectados: mapear `procedimiento_proyectado`, sedes, provider y estados legacy.
4. Dominio Protocolos/Cirugias: mapear `protocolo_data`, formularios quirurgicos, insumos, diagnosticos y firmantes.
5. Dominio Facturacion: mapear dependencias por `hc_number` + `form_id`, derivaciones, afiliacion y reglas financieras.
6. Pilotos incrementales anonimizados: 20 pacientes, luego 100, luego lote controlado con aprobacion explicita de Jorge.

## Reglas de seguridad

- No copiar PHI a Notion ni a contratos.
- No ejecutar migracion masiva desde este programa sin aprobacion de Jorge.
- No modificar datos reales desde tareas de auditoria.
- Toda decision clinica, cambio funcional o tratamiento de datos reales debe escalarse a Jorge.
- `hc_number`, `pid`, `pubpid` y `form_id` son aliases externos o llaves legacy; no son identidad soberana de MedForge.

## Artefactos

- `contracts/patient-v1.schema.json`: contrato tecnico del dominio Pacientes.
- `mappings/openemr-altavision-patient-v1.md`: auditoria y matriz de correspondencia inicial.
- `handoffs/claude-medforge-patient-v1.md`: especificacion autocontenida para Claude Code.
