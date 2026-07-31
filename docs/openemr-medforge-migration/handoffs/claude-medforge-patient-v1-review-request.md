# Solicitud de revision tecnica para Claude Code - Patient V1

Estado: pendiente. No se autoriza implementacion.

## Pedido

Revisar el contrato `patient-v1` y entregar un informe de impacto. No escribir codigo, no crear migraciones, no tocar datos reales.

## Contexto

- Programa: Alta Vision - Migracion OpenEMR a MedForge.
- Contrato: `docs/openemr-medforge-migration/contracts/patient-v1.schema.json`.
- Mapping: `docs/openemr-medforge-migration/mappings/openemr-altavision-patient-v1.md`.
- ADR propuesta: `docs/openemr-medforge-migration/architecture/adr-patient-identity-v1.md`.
- MedForge source of truth: `origin/staging=08f85d29c9f011d3c139ab56e3a6f0082c7a6d4d`.

## Preguntas a responder

1. Encaja `organization_id + instance_id + source_system + source_instance + external_type + external_value` con el Control Center actual?
2. `core_external_aliases` necesita nuevas columnas o basta con adaptar `provider/external_type/external_id` temporalmente?
3. Que riesgo existe de romper compatibilidad CIVE/SigCenter?
4. Que reglas de unicidad deben vivir en DB y cuales en servicio?
5. Como debe bloquearse un alias duplicado?
6. Como se vinculan pacientes existentes sin merge automatico riesgoso?
7. Que tests son obligatorios antes de cualquier implementacion?
8. Que rollback es seguro si un piloto temporal crea aliases incorrectos?

## Salida esperada

Informe de impacto en Markdown con:

- Veredicto: aprobar contrato, aprobar con cambios, o bloquear.
- Archivos afectados.
- Migraciones propuestas, si aplica.
- Riesgos.
- Criterios de aceptacion.
- Pruebas.
- Bloqueantes para Jorge.
