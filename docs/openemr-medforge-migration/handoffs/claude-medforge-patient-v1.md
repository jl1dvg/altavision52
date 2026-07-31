# Handoff para Claude Code - MedForge patient-v1

## Objetivo

Implementar en MedForge soporte de validacion/import dry-run para el contrato `patient-v1`, sin migracion masiva y sin escribir datos reales por defecto.

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

1. Validar JSON `patient-v1` contra el schema.
2. Resolver aliases sin escribir por defecto:
   - `openemr_pid`
   - `openemr_pubpid`
   - `openemr_patient_data_id`
   - `hc_number`
3. En modo dry-run, reportar:
   - paciente nuevo candidato;
   - alias ya existente;
   - conflicto alias -> paciente distinto;
   - dato incompleto que requiere revision.
4. En modo write, que debe quedar deshabilitado por default y protegido por flag explicito, crear `core_patients` y `core_external_aliases` solo en ambiente controlado.
5. No tratar `hc_number`, `pid`, `pubpid` ni `form_id` como identidad soberana.

## Restricciones

- No ejecutar import masivo.
- No leer ni escribir datos reales de Altavision desde MedForge en esta tarea.
- No copiar PHI a logs, Notion, tests o fixtures.
- Fixtures deben usar datos sinteticos.
- Todo modo write requiere confirmacion humana previa de Jorge y flag explicito.
- No promover supuestos CIVE/SigCenter a Altavision.

## Criterios de aceptacion

- Tests unitarios/feature cubren JSON valido, JSON invalido, alias nuevo, alias existente y conflicto de alias.
- El comando dry-run imprime solo conteos, IDs sinteticos/fingerprints y razones de revision.
- `core_external_aliases` se usa con `instance_slug`.
- El contrato permite piloto de 20 pacientes anonimizados sin tocar datos reales.
- Documentacion de comando incluye advertencia de PHI y no-mass-migration.

## Pruebas sugeridas

- `php artisan test --filter=PatientContractImportServiceTest`
- `php artisan patients:validate-contract --file=/tmp/patient-v1-sample.json --dry-run`
- Prueba negativa: archivo con `contract_version` distinto debe fallar.
- Prueba negativa: alias duplicado apuntando a otro `core_patient` debe bloquear write y marcar `requires_review`.
