# Handoff para Claude Code - MedForge patient-v1

## Objetivo

Revisar tecnicamente el contrato `patient-v1` y, solo despues de aprobacion explicita futura, implementar en MedForge soporte de validacion/import dry-run. En esta fase Claude Code debe entregar informe de impacto y no codigo.

## Fuente de contrato

Repositorio OpenEMR/AltaVision:

- `docs/openemr-medforge-migration/contracts/patient-v1.schema.json`
- `docs/openemr-medforge-migration/mappings/openemr-altavision-patient-v1.md`

Fuente de codigo MedForge: `origin/staging`.

## Archivos esperados en MedForge

Claude debe ubicar los equivalentes en `laravel-app` y proponer cambios minimos. Archivos probables:

- `app/Modules/Core/Models/Patient.php`
- `app/Modules/Core/Models/ExternalAlias.php`
- `database/migrations/*core_patients*`
- `database/migrations/*core_external_aliases*`
- nuevo servicio: `app/Modules/Core/Services/PatientContractImportService.php`
- nuevo comando dry-run: `app/Console/Commands/PatientsValidateContract.php`
- tests: `tests/Feature/Core/PatientContractImportServiceTest.php`

## Comportamiento esperado

1. Revisar si el contrato encaja con `control_center_organizations`, `control_center_instances`, `core_patients` y `core_external_aliases`.
2. Confirmar o corregir los campos obligatorios:
   - `organization_id`
   - `instance_id`
   - `source_system`
   - `source_instance`
   - `external_type`
   - `external_value`
3. Validar JSON `patient-v1` contra el schema.
4. Resolver aliases sin escribir por defecto:
   - `openemr_pid`
   - `openemr_pubpid`
   - `openemr_patient_data_id`
   - `hc_number`
5. En modo dry-run, reportar:
   - paciente nuevo candidato;
   - alias ya existente;
   - conflicto alias -> paciente distinto;
   - dato incompleto que requiere revision.
6. En modo write, que debe quedar deshabilitado por default y protegido por flag explicito, crear `core_patients` y `core_external_aliases` solo en ambiente controlado.
7. No tratar `hc_number`, `pid`, `pubpid` ni `form_id` como identidad soberana.

## Restricciones

- No ejecutar import masivo.
- No leer ni escribir datos reales de Altavision desde MedForge en esta tarea.
- No copiar PHI a logs, Notion, tests o fixtures.
- Fixtures deben usar datos sinteticos.
- Todo modo write requiere confirmacion humana previa de Jorge y flag explicito.
- No promover supuestos CIVE/SigCenter a Altavision.
- No iniciar implementacion hasta que `patient-v1` pase de Draft a Accepted o Jorge autorice un spike tecnico separado.
- No usar `provider` como sustituto ambiguo de `source_system/source_instance`.

## Criterios de aceptacion

- Tests unitarios/feature cubren JSON valido, JSON invalido, alias nuevo, alias existente y conflicto de alias.
- El comando dry-run imprime solo conteos, IDs sinteticos/fingerprints y razones de revision.
- `core_external_aliases` se usa con `instance_slug`.
- El contrato permite piloto de 20 pacientes anonimizados sin tocar datos reales.
- Documentacion de comando incluye advertencia de PHI y no-mass-migration.

## Informe de impacto requerido antes de implementar

Claude Code debe responder con:

- Archivos MedForge que se verian afectados.
- Migraciones necesarias o confirmacion de que no son necesarias.
- Riesgos de compatibilidad con CIVE/SigCenter.
- Efecto sobre Control Center y multiinstancia.
- Validacion de reglas de unicidad de alias.
- Estrategia de rollback.
- Pruebas requeridas.
- Preguntas bloqueantes para Jorge.

## Pruebas sugeridas

- `php artisan test --filter=PatientContractImportServiceTest`
- `php artisan patients:validate-contract --file=/tmp/patient-v1-sample.json --dry-run`
- Prueba negativa: archivo con `contract_version` distinto debe fallar.
- Prueba negativa: alias duplicado apuntando a otro `core_patient` debe bloquear write y marcar `requires_review`.
