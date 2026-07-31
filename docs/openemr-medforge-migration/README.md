# Alta Vision - Migracion OpenEMR a MedForge

Estado: Fase 1.1, `patient-v1` en Draft, sin migracion masiva.
Owner humano para decisiones clinicas y datos reales: Jorge Luis De Vera.
Agente responsable OpenEMR/AltaVision: Codex.
Agente responsable implementaciones MedForge: Claude Code.

## Alcance

Este directorio versiona contratos, mappings y handoffs del programa de migracion Alta Vision - OpenEMR a MedForge. La fuente de codigo activa para MedForge es `origin/staging`; la fuente local de OpenEMR/AltaVision es este repositorio `altavision52`.

## Fuentes verificadas

- OpenEMR/AltaVision repo: `/Users/jorgeluisdevera/PhpstormProjects/altavision52`, base `master=16d2ee8939058b87ac49985c34648ace515362e2`; rama documental `codex/altavision-patient-v1-contract`.
- Commit documental inicial: `e7b52bd42c00197be5762e11a59c6cd305f1512c`.
- PR documental: `https://github.com/jl1dvg/altavision52/pull/55`.
- MedForge repo: `/Users/jorgeluisdevera/PhpstormProjects/MedForge`, `origin/staging=08f85d29c9f011d3c139ab56e3a6f0082c7a6d4d`.
- Notion Knowledge OS: Protocolo Codex / Claude Code y Operational Core - Inventario Maestro.
- Base de datos viva OpenEMR: no verificada; `sites/default/sqlconf.php` existe y es el origen de credenciales de OpenEMR, pero la conexion local fallo con `DB_ERROR_CODE=2002`. `requires verification`.

## Estado del contrato

`patient-v1` permanece **Draft**. No puede pasar a `Accepted` hasta que Jorge provea uno de estos artefactos:

1. Dump estructural sin datos de la base Alta Vision OpenEMR.
2. Acceso read-only a la base real.
3. Salida agregada de los comandos SQL read-only documentados en `mappings/openemr-altavision-patient-v1.md`.

No se aceptan fixtures con datos reales ni muestras con PII.

## Roadmap de ejecucion

1. Dominio Pacientes: cerrar contrato `patient-v1`, identidad clinica MedForge y piloto 20 pacientes anonimizados. Estado: Draft bloqueado por verificacion DB y revision Claude Code.
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
- `handoffs/claude-medforge-patient-v1-review-request.md`: solicitud de revision tecnica, informe de impacto, sin codigo.
- `architecture/adr-patient-identity-v1.md`: ADR propuesta de identidad clinica nativa.
- `mappings/patient-v1-risk-matrix.md`: matriz de riesgos, aceptacion, rollback y piloto.
